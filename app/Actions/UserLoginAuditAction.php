<?php
declare(strict_types=1);

namespace App\Actions;

use App\Models\UserLoginAudit;
use App\Models\User;

class UserLoginAuditAction
{
    public function __invoke(string $email, bool $successful) : void {

        $userId = User::where('email', $email)->first()->id ?? 0;

        UserLoginAudit::create([
            'user_id' => $userId,
            'user_fingerprint_id' => session('fingerprint_id') ?? null,
            'email' => $email,
            'successful' => $successful,
        ]);
    }
}
