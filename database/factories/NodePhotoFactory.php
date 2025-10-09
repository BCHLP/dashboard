<?php

namespace Database\Factories;

use App\Models\Node;
use App\Models\NodePhoto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class NodePhotoFactory extends Factory
{
    protected $model = NodePhoto::class;

    public function definition(): array
    {
        return [
            'location' => $this->faker->word(),
            'face_detected' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'node_id' => Node::factory(),
        ];
    }
}
