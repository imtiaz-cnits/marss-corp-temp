<?php

namespace App\Http\Middleware;

use App\Support\PermissionRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isApproved()) {
            return redirect()->route('login');
        }

        if (! PermissionRegistry::allows($user, $permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            abort(403, 'You do not have permission to access this module.');
        }

        return $next($request);
    }
}
