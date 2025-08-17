<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory([
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->create();
    }
}
