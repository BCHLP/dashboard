<?php

namespace App\Http\Resources;

use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Node */
class NodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->withoutWrapping();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'coordinates' => $this->coordinates,
            'node_type' => $this->node_type,
            'metrics' => MetricResource::collection($this->whenLoaded('metrics')),
        ];
    }
}
