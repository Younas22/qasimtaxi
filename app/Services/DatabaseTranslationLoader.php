<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Facades\Cache;

/**
 * Serves translation lines from the database instead of lang files.
 *
 * Only the JSON-style "*" group is DB-backed — this is what Laravel uses
 * for whole-phrase keys, e.g. __('Dashboard'). Every other group (validation,
 * auth, passwords, pagination, ...) is delegated to the wrapped file loader
 * so framework/vendor translations keep working untouched.
 */
class DatabaseTranslationLoader implements Loader
{
    public function __construct(private readonly Loader $fallback)
    {
    }

    public function load($locale, $group, $namespace = null)
    {
        if ($group !== '*') {
            return $this->fallback->load($locale, $group, $namespace);
        }

        return Cache::rememberForever("translations.{$locale}.{$group}", function () use ($locale, $group) {
            $language = Language::findByCode($locale);

            if (! $language) {
                return [];
            }

            return Translation::where('language_id', $language->id)
                ->where('group', $group)
                ->pluck('value', 'key')
                ->all();
        });
    }

    public function addNamespace($namespace, $hint)
    {
        $this->fallback->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path)
    {
        $this->fallback->addJsonPath($path);
    }

    public function namespaces()
    {
        return $this->fallback->namespaces();
    }
}
