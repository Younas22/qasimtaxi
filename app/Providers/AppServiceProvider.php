<?php

namespace App\Providers;

use App\Models\EmailSetting;
use App\Services\DatabaseTranslationLoader;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->overrideTranslatorWithDatabaseLoader();

        Blade::if('permission', function (string $slug) {
            return Auth::guard('admin')->check() && Auth::guard('admin')->user()->hasPermission($slug);
        });

        Blade::if('role', function (string $slug) {
            return Auth::guard('admin')->check() && Auth::guard('admin')->user()->hasRole($slug);
        });

        // The admin panel must stay reachable while the public site is in
        // maintenance mode, otherwise nobody could turn maintenance mode
        // back off again.
        PreventRequestsDuringMaintenance::except(['admin/*']);

        // Let the admin-configured mail settings override .env for every
        // mailer resolved after this point. Guarded so a missing table
        // (fresh install, before migrations run) never breaks booting.
        try {
            EmailSetting::current()->applyToRuntimeConfig();
        } catch (\Throwable) {
            //
        }

        $this->configureWebPushOpenSsl();
    }

    /**
     * Swaps in the DB-backed translation loader (see DatabaseTranslationLoader)
     * for JSON-style __() keys, so Admin → Languages → Translations actually
     * takes effect instead of every non-default locale silently falling back
     * to the English source string.
     *
     * This has to happen in boot(), not register(): Illuminate\Translation\
     * TranslationServiceProvider is NOT a deferred provider, so it always
     * runs its own register() and unconditionally overwrites the
     * "translation.loader"/"translator" bindings with its plain FileLoader —
     * regardless of registration order, and regardless of whether those keys
     * were already bound. boot() only runs once every provider's register()
     * has already executed, so rebinding here is guaranteed to be the last
     * word no matter where TranslationServiceProvider lands in that order.
     */
    private function overrideTranslatorWithDatabaseLoader(): void
    {
        $frameworkLangPath = dirname((new \ReflectionClass(Translator::class))->getFileName()).'/lang';

        $fileLoader = new FileLoader($this->app['files'], [$frameworkLangPath, $this->app['path.lang']]);
        $loader = new DatabaseTranslationLoader($fileLoader);

        $this->app->instance('translation.loader', $loader);

        $translator = new Translator($loader, $this->app->getLocale());
        $translator->setFallback($this->app->getFallbackLocale());

        $this->app->instance('translator', $translator);
    }

    /**
     * web-push (minishlink/web-push) signs a fresh VAPID JWT with an EC key
     * for every push it sends, which on some Windows PHP builds fails with
     * "configuration file routines::no such file" — PHP's OpenSSL extension
     * ships a compiled-in default config path that doesn't exist on this
     * kind of local stack. Setting OPENSSL_CONF to a real openssl.cnf before
     * any EC operation runs fixes it; Linux production servers ship a valid
     * default and never need this (config('webpush.openssl_conf') stays
     * unset there, so this becomes a no-op).
     */
    private function configureWebPushOpenSsl(): void
    {
        if (getenv('OPENSSL_CONF') || PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        $candidates = array_filter([
            config('webpush.openssl_conf'),
            'E:/sv26/apache/conf/openssl.cnf',
            'C:/xampp/apache/conf/openssl.cnf',
        ]);

        foreach ($candidates as $path) {
            if (is_string($path) && $path !== '' && file_exists($path)) {
                putenv('OPENSSL_CONF='.$path);

                return;
            }
        }
    }
}
