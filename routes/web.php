<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/qr', function () {
    return view('error');
});

Route::get('/admin/login', function () {
    return view('auth.admin-login');
});

Route::get('/congrats', function () {
    return view('congrats');
})->name('congrats');
Route::get('/roulette', 'App\Http\Controllers\StationController@roulette')->name('rollet');
Route::get('/test-roulette', 'App\Http\Controllers\StationController@testRoulette')->name('test.roulette');
Route::post('/stock', 'App\Http\Controllers\StationController@stock')->name('stock');

Route::post('/checkExisting', 'App\Http\Controllers\StationController@checkExisting')->name('checkExisting');

// Called by the local server.js NFC relay — no session auth, protected by X-RFID-Token header
Route::post('/rfid/receive', 'App\Http\Controllers\StationController@receiveRfid')->name('rfid.receive');

// Station kiosk pages — open one per physical station device, no login required
Route::get('/admin/kiosk/{station}', 'App\Http\Controllers\StationController@kiosk')->name('kiosk');
Route::post('/admin/kiosk/tap', 'App\Http\Controllers\StationController@rfidTap')->name('rfid.tap');

Route::group(['middleware' => ['admin']], function () {
    Route::get('/admin', 'App\Http\Controllers\StationController@admin')->name('admin');
     Route::get('/admin/refill-logs', 'App\Http\Controllers\StationController@refillLogs')->name('refill.logs');
    Route::get('/admin/users', 'App\Http\Controllers\StationController@users')->name('users');
    Route::get('/admin/scanner', 'App\Http\Controllers\StationController@scanner')->name('scanner');
    Route::get('/admin/stocks', 'App\Http\Controllers\StationController@stocks')->name('stocks');
    Route::get('/admin/rfid', 'App\Http\Controllers\StationController@rfidAdmin')->name('rfid.admin');
    Route::post('/admin/rfid/assign', 'App\Http\Controllers\StationController@assignRfid')->name('rfid.assign');
    Route::post('/admin/rfid/unlink', 'App\Http\Controllers\StationController@unlinkRfid')->name('rfid.unlink');
    Route::post('/admin/rfid/check', 'App\Http\Controllers\StationController@checkRfid')->name('rfid.check');

    Route::post('/admin/refill', 'App\Http\Controllers\StationController@refill')->name('refill');

    Route::get('/admin/{user}', 'App\Http\Controllers\StationController@userData')->name('userData');
    Route::post('/admin/check', 'App\Http\Controllers\StationController@check')->name('check');
});

Route::group(['middleware' => ['client']], function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/station/{station}', 'App\Http\Controllers\StationController@index')->name('station.show');
    Route::get('/dashboard', 'App\Http\Controllers\StationController@welcome')->name('dashboard');
    Route::get('/landing', 'App\Http\Controllers\StationController@landing')->name('landing');
    Route::post('/process_qr_code', 'App\Http\Controllers\StationController@scan')->name('process_qr_code');
});

require __DIR__ . '/auth.php';
