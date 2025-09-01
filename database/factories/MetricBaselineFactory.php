<?php

namespace Database\Factories;

use App\Models\Metric;
use App\Models\MetricBaseline;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MetricBaselineFactory extends Factory
{
    protected $model = MetricBaseline::class;

    public function definition(): array
    {
        return [
            'hour' => $this->faker->randomNumber(),
            'dow' => $this->faker->randomNumber(),
            'mean' => $this->faker->randomFloat(),
            'median' => $this->faker->randomFloat(),
            'sd' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'metric_id' => Metric::factory(),
        ];
    }
}
