<?php
declare(strict_types=1);

namespace App\Mcp\Prompts;

use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;
use Laravel\Mcp\Server\Prompts\Arguments;
use Laravel\Mcp\Server\Prompts\PromptResult;

class AnalyzeLoginRiskPrompt extends Prompt
{
    protected string $description = 'Structured prompt for analyzing login attempt risk and determining MFA requirements';

    /**
     * Get the prompt's arguments.
     *
     * @return Arguments
     */
    public function arguments(): Arguments
    {
        return (new Arguments)->add(

            new Argument(
                name: 'user_id',
                description: 'The ID of the user attempting to login',
                required: true,
            )
        )->add(
            new Argument(
                name: 'current_fingerprint',
                description: 'JSON fingerprint data from current login attempt',
                required: true,
            )
        );
    }

    public function handle(array $arguments): PromptResult
    {
        $userId = $arguments['user_id'];
        $fingerprint = $arguments['current_fingerprint'];

        $systemPrompt = <<<PROMPT
You are a security analyst specializing in adaptive authentication. Your role is to assess login risk and recommend appropriate MFA requirements.

**Available Tools:**
- GetUserLoginHistory: Fetch historical logins (both successful and un-successful)
- GetRecentFailedAttempts: Check for suspicious activity
- RecordMFADecision: Log your decision (call this after analysis)

**Available Resources:**
- auth://resources/risk-guidelines: Risk level criteria
- auth://resources/threat-patterns: Known attack indicators
- auth://resources/mfa-capabilities: Available MFA methods

**Analysis Process:**
1. Read the risk assessment guidelines
2. Fetch user's login history (last 30 days recommended)
3. Fetch recent failed attempts (last 24 hours)
4. Compare current fingerprint against historical patterns
5. Identify anomalies and threat indicators
6. Determine risk level: LOW, MEDIUM, or HIGH
7. Recommend MFA requirements based on risk level
8. Record your decision with clear reasoning

**Output Format:**
Provide a structured analysis with:
- Risk Level: [LOW/MEDIUM/HIGH]
- Required MFA: [none/TOTP/TOTP+Voice]
- Key Factors: Bullet list of indicators
- Reasoning: 2-3 sentence explanation
- Confidence: [High/Medium/Low]
PROMPT;

        $userPrompt = <<<PROMPT
Analyze this login attempt:

**User ID:** {$userId}

**Current Fingerprint:**
{$fingerprint}

Perform a comprehensive risk analysis and recommend appropriate MFA requirements.
PROMPT;

        return new PromptResult($systemPrompt, $userPrompt);
    }
}
