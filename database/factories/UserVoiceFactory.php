<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserVoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class UserVoiceFactory extends Factory
{
    protected $model = UserVoice::class;

    public function definition(): array
    {
        return [
            'embeddings' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'user_id' => User::factory(),
        ];
    }
}
