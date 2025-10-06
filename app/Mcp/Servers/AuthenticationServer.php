<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\AnalyzeLoginRiskPrompt;
use App\Mcp\Prompts\ExplainMFADecisionPrompt;
use App\Mcp\Resources\KnownThreatPatternsResource;
use App\Mcp\Resources\MFACapabilitiesResource;
use App\Mcp\Resources\RiskAssessmentGuidelines;
use App\Mcp\Tools\GetUserLoginHistory;
use App\Mcp\Tools\GetRecentFailedAttempts;
use App\Mcp\Tools\RecordMFADecision;
use Laravel\Mcp\Server;

class AuthenticationServer extends Server
{
    public string $serverName = 'Authentication Server';

    public string $serverVersion = '0.0.1';

    public string $instructions = 'Tools for fetching user authentication history for Adaptive MFA';

    public array $tools = [
        GetUserLoginHistory::class,
        GetRecentFailedAttempts::class,
        RecordMFADecision::class,
    ];

    public array $resources = [
        RiskAssessmentGuidelines::class,
        MFACapabilitiesResource::class,
        KnownThreatPatternsResource::class,
    ];

    public array $prompts = [
        AnalyzeLoginRiskPrompt::class,
        ExplainMFADecisionPrompt::class,
    ];
}
