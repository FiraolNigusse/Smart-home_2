<?php

namespace App\Services;

use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class SystemLogService
{
    public function __construct(protected AlertService $alertService)
    {
    }

    public function log(string $eventType, string $severity = 'info', ?User $actor = null, ?string $message = null, array $context = [], array $sensitivePayload = []): SystemLog
    {
        $request = request();

        $log = SystemLog::create([
            'event_type' => $eventType,
            'severity' => $severity,
            'actor_user_id' => $actor?->id,
            'ip_address' => $context['ip'] ?? $request?->ip(),
            'user_agent' => $context['user_agent'] ?? $request?->userAgent(),
            'message' => $message,
            'context' => $context,
            'encrypted_payload' => empty($sensitivePayload) ? null : Crypt::encryptString(json_encode($sensitivePayload)),
            'logged_at' => now(),
        ]);

        $this->alertService->notify($severity, $eventType, $message, $context);

        return $log;
    }
}

