<?php


use App\Models\User;
use App\Models\UserFingerprint;
use App\Services\ClaudeAgentService;

test('real Anthropic agentic ai decision', function () {
    $user = User::factory()->create();

    Event::fake();

    $fingerprint = UserFingerprint::factory()->create(['user_id' => $user->id]);

    $eventId = Str::uuid()->toString();

    $service = new ClaudeAgentService();
    $decision = $service->decide($user->id, $fingerprint, $eventId);

    ray("Claude Decision", $decision);

    expect($decision)
        ->toBeArray()
        ->and($decision)->toHaveKeys(['totp', 'voice', 'reasoning'])
        ->and($decision['reasoning'])->not->toBeEmpty()
        ->and($decision['totp'])->toBeTrue()
        ->and($decision['voice'])->toBeTrue();
})->skip();
