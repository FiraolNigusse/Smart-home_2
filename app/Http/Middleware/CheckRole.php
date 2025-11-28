<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // If no roles specified, allow any authenticated user
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has one of the required roles
        if ($user->role && in_array($user->role->slug, $roles)) {
            return $next($request);
        }

        // Check hierarchy-based access (e.g., owner can access family/guest routes)
        $roleHierarchy = [
            'owner' => 3,
            'family' => 2,
            'guest' => 1,
        ];

        $userHierarchy = $user->role ? $user->role->hierarchy : 0;
        $requiredHierarchies = array_map(fn($role) => $roleHierarchy[$role] ?? 0, $roles);
        $minRequiredHierarchy = min($requiredHierarchies);

        if ($userHierarchy >= $minRequiredHierarchy) {
        return $next($request);
        }

        abort(403, 'Unauthorized access. Insufficient role permissions.');
    }
}
