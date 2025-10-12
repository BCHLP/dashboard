<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MfaDecisionEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public string $eventId, public bool $totp, public bool $voice)
    {
    }

    public function broadcastOn(): array
    {
        ray("mfa decision broadcasting");
        return [
            new Channel('MfaProcess.'.$this->eventId),
        ];
    }
}
