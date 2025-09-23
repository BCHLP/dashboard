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
            'totp_secret' => 'MN6O75VRYRZ4OBV7',
            'totp_activated' => date('Y-m-d H:i:s')
        ])->create();

        $admin->assignRole(RoleEnum::ADMIN);

        $itEngineer = User::factory([
            'email' => 'it@example.com',
            'password' => 'password',
            'totp_secret' => 'MN6O75VRYRZ4OBV7',
            'totp_activated' => date('Y-m-d H:i:s')
        ])->create();

        $itEngineer->assignRole(RoleEnum::SERVER_MANAGEMENT);

        $userManager = User::factory([
            'email' => 'hr@example.com',
            'password' => 'password',
            'totp_secret' => 'MN6O75VRYRZ4OBV7',
            'totp_activated' => date('Y-m-d H:i:s')
        ])->create();

        $userManager->assignRole(RoleEnum::USER_MANAGEMENT);

    }
}
