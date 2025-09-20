<?php
declare(strict_types=1);

namespace App\Actions;

use App\Models\Role;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateUser
{
    public function __invoke(string $name, string $email, string $role) : User {

        $user = User::create(['name' => $name, 'email' => $email, 'uuid' => Str::uuid()]);

        if ($role && Role::where('name', $role)->exists()) {
            $user->assignRole($role);
        }

        $user->notify(new VerifyEmailNotification);

        return $user;

    }
}
