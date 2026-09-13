<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        return redirect()->route($user->dashboardRouteName())->withErrors([
            'access' => 'You are not allowed to access that section.',
        ]);
    }
}
