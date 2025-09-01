<?php

namespace App\Models;

use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Sensor extends Model
{
    use HasFactory;

    protected $casts = [
        'coordinates' => Point::class,
    ];

    public function networks(): BelongsToMany
    {
        return $this->belongsToMany(Network::class, 'sensor_network', 'sensor_id', 'network_id');
    }

    public function metrics(): MorphToMany
    {
        return $this->morphToMany(Metric::class, 'device_metric');
    }
}
