<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory([
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->create();

        $admin->assignRole(RoleEnum::ADMIN);

        $itEngineer = User::factory([
            'email' => 'it@example.com',
            'password' => 'password',
        ])->create();

        $itEngineer->assignRole(RoleEnum::SERVER_MANAGEMENT);

        $userManager = User::factory([
            'email' => 'hr@example.com',
            'password' => 'password',
        ])->create();

        $userManager->assignRole(RoleEnum::USER_MANAGEMENT);

    }
}
