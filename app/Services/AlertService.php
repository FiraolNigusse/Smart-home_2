<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SystemAlertNotification;

class AlertService
{
    protected array $severityOrder = [
        'info' => 1,
        'notice' => 2,
        'warning' => 3,
        'error' => 4,
        'critical' => 5,
    ];

    public function notify(string $severity, string $eventType, ?string $message = null, array $context = []): void
    {
        $threshold = config('security.alert_min_severity', 'warning');

        if ($this->severityRank($severity) < $this->severityRank($threshold)) {
            return;
        }

        $owners = User::whereHas('role', fn ($query) => $query->where('slug', 'owner'))->get();

        foreach ($owners as $owner) {
            $owner->notify(new SystemAlertNotification($eventType, $severity, $message, $context));
        }
    }

    protected function severityRank(string $severity): int
    {
        return $this->severityOrder[strtolower($severity)] ?? 1;
    }
}

