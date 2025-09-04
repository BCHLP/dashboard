<?php

namespace Database\Factories;

use App\Models\Node;
use App\Models\NodeSetting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class NodeSettingFactory extends Factory
{
    protected $model = NodeSetting::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'value' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'node_id' => Node::factory(),
        ];
    }
}
