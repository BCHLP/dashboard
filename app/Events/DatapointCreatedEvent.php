<?php

namespace App\Events;

use App\Http\Resources\DatapointResource;
use App\Models\Datapoint;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DatapointCreatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Datapoint $datapoint) {}

    public function broadcastOn(): array
    {
        if ($this->datapoint->source_type === User::class) {
            return [];
        }

        ray('about to broadcast on NewDatapointEvent.'.$this->datapoint->source_id);

        return [
            new PrivateChannel('NewDatapointEvent.'.$this->datapoint->source_id),
        ];
    }

    public function broadcastWith()
    {
        return DatapointResource::make($this->datapoint)->resolve();
    }
}
