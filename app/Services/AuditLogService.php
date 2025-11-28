<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Models\Device;
use Illuminate\Http\Request;

class AuditLogService
{
    public function __construct(
        protected SystemLogService $systemLogService
    ) {
    }

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
        $auditLog = AuditLog::create([
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

        // Log denied actions to system log with alerting for critical actions
        if ($status === 'denied') {
            $criticalActions = ['delete', 'unlock', 'admin_access', 'role_change', 'permission_grant'];
            $severity = in_array($action, $criticalActions) ? 'warning' : 'info';
            
            $this->systemLogService->log(
                eventType: 'access.denied',
                severity: $severity,
                actor: $user,
                message: "Access denied: {$action} on " . ($device?->name ?? 'system'),
                context: [
                    'device_id' => $device?->id,
                    'device_name' => $device?->name,
                    'action' => $action,
                    'denial_reason' => $message,
                    'ip_address' => $request?->ip(),
                ],
                sensitivePayload: $metadata
            );
        }

        return $auditLog;
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


