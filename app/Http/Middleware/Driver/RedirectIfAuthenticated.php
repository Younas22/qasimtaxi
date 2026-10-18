<?php

namespace App\Http\Middleware\Driver;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('driver')->check()) {
            return redirect()->route('driver.dashboard');
        }

        return $next($request);
    }
}
