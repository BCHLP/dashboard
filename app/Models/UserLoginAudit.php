<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_at',
        'updated_at',
        'user_id',
        'user_fingerprint_id',
        'email',
        'successful',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fingerprint(): BelongsTo
    {
        return $this->belongsTo(UserFingerprint::class, 'user_fingerprint_id');
    }
}
