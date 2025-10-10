<?php
declare(strict_types=1);

namespace App\Mcp\Prompts;

use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;
use Laravel\Mcp\Server\Prompts\PromptResult;

class DecideMfaPrompt extends Prompt
{
    protected string $description = 'Evaluate login context and decide whether MFA should be required.';

    public function arguments(): array
    {
        return [
            new Argument(
                name: 'event_id',
                description: 'Unique event UUID for this login attempt.',
                required: true,
            ),
            new Argument(
                name: 'user',
                description: 'User object containing id, email, roles, etc.',
                required: true,
            ),
            new Argument(
                name: 'context',
                description: 'Login context including IP, user_agent, time, etc.',
                required: true,
            ),
        ];
    }

    public function handle(array $arguments): PromptResult
    {

        $systemPrompt = '
You are an adaptive MFA decider.

You have access to the following tools:
- GetRecentFailedAttempts(user_id)
- GetUserLoginHistory(user_id)
- RecordMFADecision(user_id, event_id, voice, totp)

Use these tools to assess login risk and determine if MFA is needed.

Output strictly in JSON format:
{
  "decision": "allow_login" | "require_mfa",
  "methods": ["voice" | "totp"],
  "confidence": number between 0 and 1,
  "reason": "short text explaining why"
}';

        $userPrompt = json_encode([
                    'event_id' => $arguments['event_id'],
                    'user' => $arguments['user'],
                    'context' => $arguments['context'],
                ]);
        return new PromptResult($systemPrompt, $userPrompt);
    }
}
