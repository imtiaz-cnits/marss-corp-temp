<?php

namespace App\Http\Middleware;

use App\Support\PermissionRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isApproved()) {
            return redirect()->route('login');
        }

        if (! PermissionRegistry::isAdmin($user)) {
            abort(403, 'Administrator access required.');
        }

        return $next($request);
    }
}
