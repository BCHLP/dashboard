<?php


use App\Models\UserFingerprint;
use App\Services\AdaptiveMfaService;
use App\Models\User;
use App\Services\ChatGptMfaService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

describe("run Open AI tests", function() {

    test('real OpenAI decision', function () {
        // Arrange
        $user = User::factory()->create();

        Event::fake();

        $fingerprint = UserFingerprint::factory()->create(['user_id' => $user->id]);

        $eventId = Str::uuid()->toString();

        $service = new ChatGptMfaService();
        $decision = $service->decide($user->id, $fingerprint, $eventId);

        expect($decision)
            ->toBeArray()
            ->and($decision)->toHaveKeys(['totp', 'voice'])
            ->and($decision['totp'])->toBeTrue()
            ->and($decision['voice'])->toBeTrue();
    })->skip("This test makes real calls to OpenAI. Do not include in CI pipeline");

    it('evaluates adaptive MFA decisions using GPT', function () {
        // Arrange
        $user = User::factory()->create();

        Event::fake();

        $fingerprint = UserFingerprint::factory()->create(['user_id' => $user->id]);

        // Fake the OpenAI API call
        Http::fake([
            'api.openai.com/*' => Http::response([
                'id' => Str::uuid(),
                'object' => 'chat.completion',
                'created' => now()->timestamp,
                'model' => 'gpt-4o-mini',
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => json_encode([
                                'decision' => 'require_mfa',
                                'methods' => ['totp'],
                                'confidence' => 0.9,
                                'reason' => 'Unusual device login detected',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $eventId = Str::uuid()->toString();

        $service = new ChatGptMfaService();
        $decision = $service->decide($user->id, $fingerprint, $eventId);

        expect($decision)
            ->toBeArray()
            ->and($decision)->toHaveKeys(['decision', 'methods', 'confidence', 'reason'])
            ->and($decision['decision'])->toBe('require_mfa')
            ->and($decision['methods'])->toContain('totp')
            ->and($decision['confidence'])->toBeGreaterThan(0.5);
    })->skip("This test makes real calls to OpenAI. Do not include in CI pipeline");

    it('simulates GPT using MCP tools to make an adaptive MFA decision', function () {
        // Arrange
        $user = User::factory()->create();

        $fingerprint = UserFingerprint::factory()->create(['user_id' => $user->id]);

        /**
         * This simulates how GPT might call the MCP tools internally:
         * - It gets recent failed attempts.
         * - It gets user login history.
         * - It records its MFA decision.
         */
        $mockToolResponses = [
            'GetRecentFailedAttempts' => [
                'failed_attempts' => [
                    ['created_at' => now()->subMinutes(5), 'ip' => '203.0.113.10', 'user_agent' => 'Chrome/119'],
                    ['created_at' => now()->subMinutes(10), 'ip' => '203.0.113.11', 'user_agent' => 'Chrome/119'],
                ],
            ],
            'GetUserLoginHistory' => [
                'login_history' => [
                    ['created_at' => now()->subDays(1), 'ip' => '192.168.1.10', 'user_agent' => 'Safari/17.0'],
                ],
            ],
            'RecordMFADecision' => [
                'recorded' => true,
                'decision_id' => 123,
            ],
        ];

        // Fake OpenAI API
        Http::fake([
            'api.openai.com/*' => function ($request) use ($mockToolResponses) {
                $body = json_decode($request->body(), true);

                // Verify the GPT prompt looks correct
                expect($body)
                    ->toHaveKey('messages')
                    ->and($body['messages'][1]['content'])->toContain('event_id');

                // The simulated GPT logic: "oh, multiple failed attempts — require MFA"
                $fakeResponse = [
                    'id' => Str::uuid(),
                    'object' => 'chat.completion',
                    'created' => now()->timestamp,
                    'model' => $body['model'] ?? 'gpt-4o-mini',
                    'choices' => [
                        [
                            'message' => [
                                'role' => 'assistant',
                                'content' => json_encode([
                                    'decision' => 'require_mfa',
                                    'methods' => ['totp'],
                                    'confidence' => 0.88,
                                    'reason' => 'Multiple recent failed logins from new IP detected',
                                    'tool_calls' => $mockToolResponses,
                                ]),
                            ],
                        ],
                    ],
                ];

                return Http::response($fakeResponse, 200);
            },
        ]);

        $eventId = Str::uuid()->toString();
        $service = new ChatGptMfaService();
        $decision = $service->decide($user->id, $fingerprint, $eventId);

        // Assert
        expect($decision)
            ->toBeArray()
            ->and($decision)->toHaveKeys(['decision', 'methods', 'confidence', 'reason'])
            ->and($decision['decision'])->toBe('require_mfa')
            ->and($decision['methods'])->toContain('totp')
            ->and($decision['confidence'])->toBeGreaterThan(0.7)
            ->and($decision['reason'])->toContain('failed logins')
            ->and($decision['tool_calls']['RecordMFADecision']['recorded'])->toBeTrue();

        // Simulate that GPT recorded its decision
    })->skip("This test makes real calls to OpenAI. Do not include in CI pipeline");
});
