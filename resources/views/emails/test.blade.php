<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email</title>
</head>
<body style="margin:0; padding:0; background-color:#0b0b0d; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#0b0b0d; padding:40px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px; background-color:#151517; border:1px solid #26262a; border-radius:16px; overflow:hidden;">
                    <tr>
                        <td style="padding:32px 32px 24px; text-align:center; border-bottom:1px solid #26262a;">
                            @if (setting('logo_url'))
                                <img src="{{ setting('logo_url') }}" alt="{{ setting('company_name') }}" style="height:40px; max-width:180px; object-fit:contain;">
                            @else
                                <span style="display:inline-block; height:40px; width:40px; line-height:40px; border-radius:10px; background-color:#c9a227; color:#0b0b0d; font-weight:700; font-size:18px;">
                                    {{ strtoupper(substr(setting('company_name', 'Limo Schedule'), 0, 1)) }}
                                </span>
                            @endif
                            <p style="margin:12px 0 0; color:#f4f4f5; font-size:16px; font-weight:600; letter-spacing:0.02em;">
                                {{ setting('company_name', config('app.name', 'Limo Schedule')) }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 8px; color:#c9a227; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em;">Email Configuration Test</p>
                            <h1 style="margin:0 0 16px; color:#f4f4f5; font-size:22px; font-weight:600;">This is a test email</h1>
                            <p style="margin:0 0 24px; color:#a1a1aa; font-size:14px; line-height:1.6;">
                                If you're reading this, your outgoing mail configuration is working correctly. This message was sent from the Booking Settings &rarr; Email panel to verify delivery.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#0b0b0d; border:1px solid #26262a; border-radius:12px;">
                                <tr>
                                    <td style="padding:16px 20px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:4px 0; color:#a1a1aa; font-size:13px;">Mailer</td>
                                                <td style="padding:4px 0; color:#f4f4f5; font-size:13px; text-align:right; font-weight:600;">{{ ucfirst($mailerName) }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 0; color:#a1a1aa; font-size:13px;">Sent At</td>
                                                <td style="padding:4px 0; color:#f4f4f5; font-size:13px; text-align:right; font-weight:600;">{{ $sentAt->format('M d, Y h:i A') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px; border-top:1px solid #26262a; text-align:center;">
                            <p style="margin:0; color:#6b6b70; font-size:12px;">&copy; {{ now()->year }} {{ setting('company_name', config('app.name')) }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
