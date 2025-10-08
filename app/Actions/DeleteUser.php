<?php
declare(strict_types=1);

namespace App\Actions;

use App\Models\User;

class DeleteUser
{
    public function __invoke(User $user)
    {
        if ($user->voice) {
            $user->voice->delete();
        }
        $user->delete();
    }
}
