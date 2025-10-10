<?php

namespace App\Jobs;

use App\Events\MfaDecisionEvent;
use App\Facades\AdaptiveMfaFacade;
use App\Models\User;
use App\Models\UserFingerprint;
use App\Services\AdaptiveMfaService;
use App\Services\ClaudeAgentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdaptiveMfaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $email, public string $eventId, public string $fingerprintId)
    {
    }

    public function handle(): void
    {
        $user = User::where('email', $this->email)->first();
        if (!$user) return;

        $fingerprint = null;
        if (filled($this->fingerprintId) && Str::isUuid($this->fingerprintId)) {
            $fingerprint = UserFingerprint::find($this->fingerprintId);
        }

        if (blank($fingerprint)) {
            $fingerprint = "No fingerprint";
        }

        $chatGpt = new \App\Services\ChatGptMfaService();
        $decision = $chatGpt->decide($user->id, $fingerprint, $this->eventId);
        ray("Chat GPT decision", $decision);
    }

    private function claude($user, $fingerprint) {
;
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

**User ID:** {$user->id}
**Event ID:** {$this->eventId}

**Current Fingerprint:**
{$fingerprint}

Perform a comprehensive risk analysis and recommend appropriate MFA requirements.
PROMPT;

            $claude = new ClaudeAgentService();
            $response = $claude->chat($userPrompt, $systemPrompt);

            ray($response);

    }
}
