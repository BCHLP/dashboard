<?php

namespace Database\Factories;

use App\Models\DeviceMetric;
use App\Models\Metric;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DeviceMetricFactory extends Factory
{
    protected $model = DeviceMetric::class;

    public function definition(): array
    {
        return [
            'device_metric_id' => $this->faker->randomNumber(),
            'device_metric_type' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'metric_id' => Metric::factory(),
        ];
    }
}
