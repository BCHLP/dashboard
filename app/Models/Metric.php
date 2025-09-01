<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Metric extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'baseline_minimum' => 'float',
            'baseline_maximum' => 'float',
        ];
    }

    public function devices(): HasMany
    {
        return $this->hasMany(DeviceMetric::class);
    }

    public function sensors(): MorphToMany
    {
        return $this->morphedByMany(Sensor::class, 'device_metric');
    }

    public function servers(): MorphToMany
    {
        return $this->morphedByMany(Server::class, 'device_metric');
    }

    public function routers(): MorphToMany
    {
        return $this->morphedByMany(Router::class, 'device_metric');
    }
}
