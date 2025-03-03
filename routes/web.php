<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\HourlyController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LocationController::class, 'index'])->name('locations.index');
Route::post('/', [LocationController::class, 'submit'])->name('locations.submit');

Route::get('/hourly-forecasts/range', [HourlyController::class, 'getByRange']);
Route::get('/hourly-forecasts', [HourlyController::class, 'index']);
Route::post('/hourly-forecasts', [HourlyController::class, 'store']);
Route::get('/hourly-forecasts/{id}', [HourlyController::class, 'show']);
Route::put('/hourly-forecasts/{id}', [HourlyController::class, 'update']);
Route::delete('/hourly-forecasts/{id}', [HourlyController::class, 'destroy']);

Route::resource('alerts', AlertController::class);

Route::put('/alerts/{id}/pause', [AlertController::class, 'pause'])->name('alerts.pause');
Route::put('/alerts/{id}/resume', [AlertController::class, 'resume'])->name('alerts.resume');
Route::delete('/alerts/{id}', [AlertController::class, 'destroy'])->name('alerts.destroy');
require __DIR__ . '/auth.php';
