<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('security.force_https')) {
            URL::forceScheme('https');

            // Check for secure cookies
            $this->ensureSecureCookies();

            // Redirect HTTP to HTTPS
            if ($request->isSecure() === false) {
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }

    /**
     * Ensure session cookies are secure when HTTPS is enforced.
     */
    protected function ensureSecureCookies(): void
    {
        if (config('security.force_https') && !config('session.secure')) {
            // Log warning if secure cookies are not enabled with HTTPS enforcement
            logger()->warning('HTTPS is enforced but session cookies are not secure. Set SESSION_SECURE_COOKIE=true in .env');
        }
    }
}


