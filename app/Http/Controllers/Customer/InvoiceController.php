<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(): View
    {
        $bookings = Auth::guard('customer')->user()->bookings()
            ->whereIn('status', ['completed', 'confirmed', 'assigned'])
            ->latest('pickup_datetime')
            ->paginate(15);

        return view('customer.invoices.index', compact('bookings'));
    }
}
