<?php

namespace App\Mcp\Tools;

use App\Models\UserLoginAudit;
use Generator;
use Illuminate\Support\Facades\Log;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Get User Login History')]
class GetUserLoginHistory extends Tool
{
    /**
     * A description of the tool.
     */
    public function description(): string
    {
        return 'Fetch a users login history with their digital fingerprints';
    }

    /**
     * The input schema of the tool.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('user_id')
                ->description('The ID of the user attempting login')
                ->required();

        $schema->integer('limit')
            ->description('Maximum number of records to return');

        $schema->integer('days_back')
            ->description('How many days of history to retrieve');
        return $schema;
    }

    /**
     * Execute the tool call.
     *
     * @return ToolResult|Generator
     */
    public function handle(array $arguments): ToolResult|Generator
    {

            $history = UserLoginAudit::with('fingerprint')->where('user_id', $arguments['user_id'])
                ->where('created_at', '>=', now()->subDays($arguments['days_back'] ?? 90))
                ->where('successful', true)
                ->latest()
                ->limit($arguments['limit'] ?? 50)
                ->get()
                ->map(fn($audit) => [
                    'successful' => $audit->successful,
                    'timestamp' => $audit->created_at->toIso8601String(),
                    'ip_address' => $audit->fingerprint->ip_address,
                    'city' => $audit->fingerprint['city'],
                    'country' => $audit->fingerprint['country'],
                    'timezone' => $audit->fingerprint['timezone'],
                    'timezone_offset' => $audit->fingerprint['timezone_offset'],
                    'browser' => $audit->fingerprint['browser'],
                    'platform' => $audit->fingerprint['platform'],
                    'device' => $audit->fingerprint['device'],
                    'is_mobile' => $audit->fingerprint['is_mobile'],
                    'fingerprint_hash' => $audit->fingerprint['hash'],
                ]);


        yield ToolResult::json([
            'user_id' => $arguments['user_id'],
            'login_history' => $history,
            'count' => $history->count(),
        ]);
    }
}
