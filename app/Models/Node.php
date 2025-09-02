<?php

namespace App\Models;

use App\Enums\NodeTypeEnum;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class Node extends Model
{
    use HasFactory,HasApiTokens;

    protected $guarded = [];

    protected $casts = [
        'coordinates' => Point::class,
        'node_type' => NodeTypeEnum::class,
    ];

    public function metrics(): BelongsToMany
    {
        return $this->belongsToMany(Metric::class, 'node_metric', 'node_id', 'metric_id');
    }

}
