<?php

namespace App\Mcp\Tools;

use App\Models\UserLoginAudit;
use Generator;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Get Recent Failed Attempts')]
class GetRecentFailedAttempts extends Tool
{
    /**
     * A description of the tool.
     */
    public function description(): string
    {
        return 'Fetch the failed authentication attempts for a particular user';
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
     */
    public function handle(array $arguments): ToolResult|Generator
    {

        $history = UserLoginAudit::with('fingerprint')->where('user_id', $arguments['user_id'])
            ->where('created_at', '>=', now()->subDays($arguments['days_back'] ?? 7))
            ->where('successful', false)
            ->latest()
            ->limit($arguments['limit'] ?? 25)
            ->get()
            ->map(fn ($audit) => [
                'successful' => $audit->successful,
                'timestamp' => $audit->created_at->toIso8601String(),
                'ip_address' => $audit->fingerprint['ip_address'] ?? '',
                'city' => $audit->fingerprint['city'] ?? '',
                'country' => $audit->fingerprint['country'] ?? '',
                'timezone' => $audit->fingerprint['timezone'] ?? '',
                'timezone_offset' => $audit->fingerprint['timezone_offset'] ?? '',
                'browser' => $audit->fingerprint['browser'] ?? '',
                'platform' => $audit->fingerprint['platform'] ?? '',
                'device' => $audit->fingerprint['device'] ?? '',
                'is_mobile' => $audit->fingerprint['is_mobile'] ?? '',
                'fingerprint_hash' => $audit->fingerprint['hash'] ?? '',
            ]);

        yield ToolResult::json([
            'user_id' => $arguments['user_id'],
            'failed_history' => $history,
            'count' => $history->count(),
        ]);
    }
}
