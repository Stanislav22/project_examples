<?php

namespace App\Http\Middleware;

use App\Customers\Models\CustomerStatus;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class CustomerStatusIsActive
{

    public function handle($request, Closure $next)
    {
        $this->statusCheck($request);

        return $next($request);
    }

    protected function statusCheck($request): void
    {
        if ($request->user()->customer) {
            if (!$request->user()->customer->hasVerifiedEmail()) {
                throw new AuthenticationException(
                    'Unverified email.', [null], $this->redirectTo($request)
                );
            }
            if ($request->user()->customer->status->value() !== CustomerStatus::ACTIVE) {
                throw new AuthenticationException(
                    'Inactive status.', [null], $this->redirectTo($request)
                );
            }
        }
    }

    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
