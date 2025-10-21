<?php

namespace App\Models;

use App\Enums\NodeTypeEnum;
use App\Events\NodeCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;
use Kalnoy\Nestedset\NodeTrait;
use Laravel\Sanctum\HasApiTokens;

class Node extends Authenticatable
{
    use HasApiTokens,HasFactory,NodeTrait;

    protected $guarded = [];

    protected $casts = [
        'node_type' => NodeTypeEnum::class,
    ];

    protected $dispatchesEvents = [
        'created' => NodeCreatedEvent::class,
    ];

    public function metrics(): BelongsToMany
    {
        return $this->belongsToMany(Metric::class, 'node_metric', 'node_id', 'metric_id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(NodeSetting::class);
    }

    public static function findByName(string $name): ?Node
    {
        $node = Node::where('name', $name)->first();
        if (! $node) {
            Log::error("Tried to set position for valve $name but it doesn't exist");

            return null;
        }

        return $node;
    }

    public function datapoints(): MorphMany
    {
        return $this->morphMany(Datapoint::class, 'source');
    }
}
