<?php
declare(strict_types=1);

namespace App\Services;
use App\Mcp\Tools\GetRecentFailedAttempts;
use App\Mcp\Tools\GetUserLoginHistory;
use App\Mcp\Tools\RecordMFADecision;
use App\Models\UserFingerprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
class ChatGptMfaService
{
    public function decide(int $userId, UserFingerprint $fingerprint, string $eventId): array
    {
        // Describe your local MCP tools
        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'GetRecentFailedAttempts',
                    'description' => 'Get recent failed login attempts for a user',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer'],
                        ],
                        'required' => ['user_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'GetUserLoginHistory',
                    'description' => 'Get recent successful login attempts for a user',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer'],
                        ],
                        'required' => ['user_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'RecordMFADecision',
                    'description' => 'Record the MFA decision for audit',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer'],
                            'event_id' => ['type' => 'string'],
                            'voice' => ['type' => 'boolean'],
                            'totp' => ['type' => 'boolean'],
                        ],
                        'required' => ['user_id', 'event_id'],
                    ],
                ],
            ],
        ];

        $systemPrompt = <<<PROMPT
You are a security analyst specializing in adaptive authentication. Your task is to assess login risk and enforce appropriate MFA.

**Core Principle:**
If there is little or no user history, treat this as **high risk** due to lack of behavioral context.

**Risk Assessment Rules:**
- No historical logins → HIGH risk
- New device, location, or fingerprint → HIGH risk
- Many failed attempts → HIGH risk
- Normal consistent login pattern → LOW risk
- Minor anomalies → MEDIUM risk

**Required MFA Mapping:**
- LOW → none
- MEDIUM → TOTP
- HIGH → TOTP + Voice

**Process:**
1. Call `GetUserLoginHistory` (30 days) and `GetRecentFailedAttempts` (24 hours)
2. Analyze patterns and anomalies
3. Assign a risk level
4. Always call `RecordMFADecision` with your final decision before finishing

**Output Format:**
- Risk Level: [LOW/MEDIUM/HIGH]
- Required MFA: [none/TOTP/TOTP+Voice]
- Key Factors: bullet list
- Reasoning: short paragraph
- Confidence: [High/Medium/Low]
PROMPT;

        $max = 5;
        $count = 1;

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => json_encode([
                'event_id' => $eventId,
                'user_id' => $userId,
                'fingerprint' => $fingerprint,
            ])],
        ];
        $conversation = $messages;

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => $conversation,
            'tools' => $tools,
        ]);

        $totpRequired = false;
        $voiceRequired = false;


        do {
            $toolMessages = [];

            foreach ($response->choices as $choice) {
                $msg = $choice->message;
                if (filled($msg->content)) {
                    ray("CHATGPT: " . $msg->content);
                }

                // Save assistant message
                $conversation[] = [
                    'role' => 'assistant',
                    'tool_calls' => collect($msg->toolCalls ?? [])->map(fn($tc) => [
                        'id' => $tc->id,
                        'type' => 'function',
                        'function' => [
                            'name' => $tc->function->name,
                            'arguments' => $tc->function->arguments,
                        ],
                    ])->toArray(),
                    'content' => $msg->content,
                ];

                foreach ($msg->toolCalls ?? [] as $toolCall) {
                    $args = json_decode($toolCall->function->arguments, true);
                    $result = null;

                    switch ($toolCall->function->name) {
                        case 'GetRecentFailedAttempts':
                            $result = $this->getRecentFailedAttempts($args);
                            break;
                        case 'GetUserLoginHistory':
                            $result = $this->getUserLoginHistory($args);
                            break;
                        case 'RecordMFADecision':
                            $totpRequired = $args['totp'];
                            $voiceRequired = $args['voice'];
                            $result = (new RecordMFADecision())->handle($args);
                            break;
                    }

                    $toolMessages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall->id,
                        'content' => json_encode($result),
                    ];
                }
            }

            if (!empty($toolMessages)) {
                $conversation = array_merge($conversation, $toolMessages);

                // Send next step to GPT
                $response = OpenAI::chat()->create([
                    'model' => 'gpt-4o-mini',
                    'messages' => $conversation,
                    'tools' => $tools,
                ]);
            } else {
                break;
            }

            if ($count == $max) {
                break;
            }

            $count++;
        } while (true);

        return [
            'voice' => $voiceRequired,
            'totp' => $totpRequired,
            'reasoning' => Arr::get($response->choices[0]->message, 'content')
        ];
    }

    private function getRecentFailedAttempts(array $args) : array {
        $results = (new GetRecentFailedAttempts())->handle($args);
        foreach($results as $result) {
            return json_decode($result->content[0]->text, true);
        }

        return [];
    }

    private function getUserLoginHistory(array $args) : array {
        $results = (new GetUserLoginHistory())->handle($args);
        ray("GetUserLoginHistory", $results);
        foreach($results as $result) {
            return json_decode($result->content[0]->text, true);
        }

        return [];
    }
}
