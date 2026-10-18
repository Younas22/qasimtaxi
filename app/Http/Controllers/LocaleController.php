<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $code): RedirectResponse
    {
        abort_unless(Language::findActiveByCode($code), 404);

        session(['locale' => $code]);

        return back();
    }
}
