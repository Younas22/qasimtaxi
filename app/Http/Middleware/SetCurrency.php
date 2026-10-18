<?php

namespace App\Http\Middleware;

use App\Models\Currency;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrency
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = session('currency');

        if (! $code || ! Currency::findActiveByCode($code)) {
            session(['currency' => Currency::defaultCode()]);
        }

        return $next($request);
    }
}
