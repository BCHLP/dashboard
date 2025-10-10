<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OpenAiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('X-MCP-Secret') !== config('services.openai.mcp_secret')) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
