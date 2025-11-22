<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
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
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
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
}
