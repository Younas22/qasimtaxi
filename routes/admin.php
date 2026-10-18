<?php

use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BookingSettingController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\CustomerAddressController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerLoyaltyController;
use App\Http\Controllers\Admin\CustomerWalletController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\FleetController;
use App\Http\Controllers\Admin\EmailSettingController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LocaleController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\Location\AirportController;
use App\Http\Controllers\Admin\Location\CityController;
use App\Http\Controllers\Admin\Location\CountryController;
use App\Http\Controllers\Admin\Location\PickupPointController;
use App\Http\Controllers\Admin\Location\StateController;
use App\Http\Controllers\Admin\Location\TrainStationController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\NotificationSettingController;
use App\Http\Controllers\Admin\PushNotificationSettingController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\PopularRouteController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\RouteTypeController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\PricingRuleController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\RichTextUploadController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\SystemToolController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TranslationController;
use App\Http\Controllers\Admin\VehicleCategoryController;
use App\Http\Controllers\Admin\VehicleController;
use Illuminate\Support\Facades\Route;

/**
 * Registers the uniform index/create/store/edit/update/destroy/toggle set for
 * a location CRUD resource, all gated behind the single "locations" permission.
 */
$locationResource = function (string $segment, string $paramName, string $controller): void {
    Route::prefix($segment)->name($segment.'.')->group(function () use ($paramName, $controller) {
        Route::get('/', [$controller, 'index'])->name('index')->middleware('permission:locations.view');
        Route::get('create', [$controller, 'create'])->name('create')->middleware('permission:locations.create');
        Route::post('/', [$controller, 'store'])->name('store')->middleware('permission:locations.create');
        Route::get("{{$paramName}}/edit", [$controller, 'edit'])->name('edit')->middleware('permission:locations.edit');
        Route::put("{{$paramName}}", [$controller, 'update'])->name('update')->middleware('permission:locations.edit');
        Route::delete("{{$paramName}}", [$controller, 'destroy'])->name('destroy')->middleware('permission:locations.delete');
        Route::post("{{$paramName}}/toggle", [$controller, 'toggleStatus'])->name('toggle')->middleware('permission:locations.edit');
    });
};

Route::post('locale/{code}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::post('currency/{code}', [CurrencyController::class, 'switch'])->name('currency.switch');
Route::post('theme/toggle', [ThemeController::class, 'toggle'])->name('theme.toggle');

Route::middleware('admin.guest')->group(function () {
    Route::get('login', fn () => redirect()->route('login'))->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('admin.auth:admin')->group(function () use ($locationResource) {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('rich-text/upload-image', [RichTextUploadController::class, 'store'])->name('rich-text.upload-image');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('password', [ProfileController::class, 'updatePassword'])->name('password');
    });

    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('revenue', [ReportController::class, 'revenue'])->name('revenue');
        Route::get('bookings', [ReportController::class, 'bookings'])->name('bookings');
        Route::get('vehicles', [ReportController::class, 'vehicles'])->name('vehicles');
        Route::get('drivers', [ReportController::class, 'drivers'])->name('drivers');
        Route::get('customers', [ReportController::class, 'customers'])->name('customers');

        Route::get('{report}/export/csv', [ReportController::class, 'exportCsv'])
            ->name('export.csv')->middleware('permission:reports.export')
            ->where('report', 'revenue|bookings|vehicles|drivers|customers');
        Route::get('{report}/export/excel', [ReportController::class, 'exportExcel'])
            ->name('export.excel')->middleware('permission:reports.export')
            ->where('report', 'revenue|bookings|vehicles|drivers|customers');
        Route::get('{report}/print', [ReportController::class, 'print'])
            ->name('print')->middleware('permission:reports.export')
            ->where('report', 'revenue|bookings|vehicles|drivers|customers');
    });

    Route::prefix('system-tools')->name('system-tools.')->middleware('permission:system.view')->group(function () {
        Route::get('/', [SystemToolController::class, 'index'])->name('index');
        Route::post('maintenance/toggle', [SystemToolController::class, 'toggleMaintenance'])->name('maintenance.toggle')->middleware('permission:system.edit');
        Route::post('cache/clear', [SystemToolController::class, 'clearCache'])->name('cache.clear')->middleware('permission:system.edit');

        Route::post('backups', [SystemToolController::class, 'createBackup'])->name('backups.create')->middleware('permission:system.edit');
        Route::post('backups/upload', [SystemToolController::class, 'uploadBackup'])->name('backups.upload')->middleware('permission:system.edit');
        Route::get('backups/{filename}/download', [SystemToolController::class, 'downloadBackup'])->name('backups.download')->middleware('permission:system.export');
        Route::post('backups/{filename}/restore', [SystemToolController::class, 'restoreBackup'])->name('backups.restore')->middleware('permission:system.delete');
        Route::delete('backups/{filename}', [SystemToolController::class, 'destroyBackup'])->name('backups.destroy')->middleware('permission:system.delete');

        Route::post('database/drop-tables', [SystemToolController::class, 'dropAllTables'])->name('database.drop-tables')->middleware('permission:system.delete');
        Route::post('database/migrate', [SystemToolController::class, 'runMigrations'])->name('database.migrate')->middleware('permission:system.edit');
        Route::post('composer/update', [SystemToolController::class, 'composerUpdate'])->name('composer.update')->middleware('permission:system.delete');

        Route::get('activity-logs', [SystemToolController::class, 'activityLogs'])->name('activity-logs');

        Route::get('error-logs', [SystemToolController::class, 'errorLogs'])->name('error-logs');
        Route::post('error-logs/clear', [SystemToolController::class, 'clearErrorLog'])->name('error-logs.clear')->middleware('permission:system.edit');

        Route::post('queue/failed/clear', [SystemToolController::class, 'clearFailedJobs'])->name('queue.failed.clear')->middleware('permission:system.edit');

        // Raw .env editor — same trust tier as the DB drop/restore tools
        // above (system.edit), since whoever can reach this can already do
        // far more damage via those. A timestamped backup is written
        // automatically before every save so a bad edit is always
        // reversible from the same screen.
        Route::get('env', [SystemToolController::class, 'envEditor'])->name('env.edit');
        Route::put('env', [SystemToolController::class, 'updateEnv'])->name('env.update')->middleware('permission:system.edit');
        Route::post('env/backups/{filename}/restore', [SystemToolController::class, 'restoreEnvBackup'])->name('env.backups.restore')->middleware('permission:system.edit');
        Route::delete('env/backups/{filename}', [SystemToolController::class, 'destroyEnvBackup'])->name('env.backups.destroy')->middleware('permission:system.delete');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index')->middleware('permission:bookings.view');
        Route::post('bulk-destroy', [BookingController::class, 'bulkDestroy'])->name('bulk-destroy')->middleware('permission:bookings.delete');
        Route::get('create', [BookingController::class, 'create'])->name('create')->middleware('permission:bookings.create');
        Route::post('/', [BookingController::class, 'store'])->name('store')->middleware('permission:bookings.create');
        Route::get('{booking}', [BookingController::class, 'show'])->name('show')->middleware('permission:bookings.view');
        Route::get('{booking}/edit', [BookingController::class, 'edit'])->name('edit')->middleware('permission:bookings.edit');
        Route::put('{booking}', [BookingController::class, 'update'])->name('update')->middleware('permission:bookings.edit');
        Route::delete('{booking}', [BookingController::class, 'destroy'])->name('destroy')->middleware('permission:bookings.delete');
        Route::post('{booking}/status', [BookingController::class, 'updateStatus'])->name('status')->middleware('permission:bookings.edit');
        Route::post('{booking}/mark-paid', [BookingController::class, 'markAsPaid'])->name('mark-paid')->middleware('permission:bookings.edit');
        Route::post('{booking}/process-refund', [BookingController::class, 'processRefund'])->name('process-refund')->middleware('permission:bookings.edit');
        Route::post('{booking}/suggest-driver', [BookingController::class, 'suggestDriver'])->name('suggest-driver')->middleware('permission:bookings.edit');
    });

    Route::prefix('booking-settings')->name('booking-settings.')->group(function () {
        Route::get('/', [BookingSettingController::class, 'edit'])->name('edit')->middleware('permission:bookings.view');
        Route::put('/', [BookingSettingController::class, 'update'])->name('update')->middleware('permission:bookings.edit');
    });

    Route::prefix('pricing')->name('pricing.')->group(function () {
        Route::get('/', [PricingRuleController::class, 'index'])->name('index')->middleware('permission:pricing.view');
        Route::get('global/edit', [PricingRuleController::class, 'editGlobal'])->name('global.edit')->middleware('permission:pricing.edit');
        Route::put('global', [PricingRuleController::class, 'updateGlobal'])->name('global.update')->middleware('permission:pricing.edit');
        Route::get('{category}/edit', [PricingRuleController::class, 'editCategory'])->name('category.edit')->middleware('permission:pricing.edit');
        Route::put('{category}', [PricingRuleController::class, 'updateCategory'])->name('category.update')->middleware('permission:pricing.edit');
        Route::post('{category}/reset', [PricingRuleController::class, 'resetCategory'])->name('category.reset')->middleware('permission:pricing.delete');
    });

    Route::prefix('payment-gateways')->name('payment-gateways.')->group(function () {
        Route::get('/', [PaymentGatewayController::class, 'index'])->name('index')->middleware('permission:payments.view');
        Route::get('{gateway}/edit', [PaymentGatewayController::class, 'edit'])->name('edit')->middleware('permission:payments.edit');
        Route::put('{gateway}', [PaymentGatewayController::class, 'update'])->name('update')->middleware('permission:payments.edit');
        Route::post('{gateway}/toggle', [PaymentGatewayController::class, 'toggleStatus'])->name('toggle')->middleware('permission:payments.edit');
    });

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index')->middleware('permission:roles.view');
        Route::get('create', [RoleController::class, 'create'])->name('create')->middleware('permission:roles.create');
        Route::post('/', [RoleController::class, 'store'])->name('store')->middleware('permission:roles.create');
        Route::get('{role}/edit', [RoleController::class, 'edit'])->name('edit')->middleware('permission:roles.edit');
        Route::put('{role}', [RoleController::class, 'update'])->name('update')->middleware('permission:roles.edit');
        Route::delete('{role}', [RoleController::class, 'destroy'])->name('destroy')->middleware('permission:roles.delete');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'edit'])->name('edit')->middleware('permission:settings.view');
        Route::put('/', [SettingController::class, 'update'])->name('update')->middleware('permission:settings.edit');
    });

    Route::prefix('email-settings')->name('email-settings.')->group(function () {
        Route::get('/', [EmailSettingController::class, 'edit'])->name('edit')->middleware('permission:settings.view');
        Route::put('/', [EmailSettingController::class, 'update'])->name('update')->middleware('permission:settings.edit');
        Route::post('test', [EmailSettingController::class, 'sendTest'])->name('test')->middleware('permission:settings.edit');
    });

    Route::prefix('integrations')->name('integrations.')->group(function () {
        Route::get('/', [IntegrationController::class, 'edit'])->name('edit')->middleware('permission:system.edit');
        Route::post('google-maps/test', [IntegrationController::class, 'testGoogleMaps'])->name('google-maps.test')->middleware('permission:system.edit');
        Route::put('google-maps', [IntegrationController::class, 'updateGoogleMaps'])->name('google-maps.update')->middleware('permission:system.delete');
    });

    Route::prefix('notification-settings')->name('notification-settings.')->group(function () {
        Route::get('/', [NotificationSettingController::class, 'edit'])->name('edit')->middleware('permission:settings.view');
        Route::put('/', [NotificationSettingController::class, 'update'])->name('update')->middleware('permission:settings.edit');
    });

    // Browser push notifications — rendered as a section on the same
    // "Notifications" settings page above, but posted separately so this
    // never touches NotificationSettingController's own mail/in-app logic.
    Route::prefix('push-settings')->name('push-settings.')->group(function () {
        Route::put('/', [PushNotificationSettingController::class, 'update'])->name('update')->middleware('permission:settings.edit');
        Route::post('test', [PushNotificationSettingController::class, 'sendTest'])->name('test')->middleware('permission:settings.edit');
    });

    Route::prefix('languages')->name('languages.')->group(function () {
        Route::get('/', [LanguageController::class, 'index'])->name('index')->middleware('permission:languages.view');
        Route::get('create', [LanguageController::class, 'create'])->name('create')->middleware('permission:languages.create');
        Route::post('/', [LanguageController::class, 'store'])->name('store')->middleware('permission:languages.create');
        Route::get('{language}/edit', [LanguageController::class, 'edit'])->name('edit')->middleware('permission:languages.edit');
        Route::put('{language}', [LanguageController::class, 'update'])->name('update')->middleware('permission:languages.edit');
        Route::delete('{language}', [LanguageController::class, 'destroy'])->name('destroy')->middleware('permission:languages.delete');
        Route::post('{language}/toggle', [LanguageController::class, 'toggleStatus'])->name('toggle')->middleware('permission:languages.edit');
        Route::post('{language}/default', [LanguageController::class, 'setDefault'])->name('default')->middleware('permission:languages.edit');

        Route::get('{language}/translations', [TranslationController::class, 'edit'])->name('translations.edit')->middleware('permission:languages.edit');
        Route::put('{language}/translations', [TranslationController::class, 'update'])->name('translations.update')->middleware('permission:languages.edit');
        Route::delete('{language}/translations', [TranslationController::class, 'destroy'])->name('translations.destroy')->middleware('permission:languages.edit');
        Route::post('{language}/translations/rescan', [TranslationController::class, 'rescan'])->name('translations.rescan')->middleware('permission:languages.edit');
    });

    Route::prefix('currencies')->name('currencies.')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('index')->middleware('permission:currencies.view');
        Route::get('create', [CurrencyController::class, 'create'])->name('create')->middleware('permission:currencies.create');
        Route::post('/', [CurrencyController::class, 'store'])->name('store')->middleware('permission:currencies.create');
        Route::get('{currency}/edit', [CurrencyController::class, 'edit'])->name('edit')->middleware('permission:currencies.edit');
        Route::put('{currency}', [CurrencyController::class, 'update'])->name('update')->middleware('permission:currencies.edit');
        Route::delete('{currency}', [CurrencyController::class, 'destroy'])->name('destroy')->middleware('permission:currencies.delete');
        Route::post('{currency}/toggle', [CurrencyController::class, 'toggleStatus'])->name('toggle')->middleware('permission:currencies.edit');
        Route::post('{currency}/default', [CurrencyController::class, 'setDefault'])->name('default')->middleware('permission:currencies.edit');
    });

    Route::prefix('popular-routes')->name('popular-routes.')->group(function () {
        Route::get('/', [PopularRouteController::class, 'index'])->name('index')->middleware('permission:routes.view');
        Route::get('create', [PopularRouteController::class, 'create'])->name('create')->middleware('permission:routes.create');
        Route::post('/', [PopularRouteController::class, 'store'])->name('store')->middleware('permission:routes.create');
        Route::get('{popularRoute}/edit', [PopularRouteController::class, 'edit'])->name('edit')->middleware('permission:routes.edit');
        Route::put('{popularRoute}', [PopularRouteController::class, 'update'])->name('update')->middleware('permission:routes.edit');
        Route::delete('{popularRoute}', [PopularRouteController::class, 'destroy'])->name('destroy')->middleware('permission:routes.delete');
        Route::post('{popularRoute}/toggle', [PopularRouteController::class, 'toggleStatus'])->name('toggle')->middleware('permission:routes.edit');

        Route::prefix('route-types')->name('route-types.')->group(function () {
            Route::get('/', [RouteTypeController::class, 'index'])->name('index')->middleware('permission:routes.view');
            Route::get('create', [RouteTypeController::class, 'create'])->name('create')->middleware('permission:routes.create');
            Route::post('/', [RouteTypeController::class, 'store'])->name('store')->middleware('permission:routes.create');
            Route::get('{routeType}/edit', [RouteTypeController::class, 'edit'])->name('edit')->middleware('permission:routes.edit');
            Route::put('{routeType}', [RouteTypeController::class, 'update'])->name('update')->middleware('permission:routes.edit');
            Route::delete('{routeType}', [RouteTypeController::class, 'destroy'])->name('destroy')->middleware('permission:routes.delete');
            Route::post('{routeType}/toggle', [RouteTypeController::class, 'toggleStatus'])->name('toggle')->middleware('permission:routes.edit');
            Route::post('{routeType}/move-up', [RouteTypeController::class, 'moveUp'])->name('move-up')->middleware('permission:routes.edit');
            Route::post('{routeType}/move-down', [RouteTypeController::class, 'moveDown'])->name('move-down')->middleware('permission:routes.edit');
        });
    });

    Route::prefix('areas')->name('areas.')->group(function () {
        Route::get('/', [AreaController::class, 'index'])->name('index')->middleware('permission:areas.view');
        Route::get('create', [AreaController::class, 'create'])->name('create')->middleware('permission:areas.create');
        Route::post('/', [AreaController::class, 'store'])->name('store')->middleware('permission:areas.create');
        Route::get('{area}/edit', [AreaController::class, 'edit'])->name('edit')->middleware('permission:areas.edit');
        Route::put('{area}', [AreaController::class, 'update'])->name('update')->middleware('permission:areas.edit');
        Route::delete('{area}', [AreaController::class, 'destroy'])->name('destroy')->middleware('permission:areas.delete');
        Route::post('{area}/toggle', [AreaController::class, 'toggleStatus'])->name('toggle')->middleware('permission:areas.edit');
        Route::post('{area}/move-up', [AreaController::class, 'moveUp'])->name('move-up')->middleware('permission:areas.edit');
        Route::post('{area}/move-down', [AreaController::class, 'moveDown'])->name('move-down')->middleware('permission:areas.edit');
    });

    Route::prefix('redirects')->name('redirects.')->group(function () {
        Route::get('/', [RedirectController::class, 'index'])->name('index')->middleware('permission:content.view');
        Route::get('create', [RedirectController::class, 'create'])->name('create')->middleware('permission:content.create');
        Route::post('/', [RedirectController::class, 'store'])->name('store')->middleware('permission:content.create');
        Route::get('{redirect}/edit', [RedirectController::class, 'edit'])->name('edit')->middleware('permission:content.edit');
        Route::put('{redirect}', [RedirectController::class, 'update'])->name('update')->middleware('permission:content.edit');
        Route::delete('{redirect}', [RedirectController::class, 'destroy'])->name('destroy')->middleware('permission:content.delete');
    });

    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::get('/', [CouponController::class, 'index'])->name('index')->middleware('permission:coupons.view');
        Route::get('create', [CouponController::class, 'create'])->name('create')->middleware('permission:coupons.create');
        Route::post('/', [CouponController::class, 'store'])->name('store')->middleware('permission:coupons.create');
        Route::get('{coupon}/edit', [CouponController::class, 'edit'])->name('edit')->middleware('permission:coupons.edit');
        Route::put('{coupon}', [CouponController::class, 'update'])->name('update')->middleware('permission:coupons.edit');
        Route::delete('{coupon}', [CouponController::class, 'destroy'])->name('destroy')->middleware('permission:coupons.delete');
    });

    Route::prefix('promotions')->name('promotions.')->group(function () {
        Route::get('/', [PromotionController::class, 'index'])->name('index')->middleware('permission:promotions.view');
        Route::get('create', [PromotionController::class, 'create'])->name('create')->middleware('permission:promotions.create');
        Route::post('/', [PromotionController::class, 'store'])->name('store')->middleware('permission:promotions.create');
        Route::get('{promotion}/edit', [PromotionController::class, 'edit'])->name('edit')->middleware('permission:promotions.edit');
        Route::put('{promotion}', [PromotionController::class, 'update'])->name('update')->middleware('permission:promotions.edit');
        Route::delete('{promotion}', [PromotionController::class, 'destroy'])->name('destroy')->middleware('permission:promotions.delete');
    });

    Route::prefix('locations')->name('locations.')->group(function () use ($locationResource) {
        $locationResource('countries', 'country', CountryController::class);
        $locationResource('states', 'state', StateController::class);
        $locationResource('cities', 'city', CityController::class);
        $locationResource('airports', 'airport', AirportController::class);
        $locationResource('train-stations', 'trainStation', TrainStationController::class);
        $locationResource('pickup-points', 'pickupPoint', PickupPointController::class);
    });

    Route::prefix('vehicles')->name('vehicles.')->group(function () {
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [VehicleCategoryController::class, 'index'])->name('index')->middleware('permission:vehicles.view');
            Route::get('create', [VehicleCategoryController::class, 'create'])->name('create')->middleware('permission:vehicles.create');
            Route::post('/', [VehicleCategoryController::class, 'store'])->name('store')->middleware('permission:vehicles.create');
            Route::get('{category}/edit', [VehicleCategoryController::class, 'edit'])->name('edit')->middleware('permission:vehicles.edit');
            Route::put('{category}', [VehicleCategoryController::class, 'update'])->name('update')->middleware('permission:vehicles.edit');
            Route::delete('{category}', [VehicleCategoryController::class, 'destroy'])->name('destroy')->middleware('permission:vehicles.delete');
            Route::post('{category}/toggle', [VehicleCategoryController::class, 'toggleStatus'])->name('toggle')->middleware('permission:vehicles.edit');
            Route::post('{category}/move-up', [VehicleCategoryController::class, 'moveUp'])->name('move-up')->middleware('permission:vehicles.edit');
            Route::post('{category}/move-down', [VehicleCategoryController::class, 'moveDown'])->name('move-down')->middleware('permission:vehicles.edit');
        });

        Route::get('/', [VehicleController::class, 'index'])->name('index')->middleware('permission:vehicles.view');
        Route::get('create', [VehicleController::class, 'create'])->name('create')->middleware('permission:vehicles.create');
        Route::post('/', [VehicleController::class, 'store'])->name('store')->middleware('permission:vehicles.create');
        Route::get('{vehicle}/edit', [VehicleController::class, 'edit'])->name('edit')->middleware('permission:vehicles.edit');
        Route::put('{vehicle}', [VehicleController::class, 'update'])->name('update')->middleware('permission:vehicles.edit');
        Route::delete('{vehicle}', [VehicleController::class, 'destroy'])->name('destroy')->middleware('permission:vehicles.delete');
        Route::post('{vehicle}/toggle', [VehicleController::class, 'toggleStatus'])->name('toggle')->middleware('permission:vehicles.edit');
        Route::delete('images/{image}', [VehicleController::class, 'destroyImage'])->name('images.destroy')->middleware('permission:vehicles.edit');
    });

    Route::prefix('drivers')->name('drivers.')->group(function () {
        Route::get('/', [DriverController::class, 'index'])->name('index')->middleware('permission:drivers.view');
        Route::get('create', [DriverController::class, 'create'])->name('create')->middleware('permission:drivers.create');
        Route::post('/', [DriverController::class, 'store'])->name('store')->middleware('permission:drivers.create');
        Route::get('{driver}/edit', [DriverController::class, 'edit'])->name('edit')->middleware('permission:drivers.edit');
        Route::put('{driver}', [DriverController::class, 'update'])->name('update')->middleware('permission:drivers.edit');
        Route::delete('{driver}', [DriverController::class, 'destroy'])->name('destroy')->middleware('permission:drivers.delete');
        Route::post('{driver}/toggle', [DriverController::class, 'toggleStatus'])->name('toggle')->middleware('permission:drivers.edit');
        Route::post('{driver}/toggle-online', [DriverController::class, 'toggleOnline'])->name('toggle-online')->middleware('permission:drivers.edit');
        Route::post('{driver}/toggle-available', [DriverController::class, 'toggleAvailable'])->name('toggle-available')->middleware('permission:drivers.edit');
    });

    Route::prefix('fleet')->name('fleet.')->group(function () {
        Route::get('/', [FleetController::class, 'index'])->name('index')->middleware('permission:drivers.view');
        Route::get('data', [FleetController::class, 'data'])->name('data')->middleware('permission:drivers.view');
    });

    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index')->middleware('permission:customers.view');
        Route::get('create', [CustomerController::class, 'create'])->name('create')->middleware('permission:customers.create');
        Route::post('/', [CustomerController::class, 'store'])->name('store')->middleware('permission:customers.create');
        Route::get('{customer}', [CustomerController::class, 'show'])->name('show')->middleware('permission:customers.view');
        Route::get('{customer}/edit', [CustomerController::class, 'edit'])->name('edit')->middleware('permission:customers.edit');
        Route::put('{customer}', [CustomerController::class, 'update'])->name('update')->middleware('permission:customers.edit');
        Route::delete('{customer}', [CustomerController::class, 'destroy'])->name('destroy')->middleware('permission:customers.delete');
        Route::post('{customer}/toggle', [CustomerController::class, 'toggleStatus'])->name('toggle')->middleware('permission:customers.edit');

        Route::post('{customer}/addresses', [CustomerAddressController::class, 'store'])->name('addresses.store')->middleware('permission:customers.edit');
        Route::post('{customer}/wallet', [CustomerWalletController::class, 'store'])->name('wallet.store')->middleware('permission:customers.edit');
        Route::post('{customer}/loyalty', [CustomerLoyaltyController::class, 'store'])->name('loyalty.store')->middleware('permission:customers.edit');
    });

    Route::prefix('addresses')->name('addresses.')->group(function () {
        Route::delete('{address}', [CustomerAddressController::class, 'destroy'])->name('destroy')->middleware('permission:customers.edit');
        Route::post('{address}/default', [CustomerAddressController::class, 'setDefault'])->name('default')->middleware('permission:customers.edit');
    });

    Route::prefix('contact-messages')->name('contact-messages.')->group(function () {
        Route::get('/', [ContactMessageController::class, 'index'])->name('index')->middleware('permission:messages.view');
        Route::get('{contactMessage}', [ContactMessageController::class, 'show'])->name('show')->middleware('permission:messages.view');
        Route::delete('{contactMessage}', [ContactMessageController::class, 'destroy'])->name('destroy')->middleware('permission:messages.delete');
    });

    Route::prefix('support-tickets')->name('support-tickets.')->group(function () {
        Route::get('/', [SupportTicketController::class, 'index'])->name('index')->middleware('permission:support.view');
        Route::get('{ticket}', [SupportTicketController::class, 'show'])->name('show')->middleware('permission:support.view');
        Route::post('{ticket}/reply', [SupportTicketController::class, 'reply'])->name('reply')->middleware('permission:support.edit');
        Route::put('{ticket}/status', [SupportTicketController::class, 'updateStatus'])->name('status')->middleware('permission:support.edit');
    });

    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index')->middleware('permission:reviews.view');
        Route::get('create', [ReviewController::class, 'create'])->name('create')->middleware('permission:reviews.create');
        Route::post('/', [ReviewController::class, 'store'])->name('store')->middleware('permission:reviews.create');
        Route::get('{review}/edit', [ReviewController::class, 'edit'])->name('edit')->middleware('permission:reviews.edit');
        Route::put('{review}', [ReviewController::class, 'update'])->name('update')->middleware('permission:reviews.edit');
        Route::post('{review}/approve', [ReviewController::class, 'approve'])->name('approve')->middleware('permission:reviews.edit');
        Route::post('{review}/reject', [ReviewController::class, 'reject'])->name('reject')->middleware('permission:reviews.edit');
        Route::post('{review}/toggle-featured', [ReviewController::class, 'toggleFeatured'])->name('toggle-featured')->middleware('permission:reviews.edit');
        Route::delete('{review}', [ReviewController::class, 'destroy'])->name('destroy')->middleware('permission:reviews.delete');
    });

    Route::prefix('pages')->name('pages.')->group(function () {
        Route::get('/', [PageController::class, 'index'])->name('index')->middleware('permission:content.view');
        Route::get('{page}/edit', [PageController::class, 'edit'])->name('edit')->middleware('permission:content.view');
        Route::put('{page}', [PageController::class, 'update'])->name('update')->middleware('permission:content.edit');

        Route::prefix('{page}/sections')->name('sections.')->scopeBindings()->group(function () {
            Route::get('create', [PageSectionController::class, 'create'])->name('create')->middleware('permission:content.create');
            Route::post('/', [PageSectionController::class, 'store'])->name('store')->middleware('permission:content.create');
            Route::get('{section}/edit', [PageSectionController::class, 'edit'])->name('edit')->middleware('permission:content.edit');
            Route::put('{section}', [PageSectionController::class, 'update'])->name('update')->middleware('permission:content.edit');
            Route::delete('{section}', [PageSectionController::class, 'destroy'])->name('destroy')->middleware('permission:content.delete');
            Route::post('{section}/toggle', [PageSectionController::class, 'toggleStatus'])->name('toggle')->middleware('permission:content.edit');
            Route::post('{section}/move-up', [PageSectionController::class, 'moveUp'])->name('move-up')->middleware('permission:content.edit');
            Route::post('{section}/move-down', [PageSectionController::class, 'moveDown'])->name('move-down')->middleware('permission:content.edit');
            Route::post('reorder', [PageSectionController::class, 'reorder'])->name('reorder')->middleware('permission:content.edit');
        });
    });

    Route::prefix('blog')->name('blog.')->group(function () {
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [BlogCategoryController::class, 'index'])->name('index')->middleware('permission:blog.view');
            Route::get('create', [BlogCategoryController::class, 'create'])->name('create')->middleware('permission:blog.create');
            Route::post('/', [BlogCategoryController::class, 'store'])->name('store')->middleware('permission:blog.create');
            Route::get('{category}/edit', [BlogCategoryController::class, 'edit'])->name('edit')->middleware('permission:blog.edit');
            Route::put('{category}', [BlogCategoryController::class, 'update'])->name('update')->middleware('permission:blog.edit');
            Route::delete('{category}', [BlogCategoryController::class, 'destroy'])->name('destroy')->middleware('permission:blog.delete');
            Route::post('{category}/toggle', [BlogCategoryController::class, 'toggleStatus'])->name('toggle')->middleware('permission:blog.edit');
            Route::post('{category}/move-up', [BlogCategoryController::class, 'moveUp'])->name('move-up')->middleware('permission:blog.edit');
            Route::post('{category}/move-down', [BlogCategoryController::class, 'moveDown'])->name('move-down')->middleware('permission:blog.edit');
        });

        Route::prefix('tags')->name('tags.')->group(function () {
            Route::get('/', [TagController::class, 'index'])->name('index')->middleware('permission:blog.view');
            Route::post('/', [TagController::class, 'store'])->name('store')->middleware('permission:blog.create');
            Route::put('{tag}', [TagController::class, 'update'])->name('update')->middleware('permission:blog.edit');
            Route::delete('{tag}', [TagController::class, 'destroy'])->name('destroy')->middleware('permission:blog.delete');
        });

        Route::get('/', [BlogPostController::class, 'index'])->name('index')->middleware('permission:blog.view');
        Route::get('create', [BlogPostController::class, 'create'])->name('create')->middleware('permission:blog.create');
        Route::post('/', [BlogPostController::class, 'store'])->name('store')->middleware('permission:blog.create');
        Route::get('{post}/edit', [BlogPostController::class, 'edit'])->name('edit')->middleware('permission:blog.edit');
        Route::put('{post}', [BlogPostController::class, 'update'])->name('update')->middleware('permission:blog.edit');
        Route::delete('{post}', [BlogPostController::class, 'destroy'])->name('destroy')->middleware('permission:blog.delete');
        Route::post('{post}/toggle-featured', [BlogPostController::class, 'toggleFeatured'])->name('toggle-featured')->middleware('permission:blog.edit');
    });
});
