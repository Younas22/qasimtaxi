<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\DriverDispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DispatchController extends Controller
{
    public function show(Booking $booking, DriverDispatchService $dispatchService): JsonResponse
    {
        abort_unless($booking->customer_id === Auth::guard('customer')->id(), 404);

        return response()->json([
            'dispatch' => $dispatchService->dispatchInfoFor($booking),
        ]);
    }
}
