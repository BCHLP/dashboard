<?php

namespace App\Models;

use App\Enums\NodeTypeEnum;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;
use Kalnoy\Nestedset\NodeTrait;

class Node extends Model
{
    use HasFactory,HasApiTokens,NodeTrait;

    protected $guarded = [];

    protected $casts = [
        'coordinates' => Point::class,
        'node_type' => NodeTypeEnum::class,
    ];

    public function metrics(): BelongsToMany
    {
        return $this->belongsToMany(Metric::class, 'node_metric', 'node_id', 'metric_id');
    }

    public function settings(): HasMany {
        return $this->hasMany(NodeSetting::class);
    }

    public static function findByName(string $name): ?Node {
        $node = Node::where('name', $name)->first();
        if (!$node) {
            Log::error("Tried to set position for valve $name but it doesn't exist");
            return null;
        }
        return $node;
    }

}
