<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SessionSecurityService
{
    /**
     * Generate device fingerprint from request.
     */
    public function generateFingerprint(Request $request): string
    {
        $components = [
            $request->ip(),
            $request->userAgent(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
        ];

        return hash('sha256', implode('|', array_filter($components)));
    }

    /**
     * Check if session IP matches current request.
     */
    public function validateSessionIp(string $sessionId, string $currentIp): bool
    {
        if (!config('security.session.bind_ip', false)) {
            return true; // IP binding disabled
        }

        $session = DB::table('sessions')
            ->where('id', $sessionId)
            ->first();

        if (!$session) {
            return false;
        }

        return $session->ip_address === $currentIp;
    }

    /**
     * Check if session device matches current request.
     */
    public function validateSessionDevice(string $sessionId, string $currentFingerprint): bool
    {
        if (!config('security.session.bind_device', false)) {
            return true; // Device binding disabled
        }

        $session = DB::table('sessions')
            ->where('id', $sessionId)
            ->first();

        if (!$session) {
            return false;
        }

        return $session->device_fingerprint === $currentFingerprint;
    }

    /**
     * Update session metadata.
     */
    public function updateSessionMetadata(string $sessionId, Request $request): void
    {
        $fingerprint = $this->generateFingerprint($request);
        $isMobile = $this->isMobileDevice($request);

        DB::table('sessions')
            ->where('id', $sessionId)
            ->update([
                'device_fingerprint' => $fingerprint,
                'is_mobile' => $isMobile,
                'last_activity_at' => now(),
            ]);
    }

    /**
     * Detect if request is from mobile device.
     */
    protected function isMobileDevice(Request $request): bool
    {
        $userAgent = $request->userAgent() ?? '';
        
        return (bool) preg_match(
            '/Mobile|Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i',
            $userAgent
        );
    }

    /**
     * Invalidate all sessions for a user except current.
     */
    public function invalidateOtherSessions(int $userId, string $currentSessionId): void
    {
        DB::table('sessions')
            ->where('user_id', $userId)
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }
}

