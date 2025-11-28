<?php

namespace App\Http\Middleware;

use App\Services\SessionSecurityService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ValidateSessionSecurity
{
    public function __construct(
        protected SessionSecurityService $sessionSecurity
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $sessionId = $request->session()->getId();
        $currentIp = $request->ip();
        $currentFingerprint = $this->sessionSecurity->generateFingerprint($request);

        // Validate IP binding if enabled
        if (!$this->sessionSecurity->validateSessionIp($sessionId, $currentIp)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Session invalidated due to IP address change. Please log in again.']);
        }

        // Validate device binding if enabled
        if (!$this->sessionSecurity->validateSessionDevice($sessionId, $currentFingerprint)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Session invalidated due to device change. Please log in again.']);
        }

        // Update session metadata
        $this->sessionSecurity->updateSessionMetadata($sessionId, $request);

        return $next($request);
    }
}

