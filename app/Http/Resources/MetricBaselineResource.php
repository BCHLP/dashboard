<?php

namespace App\Http\Resources;

use App\Models\MetricBaseline;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MetricBaseline */
class MetricBaselineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'hour' => $this->hour,
            'dow' => $this->dow,
            'mean' => $this->mean,
            'median' => $this->median,
            'sd' => $this->sd,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'metric_id' => $this->metric_id,

            'metric' => new MetricResource($this->whenLoaded('metric')),
        ];
    }
}
