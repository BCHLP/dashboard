<?php

namespace App\Http\Middleware;

use App\Services\VoiceRecognitionService;
use Closure;
use Illuminate\Http\Request;

class MfaMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        if (!auth()->check()) {
            abort(401);
        }

        if (is_null($request->user()->password)) {

            return redirect()->route('password.set');
        }

        if (blank($request->user()->voice)) {
            return redirect()->route('voice.register');
        }

        if (blank($request->user()->totp_activated_at)) {
            return redirect()->route('totp.register');
        }

//        if (!VoiceRecognitionService::isVoiceAuthenticated()){
//            return redirect()->route('voice');
//        }

        return $next($request);
    }
}
