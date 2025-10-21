<?php

use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\DatapointController;
use App\Http\Controllers\API\FingerprintController;
use App\Http\Controllers\API\MqttAuditController;
use App\Http\Controllers\API\UserVoiceController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->middleware(['auth:sanctum'])->group(function () {
    Route::resource('datapoints', DatapointController::class);
    Route::post('mqtt-audits', MqttAuditController::class)->name('mqtt.audits');

});

Route::middleware('web')->group(function () {
    Route::post('fingerprint', FingerprintController::class);
    Route::post('/voice/compare', [UserVoiceController::class, 'compare'])
        ->name('voice.compare');
    Route::post('/dashboard/capture/image', [DashboardController::class, 'captureImage']);

});
