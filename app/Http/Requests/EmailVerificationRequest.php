<?php

namespace App\Http\Requests;


use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class EmailVerificationRequest extends \Illuminate\Foundation\Auth\EmailVerificationRequest
{
    public function authorize(): bool
    {
        $token = PersonalAccessToken::findToken($this->query('token') ?? '');
        if (!$token || $token->tokenable_type !== User::class) {
            return false;
        }

        $user = User::find($token->tokenable_id);
        if (!$user) {
            return false;
        }

        Auth::login($user);
        $token->delete();

        return true;
    }
}
