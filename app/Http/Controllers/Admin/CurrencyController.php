<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CurrencyController extends Controller
{
    public function index(): View
    {
        $currencies = Currency::orderBy('code')->get();

        return view('admin.currencies.index', compact('currencies'));
    }

    public function create(): View
    {
        return view('admin.currencies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCurrency($request);

        $currency = Currency::create($data + ['is_active' => true]);

        if (Currency::count() === 1) {
            $currency->update(['is_default' => true, 'exchange_rate' => 1]);
        }

        return redirect()->route('admin.currencies.index')->with('status', "Currency \"{$currency->name}\" added successfully.");
    }

    public function edit(Currency $currency): View
    {
        return view('admin.currencies.edit', compact('currency'));
    }

    public function update(Request $request, Currency $currency): RedirectResponse
    {
        $data = $this->validateCurrency($request, $currency);

        if ($currency->is_default) {
            $data['exchange_rate'] = 1;
        }

        $currency->update($data);

        return redirect()->route('admin.currencies.index')->with('status', "Currency \"{$currency->name}\" updated successfully.");
    }

    public function destroy(Currency $currency): RedirectResponse
    {
        abort_if($currency->is_default, 403, 'The default currency cannot be deleted.');
        abort_if(Currency::count() <= 1, 403, 'At least one currency must remain.');

        $currency->delete();

        return back()->with('status', 'Currency deleted successfully.');
    }

    public function toggleStatus(Currency $currency): RedirectResponse
    {
        abort_if($currency->is_default && $currency->is_active, 403, 'The default currency cannot be disabled.');

        $currency->update(['is_active' => ! $currency->is_active]);

        return back()->with('status', $currency->is_active ? "\"{$currency->name}\" enabled." : "\"{$currency->name}\" disabled.");
    }

    public function setDefault(Currency $currency): RedirectResponse
    {
        abort_unless($currency->is_active, 403, 'Only an enabled currency can be set as default.');

        DB::transaction(function () use ($currency) {
            Currency::where('is_default', true)->update(['is_default' => false]);
            $currency->update(['is_default' => true, 'exchange_rate' => 1]);
        });

        return back()->with('status', "\"{$currency->name}\" is now the default currency.");
    }

    public function switch(Request $request, string $code): RedirectResponse
    {
        abort_unless(Currency::findActiveByCode($code), 404);

        session(['currency' => $code]);

        return back();
    }

    private function validateCurrency(Request $request, ?Currency $currency = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/', 'unique:currencies,code,'.($currency?->id)],
            'symbol' => ['required', 'string', 'max:10'],
            'exchange_rate' => ['required', 'numeric', 'gt:0'],
        ]);

        $data['code'] = strtoupper($data['code']);

        return $data;
    }
}
