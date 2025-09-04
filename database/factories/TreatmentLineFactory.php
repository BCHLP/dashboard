<?php

namespace Database\Factories;

use App\Models\Node;
use App\Models\TreatmentLine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TreatmentLineFactory extends Factory
{
    protected $model = TreatmentLine::class;

    public function definition(): array
    {
        return [
            'stage' => $this->faker->randomNumber(),
            'maintenance_mode' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'start_node_id' => Node::factory(),
            'end_node_id' => Node::factory(),
        ];
    }
}
