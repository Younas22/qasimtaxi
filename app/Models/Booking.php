<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    public const TYPES = [
        'airport_transfer' => 'Airport Transfer',
        'one_way' => 'One Way',
        'round_trip' => 'Round Trip',
        'hourly' => 'Hourly',
        'corporate' => 'Corporate',
    ];

    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'assigned' => 'Assigned',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const PAYMENT_STATUSES = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'refunded' => 'Refunded',
    ];

    public const REFUND_STATUSES = [
        'not_applicable' => 'Not Applicable',
        'pending' => 'Refund Pending',
        'processing' => 'Processing',
        'refunded' => 'Refunded',
        'rejected' => 'Rejected',
    ];

    public const PAYMENT_GATEWAYS = [
        'stripe' => 'Stripe',
        'paypal' => 'PayPal',
        'wallet' => 'Wallet Credit',
        'manual' => 'Manual / Cash',
    ];

    /**
     * Preset reasons offered on the customer-facing cancel dialog — kept
     * here so the admin panel's booking list can display the same wording.
     */
    public const CANCELLATION_REASONS = [
        'change_of_plans' => 'Change of plans',
        'booked_by_mistake' => 'Booked by mistake',
        'found_another_ride' => 'Found another ride',
        'price_concerns' => 'Price concerns',
        'other' => 'Other',
    ];

    protected $fillable = [
        'booking_number',
        'customer_id',
        'driver_id',
        'vehicle_id',
        'coupon_id',
        'type',
        'pickup_location',
        'pickup_lat',
        'pickup_lng',
        'pickup_place_id',
        'dropoff_location',
        'dropoff_lat',
        'dropoff_lng',
        'dropoff_place_id',
        'stops',
        'pickup_datetime',
        'return_datetime',
        'return_pickup_location',
        'return_pickup_lat',
        'return_pickup_lng',
        'return_pickup_place_id',
        'return_dropoff_location',
        'return_dropoff_lat',
        'return_dropoff_lng',
        'return_dropoff_place_id',
        'return_stops',
        'return_distance_km',
        'return_duration_minutes',
        'hours',
        'distance_km',
        'passengers',
        'luggage',
        'waiting_minutes',
        'has_toll',
        'notes',
        'fare_amount',
        'discount_amount',
        'fare_breakdown',
        'status',
        'cancellation_reason',
        'cancelled_by',
        'payment_status',
        'paid_at',
        'payment_gateway',
        'transaction_id',
        'refund_status',
        'ride_started_at',
        'estimated_arrival_at',
        'dispatch_distance_km',
        'dispatch_duration_minutes',
        'dispatch_lat',
        'dispatch_lng',
        'dispatch_calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'pickup_datetime' => 'datetime',
            'return_datetime' => 'datetime',
            'fare_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'distance_km' => 'decimal:2',
            'pickup_lat' => 'decimal:7',
            'pickup_lng' => 'decimal:7',
            'dropoff_lat' => 'decimal:7',
            'dropoff_lng' => 'decimal:7',
            'return_pickup_lat' => 'decimal:7',
            'return_pickup_lng' => 'decimal:7',
            'return_dropoff_lat' => 'decimal:7',
            'return_dropoff_lng' => 'decimal:7',
            'return_distance_km' => 'decimal:2',
            'stops' => 'array',
            'return_stops' => 'array',
            'has_toll' => 'boolean',
            'fare_breakdown' => 'array',
            'paid_at' => 'datetime',
            'ride_started_at' => 'datetime',
            'estimated_arrival_at' => 'datetime',
            'dispatch_distance_km' => 'decimal:2',
            'dispatch_lat' => 'decimal:7',
            'dispatch_lng' => 'decimal:7',
            'dispatch_calculated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            $booking->booking_number ??= 'BK-'.strtoupper(Str::random(8));
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    public function getRefundStatusLabelAttribute(): ?string
    {
        return $this->refund_status ? (self::REFUND_STATUSES[$this->refund_status] ?? ucfirst($this->refund_status)) : null;
    }

    /**
     * Minutes from now until this ride's estimated end, floored at 0 once
     * passed. Null when the ride hasn't been started yet (no estimate).
     * Computed via raw timestamp subtraction rather than Carbon's
     * diffInMinutes($absolute) flag, whose sign convention is easy to get
     * backwards.
     */
    public function getRideEndsInMinutesAttribute(): ?int
    {
        if (! $this->estimated_arrival_at) {
            return null;
        }

        return max(0, (int) ceil(($this->estimated_arrival_at->getTimestamp() - now()->getTimestamp()) / 60));
    }

    public function getPaymentGatewayLabelAttribute(): ?string
    {
        return $this->payment_gateway ? (self::PAYMENT_GATEWAYS[$this->payment_gateway] ?? ucfirst($this->payment_gateway)) : null;
    }

    public function getInvoiceNumberAttribute(): string
    {
        return 'INV-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getTaxBreakdownAttribute(): array
    {
        $rate = (float) setting('tax_rate', 0);
        $total = (float) $this->fare_amount;
        $subtotal = $rate > 0 ? round($total / (1 + $rate / 100), 2) : $total;

        return [
            'label' => setting('tax_label', 'Tax'),
            'rate' => $rate,
            'subtotal' => $subtotal,
            'tax_amount' => round($total - $subtotal, 2),
            'total' => $total,
        ];
    }

    public function getCancellationReasonLabelAttribute(): ?string
    {
        return $this->cancellation_reason
            ? (self::CANCELLATION_REASONS[$this->cancellation_reason] ?? $this->cancellation_reason)
            : null;
    }

    public function isNightPickup(): bool
    {
        $hour = (int) $this->pickup_datetime->format('H');

        return $hour >= 22 || $hour < 6;
    }
}
