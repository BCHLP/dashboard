<?php

use App\Http\Controllers\API\DatapointController;
use App\Http\Controllers\API\MapController;
use App\Http\Controllers\API\UserVoiceController;
use App\Http\Middleware\CheckUserVoiceMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','auth'])->group(function () {
    Route::get('/map/{north}/{east}/{south}/{west}', MapController::class);
    Route::post('/voice/register', [UserVoiceController::class, 'register'])
        ->name('voice.register')
        ->withoutMiddleware([CheckUserVoiceMiddleware::class]);

    Route::post('/voice/compare', [UserVoiceController::class, 'compare'])
        ->name('voice.compare')
        ->withoutMiddleware([CheckUserVoiceMiddleware::class]);

});

Route::name('api.')->middleware(['auth:sanctum'])->group(function () {
   Route::resource('datapoints', DatapointController::class);
});
