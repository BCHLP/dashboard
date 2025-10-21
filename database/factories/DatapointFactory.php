<?php

namespace Database\Factories;

use App\Models\Datapoint;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DatapointFactory extends Factory
{
    protected $model = Datapoint::class;

    public function definition(): array
    {

        return [
            'time' => time(),
            'value' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
