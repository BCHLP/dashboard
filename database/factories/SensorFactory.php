<?php

namespace Database\Factories;

use App\Models\Sensor;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SensorFactory extends Factory
{
    protected $model = Sensor::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'coordinates' => Point::make(-31.740204, 115.762746),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
