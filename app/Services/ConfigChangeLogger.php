<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\File;

class ConfigChangeLogger
{
    protected string $configHashFile;

    public function __construct(
        protected SystemLogService $systemLogService
    ) {
        $this->configHashFile = storage_path('app/config_hashes.json');
    }

    /**
     * Log configuration changes by comparing file hashes.
     */
    public function logConfigChanges(?User $actor = null): void
    {
        $configFiles = [
            'app' => config_path('app.php'),
            'auth' => config_path('auth.php'),
            'database' => config_path('database.php'),
            'security' => config_path('security.php'),
            'access' => config_path('access.php'),
            'logging' => config_path('logging.php'),
        ];

        $currentHashes = [];
        $previousHashes = $this->loadPreviousHashes();
        $changes = [];

        foreach ($configFiles as $name => $path) {
            if (!File::exists($path)) {
                continue;
            }

            $content = File::get($path);
            $hash = hash('sha256', $content);
            $currentHashes[$name] = $hash;

            if (isset($previousHashes[$name]) && $previousHashes[$name] !== $hash) {
                $changes[] = $name;
            }
        }

        if (!empty($changes)) {
            $this->systemLogService->log(
                eventType: 'config.changed',
                severity: 'warning',
                actor: $actor,
                message: 'Configuration files changed: ' . implode(', ', $changes),
                context: [
                    'changed_files' => $changes,
                    'timestamp' => now()->toIso8601String(),
                ],
                sensitivePayload: [
                    'previous_hashes' => $previousHashes,
                    'current_hashes' => $currentHashes,
                ]
            );
        }

        $this->saveHashes($currentHashes);
    }

    /**
     * Log when config cache is cleared or rebuilt.
     */
    public function logConfigCacheEvent(string $event, ?User $actor = null): void
    {
        $this->systemLogService->log(
            eventType: "config.cache.{$event}",
            severity: 'info',
            actor: $actor,
            message: "Configuration cache {$event}",
            context: [
                'event' => $event,
                'timestamp' => now()->toIso8601String(),
            ]
        );
    }

    protected function loadPreviousHashes(): array
    {
        if (!File::exists($this->configHashFile)) {
            return [];
        }

        $content = File::get($this->configHashFile);
        return json_decode($content, true) ?? [];
    }

    protected function saveHashes(array $hashes): void
    {
        File::put($this->configHashFile, json_encode($hashes, JSON_PRETTY_PRINT));
    }
}

