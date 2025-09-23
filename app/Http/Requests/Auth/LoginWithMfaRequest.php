<?php

namespace App\Http\Requests\Auth;

class LoginWithMfaRequest extends LoginRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'token' => ['required', 'string'],
        ];
    }
}
