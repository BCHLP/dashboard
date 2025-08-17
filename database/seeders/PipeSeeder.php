<?php

namespace Database\Seeders;

use App\Models\Pipe;
use Clickbar\Magellan\Data\Geometries\LineString;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Seeder;
use JsonMachine\Items;

class PipeSeeder extends Seeder
{
    public function run(): void
    {
        $pipes = Items::fromFile(storage_path("sources/pipes.geojson"),['pointer' => '/features']);
        foreach($pipes as $pipe) {

            $path = [];
            foreach($pipe->geometry->coordinates as $outerLoop) {
                foreach($outerLoop as $coordinate) {
                    $path[] = Point::make($coordinate[0],$coordinate[1]);
                }
            }

            Pipe::firstOrCreate([
                'id' => $pipe->properties->id,
            ],[
                'name' => $pipe->properties->id,
                'path' => LineString::make($path),
            ]);
        }

    }
}
