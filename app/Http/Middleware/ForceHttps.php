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

            if ($request->isSecure() === false) {
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }
}

