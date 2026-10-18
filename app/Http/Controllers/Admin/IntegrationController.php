<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\EnvFileService;
use App\Services\GoogleMapsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Throwable;

class IntegrationController extends Controller
{
    public function __construct(private readonly EnvFileService $env) {}

    public function edit(): View
    {
        $key = $this->env->get('GOOGLE_MAPS_API_KEY');

        return view('admin.integrations.edit', [
            'googleMapsKeyMasked' => $this->mask($key),
            'googleMapsKeySet' => filled($key),
        ]);
    }

    public function testGoogleMaps(Request $request, GoogleMapsService $maps): JsonResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:255'],
        ]);

        return response()->json($maps->testKey($data['key']));
    }

    public function updateGoogleMaps(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'google_maps_api_key' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->env->set('GOOGLE_MAPS_API_KEY', $data['google_maps_api_key']);
        } catch (Throwable $e) {
            return back()->with('error', 'Could not save the key: '.$e->getMessage());
        }

        // No config:cache is ever run in this app, but clearing it is a
        // cheap safety net in case that ever changes — without it, a cached
        // config would keep serving the old key until the cache expired.
        Artisan::call('config:clear');

        ActivityLog::record('integrations.google_maps_updated', 'Google Maps API key updated.', $request);

        return back()->with('status', 'Google Maps API key saved to .env.');
    }

    private function mask(?string $key): ?string
    {
        if (! $key) {
            return null;
        }

        if (strlen($key) <= 8) {
            return str_repeat('•', strlen($key));
        }

        return substr($key, 0, 4).str_repeat('•', strlen($key) - 8).substr($key, -4);
    }
}
