<?php

namespace App\Jobs;

use App\Events\MfaDecisionEvent;
use App\Models\User;
use App\Services\AdaptiveMFAService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class AdaptiveMfaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $email, public string $eventId)
    {
    }

    public function handle(): void
    {
        $user = User::where('email', $this->email)->first();
        if (!$user) return;

//        $adaptiveMfaService = new AdaptiveMfaService;
//        $adaptiveMfaService->getFactors("Login", $user);

        sleep(5);
        $result = [
            'totp' => false,
            'voice' => false,
            'user_id' => $user->id,
        ];

        Cache::put('MfaDecision.'.$this->eventId, json_encode($result), 600);
        MfaDecisionEvent::dispatch($this->eventId, $result['totp'], $result['voice']);
    }
}
