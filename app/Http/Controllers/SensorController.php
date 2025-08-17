<?php

namespace App\Http\Controllers;

use App\Http\Requests\SensorRequest;
use App\Http\Resources\SensorResource;
use App\Models\Sensor;

class SensorController extends Controller
{
    public function index()
    {
        return SensorResource::collection(Sensor::all());
    }

    public function store(SensorRequest $request)
    {
        return new SensorResource(Sensor::create($request->validated()));
    }

    public function show(Sensor $sensor)
    {
        return new SensorResource($sensor);
    }

    public function update(SensorRequest $request, Sensor $sensor)
    {
        $sensor->update($request->validated());

        return new SensorResource($sensor);
    }

    public function destroy(Sensor $sensor)
    {
        $sensor->delete();

        return response()->json();
    }
}
