<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InternController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DocumentController;

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

// Authentication middleware group
Route::middleware('auth:sanctum')->group(function () {
    
    // Intern routes
    Route::prefix('interns')->group(function () {
        // Routes available to all authenticated users
        Route::get('/', [InternController::class, 'getAll']);
        Route::get('/check-availability', [InternController::class, 'checkAvailability']);
        Route::get('/detailed-stats', [InternController::class, 'getDetailedStats']);
        Route::get('/riwayat-data', [InternController::class, 'getHistory']);
        Route::get('/completing-soon', [InternController::class, 'getCompletingSoon']);
        Route::get('/mentors', [InternController::class, 'getMentors']);
        Route::get('/export', [ReportController::class, 'exportInternsScore']);
        Route::post('/generate-receipt', [ReportController::class, 'generateReceipt']);
        Route::get('/{id}', [InternController::class, 'getDetail']);
        
        // New routes for Data Magang sub-menu
        Route::get('/management/data', [InternController::class, 'getManagementData']);
        Route::get('/positions/availability', [InternController::class, 'getPositionsAvailability']);
        
        // Routes restricted to superadmin and admin roles
        Route::middleware('role:superadmin,admin')->group(function () {
            Route::post('/add', [InternController::class, 'add']);
            Route::put('/{id}', [InternController::class, 'update']);
            Route::delete('/{id}', [InternController::class, 'delete']);
            Route::patch('/missing/{id}', [InternController::class, 'setMissingStatus']);
        });
    });
    
    // Assessment routes
    Route::prefix('assessments')->group(function () {
        Route::get('/rekap-nilai', [AssessmentController::class, 'getRekapNilai']);
        
        // Routes for assessment details and certificate
        Route::get('/intern/{id_magang}', [AssessmentController::class, 'getByInternId']);
        Route::get('/certificate/{id_magang}', [AssessmentController::class, 'generateCertificate']);
        
        // Routes restricted to superadmin and admin roles
        Route::middleware('role:superadmin,admin')->group(function () {
            Route::post('/add-score/{id}', [AssessmentController::class, 'addScore']);
            Route::put('/update-nilai/{id}', [AssessmentController::class, 'updateScore']);
        });
    });

    // History routes - new routes for Riwayat sub-menu
    Route::prefix('history')->group(function () {
        Route::get('/data', [InternController::class, 'getHistoryData']);
        Route::get('/scores', [AssessmentController::class, 'getHistoryScores']);
    });

    // API routes untuk pengaturan profil
    Route::prefix('settings')->group(function () {
        Route::get('/profile', [App\Http\Controllers\SettingsController::class, 'getProfile']);
        Route::put('/profile', [App\Http\Controllers\SettingsController::class, 'editProfileApi']);
        Route::post('/profile/picture', [App\Http\Controllers\SettingsController::class, 'uploadProfilePictureApi']);
        Route::delete('/profile/picture', [App\Http\Controllers\SettingsController::class, 'deletePhotoApi']);
        Route::put('/password', [App\Http\Controllers\SettingsController::class, 'changePasswordApi']);
    });
    
    // Admin management routes
    Route::prefix('admin')->middleware('role:superadmin')->group(function () {
        Route::get('/', [AdminController::class, 'getAdmin']);
        Route::post('/', [AdminController::class, 'addAdmin']);
        Route::patch('/{id}', [AdminController::class, 'editAdmin']);
        Route::delete('/{id}', [AdminController::class, 'deleteAdmin']);
        Route::get('/mentors', [InternController::class, 'getMentors']);
    });
    
    // Report routes
    Route::prefix('reports')->group(function () {
        Route::get('/export', [ReportController::class, 'exportInternsScore']);
        Route::post('/generate-receipt', [ReportController::class, 'generateReceipt']);
    });
    
    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'getNotifications']);
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });
    
    // Document routes
    Route::prefix('documents')->group(function () {
        Route::post('/upload', [DocumentController::class, 'uploadTemplate']);
        Route::get('/templates', [DocumentController::class, 'getTemplates']);
        Route::get('/preview/{id}', [DocumentController::class, 'previewDocument']);
        Route::delete('/template/{id}', [DocumentController::class, 'deleteTemplate']);
        Route::post('/generate-sertifikat/{id}', [DocumentController::class, 'generateSertifikat']);
        Route::get('/download-sertifikat/{id_magang}', [DocumentController::class, 'downloadSertifikat']);
    });
});

// Public routes
Route::get('/interns/check-availability', [InternController::class, 'checkAvailability']);
Route::get('/documents/certificates/{filename}', [DocumentController::class, 'viewCertificate']);
Route::get('/documents/templates/{filename}', [DocumentController::class, 'viewTemplate']);

// Web routes (bukan API) untuk sub-menu
Route::middleware(['auth', 'web'])->group(function () {
    // Untuk submenu Data Magang
    Route::get('/interns/management', [InternController::class, 'managementIndex'])->name('interns.management');
    Route::get('/interns/positions', [InternController::class, 'positionsIndex'])->name('interns.positions');

    // Untuk submenu Riwayat
    Route::get('/history/data', [InternController::class, 'historyDataIndex'])->name('history.data');
    Route::get('/history/scores', [AssessmentController::class, 'scoresIndex'])->name('history.scores');
});