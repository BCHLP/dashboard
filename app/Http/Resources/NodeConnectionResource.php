<?php

namespace App\Http\Resources;

use App\Models\NodeConnection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NodeConnection */
class NodeConnectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_node_id' => $this->from_node_id,
            'to_node_id' => $this->to_node_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
