<?php

use App\Http\Controllers\Admin\AdminActivityController;
use App\Http\Controllers\Admin\AdminChildController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\WaitlistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/registrations/{registration}/calendar', [RegistrationController::class, 'calendar'])
    ->middleware('signed')
    ->name('registrations.calendar');
Route::get('/check-in/kiosk/{token}', [AttendanceController::class, 'kioskEntry'])->name('checkin.kiosk.entry');
Route::post('/check-in/kiosk/{token}/phone', [AttendanceController::class, 'kioskCheckInByPhone'])->name('checkin.kiosk.phone');
Route::post('/check-in/kiosk/{token}/token', [AttendanceController::class, 'kioskCheckInByToken'])->name('checkin.kiosk.token');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.submit');
    Route::get('/registration', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/registration', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.submit');

    Route::get('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'sendResetLinkEmail'])->middleware('throttle:6,1')->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'reset'])->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::post('/registrations/{registration}/cancel', [RegistrationController::class, 'cancel'])->name('registrations.cancel');

    Route::get('/waitlist/accept/{token}', [WaitlistController::class, 'accept'])->name('waitlist.accept');

    Route::get('/waitlist/decline/{token}', [WaitlistController::class, 'decline'])->name('waitlist.decline');

    Route::post('/media/upload', [MediaController::class, 'upload'])->middleware('role:admin,instructor')->name('media.upload');

    Route::post('/children', [ChildController::class, 'store'])->name('children.store');
    Route::delete('/children/{child}', [ChildController::class, 'destroy'])->name('children.destroy');
});

Route::middleware(['auth', 'role:admin,instructor'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('admin.analytics');
    Route::get('/search', [GlobalSearchController::class, 'index'])->name('admin.search');

    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('admin.users.index');
        Route::get('/blacklist', [AdminDashboardController::class, 'blacklist'])->name('admin.blacklist.index');
        Route::get('/users/{user}', [AdminDashboardController::class, 'showUser'])->name('admin.users.show');
        Route::post('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('admin.users.update');
        Route::post('/users/{user}/role', [AdminDashboardController::class, 'updateUserRole'])->name('admin.users.role');
        Route::post('/registrations/{registration}/status', [AdminDashboardController::class, 'updateRegistrationStatus'])->name('admin.registrations.status');
        Route::get('/stats', [AdminDashboardController::class, 'stats'])->name('admin.stats');
        Route::post('/users/admin', [AdminUsersController::class, 'store'])->name('admin.users.store');
        Route::post('/children/{child}', [AdminChildController::class, 'update'])->name('admin.children.update');
        Route::post('/children/{child}/clear-restriction', [AdminChildController::class, 'clearRestriction'])->name('admin.children.clear_restriction');
        Route::post('/blacklist/{child}/restrict', [AdminDashboardController::class, 'restrictChild'])->name('admin.blacklist.restrict');
        Route::post('/system/backup', [AdminDashboardController::class, 'backupNow'])->name('admin.system.backup');
        Route::post('/system/restore-latest', [AdminDashboardController::class, 'restoreLatestBackup'])->name('admin.system.restore_latest');
    });

    Route::get('/activities', [AdminDashboardController::class, 'activities'])->name('admin.activities.index');
    Route::get('/activities/archive', [AdminDashboardController::class, 'archivedActivities'])->name('admin.activities.archived');
    Route::get('/activities/create', [AdminDashboardController::class, 'createActivity'])->name('admin.activities.create');
    Route::post('/activities', [AdminDashboardController::class, 'storeActivity'])->name('admin.activities.store');
    Route::get('/activities/{activity}/edit', [AdminDashboardController::class, 'editActivity'])->name('admin.activities.edit');
    Route::put('/activities/{activity}', [AdminDashboardController::class, 'updateActivity'])->name('admin.activities.update');
    Route::delete('/activities/{activity}', [AdminDashboardController::class, 'destroyActivity'])->name('admin.activities.destroy');
    Route::post('/activities/{activity}/duplicate', [AdminDashboardController::class, 'duplicate'])->name('admin.activities.duplicate');
    Route::post('/activities/{activity}/postpone', [AdminDashboardController::class, 'postpone'])->name('admin.activities.postpone');

    Route::get('/check-in/tablet', [AttendanceController::class, 'tabletIndex'])->name('admin.checkin.tablet');
    Route::get('/check-in/tablet/{activity}/public', [AttendanceController::class, 'tabletPublic'])->name('admin.checkin.tablet_public');
    Route::get('/check-in/{token}', [AttendanceController::class, 'scan'])
        ->where('token', '[A-Za-z0-9]{32}')
        ->name('admin.checkin.scan');
    Route::post('/registrations/{registration}/absent', [AttendanceController::class, 'markAbsent'])->name('admin.registrations.absent');
    Route::post('/registrations/{registration}/unmark-attendance', [AttendanceController::class, 'unmarkAttendance'])->name('admin.registrations.unmark_attendance');

    Route::get('/activities/{activity}/show', [AdminActivityController::class, 'show'])->name('admin.activities.show_details');
    Route::get('/activities/{activity}/export', [AdminActivityController::class, 'export'])->name('admin.activities.export');
    Route::post('/registrations/{registration}/promote', [AdminActivityController::class, 'promote'])->name('admin.registrations.promote');
    Route::post('/registrations/mark-attended', [AdminActivityController::class, 'markAsAttended'])->name('admin.registrations.mark_attended');
    Route::post('/registrations/{registration}/mark-paid', [AdminActivityController::class, 'markAsPaid'])->name('admin.registrations.mark_paid');
    Route::post('/registrations/{registration}/mark-unpaid', [AdminActivityController::class, 'markAsUnpaid'])->name('admin.registrations.mark_unpaid');
});
