<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserFingerprint;
use App\Services\UserFingerprintService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FingerprintController extends Controller
{
    public function __construct(private UserFingerprintService $fingerprintService) {

    }

    public function __invoke(Request $request)
    {
        try {
            $clientData = $request->validate([
                'screen_width' => 'nullable|integer',
                'screen_height' => 'nullable|integer',
                'canvas_fingerprint' => 'nullable|string',
                'webgl_vendor' => 'nullable|string',
                'webgl_renderer' => 'nullable|string',
                'audio_fingerprint' => 'nullable|string',
                'fonts_available' => 'nullable|string',
                'timezone' => 'nullable|string',
                'timezone_offset' => 'nullable|integer',
                'local_storage_enabled' => 'nullable|boolean',
                'session_storage_enabled' => 'nullable|boolean',
                'cookies_enabled' => 'nullable|boolean',
                'languages' => 'nullable|string',
                'cpu_cores' => 'nullable|integer',
                'memory_gb' => 'nullable|numeric',
                'max_touch_points' => 'nullable|integer',
                'battery_level' => 'nullable|integer',
                'battery_charging' => 'nullable|boolean',
                'plugins' => 'nullable|string',
                'webrtc_ip' => 'nullable|string',
            ]);

            $fingerprint = $this->fingerprintService->generateFingerprint($request, $clientData);
            $userFingerprint = UserFingerprint::where('user_id', auth()->id())->where('hash', $fingerprint['hash'])->first();

            if (!$userFingerprint) {
                // Store in database
                $userFingerprint = UserFingerprint::create([
                    'id' => Str::uuid()->toString(),
                    'user_id' => auth()->id(),
                    'fingerprint_data' => $fingerprint,
                    'hash' => $fingerprint['hash'],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'session_id' => session()->getId(),
                ]);
            }

            // Update user baseline
            $this->fingerprintService->updateUserBaseline(auth()->id() ?? 0, $fingerprint);

            // Check for suspicious activity
            $userBaseline = cache("user_baseline_" . auth()->id(), []);
            $isSuspicious = $this->fingerprintService->isSuspicious($fingerprint, $userBaseline);

            if ($isSuspicious) {
                // Log suspicious activity
                \Log::warning('Suspicious fingerprint detected', [
                    'user_id' => auth()->id(),
                    'fingerprint_id' => $userFingerprint->id,
                    'current_ip' => $request->ip(),
                    'baseline_ip' => $userBaseline['typical_ip'] ?? 'unknown'
                ]);
            }

            session(['fingerprint_id' => $userFingerprint->id]);

            return response()->json([
                'success' => true,
                'fingerprint_id' => $userFingerprint->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Fingerprint storage failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store fingerprint'
            ], 500);
        }
    }
}
