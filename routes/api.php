<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InternController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public API routes
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::get('/interns/check-availability', [InternController::class, 'checkAvailability']);
Route::get('/documents/certificates/{filename}', [DocumentController::class, 'viewCertificate']);
Route::get('/documents/templates/{filename}', [DocumentController::class, 'viewTemplate']);

// PENTING: Definisikan rute yang sesuai dengan JavaScript Anda
// Rute untuk assessments diluar middleware saja agar bisa diakses
Route::get('/assessments/intern/{id_magang}', [AssessmentController::class, 'getByInternId']);
Route::put('/assessments/update-nilai/{id}', [AssessmentController::class, 'updateScore']);
Route::get('/history/scores', [AssessmentController::class, 'getHistoryScores']);
Route::get('/assessments/certificate/{id_magang}', [AssessmentController::class, 'generateCertificate']);

// API routes that use traditional web auth (session based)
Route::middleware('auth')->group(function () {
    // Admin management routes (using web auth)
    Route::prefix('admin')->middleware('role:superadmin')->group(function () {
        Route::get('/', [AdminController::class, 'getAdminApi']);
        Route::post('/', [AdminController::class, 'addAdminApi']);
        Route::patch('/{id}', [AdminController::class, 'editAdminApi']);
        Route::delete('/{id}', [AdminController::class, 'deleteAdminApi']);
    });
    
    // Intern routes
    Route::prefix('interns')->group(function () {
        Route::get('/', [InternController::class, 'getAll']);
        Route::get('/detailed-stats', [InternController::class, 'getDetailedStats']);
        Route::get('/riwayat-data', [InternController::class, 'getHistory']);
        Route::get('/completing-soon', [InternController::class, 'getCompletingSoon']);
        Route::get('/mentors', [InternController::class, 'getMentors']);
        Route::get('/export', [ReportController::class, 'exportInternsScore']);
        Route::post('/generate-receipt', [ReportController::class, 'generateReceipt']);
        Route::get('/{id}', [InternController::class, 'getDetail']);
        
        // Routes restricted to superadmin and admin roles
        Route::middleware('role:superadmin,admin')->group(function () {
            Route::post('/add', [InternController::class, 'add']);
            Route::put('/{id}', [InternController::class, 'update']);
            Route::delete('/{id}', [InternController::class, 'delete']);
            Route::patch('/missing/{id}', [InternController::class, 'setMissingStatus']);
        });
    });
    
    // Assessment routes - Tetap dipertahankan di dalam auth untuk backward compatibility
    Route::prefix('assessments')->group(function () {
        Route::get('/rekap-nilai', [AssessmentController::class, 'getRekapNilai']);
        // Route::get('/certificate/{id_magang}', [AssessmentController::class, 'generateCertificate']);
        
        // Routes restricted to superadmin and admin roles
        Route::middleware('role:superadmin,admin')->group(function () {
            Route::post('/add-score/{id}', [AssessmentController::class, 'addScore']);
        });
    });

    // Notification routes
    Route::prefix('notifications')->group(function() {
        Route::get('/', [App\Http\Controllers\NotificationController::class, 'getNotifications']);
        Route::get('/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount']);
        Route::put('/{id}/mark-as-read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
        Route::put('/mark-all-as-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    });
    
    // Settings routes
    Route::prefix('settings')->group(function () {
        Route::get('/profile', [App\Http\Controllers\SettingsController::class, 'getProfile']);
        Route::put('/profile', [App\Http\Controllers\SettingsController::class, 'editProfileApi']);
        Route::post('/profile/picture', [App\Http\Controllers\SettingsController::class, 'uploadProfilePictureApi']);
        Route::delete('/profile/picture', [App\Http\Controllers\SettingsController::class, 'deletePhotoApi']);
        Route::put('/password', [App\Http\Controllers\SettingsController::class, 'changePasswordApi']);
    });
});

// Web routes (bukan API) untuk sub-menu, sebaiknya pindahkan ke web.php
Route::middleware(['auth', 'web'])->group(function () {
    // Untuk submenu Data Magang
    Route::get('/interns/management', [InternController::class, 'managementIndex'])->name('interns.management');
    Route::get('/interns/positions', [InternController::class, 'positionsIndex'])->name('interns.positions');

    // Untuk submenu Riwayat
    Route::get('/history/data', [InternController::class, 'historyDataIndex'])->name('history.data');
    Route::get('/history/scores', [AssessmentController::class, 'scoresIndex'])->name('history.scores');
});

Route::get('/export', [ReportController::class, 'exportInternsScore']);