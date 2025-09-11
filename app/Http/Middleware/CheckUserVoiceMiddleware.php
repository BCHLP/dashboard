<?php

namespace App\Http\Middleware;

use App\Services\VoiceRecognitionService;
use Closure;
use Illuminate\Http\Request;

class CheckUserVoiceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && blank($request->user()->voice)) {
            return redirect()->route('voice.register');
        }


        if (!VoiceRecognitionService::isVoiceAuthenticated()){
            return redirect()->route('voice');
        }

        return $next($request);
    }
}
