<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NodePhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'node_id',
        'location',
        'face_detected',
    ];

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class);
    }

    protected function casts(): array
    {
        return [
            'face_detected' => 'boolean',
        ];
    }
}
