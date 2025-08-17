<?php

namespace Database\Factories;

use App\Models\Manhole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ManholeFactory extends Factory
{
    protected $model = Manhole::class;

    public function definition(): array
    {
        return [
            'sap_id' => $this->faker->randomNumber(),
            'name' => $this->faker->name(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
