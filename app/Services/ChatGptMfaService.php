<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\AgentAiInterface;
use App\Mcp\Tools\GetRecentFailedAttempts;
use App\Mcp\Tools\GetUserLoginHistory;
use App\Mcp\Tools\RecordMFADecision;
use App\Models\UserFingerprint;
use OpenAI\Laravel\Facades\OpenAI;

class ChatGptMfaService implements AgentAiInterface
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

        $systemPrompt = <<<'PROMPT'
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
4. **MANDATORY: Call `RecordMFADecision` with your final decision. This is required and must be done before your response completes. Do not finish without calling this tool.**

**Output Format:**
- Risk Level: [LOW/MEDIUM/HIGH]
- Required MFA: [none/TOTP/TOTP+Voice]
- Key Factors: bullet list
- Reasoning: short paragraph
- Confidence: [High/Medium/Low]

**CRITICAL INSTRUCTION:** You must always call the RecordMFADecision tool. Your response is incomplete until this tool has been called. Never finish without making this tool call.
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
        $reasoning = '';
        $recordMFAWasCalled = false;

        ray('Start of Chat GPT conversation');
        do {
            $toolMessages = [];

            ray('Chat GPT Response', $response);

            foreach ($response->choices as $choice) {
                $msg = $choice->message;
                if (filled($msg->content) && empty($reasoning)) {
                    $reasoning = $msg->content;
                }

                // Save assistant message
                $conversation[] = [
                    'role' => 'assistant',
                    'tool_calls' => collect($msg->toolCalls ?? [])->map(fn ($tc) => [
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
                            ray('Chat gpt recording a decision', $args);
                            if (! isset($args['totp']) || ! isset($args['voice'])) {
                                ray('chat gpt error:', $toolCall)->red();
                                break;
                            }
                            $totpRequired = $args['totp'];
                            $voiceRequired = $args['voice'];
                            $result = (new RecordMFADecision)->handle($args);
                            break;
                        default:
                            ray('Tool name '.$toolCall->function->name.' does not exist?')->red();
                    }

                    $toolMessages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall->id,
                        'content' => json_encode($result),
                    ];
                }
            }

            // Check if RecordMFADecision was called

            foreach ($response->choices as $choice) {
                foreach ($choice->message->toolCalls ?? [] as $toolCall) {
                    if ($toolCall->function->name === 'RecordMFADecision') {
                        $recordMFAWasCalled = true;
                    }
                }
            }

            if (! empty($toolMessages)) {
                $conversation = array_merge($conversation, $toolMessages);

                // If RecordMFADecision wasn't called but we have reasoning, prompt for it
                if (! $recordMFAWasCalled && $count < $max) {
                    $conversation[] = [
                        'role' => 'user',
                        'content' => 'You have completed your analysis. Now you MUST call the RecordMFADecision tool with your decision (totp and voice booleans based on your risk assessment). This is required before completing.',
                    ];
                }

                // Send next step to GPT
                $response = OpenAI::chat()->create([
                    'model' => 'gpt-4o-mini',
                    'messages' => $conversation,
                    'tools' => $tools,
                ]);
            } else {
                // No tool calls made - if RecordMFADecision wasn't called, prompt for it
                if (! $recordMFAWasCalled && $count < $max) {
                    $conversation[] = [
                        'role' => 'user',
                        'content' => 'You have completed your analysis. Now you MUST call the RecordMFADecision tool with your decision (totp and voice booleans based on your risk assessment).',
                    ];

                    $response = OpenAI::chat()->create([
                        'model' => 'gpt-4o-mini',
                        'messages' => $conversation,
                        'tools' => $tools,
                    ]);
                } else {
                    break;
                }
            }

            if ($count == $max) {
                break;
            }

            $count++;
        } while (true);

        return [
            'voice' => $voiceRequired,
            'totp' => $totpRequired,
            'reasoning' => $reasoning,
        ];
    }

    private function getRecentFailedAttempts(array $args): array
    {
        $results = (new GetRecentFailedAttempts)->handle($args);
        foreach ($results as $result) {
            return json_decode($result->content[0]->text, true);
        }

        return [];
    }

    private function getUserLoginHistory(array $args): array
    {
        $results = (new GetUserLoginHistory)->handle($args);
        ray('GetUserLoginHistory', $results);
        foreach ($results as $result) {
            return json_decode($result->content[0]->text, true);
        }

        return [];
    }
}
