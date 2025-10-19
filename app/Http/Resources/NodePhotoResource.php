<?php

namespace App\Http\Resources;

use App\Models\NodePhoto;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin NodePhoto */
class NodePhotoResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'face_detected' => $this->face_detected,
            'node_id' => $this->node_id,
            'node' => new NodeResource($this->whenLoaded('node')),
            'created_at' => $this->created_at,
        ];
    }
}
