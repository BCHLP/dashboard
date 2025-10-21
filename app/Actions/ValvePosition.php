<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Node;
use App\Models\NodeSetting;
use Illuminate\Support\Facades\Log;

class ValvePosition
{
    public function __invoke(Node|string $node, int $position)
    {

        if (is_string($node)) {
            $node = Node::findByName($node);
            if (! $node) {
                return;
            }
        }

        ray("Setting valve {$node->name} to position {$position}");

        $setting = NodeSetting::where('node_id', $node->id)
            ->where('name', 'opened')
            ->update(['value' => $position]);

        if (! $setting) {
            Log::error("Tried to save position for valve {$node->name} but setting doesn't exist");
        }

    }
}
