<?php

namespace Database\Seeders;

use App\Models\Manhole;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Seeder;
use JsonMachine\Exception\InvalidArgumentException;
use \JsonMachine\Items;

class ManholeSeeder extends Seeder
{
    /**
     * @throws InvalidArgumentException
     */
    public function run(): void
    {
        $manholes = Items::fromFile(storage_path("sources/manholes.geojson"),['pointer' => '/features']);

        foreach($manholes as $manhole) {
            Manhole::firstOrCreate([
                'id' => $manhole->properties->id,
                ],[
                'sap_id' => $manhole->properties->sap_id,
                'name' => $manhole->properties->sap_name,
                'coordinates' => Point::make($manhole->geometry->coordinates[0],$manhole->geometry->coordinates[1])
            ]);
        }
    }
}
