<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InternController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\ReportController;

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
        Route::get('/{id}', [InternController::class, 'getDetail']);
        
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
        
        // Routes restricted to superadmin and admin roles
        Route::middleware('role:superadmin,admin')->group(function () {
            Route::post('/add-score/{id}', [AssessmentController::class, 'addScore']);
            Route::put('/update-nilai/{id}', [AssessmentController::class, 'updateScore']);
        });
    });

    // API routes untuk pengaturan profil
Route::middleware('auth:sanctum')->prefix('settings')->group(function () {
    Route::get('/profile', [App\Http\Controllers\SettingsController::class, 'getProfile']);
    Route::put('/profile', [App\Http\Controllers\SettingsController::class, 'editProfileApi']);
    Route::post('/profile/picture', [App\Http\Controllers\SettingsController::class, 'uploadProfilePictureApi']);
    Route::delete('/profile/picture', [App\Http\Controllers\SettingsController::class, 'deletePhotoApi']);
    Route::put('/password', [App\Http\Controllers\SettingsController::class, 'changePasswordApi']);
});
    
    // Report routes
    Route::prefix('reports')->group(function () {
        Route::get('/export', [ReportController::class, 'exportInternsScore']);
        Route::post('/generate-receipt', [ReportController::class, 'generateReceipt']);
    });
});

// Public route for checking availability (if needed without auth)
Route::get('/interns/check-availability', [InternController::class, 'checkAvailability']);