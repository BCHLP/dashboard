<?php

namespace App\Models;

use App\Events\DatapointCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Datapoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_at',
        'updated_at',
        'source_id',
        'source_type',
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

    public function source(): MorphTo {
        return $this->morphTo();
    }
}
