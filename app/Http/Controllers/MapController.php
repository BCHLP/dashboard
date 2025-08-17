<?php

namespace App\Http\Controllers;

use App\Http\Resources\PipeResource;
use App\Http\Resources\SensorResource;
use App\Models\Pipe;
use App\Models\Sensor;
use Inertia\Inertia;

class MapController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('dashboard');
    }
}
