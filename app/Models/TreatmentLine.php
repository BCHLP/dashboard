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
        'stage',
        'maintenance_mode',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
            'stage' => TreatmentStageEnum::class,
        ];
    }
}
