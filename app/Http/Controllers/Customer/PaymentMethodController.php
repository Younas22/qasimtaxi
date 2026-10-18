<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(Request $request): View
    {
        $customer = Auth::guard('customer')->user();
        $search = $request->query('search');

        $payments = $customer->bookings()
            ->whereIn('payment_status', ['paid', 'refunded'])
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('booking_number', 'like', "%{$search}%")
                ->orWhere('transaction_id', 'like', "%{$search}%")
            ))
            ->latest('paid_at')
            ->paginate(15)
            ->withQueryString();

        $gateways = PaymentGateway::whereIn('code', ['stripe', 'paypal'])->get()->keyBy('code');

        return view('customer.payment-methods.index', [
            'payments' => $payments,
            'search' => $search,
            'gateways' => $gateways,
        ]);
    }
}
