<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Single source of truth for every Reports query: the same builder methods
 * back both the on-screen (paginated) tables and the CSV/Excel/PDF exports,
 * so what an admin sees always matches what they download.
 */
class ReportService
{
    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function dateRange(Request $request): array
    {
        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfDay();

        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->subDays(29)->startOfDay();

        return [$from, $to];
    }

    public function revenueRows(Carbon $from, Carbon $to): Builder
    {
        return Booking::with(['customer', 'vehicle'])
            ->whereBetween('pickup_datetime', [$from, $to])
            ->where('payment_status', 'paid')
            ->orderByDesc('pickup_datetime');
    }

    /**
     * @return array<string, mixed>
     */
    public function revenueSummary(Carbon $from, Carbon $to): array
    {
        $base = fn () => Booking::whereBetween('pickup_datetime', [$from, $to]);

        $paidCount = (clone $base())->where('payment_status', 'paid')->count();
        $totalRevenue = (float) (clone $base())->where('payment_status', 'paid')->sum('fare_amount');

        return [
            'total_revenue' => $totalRevenue,
            'paid_count' => $paidCount,
            'pending_count' => (clone $base())->where('payment_status', 'pending')->count(),
            'refunded_count' => (clone $base())->where('payment_status', 'refunded')->count(),
            'refunded_amount' => (float) (clone $base())->where('payment_status', 'refunded')->sum('fare_amount'),
            'average_fare' => $paidCount > 0 ? round($totalRevenue / $paidCount, 2) : 0.0,
        ];
    }

    public function bookingRows(Carbon $from, Carbon $to, ?string $status = null, ?string $type = null): Builder
    {
        return Booking::with(['customer', 'driver', 'vehicle'])
            ->whereBetween('pickup_datetime', [$from, $to])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('pickup_datetime');
    }

    /**
     * @return array<string, mixed>
     */
    public function bookingSummary(Carbon $from, Carbon $to): array
    {
        $base = Booking::whereBetween('pickup_datetime', [$from, $to]);

        return [
            'total' => (clone $base)->count(),
            'by_status' => (clone $base)->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status')->all(),
            'by_type' => (clone $base)->selectRaw('type, count(*) as c')->groupBy('type')->pluck('c', 'type')->all(),
        ];
    }

    public function vehicleRows(Carbon $from, Carbon $to): Builder
    {
        return Vehicle::with('category')
            ->withCount(['bookings' => fn ($q) => $q->whereBetween('pickup_datetime', [$from, $to])])
            ->withSum(['bookings as revenue' => fn ($q) => $q->whereBetween('pickup_datetime', [$from, $to])->where('payment_status', 'paid')], 'fare_amount')
            ->orderByDesc('bookings_count');
    }

    public function driverRows(Carbon $from, Carbon $to): Builder
    {
        return Driver::withCount(['bookings' => fn ($q) => $q->whereBetween('pickup_datetime', [$from, $to])->where('status', 'completed')])
            ->withSum(['bookings as revenue' => fn ($q) => $q->whereBetween('pickup_datetime', [$from, $to])->where('payment_status', 'paid')], 'fare_amount')
            ->orderByDesc('bookings_count');
    }

    public function customerRows(Carbon $from, Carbon $to): Builder
    {
        return Customer::withCount(['bookings' => fn ($q) => $q->whereBetween('pickup_datetime', [$from, $to])])
            ->withSum(['bookings as total_spent' => fn ($q) => $q->whereBetween('pickup_datetime', [$from, $to])->where('payment_status', 'paid')], 'fare_amount')
            ->orderByDesc('total_spent');
    }

    /**
     * Last 12 months of paid revenue, oldest first.
     *
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    public function monthlyRevenueChart(): array
    {
        $start = now()->subMonths(11)->startOfMonth();

        $rows = Booking::where('payment_status', 'paid')
            ->where('pickup_datetime', '>=', $start)
            ->selectRaw('YEAR(pickup_datetime) as y, MONTH(pickup_datetime) as m, SUM(fare_amount) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn ($row) => $row->y.'-'.$row->m);

        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->year.'-'.$month->month;
            $labels[] = $month->format('M Y');
            $values[] = (float) ($rows[$key]->total ?? 0);
        }

        return compact('labels', 'values');
    }

    /**
     * Last 12 months of booking counts, oldest first.
     *
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function bookingGrowthChart(): array
    {
        $start = now()->subMonths(11)->startOfMonth();

        $rows = Booking::where('pickup_datetime', '>=', $start)
            ->selectRaw('YEAR(pickup_datetime) as y, MONTH(pickup_datetime) as m, COUNT(*) as total')
            ->groupBy('y', 'm')
            ->get()
            ->keyBy(fn ($row) => $row->y.'-'.$row->m);

        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $key = $month->year.'-'.$month->month;
            $labels[] = $month->format('M Y');
            $values[] = (int) ($rows[$key]->total ?? 0);
        }

        return compact('labels', 'values');
    }
}
