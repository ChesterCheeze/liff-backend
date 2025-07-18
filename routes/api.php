<?php

use App\Http\Controllers\Api\V1\Admin\AnalyticsController;
use App\Http\Controllers\Api\V1\Admin\QuestionController;
use App\Http\Controllers\Api\V1\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\SurveyController;
use App\Http\Controllers\Api\V1\SurveyResponseController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Legacy routes (for backward compatibility)
Route::post('/login', [LoginController::class, 'apiLogin']);
Route::post('/lineuser', [App\Http\Controllers\LineOAUserController::class, 'store'])->name('api.line.user');
Route::middleware('auth:sanctum')->post('/survey/response', [App\Http\Controllers\Api\SurveyResponseController::class, 'store'])->name('api.survey.response');

// API V1 Routes
Route::prefix('v1')->group(function () {

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/admin/login', [AuthController::class, 'adminLogin']);
        Route::post('/line', [AuthController::class, 'lineAuth']);

        Route::middleware('api.auth')->group(function () {
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refreshToken']);
        });
    });

    // Public survey routes (no authentication required)
    Route::prefix('surveys')->group(function () {
        Route::get('/', [SurveyController::class, 'index']);
        Route::get('/{survey}', [SurveyController::class, 'show']);
    });

    // Authenticated routes
    Route::middleware('api.auth')->group(function () {

        // Survey responses
        Route::prefix('survey-responses')->group(function () {
            Route::post('/', [SurveyResponseController::class, 'store']);
            Route::get('/{surveyResponse}', [SurveyResponseController::class, 'show']);
        });

        // Admin routes
        Route::middleware('api.admin')->prefix('admin')->group(function () {

            // Survey management
            Route::prefix('surveys')->group(function () {
                Route::get('/', [AdminSurveyController::class, 'index']);
                Route::post('/', [AdminSurveyController::class, 'store']);
                Route::get('/{survey}', [AdminSurveyController::class, 'show']);
                Route::put('/{survey}', [AdminSurveyController::class, 'update']);
                Route::delete('/{survey}', [AdminSurveyController::class, 'destroy']);
                Route::put('/{survey}/status', [AdminSurveyController::class, 'updateStatus']);
                Route::get('/{survey}/analytics', [AdminSurveyController::class, 'analytics']);

                // Survey questions
                Route::get('/{survey}/questions', [QuestionController::class, 'index']);
                Route::post('/{survey}/questions', [QuestionController::class, 'store']);
            });

            // Question management
            Route::prefix('questions')->group(function () {
                Route::get('/{question}', [QuestionController::class, 'show']);
                Route::put('/{question}', [QuestionController::class, 'update']);
                Route::delete('/{question}', [QuestionController::class, 'destroy']);
            });

            // User management
            Route::prefix('users')->group(function () {
                Route::get('/', [UserController::class, 'index']);
                Route::get('/{user}', [UserController::class, 'show']);
                Route::put('/{user}', [UserController::class, 'update']);
                Route::delete('/{user}', [UserController::class, 'destroy']);
            });

            // LINE user management
            Route::prefix('line-users')->group(function () {
                Route::get('/', [UserController::class, 'lineUsers']);
                Route::get('/{lineUser}', [UserController::class, 'showLineUser']);
            });

            // Analytics
            Route::prefix('analytics')->group(function () {
                Route::get('/dashboard', [AnalyticsController::class, 'dashboard']);
                Route::get('/surveys/{survey}/responses', [AnalyticsController::class, 'surveyResponses']);
                Route::get('/surveys/{survey}/stats', [AnalyticsController::class, 'surveyStats']);
                Route::post('/export', [AnalyticsController::class, 'export']);
            });
        });
    });
});

// Legacy user route (for backward compatibility)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
