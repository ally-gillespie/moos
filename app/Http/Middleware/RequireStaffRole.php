<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireStaffRole
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!in_array(session('staff_role'), $roles, true)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
