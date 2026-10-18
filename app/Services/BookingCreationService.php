<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSetting;
use App\Models\Coupon;
use App\Models\Driver;
use App\Models\PaymentGateway;
use App\Models\Vehicle;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\Customer\BookingConfirmedNotification as CustomerBookingConfirmedNotification;
use App\Notifications\Customer\BookingReceivedNotification as CustomerBookingReceivedNotification;
use App\Notifications\Customer\DriverAssignedNotification as CustomerDriverAssignedNotification;
use App\Notifications\Driver\DriverBookingNotification;
use App\Services\PushNotificationService;

/**
 * Shared booking-creation logic used by both the admin manual-booking form
 * and the public website booking widget, so fare calculation and business
 * policies (auto-confirmation, driver auto-assignment) never drift between
 * the two entry points.
 */
class BookingCreationService
{
    public function __construct(
        private readonly BookingFareCalculator $calculator,
        private readonly PushNotificationService $pushNotifications,
        private readonly AdminBookingNotifier $adminNotifier
    ) {
    }

    public function attachFareBreakdown(array $data): array
    {
        $vehicle = Vehicle::findOrFail($data['vehicle_id']);

        $data['fare_breakdown'] = $this->calculator->breakdown(
            $vehicle,
            $data['type'],
            isset($data['distance_km']) ? (float) $data['distance_km'] : null,
            $data['hours'] ?? null,
            $data['pickup_datetime'],
            (int) ($data['waiting_minutes'] ?? 0),
            (bool) ($data['has_toll'] ?? false),
            (int) ($data['passengers'] ?? 1),
            isset($data['return_distance_km']) ? (float) $data['return_distance_km'] : null,
            isset($data['approach_distance_km']) ? (float) $data['approach_distance_km'] : null
        );

        $data['fare_amount'] = $data['fare_amount'] ?? $data['fare_breakdown']['total'];

        return $data;
    }

    /**
     * Re-validates a submitted coupon code against the fare that was just
     * computed and, if it still holds up, discounts fare_amount and records
     * which coupon was used — this is the one authoritative place a coupon
     * ever actually changes what a customer is charged (the widget's own
     * live preview is a courtesy, never trusted on its own). Silently no-ops
     * for a blank/unknown/no-longer-valid code rather than failing the
     * booking outright — a lapsed coupon shouldn't block a real ride.
     *
     * @return array{0: array, 1: ?Coupon} the (possibly discounted) $data, and the coupon actually applied, if any
     */
    public function applyCoupon(array $data, ?string $couponCode): array
    {
        if (blank($couponCode)) {
            return [$data, null];
        }

        $coupon = Coupon::where('code', strtoupper($couponCode))->first();
        $fareAmount = (float) $data['fare_amount'];

        if (! $coupon || ! $coupon->isValidFor($fareAmount)) {
            return [$data, null];
        }

        $discount = $coupon->discountFor($fareAmount);

        $data['coupon_id'] = $coupon->id;
        $data['discount_amount'] = $discount;
        $data['fare_amount'] = round($fareAmount - $discount, 2);

        return [$data, $coupon];
    }

    /**
     * Applies business rules from Booking Settings to a freshly-submitted
     * booking: driver auto-assignment (takes precedence, being the more
     * advanced pipeline state) and auto-confirmation.
     *
     * Both are held back while payment is still owed and an online gateway
     * is actually available to collect it — a booking shouldn't jump to
     * "confirmed" (or get a driver dispatched) before it's paid for just
     * because it was submitted a moment ago. Once the customer pays,
     * confirmAfterPayment() re-applies these same rules.
     */
    public function applyPolicies(array $data): array
    {
        $settings = BookingSetting::current();
        $paymentSatisfied = ! $this->paymentRequired() || ($data['payment_status'] ?? null) === 'paid';

        if ($paymentSatisfied && $settings->driver_auto_assignment_enabled && empty($data['driver_id'])) {
            $driver = Driver::where('is_online', true)
                ->where('is_available', true)
                ->inRandomOrder()
                ->first();

            if ($driver) {
                $data['driver_id'] = $driver->id;

                if (in_array($data['status'], ['pending', 'confirmed'], true)) {
                    $data['status'] = 'assigned';
                }
            }
        }

        if ($paymentSatisfied && $settings->auto_confirmation_enabled && $data['status'] === 'pending') {
            $data['status'] = 'confirmed';
        }

        return $data;
    }

    /**
     * Whether an online gateway is actually enabled and ready to collect
     * payment — while true, a booking is held at "pending" (never
     * auto-confirmed or auto-assigned) until it's actually paid for.
     */
    public function paymentRequired(): bool
    {
        return PaymentGateway::whereIn('code', ['stripe', 'paypal'])->get()->contains(fn ($gateway) => $gateway->isReady());
    }

    /**
     * Re-applies the auto-assignment/auto-confirmation policies once a
     * booking that was held back by paymentRequired() has just been paid —
     * the mirror image of the gate in applyPolicies() above. No-ops for a
     * booking that isn't sitting in "pending" (e.g. an admin already
     * confirmed it manually while it was unpaid).
     */
    public function confirmAfterPayment(Booking $booking): void
    {
        if ($booking->status !== 'pending') {
            return;
        }

        $settings = BookingSetting::current();

        if ($settings->driver_auto_assignment_enabled && ! $booking->driver_id) {
            $driver = Driver::where('is_online', true)
                ->where('is_available', true)
                ->inRandomOrder()
                ->first();

            if ($driver) {
                $booking->driver_id = $driver->id;
                $booking->status = 'assigned';
            }
        }

        if ($booking->status === 'pending' && $settings->auto_confirmation_enabled) {
            $booking->status = 'confirmed';
        }

        if (! $booking->isDirty()) {
            return;
        }

        $booking->save();

        $this->notifyAdminsOfConfirmation($booking);
        $this->notifyCustomerOfCreation($booking);
        $this->notifyDriverOfAssignment($booking);
    }

    /**
     * "New Booking Assigned" notification for the driver a booking was
     * just given to — in-app (database, shown in the driver's own
     * notification center) plus browser push, via the same
     * DriverBookingNotification class every other driver-facing event uses.
     */
    public function notifyDriverOfAssignment(Booking $booking): void
    {
        if (! $booking->driver_id) {
            return;
        }

        $booking->loadMissing('driver');

        if (! $booking->driver) {
            return;
        }

        $booking->driver->notify(new DriverBookingNotification(
            $booking,
            'booking_assigned',
            __('New Booking Assigned'),
            __('Booking #:number has been assigned to you.', ['number' => $booking->booking_number]),
        ));
    }

    /**
     * Admin-side counterpart to notifyCustomerOfCreation() below, fired
     * once a booking reaches "confirmed" after the fact (i.e. via
     * confirmAfterPayment()) rather than at initial creation, where
     * notifyAdminsOfCreation() already covers it.
     */
    private function notifyAdminsOfConfirmation(Booking $booking): void
    {
        if ($booking->status !== 'confirmed') {
            return;
        }

        $this->adminNotifier->send(new BookingConfirmedNotification($booking));
    }

    /**
     * Notifies permission-holding admins that a new booking has just been
     * created, plus a follow-up confirmation notice if policies already
     * advanced it straight to "confirmed".
     */
    public function notifyAdminsOfCreation(Booking $booking): void
    {
        $this->adminNotifier->send(new BookingCreatedNotification($booking));

        if ($booking->status === 'confirmed') {
            $this->adminNotifier->send(new BookingConfirmedNotification($booking));
        }
    }

    /**
     * Notifies the customer of the outcome once policies (auto-assignment,
     * auto-confirmation) have already been applied to a freshly-created booking.
     */
    public function notifyCustomerOfCreation(Booking $booking): void
    {
        if (! $booking->customer) {
            return;
        }

        if ($booking->status === 'confirmed') {
            $booking->customer->notify(new CustomerBookingConfirmedNotification($booking));
        } elseif ($booking->status === 'pending') {
            // Awaiting payment or manual confirmation — still send the
            // customer an immediate receipt of their booking.
            $booking->customer->notify(new CustomerBookingReceivedNotification($booking));
        }

        if ($booking->driver_id) {
            $booking->customer->notify(new CustomerDriverAssignedNotification($booking));
        }
    }
}
