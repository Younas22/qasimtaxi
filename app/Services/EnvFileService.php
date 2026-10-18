<?php

namespace App\Services;

use RuntimeException;

/**
 * Safely edits single KEY=value lines in the project's real `.env` file —
 * used only where a value (like a third-party API key) must survive across
 * every request/worker rather than just the current one, which a runtime
 * `config([...])` override can't guarantee. Deliberately narrow: it only
 * ever touches the one line it's asked to change, never rewrites the file
 * wholesale, and always keeps a timestamped backup first so a bad write can
 * be undone by hand even if something goes wrong.
 */
class EnvFileService
{
    public function path(): string
    {
        return base_path('.env');
    }

    public function isWritable(): bool
    {
        return is_file($this->path()) && is_writable($this->path());
    }

    public function get(string $key): ?string
    {
        if (! is_file($this->path())) {
            return null;
        }

        foreach (file($this->path(), FILE_IGNORE_NEW_LINES) as $line) {
            if (preg_match('/^'.preg_quote($key, '/').'=(.*)$/', $line, $matches)) {
                return $this->unquote($matches[1]);
            }
        }

        return null;
    }

    /**
     * Updates the line for $key (or appends one if it isn't present yet),
     * leaving every other line untouched. Writes to a temp file and renames
     * it into place, so a crash mid-write can never leave `.env` half
     * written.
     */
    public function set(string $key, string $value): void
    {
        $path = $this->path();

        if (! is_file($path)) {
            throw new RuntimeException('.env file not found.');
        }

        if (! is_writable($path)) {
            throw new RuntimeException('.env file is not writable by the web server user.');
        }

        $this->backup($path);

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $pattern = '/^'.preg_quote($key, '/').'=/';
        $newLine = $key.'='.$this->quoteIfNeeded($value);
        $found = false;

        foreach ($lines as &$line) {
            if (preg_match($pattern, $line)) {
                $line = $newLine;
                $found = true;
                break;
            }
        }
        unset($line);

        if (! $found) {
            $lines[] = $newLine;
        }

        $tempPath = $path.'.tmp-'.uniqid();

        // Always join with a plain "\n", never PHP_EOL — this file's
        // existing lines came from file() with FILE_IGNORE_NEW_LINES, so
        // whatever line ending they originally had is already stripped;
        // using PHP_EOL here would silently rewrite every line's ending to
        // "\r\n" on Windows even though only one line's content changed.
        if (file_put_contents($tempPath, implode("\n", $lines)."\n") === false) {
            throw new RuntimeException('Failed to write a temporary .env file.');
        }

        if (! rename($tempPath, $path)) {
            @unlink($tempPath);

            throw new RuntimeException('Failed to replace .env with the updated version.');
        }
    }

    /**
     * Backups live under storage/app — private and non-web-accessible,
     * matching how database backups are stored, since `.env` holds every
     * other secret in the app too (DB credentials, APP_KEY, mail keys).
     */
    private function backup(string $path): void
    {
        $directory = storage_path('app/env-backups');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        copy($path, $directory.DIRECTORY_SEPARATOR.'.env-'.now()->format('Y-m-d-His').'.bak');
    }

    private function quoteIfNeeded(string $value): string
    {
        if ($value === '' || preg_match('/[\s#"\'\\\\]/', $value)) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        }

        return $value;
    }

    private function unquote(string $value): string
    {
        if (strlen($value) >= 2 && $value[0] === '"' && str_ends_with($value, '"')) {
            return str_replace(['\\"', '\\\\'], ['"', '\\'], substr($value, 1, -1));
        }

        return trim($value, "'");
    }
}
