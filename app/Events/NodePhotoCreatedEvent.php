<?php

namespace App\Events;

use App\Http\Resources\NodePhotoResource;
use App\Models\NodePhoto;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NodePhotoCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public NodePhoto $nodePhoto)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('NewNodePhoto.'.$this->nodePhoto->node_id)
        ];
    }

    public function broadcastWith() {
        return NodePhotoResource::make($this->nodePhoto)->resolve();
    }
}
