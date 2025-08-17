<?php

use App\Http\Controllers\API\MapController;
use App\Http\Resources\PipeResource;
use App\Http\Resources\SensorResource;
use App\Models\Pipe;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/map/{north}/{east}/{south}/{west}', MapController::class);
