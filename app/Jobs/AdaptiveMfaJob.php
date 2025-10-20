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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdaptiveMfaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $email, public string $eventId, public string $fingerprintId)
    {
        ray("construct fingerprintid = $this->fingerprintId");
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
}
