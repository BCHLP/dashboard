<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;
use Laravel\Mcp\Server\Prompts\Arguments;
use Laravel\Mcp\Server\Prompts\PromptResult;

class ExplainMFADecisionPrompt extends Prompt
{
    protected string $description = 'Generate user-friendly explanation for MFA requirements';

    public function arguments(): Arguments
    {
        return (new Arguments)->add(
            new Argument(
                name: 'risk_factors',
                description: 'List of risk factors detected',
                required: true,
            ),
        )->add(
            new Argument(
                name: 'required_mfa',
                description: 'The MFA methods required',
                required: true,
            ),
        );
    }

    public function handle(array $arguments): PromptResult
    {
        $riskFactors = $arguments['risk_factors'];
        $requiredMfa = $arguments['required_mfa'];

        $systemPrompt = <<<'PROMPT'
You are a customer support specialist explaining security decisions. Create a brief, reassuring message for users explaining why additional verification is needed.

**Tone:** Professional but friendly, security-conscious but not alarming

**Guidelines:**
- Keep it under 50 words
- Focus on positive security ("protecting your account")
- Don't reveal specific detection methods
- Be specific but not technical

**Example:**
"We noticed you're logging in from a new location. To protect your account, we need to verify it's really you. Please complete the additional verification."
PROMPT;

        $userPrompt = <<<PROMPT
Create a user-facing message explaining why {$requiredMfa} is required.

Risk factors detected:
{$riskFactors}

Generate a friendly explanation message.
PROMPT;

        return new PromptResult($systemPrompt, $userPrompt);
    }
}
