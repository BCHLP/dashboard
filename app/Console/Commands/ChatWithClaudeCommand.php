<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\ClaudeAgentService;

class ChatWithClaudeCommand extends Command
{
    protected $signature = 'claude:chat {prompt}';
    protected $description = 'Send a prompt to Claude and get a response with tool execution';

    public function handle(ClaudeAgentService $claude): void
    {
        $prompt = $this->argument('prompt');

        $this->info("Sending prompt to Claude...\n");

        try {
            $response = $claude->chat($prompt);

            $this->line("\n" . str_repeat('=', 60));
            $this->line("Claude's Response:");
            $this->line(str_repeat('=', 60) . "\n");
            $this->info($response);

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
