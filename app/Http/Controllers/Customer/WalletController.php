<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WalletController extends Controller
{
    public const MIN_RECHARGE = 10;

    public const MAX_RECHARGE = 5000;

    public const QUICK_AMOUNTS = [25, 50, 100, 200];

    public function index(Request $request): View
    {
        $customer = Auth::guard('customer')->user();
        $type = $request->query('type');

        $transactions = $customer->walletTransactions()
            ->when(in_array($type, ['credit', 'debit'], true), fn ($q) => $q->where('type', $type))
            ->paginate(15)
            ->withQueryString();

        return view('customer.wallet.index', [
            'balance' => $customer->wallet_balance,
            'loyaltyPoints' => $customer->loyalty_points,
            'totalCredited' => $customer->walletTransactions()->where('type', 'credit')->sum('amount'),
            'totalDebited' => $customer->walletTransactions()->where('type', 'debit')->sum('amount'),
            'transactions' => $transactions,
            'activeType' => $type,
        ]);
    }

    public function recharge(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:'.self::MIN_RECHARGE, 'max:'.self::MAX_RECHARGE],
        ]);

        $customer = Auth::guard('customer')->user();
        $customer->creditWallet((float) $data['amount'], __('Wallet recharge'));

        return back()->with('status', __('Wallet recharged with :amount successfully.', ['amount' => currency($data['amount'])]));
    }
}
