<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InternController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| 
| Rute yang diorganisasi untuk aplikasi PANDU - Platform Magang Dinas Pendidikan
|
*/

// Rute pengujian
Route::get('/test', function () {
    return 'Aplikasi bekerja dengan baik!';
});

// Rute publik / redirect ke login
Route::get('/', function () {
    return redirect('/login');
});

// Rute autentikasi untuk guest (belum login)
Route::middleware(['guest'])->group(function () {
    // Login routes
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Reset password routes
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendOTP'])->name('password.email');
    Route::get('/verify-otp', [AuthController::class, 'showOTPForm'])->name('password.otp');
    Route::post('/verify-otp', [AuthController::class, 'verifyOTP'])->name('password.verify');
    Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Rute yang memerlukan autentikasi
Route::middleware(['auth'])->group(function () {
    // Logout route
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Manajemen Peserta Magang
    Route::prefix('dashboard/interns')->name('interns.')->group(function () {
        Route::get('/', [InternController::class, 'index'])->name('management');
        Route::get('/positions', [InternController::class, 'checkPositionsPage'])->name('positions');
        Route::get('/add', [InternController::class, 'addPage'])->name('add');
        Route::get('/edit/{id}', [InternController::class, 'editPage'])->name('edit');
        Route::get('/detail/{id}', [InternController::class, 'detailPage'])->name('detail');
        
        // Tanda terima
        Route::post('/download-receipt', [InternController::class, 'generateReceipt'])->name('download-receipt');
    });
    
    Route::prefix('notifications')->name('notifications.')->group(function() {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\NotificationController::class, 'show'])->name('show');
    });
    
    // Riwayat
    Route::get('/history/data', [InternController::class, 'historyDataIndex'])->name('history.data');
    Route::get('/history/scores', [AssessmentController::class, 'scoresIndex'])->name('history.scores');
    
    // Penilaian
    Route::post('/assessments/add-score/{id}', [AssessmentController::class, 'addScore'])->name('assessments.add-score');
    Route::get('/nilai/edit/{id}', [AssessmentController::class, 'editPage'])->name('nilai.edit');
    
    // Pengaturan
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/update-profile', [SettingsController::class, 'updateProfile'])->name('update-profile');
        Route::post('/upload-photo', [SettingsController::class, 'uploadPhoto'])->name('upload-photo');
        Route::delete('/delete-photo', [SettingsController::class, 'deletePhoto'])->name('delete-photo');
        Route::post('/change-password', [SettingsController::class, 'changePassword'])->name('change-password');
    });
    
    // Template Document Management
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::post('/upload', [DocumentController::class, 'uploadTemplate'])->name('upload');
        Route::get('/preview/{id}', [DocumentController::class, 'previewTemplate'])->name('preview');
        Route::get('/download/{id}', [DocumentController::class, 'downloadTemplate'])->name('download');
        Route::delete('/delete/{id}', [DocumentController::class, 'deleteTemplate'])->name('delete');
    });
    
    // Sertifikat routes
    Route::prefix('certificates')->name('sertifikat.')->group(function () {
        Route::get('/generate/{id}', [DocumentController::class, 'generateSertifikat'])->name('generate');
        Route::get('/download/{id}', [DocumentController::class, 'downloadSertifikat'])->name('download');
    });
});

// Rute untuk admin
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/create', [AdminController::class, 'create'])->name('create');
    Route::post('/', [AdminController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [AdminController::class, 'edit'])->name('edit');
    Route::put('/{id}', [AdminController::class, 'update'])->name('update');
    Route::delete('/{id}', [AdminController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| 
| API routes untuk komunikasi AJAX di aplikasi PANDU
|
*/

// API Routes
Route::prefix('api')->group(function () {
    // Dashboard API
    Route::get('/dashboard/refresh', [DashboardController::class, 'refreshData'])->name('api.dashboard.refresh');
    
    // Interns API
    Route::get('/interns', [InternController::class, 'getAll'])->name('api.interns.getAll');
    Route::post('/interns/add', [InternController::class, 'add'])->name('api.interns.add');
    Route::get('/interns/detail/{id}', [InternController::class, 'getDetail'])->name('api.interns.getDetail');
    Route::match(['POST', 'PUT'], '/interns/update/{id}', [InternController::class, 'update'])->name('api.interns.update');
    Route::delete('/interns/delete/{id}', [InternController::class, 'delete'])->name('api.interns.delete');
    Route::post('/interns/missing/{id}', [InternController::class, 'setMissingStatus'])->name('api.interns.setMissing');
    Route::get('/interns/check-availability', [InternController::class, 'checkAvailability'])->name('api.interns.checkAvailability');
    Route::get('/interns/stats', [InternController::class, 'getDetailedStats'])->name('api.interns.getStats');
    Route::get('/interns/mentors', [InternController::class, 'getMentors'])->name('api.interns.getMentors');
    Route::get('/interns/completing-soon', [InternController::class, 'getCompletingSoon'])->name('api.interns.getCompletingSoon');
    Route::get('/interns/history', [InternController::class, 'getHistory'])->name('api.interns.getHistory');
    
    // Assessment API
    Route::get('/history/scores', [AssessmentController::class, 'getHistoryScores'])->name('api.history.scores');
    Route::put('/assessments/update-nilai/{id}', [AssessmentController::class, 'updateScore'])->name('api.assessments.update');
    
    // Notification API (pastikan middleware auth sudah benar)
    Route::middleware('auth')->group(function() {
        Route::post('/notifications', [NotificationController::class, 'createNotification'])->name('api.notifications.create');
        Route::get('/notifications', [NotificationController::class, 'getNotifications'])->name('api.notifications.get');
        Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('api.notifications.unread');
        Route::put('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('api.notifications.markAsRead');
        Route::put('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.markAllAsRead');
    });
    
    // Document API
    Route::middleware('auth')->group(function() {
        Route::get('/templates', [DocumentController::class, 'getTemplates'])->name('api.templates.get');
        Route::post('/templates/upload', [DocumentController::class, 'uploadTemplateApi'])->name('api.templates.upload');
        Route::delete('/templates/delete/{id}', [DocumentController::class, 'deleteTemplateApi'])->name('api.templates.delete');
        Route::get('/templates/preview/{id}', [DocumentController::class, 'previewTemplateApi'])->name('api.templates.preview');
    });
});