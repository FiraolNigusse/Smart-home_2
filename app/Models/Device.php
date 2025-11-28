<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Device extends Model
{
    protected $fillable = [
        'name',
        'type',
        'location',
        'status',
        'settings',
        'is_active',
        'min_role_hierarchy',
        'sensitivity_level_id',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get all audit logs for this device.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get all rules for this device.
     */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
    }

    public function sensitivityLevel(): BelongsTo
    {
        return $this->belongsTo(SensitivityLevel::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(DevicePermission::class);
    }

    public function isAccessibleBy(User $user): bool
    {
        $roleHierarchy = $user->role->hierarchy ?? 0;
        $clearance = $user->clearanceHierarchy();
        $deviceSensitivity = $this->sensitivityLevel?->hierarchy ?? 0;

        return $this->is_active
            && $roleHierarchy >= $this->min_role_hierarchy
            && $clearance >= $deviceSensitivity;
    }

    /**
     * Update device status.
     */
    public function updateStatus(string $status, array $settings = []): void
    {
        $this->status = $status;
        if (!empty($settings)) {
            $this->settings = array_merge($this->settings ?? [], $settings);
        }
        $this->save();
    }
}
