<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate']);
Route::get('/register', [LoginController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [LoginController::class, 'register']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin authentication routes
Route::get('/admin/login', [LoginController::class, 'showAdminLoginForm'])->name('admin.login')->middleware('guest');
Route::post('/admin/login', [LoginController::class, 'authenticateAdmin'])->middleware('guest');
Route::post('/admin/logout', [LoginController::class, 'logoutAdmin'])->name('admin.logout');

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/survey/create', [App\Http\Controllers\Api\SurveyController::class, 'create'])->name('survey.create');
Route::get('/survey', [App\Http\Controllers\Api\SurveyController::class, 'showSurveyTable']);
Route::get('/survey/{id}/edit', [App\Http\Controllers\Api\SurveyController::class, 'edit'])->name('survey.edit');
Route::put('/survey/{id}/edit', [App\Http\Controllers\Api\SurveyController::class, 'update'])->name('survey.update');
Route::post('/survey/store', [App\Http\Controllers\Api\SurveyController::class, 'store'])->name('survey.store');
Route::get('/survey/{id}/questions', [App\Http\Controllers\SurveyQuestionController::class, 'index'])->name('survey.questions');
Route::delete('/survey/{id}', [App\Http\Controllers\Api\SurveyController::class, 'destroy'])->name('survey.delete');
Route::post('/questions/store', [App\Http\Controllers\SurveyQuestionController::class, 'store'])->name('questions.store');
Route::get('/questions/{id}/edit', [App\Http\Controllers\SurveyQuestionController::class, 'edit'])->name('questions.edit');
Route::put('/questions/{id}', [App\Http\Controllers\SurveyQuestionController::class, 'update'])->name('question.update');
Route::delete('/questions/{id}', [App\Http\Controllers\SurveyQuestionController::class, 'destroy'])->name('question.delete');

Route::get('/survey/{id}', [App\Http\Controllers\Api\SurveyController::class, 'show'])->name('api.survey.show');

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // User management routes
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Survey management routes
    Route::get('/surveys', [App\Http\Controllers\Admin\SurveyController::class, 'index'])->name('surveys.index');
    Route::get('/surveys/{survey}', [App\Http\Controllers\Admin\SurveyController::class, 'show'])->name('surveys.show');
    Route::get('/surveys/{survey}/responses', [App\Http\Controllers\Admin\SurveyController::class, 'responses'])->name('surveys.responses');
    Route::delete('/surveys/{survey}', [App\Http\Controllers\Admin\SurveyController::class, 'destroy'])->name('surveys.destroy');
    Route::get('/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics');
});
