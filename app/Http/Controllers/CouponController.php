<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Live coupon lookup for the public booking widget — a customer types a
 * code, this validates it against the quoted fare and returns the discount
 * to show immediately. Purely a preview: the actual charge is only ever
 * decided by BookingRequestController::store() re-validating the same
 * coupon server-side, so nothing here can be trusted to apply a discount
 * on its own.
 */
class CouponController extends Controller
{
    public function apply(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $coupon = Coupon::where('code', strtoupper($data['code']))->first();

        if (! $coupon) {
            return response()->json(['valid' => false, 'message' => __('That coupon code was not found.')], 404);
        }

        if ($reason = $coupon->ineligibilityReason((float) $data['amount'])) {
            return response()->json(['valid' => false, 'message' => $reason], 422);
        }

        $discount = $coupon->discountFor((float) $data['amount']);

        return response()->json([
            'valid' => true,
            'code' => $coupon->code,
            'label' => $coupon->discount_label,
            'discount' => $discount,
            'new_total' => round((float) $data['amount'] - $discount, 2),
        ]);
    }
}
