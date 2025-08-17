<?php

namespace Database\Seeders;

use App\Models\Metric;
use App\Models\Pipe;
use App\Models\Sensor;
use Clickbar\Magellan\Data\Geometries\LineString;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Seeder;

class JoondalupSeeder extends Seeder
{
    public function run(): void
    {
        $sensor1 = Sensor::factory([
            'name' => $this->generateLabel(),
            'coordinates' => Point::make(115.762746,-31.740204)]
        )->create();

        $sensor2 = Sensor::factory([
            'name' => $this->generateLabel(),
            'coordinates' => Point::make(115.762706,-31.740396)]
        )->create();

        Pipe::factory([
            'name' => $this->generateLabel(),
            'path' => LineString::make([$sensor1->coordinates, $sensor2->coordinates])
        ])->create();

        Metric::factory([
            'name' => 'hydrogen_sulfide',
            'alias' => 'H2S',
            'baseline_minimum' => 0.00011,
            'baseline_maximum' => 0.00033,
        ])->create();

        Metric::factory([
            'name' => 'dissolved_oxygen',
            'alias' => 'DO',
            'baseline_minimum' => 5.0,
            'baseline_maximum' => 14,
        ])->create();
        Metric::factory([
            'name' => 'potential_of_hydrogen',
            'alias' => 'pH',
            'baseline_minimum' => 6.5,
            'baseline_maximum' => 8.5,
        ])->create();

//        Metric::factory(['name' => 'temp_c'])->create();
//        Metric::factory(['name' => 'sulfate', 'alias' => 'SO42-'])->create();
//        Metric::factory(['name' => 'ventilation'])->create();
//        Metric::factory(['name' => 'oxidation'])->create();
//        Metric::factory(['name' => 'methane'])->create();
    }

    private int $code = 100;

    private function generateLabel() : string {
        $this->code++;
        return "SEN-".$this->code;
    }
}
