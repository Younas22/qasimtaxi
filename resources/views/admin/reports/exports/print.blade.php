<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1a1a1a; margin: 0; padding: 32px; }
        .brand { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #c9a24b; padding-bottom: 16px; margin-bottom: 24px; }
        .brand h1 { font-size: 18px; margin: 0; }
        .brand p { margin: 2px 0 0; font-size: 12px; color: #666; }
        h2 { font-size: 16px; margin: 0 0 4px; }
        .meta { font-size: 12px; color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
        th { background: #f5f5f5; text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; color: #444; }
        tr:nth-child(even) { background: #fafafa; }
        .toolbar { margin-bottom: 20px; }
        .toolbar button { background: #c9a24b; color: #1a1a1a; border: none; padding: 10px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; }
        @media print {
            .toolbar { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">{{ __('Print / Save as PDF') }}</button>
    </div>

    <div class="brand">
        <div>
            <h1>{{ setting('company_name', config('app.name', 'Limo Schedule')) }}</h1>
            <p>{{ setting('address') }}</p>
        </div>
        <p>{{ __('Generated :date', ['date' => now()->format('M d, Y H:i')]) }}</p>
    </div>

    <h2>{{ $title }}</h2>
    <p class="meta">{{ $from->format('M d, Y') }} &ndash; {{ $to->format('M d, Y') }} &middot; {{ trans_choice(':count record|:count records', count($rows)) }}</p>

    <table>
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align:center; color:#888;">{{ __('No records found.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
