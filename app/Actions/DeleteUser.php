<?php
declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

class DeleteUser
{
    public function __invoke(User $user)
    {
        $user->delete();
    }
}
