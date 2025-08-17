<?php

namespace Database\Factories;

use App\Models\Pipe;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PipeFactory extends Factory
{
    protected $model = Pipe::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'path' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
