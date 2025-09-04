<?php

namespace App\Models;

use App\Enums\TreatmentStageEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreatmentLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_node_id',
        'end_node_id',
        'stage',
        'maintenance_mode',
    ];

    public function startNode(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'start_node_id');
    }

    public function endNode(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'end_node_id');
    }

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
            'stage' => TreatmentStageEnum::class,
        ];
    }
}
