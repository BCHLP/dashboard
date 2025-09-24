<?php
declare(strict_types=1);

namespace App\Services;
use App\Models\ActionAudit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Notifications\Action;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

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
            'id' => Str::uuid(),
            'action' => $action,
            'user_id' => $user->id,
            'totp' => $response['totp'],
            'voice' => $response['voice'],
        ]);

        return $audit;
    }

    public function getNextRoute(ActionAudit $audit, $authenticatedCallback=null, $unauthenticatedCallback=null) : Response|RedirectResponse {
        if (($audit->totp && blank($audit->totp_completed_at)) || ($audit->voice && blank($audit->voice_complated_at))) {
            if ($audit->action === 'Login') {
                if (!is_null($unauthenticatedCallback)) {
                    $unauthenticatedCallback();
                }

                return Inertia::render('auth/login', [
                    'auditAction' => $audit,
                ]);
            }
        }

        if (!is_null($authenticatedCallback)) {
            $authenticatedCallback();
        }

        dd("this would go to dashboard", $audit);

        $audit->delete();
        return Inertia::render('dashboard');
    }
}
