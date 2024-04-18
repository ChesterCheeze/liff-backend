<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->post('/lineuser', [App\Http\Controllers\LineOAUserController::class, 'store'])->name('api.line.user');
Route::middleware('auth:sanctum')->post('/survey/response', [App\Http\Controllers\Api\SurveyResponseController::class, 'store'])->name('api.survey.response');
