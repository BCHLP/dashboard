<?php
declare(strict_types=1);

namespace App\Services;
use App\Events\MfaDecisionEvent;
use Illuminate\Support\Facades\Cache;

class AdaptiveMfaService
{

    public function load(string $eventId='') : bool|array {
        if (blank($eventId)) {
            $eventId = session('mfa_event_id') ?? '';
        }
        if (blank($eventId)) {
            return false;
        }

        $result = Cache::get('MfaDecision.'.$eventId);
        if (blank($result)) {
            return false;
        }

        return $result;
    }

    public function setTotp(bool $required, string $eventId='', ?int $userId=null) : void {
        $event = $this->load($eventId);
        $event['totp'] = $required;
        $event['user_id'] = $event['user_id'] ?? $userId ?? null;

        $this->save($event);

    }

    public function setVoice(bool $required, string $eventId='', int $userId=null) : void {
        $event = $this->load($eventId);
        $event['voice'] = $required;
        $event['user_id'] = $event['user_id'] ?? $userId ?? null;

        $this->save($event);
    }

    public function setBoth(bool $totp, bool $voice, string $eventId='', int $userId=null) : void {
        $event = $this->load($eventId);
        $event['totp'] = $totp;
        $event['voice'] = $voice;
        $event['event_id'] = $eventId;
        $event['user_id'] = $event['user_id'] ?? $userId ?? null;

        $this->save($event);
    }

    public function clear() : void {

    }

    private function save(array $event) : void {
        Cache::put('MfaDecision.'.$event['event_id'], $event);

        ray("dispatching mfa decision");
        MfaDecisionEvent::dispatch($event['event_id'], $event['totp'], $event['voice']);
    }
}
