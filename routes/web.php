<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingPaymentController;
use App\Http\Controllers\BookingRequestController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PromoBannerSettingController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('pages.home');

Route::get('sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');

// Single front-door login for both customers and admins — see
// App\Http\Controllers\Auth\LoginController for why one form covers both.
Route::get('login', [LoginController::class, 'create'])->name('login');
Route::post('login', [LoginController::class, 'store']);

Route::post('locale/{code}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::post('currency/{code}', [CurrencyController::class, 'switch'])->name('currency.switch');
Route::post('theme/toggle', [ThemeController::class, 'toggle'])->name('theme.toggle');

Route::post('contact', [ContactController::class, 'store'])->name('contact.store');

Route::post('coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply')->middleware('throttle:30,1');

// Private, unlinked toggle page for the two vendor-only "software for sale"
// promo elements (see PromoBannerSetting) — not part of any nav, and
// deliberately not gated behind admin auth; see PromoBannerSettingController.
Route::get('sale-promo-settings', [PromoBannerSettingController::class, 'edit'])->name('promo-banner-settings.edit');
Route::post('sale-promo-settings', [PromoBannerSettingController::class, 'update'])->name('promo-banner-settings.update');

// Browser push subscription management — guard-agnostic (works for
// whichever of admin/customer/driver is actually logged in, resolved
// server-side; see PushSubscriptionController::currentSubscriber()).
// Throttled since a compromised/buggy client could otherwise hammer these.
Route::prefix('push')->name('push.')->middleware('throttle:20,1')->group(function () {
    Route::post('subscribe', [PushSubscriptionController::class, 'store'])->name('subscribe');
    Route::post('unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('unsubscribe');
    Route::get('status', [PushSubscriptionController::class, 'status'])->name('status');
});

Route::prefix('booking')->name('booking.')->group(function () {
    Route::post('/', [BookingRequestController::class, 'store'])->name('store');
    Route::post('quote', [BookingRequestController::class, 'quote'])->name('quote')->middleware('throttle:30,1');
    Route::get('confirmation/{bookingNumber}', [BookingRequestController::class, 'confirmation'])->name('confirmation');
    Route::get('invoice/{bookingNumber}', [BookingRequestController::class, 'invoice'])->name('invoice');
    Route::get('invoice/{bookingNumber}/download', [BookingRequestController::class, 'downloadInvoice'])->name('invoice.download');

    // Online payment — reachable by anyone holding the booking number,
    // same as confirmation/invoice above (no login required for a guest
    // booking to get paid).
    Route::get('pay/{bookingNumber}/{gateway}', [BookingPaymentController::class, 'pay'])->name('pay')->where('gateway', 'stripe|paypal');
    Route::get('pay/{bookingNumber}/{gateway}/cancel', [BookingPaymentController::class, 'cancel'])->name('pay.cancel')->where('gateway', 'stripe|paypal');
    Route::get('pay/{bookingNumber}/stripe/return', [BookingPaymentController::class, 'stripeReturn'])->name('pay.stripe.return');
    Route::get('pay/{bookingNumber}/paypal/return', [BookingPaymentController::class, 'paypalReturn'])->name('pay.paypal.return');
});

Route::get('areas/{area:slug}', [AreaController::class, 'show'])->name('areas.show');

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('category/{category:slug}', [BlogController::class, 'category'])->name('category');
    Route::get('tag/{tag:slug}', [BlogController::class, 'tag'])->name('tag');
});

Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', implode('|', array_diff(array_keys(\App\Models\Page::PAGES), ['home'])))
    ->name('pages.show');

// Blog post detail lives at the site root (no "/blog/" prefix) — registered
// after pages.show so known static-page slugs are matched there first, and
// any other single-segment slug falls through here to be looked up as a
// blog post. Kept as implicit model binding ({post:slug}) rather than a
// bare {slug} — a bare {slug} here would have the exact same URI signature
// as pages.show's own {slug} route above, and Laravel's route collection
// silently lets the second registration overwrite the first for routing
// purposes when two routes share a method+URI string.
Route::get('/{post:slug}', [BlogController::class, 'show'])
    ->name('blog.show')
    ->missing(function (\Illuminate\Http\Request $request) {
        if ($redirect = \App\Models\Redirect::findFor($request->route('post'))) {
            return redirect('/'.$redirect->new_path, $redirect->type);
        }

        abort(404);
    });

// Lowest priority — only ever reached once nothing above has matched, so
// this can never intercept a working route. Checks the admin-managed
// redirects table before giving up with a real 404.
// Explicit ->middleware('web') because Route::fallback() does not reliably
// inherit the implicit 'web' group middleware that withRouting() attaches to
// this file's other routes — without it, SetLocale/SetCurrency (and the
// session) never run, so the 404 page always rendered in the default locale
// regardless of the visitor's selected language.
Route::fallback(function (\Illuminate\Http\Request $request) {
    $redirect = \App\Models\Redirect::findFor($request->path());

    if ($redirect) {
        return redirect('/'.$redirect->new_path, $redirect->type);
    }

    abort(404);
})->middleware('web');
