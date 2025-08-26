<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Network extends Model
{
    use HasFactory;

    public function routers(): BelongsToMany
    {
        return $this->belongsToMany(Router::class, 'router_network', 'network_id', 'router_id');
    }

    public function sensors(): BelongsToMany
    {
        return $this->belongsToMany(Sensor::class, 'sensor_network', 'network_id', 'sensor_id');
    }
}
