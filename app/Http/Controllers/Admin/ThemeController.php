<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * The admin panel's own dark/light preference, kept in session and
     * separate from Setting::theme_mode (the public website's theme) — an
     * admin flipping their own view must never change what customers see.
     */
    public function toggle(Request $request): RedirectResponse
    {
        $current = session('admin_theme_mode', setting('theme_mode', 'dark'));

        session(['admin_theme_mode' => $current === 'light' ? 'dark' : 'light']);

        return back();
    }
}
