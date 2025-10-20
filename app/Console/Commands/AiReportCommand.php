<?php

namespace App\Console\Commands;

use App\Enums\AnthropicModelEnum;
use App\Interfaces\AgentAiInterface;
use App\Models\User;
use App\Models\UserFingerprint;
use App\Models\UserLoginAudit;
use App\Services\ChatGptMfaService;
use App\Services\ClaudeAgentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AiReportCommand extends Command
{
    protected $signature = 'ai:report';

    protected $description = 'Runs the comparison between the different LLM options';

    private $bar;

    public function handle(): void
    {
        $claudeAgent = new ClaudeAgentService(AnthropicModelEnum::HAIKU);
        $claudeSonnetAgent = new ClaudeAgentService(AnthropicModelEnum::SONNET);
        $chatGptAgent = new ChatGptMfaService();

        Event::fake();

        // setup csv
        $csv = fopen(Storage::path('ai-report-'.Str::uuid()->toString().'.csv'), 'w+');
        fputcsv($csv, ["Task",
            "Haiku TOTP", "Haiku Voice", "Haiku Response (secs)", "Haiku Reasoning",
            "Sonnet TOTP", "Sonnet Voice", "Sonnet Response (secs)", "Haiku Reasoning",
            "ChatGPT TOTP", "ChatGPT Voice", "ChatGPT Response (secs)", "Haiku Reasoning"]);

        // create our test user
        $user = User::factory()->create();
        $fingerprint = UserFingerprint::factory()->create([
            'user_id' => $user->id,
            'browser' => "Edge",
            'platform' => "Win11",
            'device' => "Windows",
            'is_mobile' => false,
            'user_agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36 Edg/118.0.2088.46"
        ]);

        // display progress bar
        $this->bar = $this->output->createProgressBar(24);
        $this->bar->start();

        // task 1 - user with no history
        $claude = $this->test($claudeAgent, $user, $fingerprint);
        $claudeSonnet = $this->test($claudeSonnetAgent, $user, $fingerprint);
        $chatgpt = $this->test($chatGptAgent, $user, $fingerprint);
        $this->save($csv, "No history", $claude, $claudeSonnet, $chatgpt);

        $defaultHistoryCount = 20;

        // task 2 - user with successful logins from same fingerprint
        UserLoginAudit::factory()->count($defaultHistoryCount)->create([
            'user_id' => $user->id,
            'user_fingerprint_id' => $fingerprint->id,
            'email' => $user->email,
            'successful' => true
        ]);
        $claude = $this->test($claudeAgent, $user, $fingerprint);
        $claudeSonnet = $this->test($claudeSonnetAgent, $user, $fingerprint);
        $chatgpt = $this->test($chatGptAgent, $user, $fingerprint);
        $this->save($csv, "$defaultHistoryCount successful, same fingerprint", $claude,  $claudeSonnet,$chatgpt);

        // task 3 - user with successful logins with different fingerprints
        $this->cleanUser($user, $fingerprint);
        UserFingerprint::factory()->count($defaultHistoryCount)->create(['user_id' => $user->id])->each(function (UserFingerprint $ufp) use ($user, $defaultHistoryCount) {
            UserLoginAudit::factory()->count($defaultHistoryCount)->create([
                'user_id' => $user->id,
                'user_fingerprint_id' => $ufp->id,
                'email' => $user->email,
                'successful' => true,
            ]);
        });
        $claude = $this->test($claudeAgent, $user, $fingerprint);
        $claudeSonnet = $this->test($claudeSonnetAgent, $user, $fingerprint);
        $chatgpt = $this->test($chatGptAgent, $user, $fingerprint);
        $this->save($csv, "$defaultHistoryCount successful, different fingerprints", $claude, $claudeSonnet, $chatgpt);


        // task 4 - user with successful and unsuccessful logins with different fingerprints
        $this->cleanUser($user, $fingerprint);
        UserFingerprint::factory()->count($defaultHistoryCount)->create(['user_id' => $user->id])->each(function (UserFingerprint $ufp) use ($user, $defaultHistoryCount) {
            UserLoginAudit::factory()->count($defaultHistoryCount)->create([
                'user_id' => $user->id,
                'user_fingerprint_id' => $ufp->id,
                'email' => $user->email,
                'successful' => true,
            ]);
        });
        UserFingerprint::factory()->count($defaultHistoryCount)->create(['user_id' => $user->id])->each(function (UserFingerprint $ufp) use ($user, $defaultHistoryCount) {
            UserLoginAudit::factory()->count($defaultHistoryCount)->create([
                'user_id' => $user->id,
                'user_fingerprint_id' => $ufp->id,
                'email' => $user->email,
                'successful' => false,
            ]);
        });
        $claude = $this->test($claudeAgent, $user, $fingerprint);
        $claudeSonnet = $this->test($claudeSonnetAgent, $user, $fingerprint);
        $chatgpt = $this->test($chatGptAgent, $user, $fingerprint);
        $this->save($csv, "$defaultHistoryCount successful, $defaultHistoryCount unsuccessful, different fingerprints", $claude,  $claudeSonnet, $chatgpt);

        // task 5 - only unsuccessful attempts
        $this->cleanUser($user, $fingerprint);
        UserFingerprint::factory()->count($defaultHistoryCount)->create(['user_id' => $user->id])->each(function (UserFingerprint $ufp) use ($user,$defaultHistoryCount) {
            UserLoginAudit::factory()->count($defaultHistoryCount)->create([
                'user_id' => $user->id,
                'user_fingerprint_id' => $ufp->id,
                'email' => $user->email,
                'successful' => false,
            ]);
        });
        $claude = $this->test($claudeAgent, $user, $fingerprint);
        $claudeSonnet = $this->test($claudeSonnetAgent, $user, $fingerprint);
        $chatgpt = $this->test($chatGptAgent, $user, $fingerprint);
        $this->save($csv, "0 successful, $defaultHistoryCount unsuccessful, different fingerprints", $claude, $claudeSonnet, $chatgpt);

        // task 6 - no unsuccessful attempts, but different geo location
        $this->cleanUser($user, $fingerprint);
        UserFingerprint::factory()->count($defaultHistoryCount)->create([
            'user_id' => $user->id,
            'city' => 'Auckland',
            'country' => 'New Zealand',
            'timezone' => 'Pacific/Auckland',
            'timezone_offset' => 780,
        ])->each(function (UserFingerprint $ufp) use ($user,$defaultHistoryCount) {
            UserLoginAudit::factory()->count($defaultHistoryCount)->create([
                'user_id' => $user->id,
                'user_fingerprint_id' => $ufp->id,
                'email' => $user->email,
                'successful' => true,
            ]);
        });
        $claude = $this->test($claudeAgent, $user, $fingerprint);
        $claudeSonnet = $this->test($claudeSonnetAgent, $user, $fingerprint);
        $chatgpt = $this->test($chatGptAgent, $user, $fingerprint);
        $this->save($csv, "$defaultHistoryCount successful but different geo-location", $claude, $claudeSonnet, $chatgpt);



        // task 7 - no unsuccessful attempts, but different time
        $this->cleanUser($user, $fingerprint);
        UserFingerprint::factory()->count($defaultHistoryCount)->create(['user_id' => $user->id])->each(function (UserFingerprint $ufp) use ($user,$defaultHistoryCount) {
            UserLoginAudit::factory()->count($defaultHistoryCount)->create([
                'user_id' => $user->id,
                'user_fingerprint_id' => $ufp->id,
                'email' => $user->email,
                'successful' => true,
            ]);
        });

        // 3 am
        $fingerprint->created_at = now()->subDay()->setHour(3);

        $claude = $this->test($claudeAgent, $user, $fingerprint);
        $claudeSonnet = $this->test($claudeSonnetAgent, $user, $fingerprint);
        $chatgpt = $this->test($chatGptAgent, $user, $fingerprint);
        $this->save($csv, "$defaultHistoryCount successful but random time to login", $claude, $claudeSonnet, $chatgpt);


        // task 8 - only a small amount of authentication history
        $this->cleanUser($user, $fingerprint);
        UserFingerprint::factory()->count(3)->create([
            'user_id' => $user->id,
            'city' => 'Auckland',
            'country' => 'New Zealand',
            'timezone' => 'Pacific/Auckland',
            'timezone_offset' => 780,
        ])->each(function (UserFingerprint $ufp) use ($user) {
            UserLoginAudit::factory()->count(1)->create([
                'user_id' => $user->id,
                'user_fingerprint_id' => $ufp->id,
                'email' => $user->email,
                'successful' => true,
            ]);
        });
        $claude = $this->test($claudeAgent, $user, $fingerprint);
        $claudeSonnet = $this->test($claudeSonnetAgent, $user, $fingerprint);
        $chatgpt = $this->test($chatGptAgent, $user, $fingerprint);
        $this->save($csv, "Small amount of authentication history", $claude, $claudeSonnet, $chatgpt);


        fclose($csv);

        $this->bar->finish();
    }

    private function cleanUser($user, $excludeFingerprint) {
        UserLoginAudit::where('user_id', $user->id)->delete();
        UserFingerprint::where('user_id', $user->id)
            ->whereNot('id', $excludeFingerprint->id)
            ->delete();
    }

    private function save($csv, $task, $claude, $claudeSonnet, $chatgpt) : void {
        fputcsv($csv, [
            $task,
            $claude['totp'] ? 1 :0,
            $claude['voice'] ? 1 :0,
            $claude['time'],
            $claude['reasoning'],
            $claudeSonnet['totp'] ? 1 :0,
            $claudeSonnet['voice'] ? 1 :0,
            $claudeSonnet['time'],
            $claudeSonnet['reasoning'],
            $chatgpt['totp'] ? 1 :0,
            $chatgpt['voice'] ? 1 :0,
            $chatgpt['time'],
            $chatgpt['reasoning'],
        ]);
        $this->line("Sleeping for 1 minute");
        sleep(60);
        $this->line("Awake");
    }

    private function test(AgentAiInterface $agent, $user, $fingerprint) : array {

        $eventId = Str::uuid()->toString();

        $start = time();
        $result = $agent->decide($user->id, $fingerprint, $eventId);
        $end = time();
        $result['time'] = $end - $start;

        $this->bar->advance();

        return $result;
    }
}
