<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CaptchaService
{
    /**
     * Get the reCAPTCHA site key for frontend display.
     */
    public function getSiteKey(): string
    {
        return config('recaptcha.site_key', '');
    }

    /**
     * Validate Google reCAPTCHA token.
     */
    public function validate(?string $token, string $context = 'default'): bool
    {
        if (empty($token)) {
            Log::warning('reCAPTCHA token is empty', ['context' => $context]);
            return false;
        }

        $secretKey = config('recaptcha.secret_key');
        $verifyUrl = config('recaptcha.verify_url', 'https://www.google.com/recaptcha/api/siteverify');

        if (empty($secretKey)) {
            Log::error('reCAPTCHA secret key is not configured');
            return false;
        }

        try {
            $response = Http::asForm()->post($verifyUrl, [
                'secret' => $secretKey,
                'response' => $token,
                'remoteip' => request()->ip(),
            ]);

            $result = $response->json();

            if (!$response->successful() || !isset($result['success'])) {
                Log::warning('reCAPTCHA verification request failed', [
                    'context' => $context,
                    'status' => $response->status(),
                    'response' => $result,
                ]);
                return false;
            }

            if ($result['success'] === true) {
                Log::info('reCAPTCHA verification successful', [
                    'context' => $context,
                    'score' => $result['score'] ?? null,
                ]);
                return true;
            }

            Log::warning('reCAPTCHA verification failed', [
                'context' => $context,
                'errors' => $result['error-codes'] ?? [],
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification exception', [
                'context' => $context,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if reCAPTCHA is enabled and configured.
     * Requires both site key (for frontend) and secret key (for backend).
     */
    public function isEnabled(): bool
    {
        $siteKey = config('recaptcha.site_key');
        $secretKey = config('recaptcha.secret_key');

        // Both keys must be present and non-empty
        $siteKeyValid = !empty($siteKey) && trim($siteKey) !== '';
        $secretKeyValid = !empty($secretKey) && trim($secretKey) !== '';

        return $siteKeyValid && $secretKeyValid;
    }
}


