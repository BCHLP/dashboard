<?php
declare(strict_types=1);

namespace App\Actions;

use App\Models\Role;
use App\Models\User;

class CreateUser
{
    public function __invoke(string $name, string $email, string $role) : bool|User {

        try {
            $user = User::create(['name' => $name, 'email' => $email]);

            if ($role && Role::where('name', $role)->exists()) {
                $user->assignRole($role);
            }

            return $user;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
