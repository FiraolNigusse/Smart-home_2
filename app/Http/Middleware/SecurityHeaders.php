<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // HSTS (HTTP Strict Transport Security)
        if ($request->secure() && config('security.headers.hsts.enabled', true)) {
            $maxAge = config('security.headers.hsts.max_age', 31536000); // 1 year
            $includeSubDomains = config('security.headers.hsts.include_subdomains', true) ? '; includeSubDomains' : '';
            $preload = config('security.headers.hsts.preload', false) ? '; preload' : '';
            
            $value = "max-age={$maxAge}{$includeSubDomains}{$preload}";
            $response->headers->set('Strict-Transport-Security', $this->sanitizeHeaderValue($value));
        }

        // X-Content-Type-Options
        if (config('security.headers.content_type_nosniff', true)) {
            $response->headers->set('X-Content-Type-Options', $this->sanitizeHeaderValue('nosniff'));
        }

        // X-Frame-Options
        $frameOptions = config('security.headers.frame_options', 'DENY');
        if ($frameOptions) {
            $response->headers->set('X-Frame-Options', $this->sanitizeHeaderValue($frameOptions));
        }

        // X-XSS-Protection
        if (config('security.headers.xss_protection', true)) {
            $response->headers->set('X-XSS-Protection', $this->sanitizeHeaderValue('1; mode=block'));
        }

        // Referrer-Policy
        $referrerPolicy = config('security.headers.referrer_policy', 'strict-origin-when-cross-origin');
        if ($referrerPolicy) {
            $response->headers->set('Referrer-Policy', $this->sanitizeHeaderValue($referrerPolicy));
        }

        // Permissions-Policy (formerly Feature-Policy)
        $permissionsPolicy = config('security.headers.permissions_policy');
        if ($permissionsPolicy) {
            $response->headers->set('Permissions-Policy', $this->sanitizeHeaderValue($permissionsPolicy));
        }

        // Content-Security-Policy
        $csp = config('security.headers.content_security_policy');
        if ($csp) {
            $response->headers->set('Content-Security-Policy', $this->sanitizeHeaderValue($csp));
        }

        // Remove X-Powered-By header
        $response->headers->remove('X-Powered-By');

        return $response;
    }

    /**
     * Sanitize header values by removing CR and LF characters.
     */
    protected function sanitizeHeaderValue(string $value): string
    {
        return trim(preg_replace('/[\r\n]+/', ' ', $value));
    }
}

