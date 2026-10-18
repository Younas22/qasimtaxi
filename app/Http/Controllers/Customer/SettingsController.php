<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('customer.settings.edit', [
            'customer' => Auth::guard('customer')->user(),
            'languages' => Language::active(),
            'currencies' => Currency::active(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        $data = $request->validate([
            'locale' => ['sometimes', Rule::in(Language::active()->pluck('code')->all())],
            'currency' => ['sometimes', Rule::in(Currency::active()->pluck('code')->all())],
            'theme_mode' => ['sometimes', Rule::in(['dark', 'light'])],
            'email_notifications_enabled' => ['sometimes', 'boolean'],
            'push_notifications_enabled' => ['sometimes', 'boolean'],
            'sms_notifications_enabled' => ['sometimes', 'boolean'],
        ]);

        $customer->update($data);

        if (array_key_exists('locale', $data)) {
            session(['locale' => $data['locale']]);
        }

        if (array_key_exists('currency', $data)) {
            session(['currency' => $data['currency']]);
        }

        if (array_key_exists('theme_mode', $data)) {
            session(['customer_theme_mode' => $data['theme_mode']]);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('Preference saved.'),
        ]);
    }
}
