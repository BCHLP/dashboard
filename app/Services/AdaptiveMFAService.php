<?php
declare(strict_types=1);

namespace App\Services;
use App\Models\ActionAudit;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class AdaptiveMFAService
{
    public function getFactors(string $action, ?User $user) : ?ActionAudit {
        $endpoint = config('scada.services.adaptive_mfa_endpoint');

        $response = Http::timeout(30)
            ->withToken(config('scada.services.voice.token'))
            ->asJson()
            ->withoutRedirecting()
            ->post($endpoint, [
                'action' => $action,
                'user_id' => $user->id ?? auth()->id(),
            ])->json() ?? [];


        if ($response['voice'] === false && $response['totp'] === false) {
            return null;
        }

        $audit = ActionAudit::create([
            'action' => $action,
            'user_id' => $user->id,
            'totp' => $response['totp'],
            'voice' => $response['voice'],
        ]);

        return $audit;
    }
}
