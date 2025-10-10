<?php
declare(strict_types=1);

namespace App\Services;
use App\Models\UserFingerprint;
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

        $messages = [
            ['role' => 'system', 'content' => 'You are an adaptive MFA decider. Use the provided tools to assess login risk.'],
            ['role' => 'user', 'content' => json_encode([
                'event_id' => $eventId,
                'user_id' => $userId,
                'fingerprint' => $fingerprint,
            ])],
        ];

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',  // or gpt-4o
            'messages' => $messages,
            'tools' => $tools,
        ]);

        $result = $response->choices[0]->message->content ?? '{}';

        try {
            return json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            Log::error('Adaptive MFA parse error', ['raw' => $result]);
            return ['decision' => 'allow_login', 'methods' => [], 'confidence' => 0, 'reason' => 'Parsing failed'];
        }
    }
}
