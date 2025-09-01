<?php

namespace App\Http\Resources;

use App\Models\DeviceMetric;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DeviceMetric */
class DeviceMetricResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_id' => $this->device_id,
            'device_type' => $this->device_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'metric_id' => $this->metric_id,

            'metric' => new MetricResource($this->whenLoaded('metric')),
        ];
    }
}
