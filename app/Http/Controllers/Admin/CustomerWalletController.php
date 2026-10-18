<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerWalletController extends Controller
{
    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['credit', 'debit'])],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['type'] === 'debit' && $data['amount'] > $customer->wallet_balance) {
            return back()->withErrors(['amount' => 'Debit amount exceeds the current wallet balance.']);
        }

        $adminId = auth('admin')->id();

        $data['type'] === 'credit'
            ? $customer->creditWallet($data['amount'], $data['reason'], $adminId)
            : $customer->debitWallet($data['amount'], $data['reason'], $adminId);

        return back()->with('status', 'Wallet updated successfully.');
    }
}
