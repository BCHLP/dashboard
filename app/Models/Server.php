<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Server extends Model
{
    use HasFactory;

    public function metrics(): MorphToMany
    {
        return $this->morphToMany(Metric::class, 'device_metric');
    }
}
