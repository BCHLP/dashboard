<?php

use App\Http\Controllers\API\MapController;
use App\Http\Controllers\API\UserVoiceController;
use App\Http\Middleware\CheckUserVoiceMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/map/{north}/{east}/{south}/{west}', MapController::class);
Route::post('/voice/register', [UserVoiceController::class, 'register'])
    ->name('voice.register')
    ->withoutMiddleware([CheckUserVoiceMiddleware::class]);
