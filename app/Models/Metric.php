<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Metric extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function nodes(): BelongsToMany
    {
        return $this->belongsToMany(Node::class, 'node_metric', 'metric_id', 'node_id');
    }
}
