<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $booking->payment_status === 'paid' ? 'Receipt' : 'Invoice' }} {{ $booking->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; }
        html { background: #d9d9d9; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1a1a1a; margin: 0; padding: 0; }
        .toolbar { max-width: 210mm; margin: 20px auto 0; padding: 0 16px; display: flex; gap: 10px; }
        .page { width: 210mm; min-height: 297mm; max-width: calc(100% - 32px); margin: 20px auto 40px; padding: 18mm 16mm; background: #fff; box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18); }
        .brand { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; border-bottom: 2px solid #c9a24b; padding-bottom: 16px; margin-bottom: 24px; }
        .brand .brand-identity { display: flex; align-items: flex-start; gap: 14px; }
        .brand .logo { max-width: 190px; max-height: 48px; object-fit: contain; display: block; }
        .brand h1 { font-size: 18px; margin: 0; }
        .brand p { margin: 2px 0 0; font-size: 12px; color: #666; }
        .brand .invoice-meta { text-align: right; }
        .invoice-meta h2 { font-size: 20px; margin: 0; letter-spacing: 0.04em; }
        .invoice-meta p { margin: 2px 0 0; font-size: 12px; color: #666; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; margin-top: 6px; }
        .badge-paid { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-refunded { background: #e5e7eb; color: #374151; }
        .columns { display: flex; gap: 32px; margin-bottom: 24px; }
        .columns > div { flex: 1; }
        h3 { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #888; margin: 0 0 8px; }
        .box { border: 1px solid #e5e5e5; border-radius: 8px; padding: 14px 16px; font-size: 13px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 8px; }
        th, td { border-bottom: 1px solid #eee; padding: 8px 10px; text-align: left; }
        th { background: #f5f5f5; text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; color: #444; text-align: left; }
        td.amount, th.amount { text-align: right; }
        tfoot td.grand-total { border-top: 2px solid #1a1a1a; border-bottom: none; font-weight: 700; font-size: 15px; }
        .toolbar { margin-bottom: 20px; display: flex; gap: 10px; }
        .toolbar button, .toolbar a { background: #c9a24b; color: #1a1a1a; border: none; padding: 10px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
        .toolbar a.secondary { background: transparent; border: 1px solid #c9a24b; color: #c9a24b; }
        .footer-note { margin-top: 32px; font-size: 11px; color: #999; text-align: center; }
        @media print {
            html { background: #fff; }
            .toolbar { display: none; }
            .page { width: auto; min-height: 0; max-width: none; margin: 0; padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Print Invoice</button>
        <a href="{{ route('booking.invoice.download', $booking->booking_number) }}">Download PDF</a>
    </div>

    <div class="page">
    <div class="brand">
        <div class="brand-identity">
            @if (setting('invoice_logo_url'))
                <img class="logo" src="{{ setting('invoice_logo_url') }}" alt="{{ setting('company_name') }}">
            @endif
            <div>
                <h1>{{ setting('company_name', config('app.name', 'Limo Schedule')) }}</h1>
                <p>{{ setting('address') }}</p>
                @if (setting('email'))<p>{{ setting('email') }}</p>@endif
                @if (setting('phone'))<p>{{ setting('phone') }}</p>@endif
            </div>
        </div>
        <div class="invoice-meta">
            <h2>{{ $booking->payment_status === 'paid' ? 'PAYMENT RECEIPT' : 'INVOICE' }}</h2>
            <p>Invoice #: {{ $booking->invoice_number }}</p>
            <p>Booking ID: {{ $booking->booking_number }}</p>
            <p>{{ ($booking->paid_at ?? $booking->pickup_datetime)->format('M d, Y') }}</p>
            <span class="badge badge-{{ $booking->payment_status }}">{{ $booking->payment_status_label }}</span>
        </div>
    </div>

    <div class="columns">
        <div>
            <h3>Billed To</h3>
            <div class="box">
                {{ $booking->customer?->name }}<br>
                {{ $booking->customer?->email }}<br>
                {{ $booking->customer?->phone }}
            </div>
        </div>
        <div>
            <h3>Trip Details</h3>
            <div class="box">
                <strong>Pickup:</strong> {{ $booking->pickup_location }}<br>
                <strong>Drop-off:</strong> {{ $booking->dropoff_location }}<br>
                <strong>Date &amp; Time:</strong> {{ $booking->pickup_datetime->format('M d, Y — h:i A') }}
            </div>
        </div>
        <div>
            <h3>Vehicle &amp; Driver</h3>
            <div class="box">
                <strong>Vehicle:</strong> {{ $booking->vehicle?->category?->name ?? $booking->vehicle?->name }}<br>
                @if ($booking->vehicle)
                    <strong>Model:</strong> {{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}<br>
                    <strong>Plate:</strong> {{ $booking->vehicle->plate_number ?? '—' }}<br>
                @endif
                <strong>Driver:</strong> {{ $booking->driver?->name ?? 'Unassigned' }}
            </div>
        </div>
    </div>

    <h3>Fare Breakdown</h3>
    <table>
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
    </div>
</body>
</html>
