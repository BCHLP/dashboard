<?php

namespace App\Http\Resources;

use App\Models\Datapoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Datapoint */
class DatapointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'alias' => $this->metric->alias,
            'x' => Carbon::parse($this->created_at)->timestamp,
            'y' => (float) $this->value,
            'node_id' => $this->node_id,
            'metric_id' => $this->metric_id,
        ];
    }
}
