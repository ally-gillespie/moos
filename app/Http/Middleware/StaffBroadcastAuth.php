<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

class StaffBroadcastAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('staff_id') && ! auth()->check()) {
            auth()->setUser(new class (session('staff_id')) implements Authenticatable {
                public function __construct(private int $id) {}
                public function getAuthIdentifierName(): string  { return 'id'; }
                public function getAuthIdentifier(): mixed       { return $this->id; }
                public function getAuthPasswordName(): string    { return 'password'; }
                public function getAuthPassword(): string        { return ''; }
                public function getRememberToken(): ?string      { return null; }
                public function setRememberToken($value): void   {}
                public function getRememberTokenName(): string   { return ''; }
            });
        }

        return $next($request);
    }
}
