<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Promo Banner Settings</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0a0a0a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #f4f4f5;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 420px;
            background: #141414;
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            padding: 28px;
        }
        h1 { font-size: 18px; margin: 0 0 4px; }
        p.sub { color: #9a9a9a; font-size: 13px; margin: 0 0 24px; }
        label.row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            border: 1px solid #2a2a2a;
            border-radius: 10px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: border-color .15s;
        }
        label.row:hover { border-color: #c9a227; }
        label.row input[type=checkbox] {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: #c9a227;
            flex-shrink: 0;
        }
        label.row .title { font-size: 14px; font-weight: 600; }
        label.row .desc { font-size: 12px; color: #9a9a9a; margin-top: 2px; }
        button {
            width: 100%;
            margin-top: 8px;
            padding: 12px;
            border: none;
            border-radius: 10px;
            background: #c9a227;
            color: #0a0a0a;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
        }
        button:hover { background: #ddb84a; }
        .status {
            margin-top: 16px;
            padding: 10px 14px;
            border-radius: 8px;
            background: rgba(201, 162, 39, .12);
            border: 1px solid rgba(201, 162, 39, .3);
            color: #c9a227;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Promo Banner Settings</h1>
        <p class="sub">Controls the two "software for sale" promos on the sale domain. Whichever is checked shows; unchecked stays hidden — no code changes needed.</p>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('promo-banner-settings.update') }}" style="margin-top: 16px;">
            @csrf

            <label class="row">
                <input type="checkbox" name="sale_modal_enabled" value="1" {{ $settings->sale_modal_enabled ? 'checked' : '' }}>
                <span>
                    <span class="title">Popup modal</span>
                    <span class="desc">The centered "White-Label Taxi Booking Software" popup with the countdown and price.</span>
                </span>
            </label>

            <label class="row">
                <input type="checkbox" name="sticky_banner_enabled" value="1" {{ $settings->sticky_banner_enabled ? 'checked' : '' }}>
                <span>
                    <span class="title">Sticky corner banner</span>
                    <span class="desc">The small card pinned to the bottom-right corner with a Price / Hide button.</span>
                </span>
            </label>

            <button type="submit">Save</button>
        </form>
    </div>
</body>
</html>
