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

Route::get('/survey/create', [App\Http\Controllers\Api\SurveyController::class, 'show'])->name('survey.create');
Route::get('/survey', [App\Http\Controllers\Api\SurveyController::class, 'showSurveyTable']);
Route::post('/survey', [App\Http\Controllers\Api\SurveyController::class, 'store'])->name('survey.store');
