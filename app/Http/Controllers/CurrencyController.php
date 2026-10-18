<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function switch(Request $request, string $code): RedirectResponse
    {
        abort_unless(Currency::findActiveByCode($code), 404);

        session(['currency' => $code]);

        return back();
    }
}
