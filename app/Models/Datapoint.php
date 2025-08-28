<?php

namespace App\Models;

use App\Events\DatapointCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Datapoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'sensor_id',
        'metric_id',
        'value',
    ];

    protected $dispatchesEvents = [
        'created' => DatapointCreatedEvent::class,
    ];

    public function metric(): BelongsTo
    {
        return $this->belongsTo(Metric::class);
    }

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }
}
