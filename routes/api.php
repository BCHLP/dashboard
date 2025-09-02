<?php

use App\Http\Controllers\API\MapController;
use Illuminate\Support\Facades\Route;

Route::get('/map/{north}/{east}/{south}/{west}', MapController::class);
