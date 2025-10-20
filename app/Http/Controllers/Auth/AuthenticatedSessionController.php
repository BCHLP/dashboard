<?php

namespace App\Http\Controllers\Auth;

use App\Actions\UserLoginAuditAction;
use App\Facades\AdaptiveMfaFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LoginWithMfaRequest;
use App\Jobs\AdaptiveMfaJob;
use App\Models\ActionAudit;
use App\Models\User;
use App\Models\UserLoginAudit;
use App\Services\AdaptiveMfaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FAQRCode\Google2FA;

class AuthenticatedSessionController extends Controller
{

    public function processing() {
        $eventId = session('mfa_event_id');
        return Inertia::render('auth/Processing', ['eventId' => $eventId]);
    }

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

    public function voice(Request $request): JsonResponse|Response|RedirectResponse
    {
        return Inertia::render('auth/VerifyVoice');
    }

    public function totp(LoginWithMfaRequest $request, UserLoginAuditAction $userLoginAudit) : JsonResponse
    {
        $event = AdaptiveMfaFacade::load();
        $user = User::find($event['user_id']);
        abort_if(!$user, 404);

        // Temporarily authenticate
        Auth::login($user);
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(auth()->user()->totp_secret, $request->token);

        if (!$valid) {
            Auth::logout();
            $userLoginAudit($user->email, false);
            return response()->json(['success' => false, 'error' => 'Invalid token', 'voice' => true]);
        }

        if ($event['voice']) {
            Auth::logout();
        } else {
            $userLoginAudit($user->email, true);
            AdaptiveMfaFacade::clear();
        }

        return response()->json(['success' => true, 'error' => '', 'voice' => true]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request, UserLoginAuditAction $userLoginAudit): RedirectResponse|Response
    {
        $validCredentials = Auth::attempt($request->only(['email', 'password']));
        if (!$validCredentials) {
            $userLoginAudit($request->email, false);
            return back()->withErrors(["password" => "Invalid credentials"]);
        }

        if (!config('scada.amfa.enabled')) {
            $userLoginAudit($request->email, true);
            return redirect()->route('home');
        }

        $eventId = Str::uuid()->toString();
        session(['mfa_event_id' => $eventId]);

        AdaptiveMfaJob::dispatch($request->email, $eventId, session('fingerprint_id') ?? '');
        Auth::logout();

        return redirect()->route('login.processing');

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {

        $fingerprintId = session('fingerprint_id');

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (filled($fingerprintId)) {
            session(['fingerprint_id' => $fingerprintId]);
        }

        return redirect('/login');
    }

    public function validate(UserLoginAuditAction $userLoginAudit, string $event_id) {
        $event = AdaptiveMfaFacade::load();
        abort_if(blank($event), 404);

        if ($event['totp'] === false && $event['voice'] === false) {
            $user = User::find($event['user_id']);
            abort_if(!$user, 404);

            Auth::login($user);
            $userLoginAudit($user->email, true);

            return response()->redirectToRoute('home');
        }

        abort(404);
    }
}
