<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserVoiceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (blank($request->user()->voice)) {
            return redirect()->route('voice.register');
        }
        return $next($request);
    }
}
