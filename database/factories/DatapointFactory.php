<?php

namespace Database\Factories;

use App\Models\Datapoint;
use App\Models\Metric;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DatapointFactory extends Factory
{
    protected $model = Datapoint::class;

    public function definition(): array
    {
        return [
            'value' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'metric_id' => Metric::factory(),
            'sensor_id' => Sensor::factory(),
        ];
    }
}
