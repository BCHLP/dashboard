<?php

namespace App\Models;

use App\Enums\NodeTypeEnum;
use App\Enums\TreatmentStageEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreatmentLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stage_1',
        'stage_2',
        'stage_3',
        'maintenance_mode',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
            'stage_1' => TreatmentStageEnum::class,
            'stage_2' => TreatmentStageEnum::class,
            'stage_3' => TreatmentStageEnum::class,
        ];
    }

    public function nodes(): HasMany {
        return $this->hasMany(Node::class);
    }

    public function tanks() : HasMany {
        return $this->nodes()
            ->whereIn('node_type',
                [NodeTypeEnum::AERATION_TANK,NodeTypeEnum::DIGESTION_TANK, NodeTypeEnum::SEDIMENTATION_TANK]);
    }
}
