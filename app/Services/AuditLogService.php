<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Device;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Log an action.
     *
     * @param User|null $user
     * @param Device|null $device
     * @param string $action
     * @param string $status
     * @param string|null $message
     * @param Request|null $request
     * @param array $metadata
     * @return AuditLog
     */
    public function log(
        ?User $user,
        ?Device $device,
        string $action,
        string $status,
        ?string $message = null,
        ?Request $request = null,
        array $metadata = []
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user?->id,
            'device_id' => $device?->id,
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata,
            'performed_at' => now(),
        ]);
    }

    /**
     * Log an allowed action.
     */
    public function logAllowed(
        User $user,
        Device $device,
        string $action,
        ?Request $request = null,
        array $metadata = []
    ): AuditLog {
        return $this->log($user, $device, $action, 'allowed', null, $request, $metadata);
    }

    /**
     * Log a denied action.
     */
    public function logDenied(
        User $user,
        Device $device,
        string $action,
        string $message,
        ?Request $request = null,
        array $metadata = []
    ): AuditLog {
        return $this->log($user, $device, $action, 'denied', $message, $request, $metadata);
    }
}


