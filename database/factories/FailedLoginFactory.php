<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserFingerprint;
use App\Models\UserLoginAudit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FailedLoginFactory extends Factory
{
    protected $model = UserLoginAudit::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
            'user_fingerprint_id' => UserFingerprint::factory(),
        ];
    }
}
