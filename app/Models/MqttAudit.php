<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MqttAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'when',
        'unusual',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'when' => 'datetime',
            'unusual' => 'boolean',
        ];
    }
}
