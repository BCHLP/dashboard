<?php

use App\Enums\PermissionEnum;
use App\Http\Controllers\Auth\VoiceRecognitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\MfaMiddleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route::get('/', function () {
//    return Inertia::render('welcome');
// })->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/', DashboardController::class)->name('home');
    Route::resource('servers', ServerController::class);
    Route::resource('sensors', SensorController::class);
    Route::get('media/{path}', MediaController::class)->where(['path' => '(.*)'])->name('media');

    Route::get('/voice/register', [VoiceRecognitionController::class, 'index'])
        ->name('voice.register')
        ->withoutMiddleware([MfaMiddleware::class]);

    Route::resource('users', UserController::class)->middleware('can:'.PermissionEnum::USERS->value);

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
