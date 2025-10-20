<?php
declare(strict_types=1);

namespace App\Interfaces;

use App\Models\UserFingerprint;

interface AgentAiInterface
{
    public function decide(int $userId, UserFingerprint $fingerprint, string $eventId): array;
}
