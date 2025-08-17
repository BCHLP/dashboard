<?php

namespace Database\Factories;

use App\Models\StateHistory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class StateHistoryFactory extends Factory
{
    protected $model = StateHistory::class;

    public function definition(): array
    {
        return [
            'device_class' => $this->faker->word(),
            'device_id' => $this->faker->randomNumber(),
            'state' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
