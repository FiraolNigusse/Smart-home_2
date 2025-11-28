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
            
            $response->headers->set('Strict-Transport-Security', "max-age={$maxAge}{$includeSubDomains}{$preload}");
        }

        // X-Content-Type-Options
        if (config('security.headers.content_type_nosniff', true)) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        // X-Frame-Options
        $frameOptions = config('security.headers.frame_options', 'DENY');
        if ($frameOptions) {
            $response->headers->set('X-Frame-Options', $frameOptions);
        }

        // X-XSS-Protection
        if (config('security.headers.xss_protection', true)) {
            $response->headers->set('X-XSS-Protection', '1; mode=block');
        }

        // Referrer-Policy
        $referrerPolicy = config('security.headers.referrer_policy', 'strict-origin-when-cross-origin');
        if ($referrerPolicy) {
            $response->headers->set('Referrer-Policy', $referrerPolicy);
        }

        // Permissions-Policy (formerly Feature-Policy)
        $permissionsPolicy = config('security.headers.permissions_policy');
        if ($permissionsPolicy) {
            $response->headers->set('Permissions-Policy', $permissionsPolicy);
        }

        // Content-Security-Policy
        $csp = config('security.headers.content_security_policy');
        if ($csp) {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // Remove X-Powered-By header
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}

