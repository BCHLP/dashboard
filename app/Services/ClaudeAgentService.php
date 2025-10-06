<?php
declare(strict_types=1);

namespace App\Services;

use Anthropic\Client;
use App\Events\MfaDecisionEvent;
use App\Mcp\Tools\GetUserLoginHistory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClaudeAgentService
{
    private array $tools;
    private int $maxIterations = 10;
    private $client;

    public function __construct()
    {
        $this->client = new Client(config('services.anthropic.api_key'));
        $this->tools = $this->getToolDefinitions();
    }

    /**
     * Send a prompt to Claude and handle the full conversation loop with tool execution
     */
    public function chat(string $prompt, string $system=null): string
    {
        $messages = [
            ['role' => 'user', 'content' => $prompt]
        ];

        $iterations = 0;

        while ($iterations < $this->maxIterations) {
            $iterations++;

            try {
                $response = $this->client->messages->create(
                    maxTokens: 4096,
                    messages: $messages,
                    model: 'claude-sonnet-4-5-20250929',
                    system: $system,
                    tools: $this->tools
                );

                // If Claude is done, return the text response
                if ($response->stopReason === 'end_turn') {
                    return $this->extractTextContent($response->content);
                }

                // If Claude wants to use tools
                if ($response->stopReason === 'tool_use') {
                    // Add Claude's response to messages
                    $messages[] = [
                        'role' => 'assistant',
                        'content' => $response->content
                    ];

                    // Execute tools and get results
                    $toolResults = $this->executeTools($response->content);

                    // Add tool results to messages
                    $messages[] = [
                        'role' => 'user',
                        'content' => $toolResults
                    ];

                    // Continue the loop to get Claude's next response
                    continue;
                }

                // If max_tokens reached, return what we have
                if ($response->stopReason === 'max_tokens') {
                    return $this->extractTextContent($response->content) . "\n\n[Response truncated - max tokens reached]";
                }

                // Unknown stop reason
                Log::warning('Unknown stop reason: ' . $response->stopReason);
                return $this->extractTextContent($response->content);

            } catch (\Exception $e) {
                Log::error('Claude API Error: ' . $e->getMessage());
                throw $e;
            }
        }

        return "Maximum iterations reached. The conversation loop exceeded the limit.";
    }

    /**
     * Execute tool calls from Claude's response
     */
    private function executeTools(array $content): array
    {
        $results = [];

        foreach ($content as $block) {
            if ($block->type === 'tool_use') {
                $toolName = $block->name;
                $toolInput = (array) $block->input;
                $toolUseId = $block->id;

                try {
                    $result = $this->executeTool($toolName, $toolInput);

                    $results[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $toolUseId,
                        'content' => json_encode($result)
                    ];

                    Log::info("Tool executed: {$toolName}", ['input' => $toolInput, 'result' => $result]);

                } catch (\Exception $e) {
                    // Return error to Claude
                    $results[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $toolUseId,
                        'content' => json_encode([
                            'error' => $e->getMessage()
                        ]),
                        'is_error' => true
                    ];

                    Log::error("Tool execution failed: {$toolName}", [
                        'input' => $toolInput,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $results;
    }

    /**
     * Route and execute individual tools
     */
    private function executeTool(string $toolName, array $input): array
    {
        return match($toolName) {
            'get_user_login_history' => $this->getUserLoginHistory($input),
            'get_recent_failed_attempts' => $this->getRecentFailedAttempts($input),
            'record_mfa_decision' => $this->recordMFADecision($input),
            default => throw new \Exception("Unknown tool: {$toolName}")
        };
    }

    /**
     * Tool Implementation: Get User Login History
     */
    private function getUserLoginHistory(array $input): array
    {
        $tool = new GetUserLoginHistory();
        $results = $tool->handle($input);
        foreach($results as $result) {
            $json = json_decode($result->content[0]->text, true);
            ray("return this array", $json);
            return $json;

        }

        return [];
    }

    /**
     * Tool Implementation: Get Recent Failed Attempts
     */
    private function getRecentFailedAttempts(array $input): array
    {
        $userId = $input['user_id'];
        $daysBack = $input['days_back'] ?? 7;
        $limit = $input['limit'] ?? 50;

        return [
            'user_id' => $userId,
            'days_back' => $daysBack,
            'failed_attempts_count' => 0,
            'attempts' => []
        ];
    }

    /**
     * Tool Implementation: Record MFA Decision
     */
    private function recordMFADecision(array $input): array
    {
        $userId = $input['user_id'];
        $eventId = $input['event_id'];
        $totp = $input['totp'];
        $voice = $input['voice'];

        ray("record mfa decision", $input);

        Cache::put('MfaDecision.'.$eventId, json_encode($input), 600);
        MfaDecisionEvent::dispatch($eventId, $totp, $voice);

        return [
            'success' => true,
            'user_id' => $userId,
            'recorded_at' => now()->toIso8601String()
        ];
    }

    /**
     * Define available tools for Claude
     */
    private function getToolDefinitions(): array
    {
        return [
            [
                'name' => 'get_user_login_history',
                'description' => 'Fetch a users login history with their digital fingerprints',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'The ID of the user'
                        ],
                        'days_back' => [
                            'type' => 'integer',
                            'description' => 'How many days of history to retrieve (default: 30)'
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Maximum number of records to return (default: 100)'
                        ]
                    ],
                    'required' => ['user_id']
                ]
            ],
            [
                'name' => 'get_recent_failed_attempts',
                'description' => 'Fetch the failed authentication attempts for a particular user',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'The ID of the user'
                        ],
                        'days_back' => [
                            'type' => 'integer',
                            'description' => 'How many days of history to retrieve (default: 7)'
                        ],
                        'limit' => [
                            'type' => 'integer',
                            'description' => 'Maximum number of records to return (default: 50)'
                        ]
                    ],
                    'required' => ['user_id']
                ]
            ],
            [
                'name' => 'record_mfa_decision',
                'description' => 'Record the MFA decision for a particular user and action',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'user_id' => [
                            'type' => 'integer',
                            'description' => 'The ID of the user'
                        ],'event_id' => [
                            'type' => 'string',
                            'description' => 'A UUID of the event'
                        ],
                        'totp' => [
                            'type' => 'boolean',
                            'description' => 'Is TOTP required for this user?'
                        ],
                        'voice' => [
                            'type' => 'boolean',
                            'description' => 'Is Voice Recognition required for this user?'
                        ]
                    ],
                    'required' => ['user_id', 'event_id', 'totp_mfa', 'voice_mfa']
                ]
            ]
        ];
    }

    /**
     * Extract text content from Claude's response
     */
    private function extractTextContent(array $content): string
    {
        $text = '';

        foreach ($content as $block) {
            if ($block->type === 'text') {
                $text .= $block->text;
            }
        }

        return trim($text);
    }

    /**
     * Set maximum iterations for the conversation loop
     */
    public function setMaxIterations(int $max): self
    {
        $this->maxIterations = $max;
        return $this;
    }
}
