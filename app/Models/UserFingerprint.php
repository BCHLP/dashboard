<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFingerprint extends Model
{

    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'fingerprint_data',
        'hash',
        'ip_address',
        'city',
        'country',
        'timezone',
        'timezone_offset',
        'browser',
        'platform',
        'device',
        'is_mobile',
        'user_agent',
        'session_id',
        'is_suspicious'
    ];

    protected $casts = [
        'fingerprint_data' => 'json',
        'is_suspicious' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
