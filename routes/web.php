<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/test', function () {
    return 'Aplikasi bekerja dengan baik!';
});

// Rute publik
Route::get('/', function () {
    return redirect('/login');
});

// Rute autentikasi untuk guest (belum login)
Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Semua rute reset password harus di luar middleware auth
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendOTP'])->name('password.email');
    Route::get('/verify-otp', [AuthController::class, 'showOTPForm'])->name('password.otp');
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP'])->name('password.verify');
    Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    Route::get('/interns', [App\Http\Controllers\InternController::class, 'getAll'])->name('interns.index');
    Route::get('/interns/{id}', [App\Http\Controllers\InternController::class, 'getDetail'])->name('interns.show');
    Route::get('/history', [App\Http\Controllers\InternController::class, 'getHistory'])->name('history.index');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\AdminController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\AdminController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [App\Http\Controllers\AdminController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\AdminController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\AdminController::class, 'destroy'])->name('destroy');
});

use App\Http\Controllers\NotificationController;

Route::middleware('auth:api')->group(function () {
    Route::post('/notifications', [NotificationController::class, 'createNotification']);
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::put('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
});


// Web Routes
Route::get('/dashboard/interns', [App\Http\Controllers\InternController::class, 'index'])->name('interns.management');
Route::get('/dashboard/interns/positions', [App\Http\Controllers\InternController::class, 'checkPositionsPage'])->name('interns.positions');
Route::get('/dashboard/interns/add', [App\Http\Controllers\InternController::class, 'addPage'])->name('interns.add');
Route::get('/dashboard/interns/edit/{id}', [App\Http\Controllers\InternController::class, 'editPage'])->name('interns.edit');
Route::get('/dashboard/interns/detail/{id}', [App\Http\Controllers\InternController::class, 'detailPage'])->name('interns.detail');
// Rute untuk tanda terima
// Route::get('/dashboard/interns/generate-receipt', [App\Http\Controllers\InternController::class, 'generateReceiptPage'])->name('interns.generate-receipt');
Route::post('interns/download-receipt', [InternController::class, 'downloadReceipt'])->name('interns.download-receipt');
Route::post('/dashboard/interns/download-receipt', [App\Http\Controllers\InternController::class, 'generateReceipt'])->name('interns.download-receipt');
// API Routes
Route::prefix('api')->group(function () {
    Route::get('/interns', [App\Http\Controllers\InternController::class, 'getAll'])->name('api.interns.getAll');
    Route::post('/interns/add', [App\Http\Controllers\InternController::class, 'add'])->name('api.interns.add');
    Route::get('/interns/detail/{id}', [App\Http\Controllers\InternController::class, 'getDetail'])->name('api.interns.getDetail');
    Route::match(['POST', 'PUT'], '/interns/update/{id}', [App\Http\Controllers\InternController::class, 'update'])->name('api.interns.update');
    Route::delete('/interns/delete/{id}', [App\Http\Controllers\InternController::class, 'delete'])->name('api.interns.delete');
    Route::post('/interns/missing/{id}', [App\Http\Controllers\InternController::class, 'setMissingStatus'])->name('api.interns.setMissing');
    Route::get('/interns/check-availability', [App\Http\Controllers\InternController::class, 'checkAvailability'])->name('api.interns.checkAvailability');
    Route::get('/interns/stats', [App\Http\Controllers\InternController::class, 'getDetailedStats'])->name('api.interns.getStats');
    Route::get('/interns/mentors', [App\Http\Controllers\InternController::class, 'getMentors'])->name('api.interns.getMentors');
    Route::get('/interns/completing-soon', [App\Http\Controllers\InternController::class, 'getCompletingSoon'])->name('api.interns.getCompletingSoon');
    Route::get('/interns/history', [App\Http\Controllers\InternController::class, 'getHistory'])->name('api.interns.getHistory');
});
// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
     // Basic settings routes
     Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
     Route::post('/settings/update-profile', [App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('settings.update-profile');
     Route::post('/settings/upload-photo', [App\Http\Controllers\SettingsController::class, 'uploadProfilePicture'])->name('settings.upload-photo');
     Route::delete('/settings/delete-photo', [App\Http\Controllers\SettingsController::class, 'deletePhoto'])->name('settings.delete-photo');
     Route::post('/settings/change-password', [App\Http\Controllers\SettingsController::class, 'changePassword'])->name('settings.change-password');
 
    // Template routes
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/update-profile', [App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('settings.update-profile');
    Route::post('/settings/upload-photo', [App\Http\Controllers\SettingsController::class, 'uploadPhoto'])->name('settings.upload-photo');
    Route::delete('/settings/delete-photo', [App\Http\Controllers\SettingsController::class, 'deletePhoto'])->name('settings.delete-photo');
    Route::post('/settings/change-password', [App\Http\Controllers\SettingsController::class, 'changePassword'])->name('settings.change-password');
    Route::post('/settings/upload-template', [App\Http\Controllers\SettingsController::class, 'uploadTemplate'])->name('settings.upload-template');
    Route::get('/settings/preview-template/{id}', [App\Http\Controllers\SettingsController::class, 'previewTemplate'])->name('settings.preview-template');
    Route::delete('/settings/delete-template/{id}', [App\Http\Controllers\SettingsController::class, 'deleteTemplate'])->name('settings.delete-template');
});

// Rute yang memerlukan autentikasi
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Sementara redirect ke login untuk dashboard
    Route::get('/dashboard', function() {
        return view('dashboard'); 
    })->name('dashboard');
});