<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MapResourceCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        $this->withoutWrapping();
        $return = [];
        foreach($this->collection as $manhole){
            $return[] = [
                "type" => "Feature",
                "properties" => [
                    "name" => $manhole->name,
                ], "geometry" => [
                    "type" => "Point",
                    // "coordinates" => [$manhole->coordinates->getLatitude(),$manhole->coordinates->getLongitude()]
                    "coordinates" => [$manhole->coordinates->getLongitude(),$manhole->coordinates->getLatitude()]
                ]
            ];
        }

        foreach($this->additional['pipes'] as $pipe){
            $return[] = [
                "type" => "Feature",
                "properties" => [
                    "name" => $pipe->name,
                    "color" => "#00aeef",
                ],
                "geometry" => [
                    "type" => "MultiLineString",
                    "coordinates" => [array_map(function($coordinates) {
                        return [$coordinates->getX(),$coordinates->getY()];
                    }, $pipe->path->getPoints())]
                ]
            ];
        }

        unset($this->additional['pipes']);

        return ["type" => "FeatureCollection", 'features' => $return];
    }
}
