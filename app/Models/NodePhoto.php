<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function path(): Attribute
    {
        return Attribute::make(
            get: fn () => '/media/'.$this->location,
        );
    }
}
