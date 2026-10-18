<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * The public site's own dark/light preference — session-scoped per
     * visitor, and deliberately independent from Setting::theme_mode (the
     * admin/customer/driver panels' theme) and from Admin\ThemeController's
     * `admin_theme_mode` session key. Flipping this can never affect any
     * other visitor or any panel. Responds with JSON rather than a
     * redirect since the toggle applies itself client-side without a page
     * reload (see the toggleTheme() Alpine method in layouts/public.blade.php).
     */
    public function toggle(Request $request): JsonResponse
    {
        $current = session('public_theme_mode', 'dark');
        $mode = $current === 'light' ? 'dark' : 'light';

        session(['public_theme_mode' => $mode]);

        return response()->json(['theme' => $mode]);
    }
}
