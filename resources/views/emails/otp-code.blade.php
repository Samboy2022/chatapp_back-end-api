{{-- Table-based layout on purpose: Outlook and several Android mail clients
     still ignore flexbox/grid, and a verification code that renders as a
     jumbled column is a support ticket. Styles are inline for the same reason. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <title>{{ $headline }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">

    {{-- Preheader: the grey preview line in the inbox list. Hidden in the body
         itself, but it's what the user reads before opening. --}}
    <div style="display:none; max-height:0; overflow:hidden; opacity:0;">
        {{ $code }} is your {{ $appName }} code. It expires in {{ $expiryMinutes }} minutes.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f6f8; padding:32px 16px;">
        <tr>
            <td align="center">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:520px; background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 1px 3px rgba(16,24,40,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td align="center" style="padding:32px 32px 8px 32px;">
                            @if (!empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="56" height="56" style="display:block; border:0; border-radius:12px; margin-bottom:16px;">
                            @endif
                            <div style="font-size:14px; font-weight:600; color:#16a34a; letter-spacing:0.4px; text-transform:uppercase;">
                                {{ $appName }}
                            </div>
                        </td>
                    </tr>

                    {{-- Headline --}}
                    <tr>
                        <td align="center" style="padding:8px 32px 0 32px;">
                            <h1 style="margin:0; font-size:22px; line-height:1.3; font-weight:700; color:#111b21;">
                                {{ $headline }}
                            </h1>
                        </td>
                    </tr>

                    {{-- Greeting --}}
                    @if (!empty($userName))
                        <tr>
                            <td align="center" style="padding:16px 32px 0 32px;">
                                <p style="margin:0; font-size:15px; line-height:1.6; color:#667781;">
                                    Hi {{ $userName }},
                                </p>
                            </td>
                        </tr>
                    @endif

                    {{-- The code itself: the whole reason for the email, so it
                         gets the most visual weight on the page. --}}
                    <tr>
                        <td align="center" style="padding:24px 32px 0 32px;">
                            <div style="background-color:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:20px 24px;">
                                <div style="font-size:36px; font-weight:700; letter-spacing:10px; color:#15803d; font-family:'SF Mono',Menlo,Consolas,monospace; text-indent:10px;">
                                    {{ $code }}
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Expiry --}}
                    <tr>
                        <td align="center" style="padding:16px 32px 0 32px;">
                            <p style="margin:0; font-size:14px; line-height:1.6; color:#667781;">
                                This code expires in <strong style="color:#111b21;">{{ $expiryMinutes }} minutes</strong>.
                            </p>
                        </td>
                    </tr>

                    {{-- Context --}}
                    <tr>
                        <td align="center" style="padding:20px 32px 0 32px;">
                            <p style="margin:0; font-size:14px; line-height:1.6; color:#667781;">
                                {{ $explainer }}
                            </p>
                        </td>
                    </tr>

                    {{-- Security note --}}
                    <tr>
                        <td style="padding:24px 32px 0 32px;">
                            <div style="border-top:1px solid #e9edef; padding-top:20px;">
                                <p style="margin:0; font-size:13px; line-height:1.6; color:#8696a0;">
                                    Nobody from {{ $appName }} will ever ask you for this code. If someone does, do not share it.
                                </p>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:24px 32px 32px 32px;">
                            <p style="margin:0; font-size:12px; line-height:1.5; color:#8696a0;">
                                &copy; {{ date('Y') }} {{ $appName }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
