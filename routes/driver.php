<?php

use App\Http\Controllers\Driver\Auth\ForgotPasswordController;
use App\Http\Controllers\Driver\Auth\LoginController;
use App\Http\Controllers\Driver\Auth\ResetPasswordController;
use App\Http\Controllers\Driver\BookingController;
use App\Http\Controllers\Driver\DashboardController;
use App\Http\Controllers\Driver\EarningsController;
use App\Http\Controllers\Driver\LocationController;
use App\Http\Controllers\Driver\NotificationController;
use App\Http\Controllers\Driver\ProfileController;
use App\Http\Controllers\Driver\RideController;
use App\Http\Controllers\Driver\ReviewController;
use App\Http\Controllers\Driver\SecurityController;
use App\Http\Controllers\Driver\SettingsController;
use App\Http\Controllers\Driver\StatusController;
use Illuminate\Support\Facades\Route;

Route::middleware('driver.guest')->group(function () {
    Route::get('login', fn () => redirect()->route('login'))->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('driver.auth:driver')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('status/toggle', [StatusController::class, 'toggle'])->name('status.toggle');

    Route::post('location', [LocationController::class, 'update'])->name('location.update');

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::post('{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::delete('{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('{booking}', [BookingController::class, 'show'])->name('show');
        Route::post('{booking}/start', [RideController::class, 'start'])->name('start');
        Route::post('{booking}/complete', [RideController::class, 'complete'])->name('complete');
    });

    Route::get('earnings', [EarningsController::class, 'index'])->name('earnings.index');

    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
    });

    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/', [SecurityController::class, 'edit'])->name('edit');
        Route::put('/', [SecurityController::class, 'update'])->name('update');
    });

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'edit'])->name('edit');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
    });
});
