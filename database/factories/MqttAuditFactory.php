<?php

namespace Database\Factories;

use App\Models\MqttAudit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class MqttAuditFactory extends Factory
{
    protected $model = MqttAudit::class;

    public function definition(): array
    {
        return [
            'client_id' => $this->faker->word(),
            'when' => Carbon::now(),
            'unusual' => $this->faker->boolean(),
            'message' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
