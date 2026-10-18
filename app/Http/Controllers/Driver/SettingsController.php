<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
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
        return view('driver.settings.edit', [
            'driver' => Auth::guard('driver')->user(),
            'languages' => Language::active(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $driver = Auth::guard('driver')->user();

        $data = $request->validate([
            'locale' => ['sometimes', Rule::in(Language::active()->pluck('code')->all())],
            'theme_mode' => ['sometimes', Rule::in(['dark', 'light'])],
        ]);

        $driver->update($data);

        if (array_key_exists('locale', $data)) {
            session(['locale' => $data['locale']]);
        }

        if (array_key_exists('theme_mode', $data)) {
            session(['driver_theme_mode' => $data['theme_mode']]);
        }

        return response()->json([
            'status' => 'success',
            'message' => __('Preference saved.'),
        ]);
    }
}
