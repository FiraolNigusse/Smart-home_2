<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Check if device is accessible by role hierarchy.
     */
    public function isAccessibleBy(int $hierarchy): bool
    {
        return $this->is_active && $hierarchy >= $this->min_role_hierarchy;
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
