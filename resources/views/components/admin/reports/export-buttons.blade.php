@props(['report'])

@php
    $query = request()->query();
@endphp

<div class="flex flex-wrap items-center gap-2">
    @permission('reports.export')
        <a href="{{ route('admin.reports.export.csv', ['report' => $report, ...$query]) }}"
            class="rounded-lg border border-luxury-border px-3 py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
            {{ __('Export CSV') }}
        </a>
        <a href="{{ route('admin.reports.export.excel', ['report' => $report, ...$query]) }}"
            class="rounded-lg border border-luxury-border px-3 py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
            {{ __('Export Excel') }}
        </a>
        <a href="{{ route('admin.reports.print', ['report' => $report, ...$query]) }}" target="_blank" rel="noopener"
            class="rounded-lg border border-luxury-border px-3 py-2 text-xs font-medium text-luxury-muted transition hover:border-luxury-gold/40 hover:text-luxury-gold">
            {{ __('Export PDF') }}
        </a>
    @endpermission
</div>
