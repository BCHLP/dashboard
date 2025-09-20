<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class SetupController extends Controller
{
    public function totp()
    {
        return Inertia::render('auth/totp');
    }

    public function password() {
        if (filled(auth()->user()->password)) {
            return response()->redirectToRoute('password.edit');
        }

        return Inertia::render('auth/SetPassword');
    }

    public function voice() {
        return Inertia::render('RegisterVoice');
    }
}
