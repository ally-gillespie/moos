<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireStaffSession
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->session()->has('staff_id')) {
            return redirect()->route('kitchen.login');
        }

        return $next($request);
    }
}
