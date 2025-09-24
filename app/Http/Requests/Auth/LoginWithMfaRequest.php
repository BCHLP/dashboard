<?php

namespace App\Http\Requests\Auth;

class LoginWithMfaRequest extends LoginRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }
}
