<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserFingerprint;
use App\Models\UserLoginAudit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserLoginAuditFactory extends Factory
{
    protected $model = UserLoginAudit::class;

    public function definition(): array
    {
        $time = now()->subDays(rand(1, 3))->setHour(rand(9, 17))->setMinute(rand(0, 59));

        return [
            'email' => $this->faker->unique()->safeEmail(),
            'successful' => $this->faker->boolean(),
            'created_at' => $time,
            'updated_at' => $time,

            'user_id' => User::factory(),
            'user_fingerprint_id' => UserFingerprint::factory(),
        ];
    }
}
