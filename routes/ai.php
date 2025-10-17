<?php

use App\Mcp\Servers\AuthenticationServer;
use Laravel\Mcp\Server\Facades\Mcp;
use Illuminate\Support\Facades\App;

// Skip MCP registration during route:cache to prevent stack overflow
// This is a workaround for a known issue with Laravel MCP package v0.1.1
if (!App::runningInConsole() || !in_array('route:cache', $_SERVER['argv'] ?? [])) {
    Mcp::local('auth', AuthenticationServer::class);
    Mcp::web('/mcp/auth', AuthenticationServer::class)
        ->middleware(['auth:api', 'throttle:mcp']);
}

// Mcp::web('demo', \App\Mcp\Servers\PublicServer::class); // Available at /mcp/demo
// Mcp::local('demo', \App\Mcp\Servers\LocalServer::class); // Start with ./artisan mcp:start demo
