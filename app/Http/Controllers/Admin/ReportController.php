<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\ReportExportService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reports)
    {
    }

    public function index(): View
    {
        $revenueChart = $this->reports->monthlyRevenueChart();
        $bookingChart = $this->reports->bookingGrowthChart();

        $summary = [
            'total_revenue' => (float) Booking::where('payment_status', 'paid')->sum('fare_amount'),
            'total_bookings' => Booking::count(),
            'active_vehicles' => \App\Models\Vehicle::where('status', true)->count(),
            'active_drivers' => \App\Models\Driver::where('status', true)->count(),
            'total_customers' => \App\Models\Customer::count(),
        ];

        return view('admin.reports.index', compact('revenueChart', 'bookingChart', 'summary'));
    }

    public function revenue(Request $request): View
    {
        [$from, $to] = $this->reports->dateRange($request);

        $summary = $this->reports->revenueSummary($from, $to);
        $rows = $this->reports->revenueRows($from, $to)->paginate(20)->withQueryString();

        return view('admin.reports.revenue', compact('summary', 'rows', 'from', 'to'));
    }

    public function bookings(Request $request): View
    {
        [$from, $to] = $this->reports->dateRange($request);
        $status = $request->query('status') ?: null;
        $type = $request->query('type') ?: null;

        $summary = $this->reports->bookingSummary($from, $to);
        $rows = $this->reports->bookingRows($from, $to, $status, $type)->paginate(20)->withQueryString();

        return view('admin.reports.bookings', compact('summary', 'rows', 'from', 'to', 'status', 'type'));
    }

    public function vehicles(Request $request): View
    {
        [$from, $to] = $this->reports->dateRange($request);
        $rows = $this->reports->vehicleRows($from, $to)->paginate(20)->withQueryString();

        return view('admin.reports.vehicles', compact('rows', 'from', 'to'));
    }

    public function drivers(Request $request): View
    {
        [$from, $to] = $this->reports->dateRange($request);
        $rows = $this->reports->driverRows($from, $to)->paginate(20)->withQueryString();

        return view('admin.reports.drivers', compact('rows', 'from', 'to'));
    }

    public function customers(Request $request): View
    {
        [$from, $to] = $this->reports->dateRange($request);
        $rows = $this->reports->customerRows($from, $to)->paginate(20)->withQueryString();

        return view('admin.reports.customers', compact('rows', 'from', 'to'));
    }

    public function exportCsv(Request $request, string $report, ReportExportService $exporter): StreamedResponse
    {
        [$headers, $rows, $filename] = $this->exportData($request, $report);

        return $exporter->csv($filename, $headers, $rows);
    }

    public function exportExcel(Request $request, string $report, ReportExportService $exporter): Response
    {
        [$headers, $rows, $filename, $title] = $this->exportData($request, $report);

        return $exporter->excel($filename, $title, $headers, $rows);
    }

    public function print(Request $request, string $report): View
    {
        [$headers, $rows, , $title] = $this->exportData($request, $report);
        [$from, $to] = $this->reports->dateRange($request);

        return view('admin.reports.exports.print', compact('title', 'headers', 'rows', 'from', 'to'));
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, array<int, mixed>>, 2: string, 3: string}
     */
    private function exportData(Request $request, string $report): array
    {
        abort_unless(in_array($report, ['revenue', 'bookings', 'vehicles', 'drivers', 'customers'], true), 404);

        [$from, $to] = $this->reports->dateRange($request);
        $rangeLabel = $from->format('Y-m-d').'_to_'.$to->format('Y-m-d');

        return match ($report) {
            'revenue' => [
                ['Booking #', 'Customer', 'Vehicle', 'Pickup Date', 'Type', 'Fare'],
                $this->reports->revenueRows($from, $to)->get()->map(fn ($b) => [
                    $b->booking_number,
                    $b->customer?->name ?? '—',
                    $b->vehicle?->name ?? '—',
                    $b->pickup_datetime->format('Y-m-d H:i'),
                    $b->type_label,
                    number_format((float) $b->fare_amount, 2),
                ])->all(),
                "revenue-report_{$rangeLabel}",
                'Revenue Report ('.$from->format('M d, Y').' – '.$to->format('M d, Y').')',
            ],
            'bookings' => [
                ['Booking #', 'Customer', 'Driver', 'Vehicle', 'Type', 'Status', 'Pickup Date', 'Fare'],
                $this->reports->bookingRows($from, $to, $request->query('status') ?: null, $request->query('type') ?: null)->get()->map(fn ($b) => [
                    $b->booking_number,
                    $b->customer?->name ?? '—',
                    $b->driver?->name ?? '—',
                    $b->vehicle?->name ?? '—',
                    $b->type_label,
                    $b->status_label,
                    $b->pickup_datetime->format('Y-m-d H:i'),
                    number_format((float) $b->fare_amount, 2),
                ])->all(),
                "bookings-report_{$rangeLabel}",
                'Bookings Report ('.$from->format('M d, Y').' – '.$to->format('M d, Y').')',
            ],
            'vehicles' => [
                ['Vehicle', 'Category', 'Bookings', 'Revenue', 'Avg Rating'],
                $this->reports->vehicleRows($from, $to)->get()->map(fn ($v) => [
                    $v->name,
                    $v->category?->name ?? '—',
                    $v->bookings_count,
                    number_format((float) ($v->revenue ?? 0), 2),
                    $v->average_rating ?? '—',
                ])->all(),
                "vehicles-report_{$rangeLabel}",
                'Vehicles Report ('.$from->format('M d, Y').' – '.$to->format('M d, Y').')',
            ],
            'drivers' => [
                ['Driver', 'Completed Bookings', 'Revenue', 'Commission Rate', 'Commission Earned', 'Avg Rating'],
                $this->reports->driverRows($from, $to)->get()->map(fn ($d) => [
                    $d->name,
                    $d->bookings_count,
                    number_format((float) ($d->revenue ?? 0), 2),
                    number_format((float) $d->commission_rate, 2).'%',
                    number_format((float) ($d->revenue ?? 0) * ((float) $d->commission_rate / 100), 2),
                    $d->average_rating ?? '—',
                ])->all(),
                "drivers-report_{$rangeLabel}",
                'Drivers Report ('.$from->format('M d, Y').' – '.$to->format('M d, Y').')',
            ],
            'customers' => [
                ['Customer', 'Email', 'Bookings', 'Total Spent', 'Wallet Balance', 'Loyalty Points'],
                $this->reports->customerRows($from, $to)->get()->map(fn ($c) => [
                    $c->name,
                    $c->email,
                    $c->bookings_count,
                    number_format((float) ($c->total_spent ?? 0), 2),
                    number_format((float) $c->wallet_balance, 2),
                    $c->loyalty_points,
                ])->all(),
                "customers-report_{$rangeLabel}",
                'Customers Report ('.$from->format('M d, Y').' – '.$to->format('M d, Y').')',
            ],
        };
    }
}
