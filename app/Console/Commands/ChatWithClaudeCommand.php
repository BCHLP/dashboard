<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\ClaudeAgentService;

class ChatWithClaudeCommand extends Command
{
    protected $signature = 'claude:chat';
    protected $description = 'Send a prompt to Claude and get a response with tool execution';

    public function handle(ClaudeAgentService $claude): void
    {
        $userId = 14;
        $fingerprint = '00058602-608b-4906-bd71-4e820bf021ce';
        $eventId = '00058602-608b-4906-bd71-4e820bf021cd';

        $systemPrompt = <<<PROMPT
You are a security analyst specializing in adaptive authentication. Your role is to assess login risk and recommend appropriate MFA requirements.

**Available Tools:**
- get_user_login_history: Fetch historical logins (both successful and un-successful)
- get_recent_failed_attempts: Check for suspicious activity
- record_mfa_decision: Log your decision (call this after analysis)

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
**Event ID:** {$eventId}

**Current Fingerprint:**
{$fingerprint}

Perform a comprehensive risk analysis and recommend appropriate MFA requirements.
PROMPT;

        $this->info("Sending prompt to Claude...\n");

        try {
            $response = $claude->chat($userPrompt, $systemPrompt);

            $this->line("\n" . str_repeat('=', 60));
            $this->line("Claude's Response:");
            $this->line(str_repeat('=', 60) . "\n");
            $this->info($response);

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
