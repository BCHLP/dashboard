<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActionAudit extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'action',
        'voice',
        'totp',
        'voice_complated_at',
        'totp_completed_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'voice' => 'boolean',
            'totp' => 'boolean',
            'voice_complated_at' => 'datetime',
            'totp_completed_at' => 'datetime',
        ];
    }

    protected $hidden = [
        'created_at',
        'updated_at',
        'action',
    ];
}
