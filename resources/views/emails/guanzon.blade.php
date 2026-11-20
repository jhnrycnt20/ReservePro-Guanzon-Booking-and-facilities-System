@php
    $resortName = $resortName ?? 'Guanzon Resort';
    $appName = $appName ?? config('app.name', 'ReservePro');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $emailSubject ?? $appName }}</title>
</head>
<body style="margin:0;padding:0;background:#FAFAFA;font-family:'Segoe UI',Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#FAFAFA;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;border-collapse:separate;border:1px solid #d5e8e4;border-radius:16px;overflow:hidden;">
                    {{-- Brand header: deep-sea + lagoon (ReservePro system colors) --}}
                    <tr>
                        <td style="background:#1F4B5D;padding:26px 24px 20px;text-align:center;">
                            @if (! empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="{{ $resortName }}" width="160" style="display:block;margin:0 auto 12px;width:160px;max-width:68%;height:auto;border:0;">
                            @endif
                            <div style="color:#96DFD3;font-size:11px;font-weight:700;letter-spacing:.28em;text-transform:uppercase;">{{ $resortName }}</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#ffffff;padding:28px 28px 10px;">
                            <h1 style="margin:0 0 14px;color:#1F4B5D;font-family:Georgia,'Times New Roman',serif;font-size:24px;font-weight:600;line-height:1.25;">
                                {{ $heading }}
                            </h1>

                            @foreach (($introLines ?? []) as $line)
                                <p style="margin:0 0 14px;color:#5a7a86;font-size:15px;line-height:1.6;">{{ $line }}</p>
                            @endforeach

                            @isset($actionUrl, $actionText)
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:22px 0 10px;">
                                    <tr>
                                        <td style="border-radius:10px;background:#1F4B5D;">
                                            <a href="{{ $actionUrl }}" style="display:inline-block;padding:13px 26px;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;">
                                                {{ $actionText }}
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            @endisset

                            @foreach (($outroLines ?? []) as $line)
                                <p style="margin:14px 0 0;color:#7a939c;font-size:13px;line-height:1.55;">{{ $line }}</p>
                            @endforeach

                            <p style="margin:22px 0 8px;color:#1F4B5D;font-size:14px;line-height:1.5;">
                                Regards,<br>
                                <strong style="color:#1F4B5D;">{{ $resortName }}</strong>
                                <span style="color:#5a7a86;"> · {{ $appName }}</span>
                            </p>
                        </td>
                    </tr>

                    @isset($actionUrl)
                        <tr>
                            <td style="background:#e8f8f5;padding:16px 28px;border-top:1px solid #d5e8e4;">
                                <p style="margin:0;color:#5a7a86;font-size:12px;line-height:1.5;word-break:break-all;">
                                    If the button doesn’t work, copy and paste this link into your browser:<br>
                                    <a href="{{ $actionUrl }}" style="color:#1F4B5D;text-decoration:underline;">{{ $actionUrl }}</a>
                                </p>
                            </td>
                        </tr>
                    @endisset

                    <tr>
                        <td style="background:#163844;padding:14px 24px;text-align:center;">
                            <p style="margin:0;color:rgba(255,255,255,.72);font-size:11px;line-height:1.45;">
                                © {{ date('Y') }} {{ $resortName }}. Powered by {{ $appName }}.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
