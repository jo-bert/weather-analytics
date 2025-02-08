<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\HourlyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('location')->group(function () {
    Route::get('/', [LocationController::class, 'index'])->name('locations.index');
    Route::post('/', [LocationController::class, 'submit'])->name('locations.submit');
});

Route::get('/hourly-forecasts/range', [HourlyController::class, 'getByRange']);
Route::get('/hourly-forecasts', [HourlyController::class, 'index']);
Route::post('/hourly-forecasts', [HourlyController::class, 'store'])->middleware(['auth', 'verified']);
Route::get('/hourly-forecasts/{id}', [HourlyController::class, 'show']);
Route::put('/hourly-forecasts/{id}', [HourlyController::class, 'update'])->middleware(['auth', 'verified']);
Route::delete('/hourly-forecasts/{id}', [HourlyController::class, 'destroy'])->middleware(['auth', 'verified']);
require __DIR__ . '/auth.php';
