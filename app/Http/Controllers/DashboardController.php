<?php

namespace App\Http\Controllers;

use App\Http\Resources\NodePhotoResource;
use App\Models\Datapoint;
use App\Models\Node;
use App\Models\NodePhoto;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {

        $node = Node::with('metrics')->where('name', 'SEN-001')->first();
        $photo = NodePhoto::where('node_id', $node->id)->latest()->first();

        $datapoints = [];
        if ($node) {
            foreach ($node->metrics as $metric) {
                $datapoint = Datapoint::where('metric_id', $metric->id)
                    ->where('source_id', $node->id)
                    ->where('source_type', Node::class)
                    ->latest()
                    ->first();

                if ($datapoint) {
                    $datapoints[] = [
                        'alias' => $metric->alias,
                        'x' => $datapoint->time,
                        'y' => $datapoint->value,
                        'node_id' => $node->id,
                        'metric_id' => $metric->id,
                    ];
                }
            }
        }

        return Inertia::render('dashboard', [
            'node' => $node,
            'datapoints' => $datapoints,
            'photo' => ($photo ? NodePhotoResource::make($photo) : []),
        ]);
    }
}
