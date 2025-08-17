<?php

namespace App\Http\Resources;

use App\Models\Pipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pipe */
class PipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $points = $this->path->getPoints();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => [
                [
                    'lat' => $points[0]->getX(),
                    'lng' => $points[0]->getY()
                ],[
                    'lat' => $points[1]->getX(),
                    'lng' => $points[1]->getY()
                ],
            ]
        ];
    }
}
