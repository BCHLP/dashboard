<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MapResource;
use App\Http\Resources\MapResourceCollection;
use App\Models\Manhole;
use App\Models\Pipe;
use App\Models\Sensor;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __invoke(float $north, float $east, float $south, float $west)
    {
        $manholes = Manhole::whereRaw(
            "ST_Intersects(coordinates, ST_MakeEnvelope(?, ?, ?, ?, 4326))",
            [$west, $south, $east, $north]
        )->get();

        $pipes = Pipe::whereRaw(
            "ST_Intersects(path, ST_MakeEnvelope(?, ?, ?, ?, 4326))",
            [$west, $south, $east, $north]
        )->get();

        return MapResourceCollection::make($manholes)
            ->additional(['pipes' => $pipes])
            ->response()
            ->withHeaders([
                'Content-Type' => 'application/geo+json',
            ]);
    }
}
