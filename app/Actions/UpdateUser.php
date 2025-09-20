<?php
declare(strict_types=1);

namespace App\Actions;

use App\Models\Role;
use App\Models\User;

class UpdateUser
{
    public function __invoke(User $user, string $name, string $role) {
        $user->update([
            'name' => $name,
        ]);

        if (isset($data['role'])) {
            $role = Role::where('name', $data['role'])->first();
            if ($role) {
                $user->syncRoles([$role]);
            }
        }
    }
}
