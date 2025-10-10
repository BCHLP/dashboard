<?php

use App\Mcp\Servers\AuthenticationServer;
use Laravel\Mcp\Server\Facades\Mcp;

Mcp::local('auth', AuthenticationServer::class);
Mcp::web('/mcp/auth', AuthenticationServer::class)
    ->middleware(['auth:api', 'throttle:mcp']);

// Mcp::web('demo', \App\Mcp\Servers\PublicServer::class); // Available at /mcp/demo
// Mcp::local('demo', \App\Mcp\Servers\LocalServer::class); // Start with ./artisan mcp:start demo
