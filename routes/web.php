<?php

use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->name('login.submit');
Route::get('/registration', [AuthController::class, 'showRegistrationForm'])
    ->name('register');
Route::post('/registration', [AuthController::class, 'register'])
    ->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/registrations', [RegistrationController::class, 'store'])
        ->name('registrations.store');
    Route::post('/registrations/{registration}/cancel', [RegistrationController::class, 'cancel'])
        ->name('registrations.cancel');

    Route::get('/waitlist/accept/{token}', [WaitlistController::class, 'accept'])
        ->name('waitlist.accept');
    Route::post('/waitlist/decline/{token}', [WaitlistController::class, 'decline'])
        ->name('waitlist.decline');

    Route::post('/media/upload', [MediaController::class, 'upload'])
        ->name('media.upload');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::post('/users/admin', [AdminUsersController::class, 'store'])
        ->name('admin.users.store');
});
