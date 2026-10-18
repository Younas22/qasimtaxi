<?php

use App\Http\Middleware\Admin\Authenticate as AdminAuthenticate;
use App\Http\Middleware\Admin\EnsurePermission;
use App\Http\Middleware\Admin\RedirectIfAuthenticated as AdminRedirectIfAuthenticated;
use App\Http\Middleware\Customer\Authenticate as CustomerAuthenticate;
use App\Http\Middleware\Customer\RedirectIfAuthenticated as CustomerRedirectIfAuthenticated;
use App\Http\Middleware\Driver\Authenticate as DriverAuthenticate;
use App\Http\Middleware\Driver\RedirectIfAuthenticated as DriverRedirectIfAuthenticated;
use App\Http\Middleware\SetCurrency;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            Illuminate\Support\Facades\Route::middleware(['web'])
                ->prefix('admin')
                ->name('admin.')
                ->group(__DIR__.'/../routes/admin.php');

            Illuminate\Support\Facades\Route::middleware(['web'])
                ->prefix('account')
                ->name('customer.')
                ->group(__DIR__.'/../routes/customer.php');

            Illuminate\Support\Facades\Route::middleware(['web'])
                ->prefix('driver')
                ->name('driver.')
                ->group(__DIR__.'/../routes/driver.php');
        },
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => AdminAuthenticate::class,
            'admin.guest' => AdminRedirectIfAuthenticated::class,
            'permission' => EnsurePermission::class,
            'customer.auth' => CustomerAuthenticate::class,
            'customer.guest' => CustomerRedirectIfAuthenticated::class,
            'driver.auth' => DriverAuthenticate::class,
            'driver.guest' => DriverRedirectIfAuthenticated::class,
        ]);

        // Locale/currency must be resolved for every request — both the
        // public site and the admin panel — since they drive translated
        // strings and currency() formatting everywhere.
        $middleware->web(append: [
            SetLocale::class,
            SetCurrency::class,
        ]);

        // `append` above only controls position within the 'web' group's own
        // array — Laravel still re-sorts the final pipeline by its global
        // middleware priority list, where SubstituteBindings (route-model
        // binding) sits ahead of anything not explicitly prioritized. That
        // let a failed binding (e.g. a 404 on `/{post:slug}`) throw before
        // SetLocale ever ran, so error pages always rendered in the default
        // locale regardless of the visitor's selected language. Explicitly
        // slotting both in right after StartSession (which they depend on
        // for session('locale')/session('currency')) and therefore ahead of
        // SubstituteBindings fixes that.
        $middleware->appendToPriorityList(
            after: \Illuminate\Session\Middleware\StartSession::class,
            append: SetLocale::class,
        );
        $middleware->appendToPriorityList(
            after: SetLocale::class,
            append: SetCurrency::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
