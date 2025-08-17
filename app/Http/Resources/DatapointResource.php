<?php

namespace App\Http\Resources;

use App\Models\Datapoint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Datapoint */
class DatapointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'metric_id' => $this->metric_id,
            'sensor_id' => $this->sensor_id,

            'metric' => new MetricResource($this->whenLoaded('metric')),
            'sensor' => new SensorResource($this->whenLoaded('sensor')),
        ];
    }
}
