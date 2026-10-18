<?php

namespace App\Http\Controllers;

use App\Models\PromoBannerSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * A deliberately unlinked, un-navigated page (reachable only by whoever has
 * its URL) for the vendor to toggle the two self-promotional "this software
 * is for sale" elements — see PromoBannerSetting. Not gated behind admin
 * auth on purpose: this controls vendor-only marketing chrome, not anything
 * about a client's actual data, and the point of a private URL here is
 * convenience (bookmark it, no login step) rather than defense against a
 * determined attacker.
 */
class PromoBannerSettingController extends Controller
{
    public function edit(): View
    {
        return view('promo-banner-settings.edit', [
            'settings' => PromoBannerSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sale_modal_enabled' => ['nullable', 'boolean'],
            'sticky_banner_enabled' => ['nullable', 'boolean'],
        ]);

        PromoBannerSetting::current()->update([
            'sale_modal_enabled' => $request->boolean('sale_modal_enabled'),
            'sticky_banner_enabled' => $request->boolean('sticky_banner_enabled'),
        ]);

        return back()->with('status', 'Saved.');
    }
}
