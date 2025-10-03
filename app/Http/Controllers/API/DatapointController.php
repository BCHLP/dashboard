<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Datapoint;
use App\Models\Node;
use Illuminate\Http\Request;

class DatapointController extends Controller
{
    public function index()
    {

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'points' => 'required|array',
        ]);
        $server = $request->user();

        foreach ($data['points'] as $point) {
            $metric = $server->metrics->where('alias', $point['metric'] ?? '')->first();
            if (blank($metric)) {
                continue;
            }

            Datapoint::create([
                'source_id' => $server->id,
                'source_type' => Node::class,
                'metric_id' => $metric->id,
                'time' => $point['time'],
                'value' => $point['value'],
            ]);
        }

        return response()->noContent();
    }

    public function show(Datapoint $datapoint)
    {
    }

    public function update(Request $request, Datapoint $datapoint)
    {
    }

    public function destroy(Datapoint $datapoint)
    {
    }
}
