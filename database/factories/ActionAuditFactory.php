<?php

namespace Database\Factories;

use App\Models\ActionAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ActionAuditFactory extends Factory
{
    protected $model = ActionAudit::class;

    public function definition(): array
    {
        return [
            'action' => $this->faker->word(),
            'voice' => $this->faker->boolean(),
            'totp' => $this->faker->boolean(),
            'voice_complated_at' => Carbon::now(),
            'totp_completed_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }
}
