<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserFingerprint;
use App\Services\UserFingerprintService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserFingerprintFactory extends Factory
{
    protected $model = UserFingerprint::class;

    public function definition(): array
    {
        $fingerprint = [
            'ip_address' => rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 250),
            'country' => 'Australia',
            'city' => 'Perth',
            "user_agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36",
            "accept_language" => "en-US,en;q=0.9",
            "accept_encoding" => "gzip, deflate, br, zstd",
            "accept" => "*/*",
            "referer" => "https://project.test/",
            "x_forwarded_for" => null,
            "x_real_ip" => null,
            "platform" => "Win10",
            "platform_version" => "1507",
            "browser" => "Chrome",
            "browser_version" => '139.0',
            "is_mobile" => false,
            "is_tablet" => false,
            "is_desktop" => true,
            "is_robot" => false,
            "connection_type" => null,
            "dnt" => "1",
            "upgrade_insecure_requests" => null,
            "timezone_offset" => -480,
            "is_secure" => true,
            "method" => "POST",
            "port" => 443,
            "scheme" => "https",
            "screen_width" => 1920,
            "screen_height" => 1080,
            "screen_color_depth" => null,
            "screen_pixel_ratio" => null,
            "available_screen_width" => null,
            "available_screen_height" => null,
            "canvas_fingerprint" => "data:image/png;base64,",
            "audio_fingerprint" => null,
            "fonts_available" => "Arial,Arial Black,Arial Narrow,Arial Unicode MS,Comic Sans MS,Courier,Courier New,Georgia,Helvetica,Impact,Tahoma,Times,Times New Roman,Trebuchet MS,Verdana",
            "timezone" => "Australia/Perth",
            "performance_timing" => null,
            "local_storage_enabled" => true,
            "session_storage_enabled" => true,
            "cookies_enabled" => true,
            "languages" => "en-US",
            "cpu_cores" => rand(4,6)*2,
            "memory_gb" => rand(4,6)*2,
            "max_touch_points" => 0,
            "battery_level" => 100,
            "battery_charging" => true,
            "plugins" => "[{\"name\" =>\"PDF Viewer\",\"filename\" =>\"internal-pdf-viewer\"},{\"name\" =>\"Chrome PDF Viewer\",\"filename\" =>\"internal-pdf-viewer\"},{\"name\" =>\"Chromium PDF Viewer\",\"filename\" =>\"internal-pdf-viewer\"},{\"name\" =>\"Microsoft Edge PDF Viewer\",\"filename\" =>\"internal-pdf-viewer\"},{\"name\" =>\"WebKit built-in PDF\",\"filename\" =>\"internal-pdf-viewer\"}]",
            'device' => 'PC',
        ];

        $service = new UserFingerprintService();
        $fingerprint['webrtc_ip'] = $fingerprint['ip_address'];
        $fingerprint['hash'] = $service->generateFingerprintHash($fingerprint);



        return [
            'id' => Str::uuid()->toString(),
            'user_id' => User::factory(),
            'fingerprint_data' => $fingerprint,
            'hash' => $fingerprint['hash'],
            'ip_address' => $fingerprint['ip_address'],
            'city' => $fingerprint['city'],
            'country' => $fingerprint['country'],
            'timezone' => $fingerprint['timezone'],
            'timezone_offset' => $fingerprint['timezone_offset'],
            'browser' => $fingerprint['browser'],
            'platform' => $fingerprint['platform'],
            'device' => $fingerprint['device'],
            'is_mobile' => $fingerprint['is_mobile'],
            'user_agent' => $fingerprint['user_agent'],
            'session_id' => Str::uuid()->toString(),
        ];
    }
}
