<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'uuid',
        'totp_secret',
        'totp_activated_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'totp_secret',
        'created_at',
        'updated_at',
        'voice',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function voice(): HasOne {
        return $this->hasOne(UserVoice::class);
    }

    public function webAuthnCredentials()
    {
        return $this->hasMany(WebauthnCredential::class);
    }

    public function webAuthnAuthLogs()
    {
        return $this->hasMany(WebauthnAuthLog::class);
    }

    public function hasWebAuthnCredentials(): bool
    {
        return $this->webAuthnCredentials()->exists();
    }

    public function getWebAuthnCredentialsByType(string $type = null): Collection
    {
        $query = $this->webAuthnCredentials();

        if ($type) {
            // Filter by authenticator type if needed
            $query->where('aaguid', $type);
        }

        return $query->get();
    }

    public function datapoints(): MorphMany {
        return $this->morphMany(Datapoint::class, 'source');
    }
}
