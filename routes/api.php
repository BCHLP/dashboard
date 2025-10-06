<?php

use App\Http\Controllers\API\DatapointController;
use App\Http\Controllers\API\FingerprintController;
use App\Http\Controllers\API\MapController;
use App\Http\Controllers\API\MqttAuditController;
use App\Http\Controllers\API\UserVoiceController;
use App\Http\Middleware\MfaMiddleware;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','auth'])->group(function () {
    Route::get('/map/{north}/{east}/{south}/{west}', MapController::class);
    Route::post('/voice/register', [UserVoiceController::class, 'register'])
        ->name('voice.register')
        ->withoutMiddleware([MfaMiddleware::class]);

});

Route::name('api.')->middleware(['auth:sanctum'])->group(function () {
   Route::resource('datapoints', DatapointController::class);
   Route::post('mqtt-audits', MqttAuditController::class)->name('mqtt.audits');

});

Route::post('fingerprint', FingerprintController::class)->middleware('web');
Route::post('/voice/compare', [UserVoiceController::class, 'compare'])
    ->name('voice.compare')
    ->middleware('web');
