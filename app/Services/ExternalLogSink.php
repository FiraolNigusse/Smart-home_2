<?php

namespace App\Services;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ExternalLogSink
{
    protected ?string $filePath;
    protected ?string $apiUrl;
    protected ?string $apiKey;
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = config('logging.external_sink.enabled', false);
        $this->filePath = config('logging.external_sink.file_path');
        $this->apiUrl = config('logging.external_sink.api_url');
        $this->apiKey = config('logging.external_sink.api_key');
    }

    /**
     * Send a log entry to external sink(s).
     */
    public function send(SystemLog $log): void
    {
        if (!$this->enabled) {
            return;
        }

        $payload = $this->formatLogEntry($log);

        // File-based sink
        if ($this->filePath) {
            $this->writeToFile($payload);
        }

        // API-based sink
        if ($this->apiUrl) {
            $this->sendToApi($payload);
        }
    }

    protected function formatLogEntry(SystemLog $log): array
    {
        return [
            'id' => $log->id,
            'event_type' => $log->event_type,
            'severity' => $log->severity,
            'actor_id' => $log->actor_user_id,
            'actor_email' => $log->actor?->email,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'message' => $log->message,
            'context' => $log->context,
            'logged_at' => $log->logged_at->toIso8601String(),
            'created_at' => $log->created_at->toIso8601String(),
        ];
    }

    protected function writeToFile(array $payload): void
    {
        if (!$this->filePath) {
            return;
        }

        $directory = dirname($this->filePath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $line = json_encode($payload) . PHP_EOL;
        File::append($this->filePath, $line);
    }

    protected function sendToApi(array $payload): void
    {
        if (!$this->apiUrl) {
            return;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders(array_filter([
                    'Authorization' => $this->apiKey ? "Bearer {$this->apiKey}" : null,
                    'Content-Type' => 'application/json',
                ]))
                ->post($this->apiUrl, $payload);

            if (!$response->successful()) {
                Log::warning('External log sink API request failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('External log sink API error', [
                'message' => $e->getMessage(),
                'url' => $this->apiUrl,
            ]);
        }
    }
}

