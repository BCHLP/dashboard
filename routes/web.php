<?php

use App\Http\Controllers\MapController;
use App\Http\Resources\SensorResource;
use App\Models\Sensor;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('map', function(){
        return Inertia::render('PerthMap');
    })->name('map');

    Route::get('dashboard', function(){
        $sensors = Sensor::all();
        return Inertia::render('dashboard', ['sensors' => SensorResource::collection($sensors)]);
    })->name('dashboard');

    Route::get('test', function() {
        return Inertia::render('test');
    })->name('test');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
