<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\LoginController;

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

Route::get('/', function () {
    return view('welcome');
});

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

