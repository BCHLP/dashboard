<?php

use App\Http\Controllers\Auth\VoiceRecognitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckUserVoiceMiddleware;
use App\Http\Resources\NodeResource;
use App\Models\Node;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('map', function(){
        return Inertia::render('PerthMap');
    })->name('map');

    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::resource('servers', ServerController::class);

    Route::get('/voice/register', [VoiceRecognitionController::class, 'index'])
        ->name('voice.register')
        ->withoutMiddleware([CheckUserVoiceMiddleware::class]);

    Route::resource('users', UserController::class);

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
