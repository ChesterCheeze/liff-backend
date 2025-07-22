<?php

use App\Http\Controllers\Api\V1\Admin\AnalyticsController;
use App\Http\Controllers\Api\V1\Admin\ExportController;
use App\Http\Controllers\Api\V1\Admin\ImportController;
use App\Http\Controllers\Api\V1\Admin\QuestionController;
use App\Http\Controllers\Api\V1\Admin\SurveyController as AdminSurveyController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\SurveyController;
use App\Http\Controllers\Api\V1\SurveyResponseController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
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

// Broadcast authentication
Route::middleware('api.auth')->post('/broadcasting/auth', function (Request $request) {
    return Broadcast::auth($request);
});

// API V1 Routes
Route::prefix('v1')->group(function () {

    // Authentication routes - strict rate limiting
    Route::prefix('auth')->middleware('rate.limit:auth,5,1')->group(function () {
        Route::post('/admin/login', [AuthController::class, 'adminLogin']);
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/verify-email', [AuthController::class, 'verifyEmail']);
        Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/line', [AuthController::class, 'lineAuth']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refreshToken']);
        });
    });

    // Public survey routes - moderate rate limiting
    Route::prefix('surveys')->middleware('rate.limit:public,30,1')->group(function () {
        Route::get('/', [SurveyController::class, 'index']);
        Route::get('/{survey}', [SurveyController::class, 'show']);
    });

    // Authenticated routes - standard rate limiting
    Route::middleware(['api.auth', 'rate.limit:api,60,1'])->group(function () {

        // Broadcast authentication for authenticated users
        Route::post('/broadcasting/auth', function (Request $request) {
            return Broadcast::auth($request);
        });

        // Survey responses - stricter rate limiting for submissions
        Route::prefix('survey-responses')->group(function () {
            Route::post('/', [SurveyResponseController::class, 'store'])->middleware('rate.limit:submission,10,1');
            Route::get('/{surveyResponse}', [SurveyResponseController::class, 'show']);
        });

        // Admin routes - higher rate limits for authenticated admins
        Route::middleware(['api.admin', 'rate.limit:admin,120,1'])->prefix('admin')->group(function () {

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

            // Export functionality - lower rate limits for resource-intensive operations
            Route::prefix('export')->middleware('rate.limit:export,10,5')->group(function () {
                Route::post('/surveys', [ExportController::class, 'surveys']);
                Route::post('/surveys/{survey}/responses', [ExportController::class, 'surveyResponses']);
                Route::post('/responses', [ExportController::class, 'allResponses']);
                Route::post('/analytics', [ExportController::class, 'analytics']);
            });

            // Import functionality - very low rate limits for heavy operations
            Route::prefix('import')->middleware('rate.limit:import,5,10')->group(function () {
                Route::post('/surveys', [ImportController::class, 'surveys']);
                Route::post('/surveys/{survey}/questions', [ImportController::class, 'surveyQuestions']);
                Route::post('/validate', [ImportController::class, 'validateFile']);
                Route::get('/templates', [ImportController::class, 'downloadTemplate']);
            });
        });
    });
});

// Legacy user route (for backward compatibility)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
