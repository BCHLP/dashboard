<?php

namespace App\Http\Controllers;

use App\Enums\NodeTypeEnum;
use App\Http\Resources\NodeResource;
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
        $tanks = $nodes->whereIn('node_type',[
            NodeTypeEnum::SEDIMENTATION_TANK,
            NodeTypeEnum::AERATION_TANK,
            NodeTypeEnum::DIGESTION_TANK]);

        $nodeMetrics = [];
        foreach($nodes as $node) {
            if ($node->metrics->count() === 0) {
                continue;
            }

            $nodeMetrics[$node->id] = [];
            foreach($node->metrics as $metric) {
                $nodeMetrics[$node->id][$metric->alias] = 0;
            }
        }

        return Inertia::render('dashboard', [
            'servers' => NodeResource::collection($servers),
            'sensors' => NodeResource::collection($sensors),
            'routers' => NodeResource::collection($routers),
            'tanks' => NodeResource::collection($tanks),
            'nodeMetrics' => $nodeMetrics,
        ]);
    }
}
