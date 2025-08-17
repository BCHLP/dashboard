<?php

namespace App\Http\Resources;

use App\Models\Sensor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Sensor */
class SensorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'coordinates' => [
                'lat' => $this->coordinates->getX(),
                'lng' => $this->coordinates->getY(),
            ],
        ];
    }
}
