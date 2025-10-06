<?php

namespace App\Http\Controllers\Auth;

use App\Actions\UserLoginAuditAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LoginWithMfaRequest;
use App\Jobs\AdaptiveMfaJob;
use App\Models\ActionAudit;
use App\Models\User;
use App\Models\UserLoginAudit;
use App\Services\AdaptiveMFAService;
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
//        if (!auth()->check()) {
//            return response()->redirectToRoute('login');
//        }
        return Inertia::render('auth/VerifyVoice', []);
    }

    public function totp(ActionAudit $audit, LoginWithMfaRequest $request, UserLoginAuditAction $userLoginAudit) : JsonResponse
    {

        abort_if($audit->action !== 'Login', 404);

        $user = $audit->user;
        abort_if(blank($user), 404);

        // Temporarily authenticate
        Auth::login($user);
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey(auth()->user()->totp_secret, $request->token);

        if (!$valid) {
            Auth::logout();
            $userLoginAudit($request->email, false);
            return response()->json(['success' => false, 'audit' => $audit, 'error' => 'Invalid token']);
        }

        $audit->update(['totp_completed_at' => Carbon::now(), 'totp' => null]);
        if ($audit->voice) {
            Auth::logout();
        } else {
            $userLoginAudit($request->email, true);
        }
        // $adaptiveService = new AdaptiveMFAService();

        return response()->json(['success' => true, 'audit' => $audit]);
        // return $adaptiveService->getNextRoute($audit, null, fn() => Auth::logout());

        // return redirect()->intended(route($nextRoute, absolute: false));
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

        $eventId = Str::uuid()->toString();
        session(['mfa_process' => 0, 'mfa_event_id' => $eventId]);

        AdaptiveMfaJob::dispatch($request->email, $eventId);
        /*if (blank($auditAction->id)) {
            $auditAction = $amfaService->getFactors("Login", auth()->user());
        }

        if (blank($auditAction)) {
            $userLoginAudit($request->email, true);
            return redirect()->intended(route('home', absolute: false));
        }*/

        Auth::logout();

        return redirect()->route('login.processing');

        // return $amfaService->getNextRoute($auditAction, fn() => $request->session()->regenerate());

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

    public function validate(UserLoginAuditAction $userLoginAudit, string $event_id) {
        $result = Cache::get('MfaDecision.'.$event_id);
        abort_if(blank($result), 404);

        $result = json_decode($result);

        if ($result->totp === false && $result->voice === false) {
            $user = User::find($result->user_id);
            abort_if(!$user, 404);

            Auth::login($user);
            $userLoginAudit($user->email, true);

            return response()->redirectToRoute('home');
        }

        abort(404);
    }
}
