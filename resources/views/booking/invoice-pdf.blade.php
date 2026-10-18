<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $booking->payment_status === 'paid' ? 'Receipt' : 'Invoice' }} {{ $booking->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { size: A4; margin: 18mm 16mm; }
        body { font-family: Helvetica, Arial, sans-serif; color: #1a1a1a; margin: 0; padding: 0; font-size: 12px; }
        .brand-table { width: 100%; border-bottom: 2px solid #c9a24b; padding-bottom: 14px; margin-bottom: 20px; }
        .brand-table td { border: none; padding: 0; vertical-align: top; }
        .brand-table td.logo-cell { width: 180px; padding-right: 14px; }
        .brand-table img.logo { max-width: 170px; max-height: 44px; }
        .brand h1 { font-size: 17px; margin: 0; }
        .brand p { margin: 2px 0 0; font-size: 11px; color: #666; }
        .invoice-meta { text-align: right; }
        .invoice-meta h2 { font-size: 18px; margin: 0; letter-spacing: 0.04em; }
        .invoice-meta p { margin: 2px 0 0; font-size: 11px; color: #666; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; margin-top: 6px; }
        .badge-paid { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-refunded { background: #e5e7eb; color: #374151; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { border: none; padding: 0 10px 0 0; vertical-align: top; width: 33.33%; }
        h3 { font-size: 10px; text-transform: uppercase; letter-spacing: 0.06em; color: #888; margin: 0 0 6px; }
        .box { border: 1px solid #e5e5e5; border-radius: 6px; padding: 10px 12px; font-size: 11px; line-height: 1.6; }
        table.fare { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 6px; }
        table.fare th, table.fare td { border-bottom: 1px solid #eee; padding: 7px 8px; text-align: left; }
        table.fare th { background: #f5f5f5; text-transform: uppercase; font-size: 9px; letter-spacing: 0.04em; color: #444; }
        table.fare td.amount, table.fare th.amount { text-align: right; }
        table.fare tfoot td.grand-total { border-top: 2px solid #1a1a1a; border-bottom: none; font-weight: 700; font-size: 13px; }
        .footer-note { margin-top: 28px; font-size: 10px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <table class="brand-table">
        <tr>
            @if ($logoPath)
                <td class="logo-cell">
                    <img class="logo" src="{{ $logoPath }}" alt="{{ setting('company_name') }}">
                </td>
            @endif
            <td class="brand">
                <h1>{{ setting('company_name', config('app.name', 'Limo Schedule')) }}</h1>
                <p>{{ setting('address') }}</p>
                @if (setting('email'))<p>{{ setting('email') }}</p>@endif
                @if (setting('phone'))<p>{{ setting('phone') }}</p>@endif
            </td>
            <td class="invoice-meta">
                <h2>{{ $booking->payment_status === 'paid' ? 'PAYMENT RECEIPT' : 'INVOICE' }}</h2>
                <p>Invoice #: {{ $booking->invoice_number }}</p>
                <p>Booking ID: {{ $booking->booking_number }}</p>
                <p>{{ ($booking->paid_at ?? $booking->pickup_datetime)->format('M d, Y') }}</p>
                <span class="badge badge-{{ $booking->payment_status }}">{{ $booking->payment_status_label }}</span>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td>
                <h3>Billed To</h3>
                <div class="box">
                    {{ $booking->customer?->name }}<br>
                    {{ $booking->customer?->email }}<br>
                    {{ $booking->customer?->phone }}
                </div>
            </td>
            <td>
                <h3>Trip Details</h3>
                <div class="box">
                    <strong>Pickup:</strong> {{ $booking->pickup_location }}<br>
                    <strong>Drop-off:</strong> {{ $booking->dropoff_location }}<br>
                    <strong>Date &amp; Time:</strong> {{ $booking->pickup_datetime->format('M d, Y — h:i A') }}
                </div>
            </td>
            <td>
                <h3>Vehicle &amp; Driver</h3>
                <div class="box">
                    <strong>Vehicle:</strong> {{ $booking->vehicle?->category?->name ?? $booking->vehicle?->name }}<br>
                    @if ($booking->vehicle)
                        <strong>Model:</strong> {{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}<br>
                        <strong>Plate:</strong> {{ $booking->vehicle->plate_number ?? '—' }}<br>
                    @endif
                    <strong>Driver:</strong> {{ $booking->driver?->name ?? 'Unassigned' }}
                </div>
            </td>
        </tr>
    </table>

    <h3>Fare Breakdown</h3>
    <table class="fare">
        <thead>
            <tr>
                <th>Description</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $breakdown = $booking->fare_breakdown ?? []; @endphp
            @foreach ([
                'base_fare' => 'Base Fare',
                'distance_fare' => 'Distance Fare',
                'hour_fare' => 'Hourly Fare',
                'waiting_charge' => 'Waiting Charge',
                'night_charge' => 'Night Surcharge',
                'weekend_charge' => 'Weekend Surcharge',
                'toll_charge' => 'Toll Charge',
                'airport_surcharge' => 'Airport Surcharge',
                'service_fee' => 'Service Fee',
                'extra_passenger_charge' => 'Extra Passenger Charge',
                'approach_charge' => 'Pickup Distance Charge',
            ] as $key => $label)
                @continue(empty($breakdown[$key]))
                <tr>
                    <td>{{ $label }}</td>
                    <td class="amount">{{ currency($breakdown[$key]) }}</td>
                </tr>
            @endforeach
            @if ($booking->discount_amount > 0)
                <tr>
                    <td>Coupon Discount{{ $booking->coupon ? ' ('.$booking->coupon->code.')' : '' }}</td>
                    <td class="amount">&minus;{{ currency($booking->discount_amount) }}</td>
                </tr>
            @endif
        </tbody>
        @php $tax = $booking->tax_breakdown; @endphp
        <tfoot>
            <tr>
                <td>Subtotal</td>
                <td class="amount">{{ currency($tax['subtotal']) }}</td>
            </tr>
            @if ($tax['tax_amount'] > 0)
                <tr>
                    <td>{{ $tax['label'] }} ({{ rtrim(rtrim(number_format($tax['rate'], 2), '0'), '.') }}%)</td>
                    <td class="amount">{{ currency($tax['tax_amount']) }}</td>
                </tr>
            @endif
            <tr>
                <td class="grand-total">Total {{ $booking->payment_status === 'paid' ? 'Paid' : 'Due' }}</td>
                <td class="amount grand-total">{{ currency($tax['total']) }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="footer-note">Thank you for choosing {{ setting('company_name', config('app.name', 'Limo Schedule')) }}. This {{ $booking->payment_status === 'paid' ? 'receipt' : 'invoice' }} was generated on {{ now()->format('M d, Y H:i') }}.</p>
</body>
</html>
