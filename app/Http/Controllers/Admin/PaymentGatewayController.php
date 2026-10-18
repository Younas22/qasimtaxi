<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    public function index(): View
    {
        $gateways = PaymentGateway::orderBy('name')->get();

        return view('admin.payment-gateways.index', compact('gateways'));
    }

    public function edit(PaymentGateway $gateway): View
    {
        return view('admin.payment-gateways.edit', compact('gateway'));
    }

    public function update(Request $request, PaymentGateway $gateway): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['sandbox', 'live'])],
            'sandbox_key_1' => ['nullable', 'string', 'max:500'],
            'sandbox_key_2' => ['nullable', 'string', 'max:500'],
            'live_key_1' => ['nullable', 'string', 'max:500'],
            'live_key_2' => ['nullable', 'string', 'max:500'],
        ]);

        $isEnabled = $request->boolean('is_enabled');

        // Only overwrite a key if the admin actually typed a new value —
        // this lets the form round-trip without forcing keys to be re-entered.
        foreach (['sandbox_key_1', 'sandbox_key_2', 'live_key_1', 'live_key_2'] as $field) {
            if (blank($data[$field] ?? null)) {
                unset($data[$field]);
            }
        }

        $gateway->fill($data);

        if ($isEnabled && ! $gateway->hasActiveKeys()) {
            throw ValidationException::withMessages([
                'is_enabled' => "Cannot enable {$gateway->name} without both {$gateway->mode} keys configured.",
            ]);
        }

        $gateway->is_enabled = $isEnabled;
        $gateway->save();

        return redirect()
            ->route('admin.payment-gateways.index')
            ->with('status', "{$gateway->name} settings updated successfully.");
    }

    public function toggleStatus(PaymentGateway $gateway): RedirectResponse
    {
        if (! $gateway->is_enabled && ! $gateway->hasActiveKeys()) {
            return back()->with('error', "Cannot enable {$gateway->name}: configure both {$gateway->mode} keys first.");
        }

        $gateway->update(['is_enabled' => ! $gateway->is_enabled]);

        return back()->with('status', $gateway->is_enabled ? "{$gateway->name} enabled." : "{$gateway->name} disabled.");
    }
}
