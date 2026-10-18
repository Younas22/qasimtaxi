<?php

use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\Auth\ForgotPasswordController;
use App\Http\Controllers\Customer\Auth\LoginController;
use App\Http\Controllers\Customer\Auth\RegisterController;
use App\Http\Controllers\Customer\Auth\ResetPasswordController;
use App\Http\Controllers\Customer\Auth\VerificationController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\DispatchController;
use App\Http\Controllers\Customer\FavoriteController;
use App\Http\Controllers\Customer\InvoiceController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\Customer\PaymentMethodController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\SecurityController;
use App\Http\Controllers\Customer\SettingsController;
use App\Http\Controllers\Customer\SupportController;
use App\Http\Controllers\Customer\WalletController;
use Illuminate\Support\Facades\Route;

Route::middleware('customer.guest')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    Route::get('login', fn () => redirect()->route('login'))->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('customer.auth:customer')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('verify-email', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [VerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('email/verification-notification', [VerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.resend');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('upcoming', [BookingController::class, 'upcoming'])->name('upcoming');
        Route::get('completed', [BookingController::class, 'completed'])->name('completed');
        Route::get('cancelled', [BookingController::class, 'cancelled'])->name('cancelled');
        Route::get('create', [BookingController::class, 'create'])->name('create');
        Route::get('{booking}', [BookingController::class, 'show'])->name('show');
        Route::post('{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
        Route::get('{booking}/dispatch', [DispatchController::class, 'show'])->name('dispatch');
    });

    Route::prefix('favorites')->name('favorites.')->group(function () {
        Route::get('/', [FavoriteController::class, 'index'])->name('index');
        Route::post('{vehicle}/toggle', [FavoriteController::class, 'toggle'])->name('toggle');
    });

    Route::prefix('addresses')->name('addresses.')->group(function () {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::post('/', [AddressController::class, 'store'])->name('store');
        Route::put('{address}', [AddressController::class, 'update'])->name('update');
        Route::delete('{address}', [AddressController::class, 'destroy'])->name('destroy');
        Route::post('{address}/default', [AddressController::class, 'setDefault'])->name('default');
    });

    Route::get('wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('wallet/recharge', [WalletController::class, 'recharge'])->name('wallet.recharge');

    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');

    Route::get('payment-methods', [PaymentMethodController::class, 'index'])->name('payment-methods.index');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::get('{booking}/create', [ReviewController::class, 'create'])->name('create');
        Route::post('{booking}', [ReviewController::class, 'store'])->name('store');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
    });

    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/', [SecurityController::class, 'edit'])->name('edit');
        Route::put('/', [SecurityController::class, 'update'])->name('update');
        Route::delete('sessions/{session}', [SecurityController::class, 'revokeSession'])->name('sessions.revoke');
        Route::post('sessions/revoke-others', [SecurityController::class, 'revokeOtherSessions'])->name('sessions.revoke-others');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'edit'])->name('edit');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
    });

    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/', [SupportController::class, 'index'])->name('index');
        Route::get('create', [SupportController::class, 'create'])->name('create');
        Route::post('/', [SupportController::class, 'store'])->name('store');
        Route::get('{ticket}', [SupportController::class, 'show'])->name('show');
        Route::post('{ticket}/reply', [SupportController::class, 'reply'])->name('reply');
        Route::post('{ticket}/close', [SupportController::class, 'close'])->name('close');
    });
});
