<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SensorReadingController;

Route::view('/', 'dashboard');

Route::view('/offline', 'offline')
    ->name('offline');

Route::get('/readings/latest', [
    SensorReadingController::class,
    'latest'
]);

Route::get('/readings/history', [
    SensorReadingController::class,
    'history'
]);
