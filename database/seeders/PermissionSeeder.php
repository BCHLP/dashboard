<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $userPermission = Permission::create(['name' => PermissionEnum::USERS]);
        $serverPermission = Permission::create(['name' => PermissionEnum::SERVERS]);

        $adminRole = Role::create(['name' => 'Admin']);
        $adminRole->syncPermissions([$userPermission, $serverPermission]);

        $serverRole = Role::create(['name' => 'Server Management']);
        $serverRole->syncPermissions([$serverPermission]);

        $usersRole = Role::create(['name' => 'User Management']);
        $usersRole->syncPermissions([$userPermission]);

    }
}
