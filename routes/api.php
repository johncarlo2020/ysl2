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

// Users without an NFC/RFID card assigned � protected by X-API-Secret header
Route::get('/users/without-nfc', 'App\Http\Controllers\StationController@usersWithoutNfc');


Route::post('/admin/login', [\App\Http\Controllers\Api\AdminController::class, 'login'])->middleware('throttle:5,1');
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::get('/user', [\App\Http\Controllers\Api\AdminController::class, 'me']);
    Route::get('/stations', [\App\Http\Controllers\Api\AdminController::class, 'stations']);
    Route::post('/stations/check-in', [\App\Http\Controllers\Api\AdminController::class, 'checkIn']);
    Route::get('/users', [\App\Http\Controllers\Api\AdminController::class, 'users']);
    Route::get('/users/{user}', [\App\Http\Controllers\Api\AdminController::class, 'show']);
    Route::put('/users/{user}/nfc', [\App\Http\Controllers\Api\AdminController::class, 'assign']);
    Route::delete('/users/{user}/nfc', [\App\Http\Controllers\Api\AdminController::class, 'unassign']);
    Route::post('/logout', [\App\Http\Controllers\Api\AdminController::class, 'logout']);
});
