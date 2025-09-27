<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;

class UserFingerprintService
{
    protected $agent;

    public function __construct()
    {
        $this->agent = new Agent();
    }

    /**
     * Generate a comprehensive user fingerprint
     */
    public function generateFingerprint(Request $request, array $clientData = []): array
    {
        $clientFingerprint = $this->processClientSideData($clientData);
        $serverFingerprint = $this->getServerSideFingerprint($request, $clientFingerprint);

        $combinedFingerprint = array_merge($serverFingerprint, $clientFingerprint);

        // Generate a hash of the complete fingerprint for quick comparison
        $combinedFingerprint['hash'] = $this->generateFingerprintHash($combinedFingerprint);
        $combinedFingerprint['collected_at'] = now()->toISOString();

        return $combinedFingerprint;
    }

    /**
     * Collect server-side fingerprint data
     */
    protected function getServerSideFingerprint(Request $request, array $clientData): array
    {
        $location = $this->getLocationFromIP($request->ip());
        if (blank($location)) {
            $location = $this->getLocationFromIP($clientData['webrtc_ip']);
        }

        return [
            // Network Information
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accept_language' => $request->header('Accept-Language'),
            'accept_encoding' => $request->header('Accept-Encoding'),
            'accept' => $request->header('Accept'),
            'referer' => $request->header('Referer'),
            'x_forwarded_for' => $request->header('X-Forwarded-For'),
            'x_real_ip' => $request->header('X-Real-IP'),

            // Device/Browser Information
            'platform' => $this->agent->platform(),
            'platform_version' => $this->agent->version($this->agent->platform()),
            'browser' => $this->agent->browser(),
            'browser_version' => $this->agent->version($this->agent->browser()),
            'is_mobile' => $this->agent->isMobile(),
            'is_tablet' => $this->agent->isTablet(),
            'is_desktop' => $this->agent->isDesktop(),
            'is_robot' => $this->agent->isRobot(),
            'device' => $this->agent->device(),

            // Connection Information
            'connection_type' => $request->header('Connection'),
            'dnt' => $request->header('DNT'), // Do Not Track
            'upgrade_insecure_requests' => $request->header('Upgrade-Insecure-Requests'),

            // Geographic (basic)
            'timezone_offset' => $request->header('X-Timezone-Offset'),
            'country' => $location['country'] ?? null,
            'country_code' => $location['country_code'] ?? null,
            'region' => $location['region'] ?? null,
            'region_code' => $location['region_code'] ?? null,
            'city' => $location['city'] ?? null,
            'postal_code' => $location['postal_code'] ?? null,

            // Request Context
            'is_secure' => $request->secure(),
            'method' => $request->method(),
            'port' => $request->getPort(),
            'scheme' => $request->getScheme(),
        ];
    }

    /**
     * Process client-side collected data
     */
    protected function processClientSideData(array $clientData): array
    {
        return [
            // Screen Information
            'screen_width' => $clientData['screen_width'] ?? null,
            'screen_height' => $clientData['screen_height'] ?? null,
            'screen_color_depth' => $clientData['screen_color_depth'] ?? null,
            'screen_pixel_ratio' => $clientData['screen_pixel_ratio'] ?? null,
            'available_screen_width' => $clientData['available_screen_width'] ?? null,
            'available_screen_height' => $clientData['available_screen_height'] ?? null,

            // Browser Capabilities
            'canvas_fingerprint' => $clientData['canvas_fingerprint'] ?? null,
            'webgl_vendor' => $clientData['webgl_vendor'] ?? null,
            'webgl_renderer' => $clientData['webgl_renderer'] ?? null,
            'audio_fingerprint' => $clientData['audio_fingerprint'] ?? null,
            'fonts_available' => $clientData['fonts_available'] ?? null,

            // Timing and Performance
            'timezone' => $clientData['timezone'] ?? null,
            'timezone_offset' => $clientData['timezone_offset'] ?? null,
            'performance_timing' => $clientData['performance_timing'] ?? null,

            // Storage and Preferences
            'local_storage_enabled' => $clientData['local_storage_enabled'] ?? null,
            'session_storage_enabled' => $clientData['session_storage_enabled'] ?? null,
            'cookies_enabled' => $clientData['cookies_enabled'] ?? null,
            'languages' => $clientData['languages'] ?? null,

            // Hardware Information
            'cpu_cores' => $clientData['cpu_cores'] ?? null,
            'memory_gb' => $clientData['memory_gb'] ?? null,
            'max_touch_points' => $clientData['max_touch_points'] ?? null,
            'battery_level' => $clientData['battery_level'] ?? null,
            'battery_charging' => $clientData['battery_charging'] ?? null,

            // Plugins and Extensions
            'plugins' => $clientData['plugins'] ?? null,
            'webrtc_ip' => $clientData['webrtc_ip'] ?? null,
        ];
    }

    /**
     * Generate a hash from the fingerprint data
     */
    public function generateFingerprintHash(array $fingerprint): string
    {
        // Remove timestamp and hash itself to avoid circular reference
        $hashableData = $fingerprint;
        unset($hashableData['hash'], $hashableData['collected_at']);

        // Sort array to ensure consistent hashing
        ksort($hashableData);

        return hash('sha256', serialize($hashableData));
    }

    protected function getLocationFromIP(string $ip): ?array
    {
        $geoipService = app(GeoIpService::class);
        return $geoipService->getLocation($ip);
    }

    /**
     * Compare two fingerprints and calculate similarity score
     */
    public function calculateSimilarity(array $fingerprint1, array $fingerprint2): float
    {
        $totalFields = 0;
        $matchingFields = 0;

        // Define weighted importance of different fields
        $fieldWeights = [
            'ip_address' => 3.0,
            'user_agent' => 2.5,
            'screen_width' => 2.0,
            'screen_height' => 2.0,
            'canvas_fingerprint' => 3.0,
            'webgl_renderer' => 2.5,
            'audio_fingerprint' => 2.5,
            'timezone' => 1.5,
            'fonts_available' => 2.0,
            'platform' => 1.5,
            'browser' => 1.5,
        ];

        foreach ($fieldWeights as $field => $weight) {
            if (isset($fingerprint1[$field]) && isset($fingerprint2[$field])) {
                $totalFields += $weight;
                if ($fingerprint1[$field] === $fingerprint2[$field]) {
                    $matchingFields += $weight;
                }
            }
        }

        return $totalFields > 0 ? ($matchingFields / $totalFields) * 100 : 0;
    }

    /**
     * Check if a fingerprint represents suspicious activity
     */
    public function isSuspicious(array $currentFingerprint, array $userBaseline = []): bool
    {
        if (empty($userBaseline)) {
            return false; // Can't determine without baseline
        }

        $suspiciousIndicators = 0;

        // Check for major changes
        if (($currentFingerprint['ip_address'] ?? '') !== ($userBaseline['typical_ip'] ?? '')) {
            $suspiciousIndicators++;
        }

        if (($currentFingerprint['user_agent'] ?? '') !== ($userBaseline['typical_user_agent'] ?? '')) {
            $suspiciousIndicators++;
        }

        if (($currentFingerprint['canvas_fingerprint'] ?? '') !== ($userBaseline['typical_canvas'] ?? '')) {
            $suspiciousIndicators += 2; // Canvas changes are more significant
        }

        // Add more sophisticated checks based on your needs

        return $suspiciousIndicators >= 2;
    }

    /**
     * Store fingerprint for user baseline
     */
    public function updateUserBaseline(int $userId, array $fingerprint): void
    {
        $cacheKey = "user_baseline_{$userId}";
        $baseline = Cache::get($cacheKey, []);

        // Update baseline with current fingerprint data
        $baseline = array_merge($baseline, [
            'typical_ip' => $fingerprint['ip_address'] ?? null,
            'typical_user_agent' => $fingerprint['user_agent'] ?? null,
            'typical_canvas' => $fingerprint['canvas_fingerprint'] ?? null,
            'typical_screen_resolution' => ($fingerprint['screen_width'] ?? '') . 'x' . ($fingerprint['screen_height'] ?? ''),
            'last_updated' => now()->toISOString(),
        ]);

        // Cache for 30 days
        Cache::put($cacheKey, $baseline, now()->addDays(30));
    }
}
