<?php

namespace App\Events;

use App\Http\Resources\DatapointResource;
use App\Models\Datapoint;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DatapointCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Datapoint $datapoint)
    {

    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("NewDatapointEvent"),
        ];
    }

    public function broadcastWith() {
        return DatapointResource::make($this->datapoint)->resolve();
    }
}
