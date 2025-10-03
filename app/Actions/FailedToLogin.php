<?php
declare(strict_types=1);

namespace App\Actions;

use App\Models\FailedLogin;
use App\Models\User;
use Illuminate\Http\Client\Request;

class FailedToLogin
{
    public function __invoke(string $email) : void {

        $userId = User::where('email', $email)->first()->id ?? 0;

        FailedLogin::create([
            'user_id' => $userId,
            'user_fingerprint_id' => session('fingerprint_id') ?? null,
            'email' => $email,
        ]);
    }
}
