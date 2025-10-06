<?php
declare(strict_types=1);

namespace App\Services;
use App\Models\User;
use App\Models\UserVoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoiceRecognitionService
{
    public function register(string $base64audio) : bool {
        $endpoint = config('scada.services.voice.register');

        $response = Http::timeout(30)
            ->withToken(config('scada.services.voice.token'))
            ->asJson()
            ->withoutRedirecting()
            ->post($endpoint, [
                'audio' => $base64audio,
            ])->json() ?? [];

        if (filled($response["embeddings"])) {

            $voice = UserVoice::create([
                 'user_id' => auth()->user()->id,
                'embeddings' => $response["embeddings"]
            ]);

            return true;
        }

        return false;
    }

    public function compare(string $base64audio, User $user) : bool {
        $endpoint = config('scada.services.voice.compare');

        $embeddings = $user->voice->embeddings;
        if (blank($embeddings)) {
            return false;
        }

        $response = Http::timeout(30)
            ->withToken(config('scada.services.voice.token'))
            ->asJson()
            ->withoutRedirecting()
            ->post($endpoint, [
                'audio' => $base64audio,
                'embeddings' => $embeddings,
            ])->json() ?? [];

        Log::debug("response from server:" . print_r($response,true));

        $authenticated = $response['authenticated'] ?? false;

        return $authenticated;
    }

    public static function isVoiceAuthenticated() : bool {
        return (auth()->check() && session()->has('voice'));
    }
}
