<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginWithMfaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }
}
