<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Rule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'role_id',
        'device_id',
        'action',
        'condition_type',
        'condition_params',
        'is_active',
        'effect',
        'denial_message',
    ];

    protected $casts = [
        'condition_params' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the role that owns the rule.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the device that owns the rule.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Check if rule applies to given context.
     */
    public function appliesTo(?int $roleId, ?int $deviceId, ?string $action): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check role match (null means applies to all roles)
        if ($this->role_id !== null && $this->role_id !== $roleId) {
            return false;
        }

        // Check device match (null means applies to all devices)
        if ($this->device_id !== null && $this->device_id !== $deviceId) {
            return false;
        }

        // Check action match (null means applies to all actions)
        if ($this->action !== null && $this->action !== $action) {
            return false;
        }

        return true;
    }

    /**
     * Evaluate the rule condition.
     */
    public function evaluateCondition(): bool
    {
        $params = $this->condition_params ?? [];

        switch ($this->condition_type) {
            case 'time_window':
                return $this->evaluateTimeWindow($params);
            case 'day_of_week':
                return $this->evaluateDayOfWeek($params);
            case 'always':
                return true;
            default:
                return false;
        }
    }

    /**
     * Evaluate time window condition.
     */
    protected function evaluateTimeWindow(array $params): bool
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i');

        $startTime = $params['start_time'] ?? '00:00';
        $endTime = $params['end_time'] ?? '23:59';

        // Handle overnight windows (e.g., 22:00 to 06:00)
        if ($startTime > $endTime) {
            return $currentTime >= $startTime || $currentTime <= $endTime;
        }

        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    /**
     * Evaluate day of week condition.
     */
    protected function evaluateDayOfWeek(array $params): bool
    {
        $allowedDays = $params['days'] ?? [];
        $currentDay = Carbon::now()->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

        return in_array($currentDay, $allowedDays);
    }
}
