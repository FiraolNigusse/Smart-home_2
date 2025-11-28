<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsVerificationService
{
    protected ?string $apiKey;
    protected ?string $apiUrl;
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = config('security.sms.enabled', false);
        $this->apiKey = config('security.sms.api_key');
        $this->apiUrl = config('security.sms.api_url');
    }

    /**
     * Send SMS verification code to user.
     */
    public function sendCode(User $user, string $code): bool
    {
        if (!$this->enabled || !$user->phone) {
            return false;
        }

        // In production, integrate with SMS provider (Twilio, AWS SNS, etc.)
        // For now, log the code (similar to email MFA)
        Log::info("SMS verification code for {$user->phone}: {$code}");

        // Example integration with external SMS API
        if ($this->apiUrl && $this->apiKey) {
            try {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Authorization' => "Bearer {$this->apiKey}",
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->apiUrl, [
                        'to' => $user->phone,
                        'message' => "Your Smart Home verification code is: {$code}",
                    ]);

                return $response->successful();
            } catch (\Exception $e) {
                Log::error('SMS verification failed', [
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                    'error' => $e->getMessage(),
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Generate a 6-digit verification code.
     */
    public function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}

