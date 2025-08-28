<?php

namespace App\Events;

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
        Log::debug("DatapointCreatedEvent");
    }

    public function broadcastOn(): array
    {
        Log::debug("Broadcast on " . "App.Models.Sensor.".$this->datapoint->sensor_id);
        return [
            new PrivateChannel("App.Models.Sensor.".$this->datapoint->sensor_id),
        ];
    }
}
