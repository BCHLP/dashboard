<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class SetPasswordController extends Controller
{
    public function show()
    {
        if (filled(auth()->user()->password)) {
            return response()->redirectToRoute('password.edit');
        }

        return Inertia::render('auth/SetPassword');
    }

    public function submit(Request $request) {

        if (filled(auth()->user()->password)) {
            return response()->redirectToRoute('password.edit');
        }

        $validated = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('dashboard');
    }
}
