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
        'node_id',
        'metric_id',
        'value',
        'time'
    ];

    protected $casts = [
        'value' => 'float',
    ];

    public function metric(): BelongsTo
    {
        return $this->belongsTo(Metric::class);
    }
}
