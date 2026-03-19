<?php

use App\Http\Controllers\Admin\AdminActivityController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.submit');
    Route::get('/registration', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/registration', [AuthController::class, 'register'])->name('register.submit');

    // Password Reset Routes
    Route::get('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'reset'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::post('/registrations/{registration}/cancel', [RegistrationController::class, 'cancel'])->name('registrations.cancel');

    // Payments
    Route::get('/registrations/{registration}/invoice', [PaymentController::class, 'showInvoice'])->name('invoices.show');
    Route::post('/registrations/{registration}/pay', [PaymentController::class, 'processMockPayment'])->name('payments.process');

    Route::get('/waitlist/accept/{token}', [WaitlistController::class, 'accept'])->name('waitlist.accept');
    Route::get('/waitlist/decline/{token}', [WaitlistController::class, 'decline'])->name('waitlist.decline');

    Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');

    // Children Management
    Route::post('/children', [ChildController::class, 'store'])->name('children.store');
    Route::delete('/children/{child}', [ChildController::class, 'destroy'])->name('children.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // User Management
    Route::get('/users', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'users'])->name('admin.users.index');
    Route::get('/users/{user}', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'showUser'])->name('admin.users.show');
    Route::post('/users/{user}/role', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'updateUserRole'])->name('admin.users.role');

    // Activity Management
    Route::get('/activities', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'activities'])->name('admin.activities.index');
    Route::get('/activities/create', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'createActivity'])->name('admin.activities.create');
    Route::post('/activities', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'storeActivity'])->name('admin.activities.store');
    Route::get('/activities/{activity}/edit', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'editActivity'])->name('admin.activities.edit');
    Route::put('/activities/{activity}', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'updateActivity'])->name('admin.activities.update');
    Route::delete('/activities/{activity}', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'destroyActivity'])->name('admin.activities.destroy');

    Route::post('/users/admin', [AdminUsersController::class, 'store'])->name('admin.users.store');

    // Attendance
    Route::get('/check-in/{token}', [AttendanceController::class, 'scan'])->name('admin.checkin.scan');
    Route::post('/registrations/{registration}/absent', [AttendanceController::class, 'markAbsent'])->name('admin.registrations.absent');

    // Activity Details & Specialized actions
    Route::get('/activities/{activity}/show', [AdminActivityController::class, 'show'])->name('admin.activities.show_details');
    Route::get('/activities/{activity}/export', [AdminActivityController::class, 'export'])->name('admin.activities.export');
    Route::post('/registrations/{registration}/promote', [AdminActivityController::class, 'promote'])->name('admin.registrations.promote');
});
