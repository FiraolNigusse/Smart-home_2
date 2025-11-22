<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'action',
        'status',
        'message',
        'ip_address',
        'user_agent',
        'metadata',
        'performed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the device that was acted upon.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeAllowed($query)
    {
        return $query->where('status', 'allowed');
    }

    /**
     * Scope to filter by denied.
     */
    public function scopeDenied($query)
    {
        return $query->where('status', 'denied');
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by device.
     */
    public function scopeForDevice($query, int $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }
}
