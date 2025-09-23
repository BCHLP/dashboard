<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LoginWithMfaRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FAQRCode\Google2FA;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    public function voice(Request $request): Response|RedirectResponse
    {
        if (!auth()->check()) {
            return response()->redirectToRoute('login');
        }
        return Inertia::render('auth/VerifyVoice', []);
    }

    public function totp(LoginWithMfaRequest $request): Response|RedirectResponse
    {

        dd($request->all());
        $validCredentials = Auth::validate($request->only(['email', 'password']));
        if (!$validCredentials) {
            return back()->withErrors(["Invalid credentials"]);
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(Auth::user()->totp_secret, $request->token);
        if (!$valid) {
            auth()->user()->update(['totp_activated_at' => Carbon::now()]);
            return back()->withErrors(['token' => 'Invalid token']);
        }

        return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // $request->authenticate();

        $validCredentials = Auth::validate($request->only(['email', 'password']));
        if (!$validCredentials) {
            return back()->withErrors(["Invalid credentials"]);
        }

        $request->session()->regenerate();

        return back();

        // return redirect()->intended(route('home', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
