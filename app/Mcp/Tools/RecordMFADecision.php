<?php

namespace App\Mcp\Tools;

use App\Events\MfaDecisionEvent;
use App\Facades\AdaptiveMfaFacade;
use Generator;
use Illuminate\Support\Facades\Cache;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\Title;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;

#[Title('Record M F A Decision')]
class RecordMFADecision extends Tool
{
    /**
     * A description of the tool.
     */
    public function description(): string
    {
        return 'Record the MFA decision for a particular user and action';
    }

    /**
     * The input schema of the tool.
     */
    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        $schema->integer('event_id')
            ->description('The ID of the event')
            ->required();

        $schema->integer('user_id')
            ->description('The ID of the user')
            ->required();

        $schema->boolean('voice')
            ->description('Is Voice Recognition required for this user?')
            ->required();

        $schema->boolean('totp')
            ->description('Is TOTP required for this user?')
            ->required();

        return $schema;
    }

    /**
     * Execute the tool call.
     *
     * @return ToolResult|Generator
     */
    public function handle(array $arguments): ToolResult|Generator
    {
        $userId = $arguments['user_id'];
        $eventId = $arguments['event_id'];
        $totp = $arguments['totp'];
        $voice = $arguments['voice'];

        AdaptiveMfaFacade::setBoth( $totp, $voice, $eventId, $userId);

        return ToolResult::text('Tool executed successfully.');
    }
}
