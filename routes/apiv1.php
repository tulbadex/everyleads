<?php

use App\Http\Controllers\API\V1\{AuthController, LeadController, UserController};
// use App\Http\Controllers\API\V1\LeadController;
use Illuminate\Support\Facades\Route;

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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// protected route
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', [UserController::class, 'index']);
    Route::post('/user', [UserController::class, 'create']);
    Route::put('/user/{user}', [UserController::class, 'update']);
    Route::delete('/user/{user}', [UserController::class, 'destroy']);
    Route::get('/user/{user}', [UserController::class, 'show']);
});

Route::get('/leads', [LeadController::class, 'index'])->middleware('auth:sanctum');
Route::post('/leads', [LeadController::class, 'create'])->middleware('auth:sanctum');
Route::get('/leads/{lead}', [LeadController::class, 'show'])->middleware('auth:sanctum');

Route::put('/leads/{lead}', [LeadController::class, 'update'])->middleware('auth:sanctum');
Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->middleware('auth:sanctum');
