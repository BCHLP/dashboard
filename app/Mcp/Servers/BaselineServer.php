<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class BaselineServer extends Server
{
    public string $serverName = 'Baseline Server';

    public string $serverVersion = '0.0.1';

    public string $instructions = '';

    public array $tools = [
        // ExampleTool::class,
    ];

    public array $resources = [
        // ExampleResource::class,
    ];

    public array $prompts = [
        // ExamplePrompt::class,
    ];
}
