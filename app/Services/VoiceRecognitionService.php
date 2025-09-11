<?php
declare(strict_types=1);

namespace App\Services;
use App\Models\UserVoice;
use Illuminate\Support\Facades\Http;
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

    public function compare(string $base64audio) {
        $endpoint = config('scada.services.voice.compare');

        $embeddings = auth()->user()->voice->embeddings;
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

        $authenticated = $response['authenticated'] ?? false;
        if ($authenticated) {
            session(["voice" => time()]);
        }

        return $authenticated;
    }

    public static function isVoiceAuthenticated() : bool {
        return (auth()->check() && session()->has('voice'));
    }
}
