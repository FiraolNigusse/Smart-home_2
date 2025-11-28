<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\PasswordHistory;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get all audit logs for the user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function devicePermissions(): HasMany
    {
        return $this->hasMany(DevicePermission::class, 'target_user_id');
    }

    public function ownedDevicePermissions(): HasMany
    {
        return $this->hasMany(DevicePermission::class, 'owner_user_id');
    }

    public function roleChangeRequests(): HasMany
    {
        return $this->hasMany(RoleChangeRequest::class);
    }

    public function attributeProfile(): HasOne
    {
        return $this->hasOne(UserAttribute::class);
    }

    public function biometricCredentials(): HasMany
    {
        return $this->hasMany(BiometricCredential::class);
    }

    public function mfaCodes(): HasMany
    {
        return $this->hasMany(MfaCode::class);
    }

    public function systemLogs(): HasMany
    {
        return $this->hasMany(SystemLog::class, 'actor_user_id');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    /**
     * Check if user has minimum role hierarchy.
     */
    public function hasMinimumHierarchy(int $minHierarchy): bool
    {
        return $this->role && $this->role->hierarchy >= $minHierarchy;
    }

    /**
     * Check if user is owner.
     */
    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    /**
     * Check if user is family member.
     */
    public function isFamily(): bool
    {
        return $this->hasRole('family');
    }

    /**
     * Check if user is guest.
     */
    public function isGuest(): bool
    {
        return $this->hasRole('guest');
    }

    public function clearanceHierarchy(): int
    {
        return $this->role?->sensitivityLevel?->hierarchy ?? 0;
    }

    public function passwordHistory(): HasMany
    {
        return $this->hasMany(PasswordHistory::class);
    }

    /**
     * Check if phone is verified.
     */
    public function hasVerifiedPhone(): bool
    {
        return !is_null($this->phone_verified_at);
    }
}
