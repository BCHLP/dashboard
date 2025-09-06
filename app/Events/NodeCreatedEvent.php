<?php

namespace App\Events;

use App\Models\Node;
use Illuminate\Foundation\Events\Dispatchable;

class NodeCreatedEvent
{
    use Dispatchable;

    public function __construct(public Node $node)
    {

    }
}
