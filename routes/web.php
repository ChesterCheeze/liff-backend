<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/survey/create', [App\Http\Controllers\Api\SurveyController::class, 'create']);
Route::get('/survey', [App\Http\Controllers\Api\SurveyController::class, 'showSurveyTable']);
Route::get('/survey/{id}/edit', [App\Http\Controllers\Api\SurveyController::class, 'edit'])->name('survey.edit');
Route::post('/survey/store', [App\Http\Controllers\Api\SurveyController::class, 'store'])->name('survey.store');
Route::get('/survey/{id}/questions', [App\Http\Controllers\SurveyQuestionController::class, 'index'])->name('survey.questions');
Route::post('/questions/store', [App\Http\Controllers\SurveyQuestionController::class, 'store'])->name('questions.store');
