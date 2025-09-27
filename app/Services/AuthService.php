<?php

namespace App\Services;

use App\Models\FailedLogin;
use Illuminate\Http\Client\Request;

class AuthService
{
    public function __construct()
    {
    }

    public function failed(Request $request, string $email) : void {

        FailedLogin::create([
            'user_id' => 0,
            'user_fingerprint_id' =>
            'email' => $email,
        ]);
    }
}
