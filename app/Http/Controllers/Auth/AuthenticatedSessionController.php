<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LoginWithMfaRequest;
use App\Models\ActionAudit;
use App\Models\User;
use App\Services\AdaptiveMFAService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
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
    public function create(Request $request, ?ActionAudit $audit): Response
    {
        return Inertia::render('auth/login', [
            'status' => $request->session()->get('status'),
            'auditAction' => $audit,
        ]);
    }

    public function voice(Request $request): Response|RedirectResponse
    {
        if (!auth()->check()) {
            return response()->redirectToRoute('login');
        }
        return Inertia::render('auth/VerifyVoice', []);
    }

    public function totp(ActionAudit $audit, LoginWithMfaRequest $request): Response|RedirectResponse
    {

        abort_if($audit->action !== 'Login', 404);

        $validCredentials = Auth::validate($request->only(['email', 'password']));
        if (!$validCredentials) {
            return back()->withErrors(["Invalid credentials"]);
        }

        $user = User::where('email', $request->email)->first();
        abort_if(blank($user), 404);

        abort_if($audit->user_id !== $user->id, 404);

        // Temporarily authenticate
        Auth::login($user);

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(auth()->user()->totp_secret, $request->token);

        if (!$valid) {
            Auth::logout();
            return redirect()->back()->withErrors(['token' => 'Invalid token']);
        }

        $audit->update(['totp_completed_at' => Carbon::now(), 'totp' => null]);
        $adaptiveService = new AdaptiveMFAService();
        return $adaptiveService->getNextRoute($audit, null, fn() => Auth::logout());

        // return redirect()->intended(route($nextRoute, absolute: false));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, ?ActionAudit $auditAction): RedirectResponse|Response
    {
        $validCredentials = Auth::validate($request->only(['email', 'password']));
        if (!$validCredentials) {
            return back()->withErrors(["password" => "Invalid credentials"]);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors(["password" => "Invalid credentials!"]);
        }

        $amfaService = new AdaptiveMFAService;
        if (blank($auditAction->id)) {
            $auditAction = $amfaService->getFactors("Login", $user);

        }

        if (blank($auditAction)) {
            Auth::login($user);
            return redirect()->intended(route('home', absolute: false));
        }

        return $amfaService->getNextRoute($auditAction, fn() => $request->session()->regenerate());

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
