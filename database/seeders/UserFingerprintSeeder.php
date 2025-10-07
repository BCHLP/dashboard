<?php

namespace Database\Seeders;

use App\Actions\UserLoginAuditAction;
use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserFingerprint;
use App\Models\UserLoginAudit;
use App\Services\BaselineService;
use App\Services\UserFingerprintService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UserFingerprintSeeder extends Seeder
{

    private Collection $operators;

    private ?User $currentOperator = null;
    private ?int $currentOperatorIndex = null;

    private array $operatorMetadata = [];

    public function run(UserLoginAuditAction $userLoginAudit): void
    {
        if (app()->isProduction()) {
            return;
        }

        $this->operators = User::factory()->count(12)->create();
        foreach ($this->operators as $operator) {
            $operator->roles->add(RoleEnum::NONE);
            $this->operatorMetadata[$operator->id] = $this->generateOperatorMetadata();
        }


        $daysToCreate = 30;
        $date = Carbon::now()->subDays($daysToCreate+1);
        $currentShiftHours = 0;
        $sessionId = '';


        for ($day = 1; $day <= $daysToCreate; $day++) {
            $date->addDay();
            for ($hour = 0; $hour < 24; $hour++) {
                $date->setHour($hour);
                for ($minute = 0; $minute < 60; $minute++) {

                    $date->setMinute($minute);

                    $createLoginAudit = false;
                    if ($this->currentOperator === null || $currentShiftHours > 8) {
                        $this->getNextOperator();
                        $currentShiftHours = 0;
                        $sessionId = Str::uuid();
                        $createLoginAudit = true;

                    }

                    $fingerprint = array_merge(
                        $this->operatorMetadata[$operator->id],
                        [
                            "collected_at" => $date->format('Y-m-d\TH:i:s.u\Z') // "2025-09-24T07:14:25.698821Z"
                        ]
                    );


                    $fingerprint = UserFingerprint::create([
                        'id' => Str::uuid()->toString(),
                        'user_id' => $this->currentOperator->id,
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
                        'session_id' => $sessionId,
                    ]);

                    if ($createLoginAudit) {
                        UserLoginAudit::create([
                            'user_id' => $this->currentOperator->id,
                            'user_fingerprint_id' =>$fingerprint->id,
                            'email' => $this->currentOperator->email,
                            'successful' => true,
                            'created_at' => $date->format('Y-m-d H:i:s'),
                            'updated_at' => $date->format('Y-m-d H:i:s'),
                        ]);
                    }
                }

                Carbon::setTestNow($date);
                (new BaselineService())->createLoginAuditDatapoints()->execute();

                $currentShiftHours++;
            }
        }
    }

    private function getNextOperator() : void
    {
        if (is_null($this->currentOperatorIndex)) {
            $this->currentOperatorIndex = 0;
            $this->currentOperator = $this->operators->first();
            return;
        }

        $this->currentOperatorIndex++;
        if ($this->currentOperatorIndex >= $this->operators->count()) {
            $this->currentOperatorIndex = 0;
            $this->currentOperator = $this->operators->first();
        }

        $this->currentOperator = $this->operators[$this->currentOperatorIndex];
    }

    private function generateOperatorMetadata() : array
    {
        $environments = [
            [
                'os' => 'Win10',
                'platform_version' => '1507',
                'browser' => 'Chrome',
                'browser_version' => '139.0',
                'device' => 'PC',
                "webgl_vendor" => "Google Inc. (Apple)",
                "webgl_renderer" => "ANGLE (Apple, ANGLE Metal Renderer: Apple M2 Max, Unspecified Version)",
                'useragent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'
            ],[
                'os' => 'Win10',
                'platform_version' => '1507',
                'browser' => 'Chrome',
                'browser_version' => '140.0',
                'device' => 'PC',
                "webgl_vendor" => "Google Inc. (Apple)",
                "webgl_renderer" => "ANGLE (Apple, ANGLE Metal Renderer: Apple M2 Max, Unspecified Version)",
                'useragent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'
            ],[
                'os' => 'macOS',
                'platform_version' => '10_15_7',
                'browser' => 'Chrome',
                'browser_version' => '139.0',
                'device' => 'Macintosh',
                "webgl_vendor" => "Google Inc. (Apple)",
                "webgl_renderer" => "ANGLE (Apple, ANGLE Metal Renderer: Apple M2 Max, Unspecified Version)",
                'useragent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36'
            ],[
                'os' => 'macOS',
                'platform_version' => '10_15_7',
                'browser' => 'Chrome',
                'browser_version' => '140.0',
                'device' => 'Macintosh',
                "webgl_vendor" => "Google Inc. (Apple)",
                "webgl_renderer" => "ANGLE (Apple, ANGLE Metal Renderer: Apple M2 Max, Unspecified Version)",
                'useragent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'
            ],[
                'os' => 'Win10',
                'platform_version' => '1507',
                'browser' => 'Firefox',
                'browser_version' => '142.0',
                'device' => 'PC',
                "webgl_vendor" => "Google Inc. (Apple)",
                "webgl_renderer" => "ANGLE (Apple, ANGLE Metal Renderer: Apple M2 Max, Unspecified Version)",
                'useragent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36'
            ],[
                'os' => 'Linux',
                'platform_version' => 'Ubuntu 24.04.2 LTS',
                'browser' => 'Firefox',
                'browser_version' => '142.0',
                'device' => 'PC',
                "webgl_vendor" => "Google Inc. (Apple)",
                "webgl_renderer" => "ANGLE (Apple, ANGLE Metal Renderer: Apple M2 Max, Unspecified Version)",
                'useragent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:142.0) Gecko/20100101 Firefox/142.0'
            ]
        ];

        $environmentId = array_rand($environments);

        $fingerprint = [
            'ip_address' => rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(1, 250),
            'country' => 'Australia',
            'city' => 'Perth',
            "user_agent" => $environments[$environmentId]['useragent'],
            "accept_language" => "en-US,en;q=0.9",
            "accept_encoding" => "gzip, deflate, br, zstd",
            "accept" => "*/*",
            "referer" => "https://project.test/",
            "x_forwarded_for" => null,
            "x_real_ip" => null,
            "platform" => $environments[$environmentId]['os'],
            "platform_version" => $environments[$environmentId]['platform_version'],
            "browser" => $environments[$environmentId]['browser'],
            "browser_version" => $environments[$environmentId]['browser_version'],
            "is_mobile" => false,
            "is_tablet" => false,
            "is_desktop" => true,
            "is_robot" => false,
            "device" => $environments[$environmentId]['device'],
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
            "webgl_vendor" => $environments[$environmentId]['webgl_vendor'],
            "webgl_renderer" => $environments[$environmentId]['webgl_renderer'],
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
        ];

        $service = new UserFingerprintService();
        $fingerprint['webrtc_ip'] = $fingerprint['ip_address'];
        $fingerprint['hash'] = $service->generateFingerprintHash($fingerprint);

        return $fingerprint;
    }
}
