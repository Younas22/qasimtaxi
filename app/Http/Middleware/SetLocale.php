<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (! $locale || ! Language::findActiveByCode($locale)) {
            $locale = Language::defaultCode();
        }

        app()->setLocale($locale);
        config(['app.fallback_locale' => Language::defaultCode()]);

        return $next($request);
    }
}
