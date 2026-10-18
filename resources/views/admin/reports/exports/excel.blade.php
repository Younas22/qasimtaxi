<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
</head>
<body>
    <table border="1" cellspacing="0" cellpadding="4">
        <tr>
            <th colspan="{{ count($headers) }}" style="font-size:14px; text-align:left; background:#f2f2f2;">{{ $title }}</th>
        </tr>
        <tr>
            @foreach ($headers as $header)
                <th style="background:#e5e5e5; text-align:left;">{{ $header }}</th>
            @endforeach
        </tr>
        @foreach ($rows as $row)
            <tr>
                @foreach ($row as $cell)
                    <td>{{ $cell }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
</body>
</html>
