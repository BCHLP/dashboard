<?php

namespace Database\Factories;

use App\Models\UserTotp;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class UserTotpFactory extends Factory
{
    protected $model = UserTotp::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
