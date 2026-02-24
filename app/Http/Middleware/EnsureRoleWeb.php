<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleWeb
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = session('user');

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Insufficient role.');
        }

        return $next($request);
    }
}
