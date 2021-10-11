<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// protected route
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Leads fetch
Route::get('/leads', [LeadController::class, 'index']);
Route::post('/leads', [LeadController::class, 'create'])->middleware('auth:sanctum');

Route::put('/leads/{lead}', [LeadController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->middleware('auth:sanctum');
