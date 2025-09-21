<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebauthnCredential extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'credential_id',
        'public_key',
        'counter',
        'name',
        'type',
        'transports',
        'aaguid',
        'attestation_type',
        'attestation_object',
        'user_verified',
        'backup_eligible',
        'backup_state',
        'last_used_at',
        'usage_count',
    ];

    protected $casts = [
        'transports' => 'array',
        'user_verified' => 'boolean',
        'backup_eligible' => 'boolean',
        'backup_state' => 'boolean',
        'last_used_at' => 'datetime',
        'counter' => 'integer',
        'usage_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authLogs()
    {
        return $this->hasMany(WebAuthnAuthLog::class);
    }

    // Helper methods
    public function incrementUsage()
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    public function getReadableTransports(): string
    {
        $transports = $this->transports ?? [];
        return implode(', ', array_map('ucfirst', $transports));
    }
}
