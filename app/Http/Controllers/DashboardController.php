<?php

namespace App\Http\Controllers;

use App\Enums\NodeTypeEnum;
use App\Http\Resources\DatapointResource;
use App\Http\Resources\NodeResource;
use App\Models\Datapoint;
use App\Models\Node;
use Inertia\Inertia;
class DashboardController extends Controller
{
    public function __invoke()
    {
        $nodes = Node::with('metrics')->get();
        $servers = $nodes->where('node_type', NodeTypeEnum::SERVER);
        $sensors = $nodes->where('node_type', NodeTypeEnum::SENSOR);
        $routers = $nodes->where('node_type', NodeTypeEnum::ROUTER);
        $datapoints = Datapoint::whereIn('node_id', $nodes->pluck('id'))
            ->where('created_at','>', now()->subMinute())
            ->get();



        return Inertia::render('dashboard', [
            'servers' => NodeResource::collection($servers),
            'sensors' => NodeResource::collection($sensors),
            'routers' => NodeResource::collection($routers),
            'datapoints' => DatapointResource::collection($datapoints),
        ]);
    }
}
