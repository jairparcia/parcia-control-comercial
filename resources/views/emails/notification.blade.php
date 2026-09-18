<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f6f6f7; font-family:Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f6f7; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:480px; background-color:#ffffff; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td style="padding:24px 32px 0 32px;">
                            <div style="width:28px; height:28px; border-radius:8px; background-color:#353636; text-align:center; line-height:28px; color:#f9f9f9; font-weight:bold; font-size:14px;">P</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px 8px 32px;">
                            <h1 style="margin:0; font-size:18px; font-weight:600; color:#353636;">{{ $title }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 28px 32px;">
                            <p style="margin:0; font-size:14px; line-height:1.6; color:#5f5f66;">{{ $body }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px; border-top:1px solid #eaeaea;">
                            <p style="margin:0; font-size:12px; color:#a0a0a8;">&copy; {{ date('Y') }} Parcia. {{ __('common.all_rights_reserved') }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
