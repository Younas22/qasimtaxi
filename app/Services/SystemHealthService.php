<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemHealthService
{
    /**
     * @return array<int, array{label: string, status: string, detail: string}>
     */
    public function checks(): array
    {
        $checks = [];

        $checks[] = $this->databaseCheck();
        $checks[] = $this->storageWritableCheck();
        $checks[] = $this->uploadsWritableCheck();
        $checks[] = $this->diskSpaceCheck();
        $checks[] = $this->debugModeCheck();
        $checks[] = $this->pendingMigrationsCheck();

        return $checks;
    }

    /**
     * @return array<string, string>
     */
    public function systemInfo(): array
    {
        return [
            'PHP Version' => PHP_VERSION,
            'Laravel Version' => app()->version(),
            'Environment' => app()->environment(),
            'Server Time' => now()->format('M d, Y H:i:s T'),
            'Timezone' => config('app.timezone'),
        ];
    }

    /**
     * @return array{driver: string, pending: int|null, failed: int|null, recent_failed: array<int, object>}
     */
    public function queueStatus(): array
    {
        $driver = config('queue.default');

        if ($driver !== 'database') {
            return [
                'driver' => $driver,
                'pending' => null,
                'failed' => null,
                'recent_failed' => [],
            ];
        }

        return [
            'driver' => $driver,
            'pending' => DB::table('jobs')->count(),
            'failed' => DB::table('failed_jobs')->count(),
            'recent_failed' => DB::table('failed_jobs')->latest('failed_at')->limit(10)->get()->all(),
        ];
    }

    private function databaseCheck(): array
    {
        try {
            DB::connection()->getPdo();

            return ['label' => 'Database Connection', 'status' => 'ok', 'detail' => 'Connected to '.DB::connection()->getDatabaseName()];
        } catch (\Throwable $e) {
            return ['label' => 'Database Connection', 'status' => 'error', 'detail' => 'Unable to connect: '.$e->getMessage()];
        }
    }

    private function storageWritableCheck(): array
    {
        $writable = is_writable(storage_path());

        return [
            'label' => 'Storage Directory Writable',
            'status' => $writable ? 'ok' : 'error',
            'detail' => $writable ? storage_path() : storage_path().' is not writable',
        ];
    }

    private function uploadsWritableCheck(): array
    {
        $path = public_path('uploads');

        if (! is_dir($path)) {
            @mkdir($path, 0755, true);
        }

        $writable = is_writable($path);

        return [
            'label' => 'Uploads Directory Writable',
            'status' => $writable ? 'ok' : 'error',
            'detail' => $writable ? $path : $path.' is not writable',
        ];
    }

    private function diskSpaceCheck(): array
    {
        $free = @disk_free_space(base_path());
        $total = @disk_total_space(base_path());

        if (! $free || ! $total) {
            return ['label' => 'Disk Space', 'status' => 'warning', 'detail' => 'Unable to determine disk space'];
        }

        $usedPercent = round((($total - $free) / $total) * 100, 1);
        $status = $usedPercent >= 90 ? 'error' : ($usedPercent >= 75 ? 'warning' : 'ok');

        return [
            'label' => 'Disk Space',
            'status' => $status,
            'detail' => $this->formatBytes($free).' free of '.$this->formatBytes($total)." ({$usedPercent}% used)",
        ];
    }

    private function debugModeCheck(): array
    {
        $debug = config('app.debug');
        $isProduction = app()->environment('production');

        return [
            'label' => 'Debug Mode',
            'status' => ($debug && $isProduction) ? 'warning' : 'ok',
            'detail' => $debug
                ? 'Enabled'.($isProduction ? ' — should be disabled in production' : ' (local/dev)')
                : 'Disabled',
        ];
    }

    private function pendingMigrationsCheck(): array
    {
        if (! Schema::hasTable('migrations')) {
            return ['label' => 'Migrations', 'status' => 'warning', 'detail' => 'Migrations table not found'];
        }

        $ran = DB::table('migrations')->pluck('migration')->all();
        $files = collect(glob(database_path('migrations/*.php')))
            ->map(fn ($path) => basename($path, '.php'))
            ->all();

        $pending = array_diff($files, $ran);

        return [
            'label' => 'Pending Migrations',
            'status' => count($pending) > 0 ? 'warning' : 'ok',
            'detail' => count($pending) > 0 ? count($pending).' migration(s) not yet run' : 'Up to date',
        ];
    }

    private function formatBytes(float $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
