<?php

namespace Database\Factories;

use App\Models\NodeConnection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class NodeConnectionFactory extends Factory
{
    protected $model = NodeConnection::class;

    public function definition(): array
    {
        return [
            'from_node_id' => $this->faker->randomNumber(),
            'to_node_id' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
