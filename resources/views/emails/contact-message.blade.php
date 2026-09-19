<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Contact Message</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #333;">
    <div style="max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); border: 1px solid #eef2f5;">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #004727 0%, #0A9051 100%); padding: 25px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 20px; font-weight: 700;">New Contact Message</h1>
        </div>

        <!-- Body -->
        <div style="padding: 30px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 15px;">
                <tr>
                    <td style="padding: 8px 0; color: #718096; width: 140px;">Name</td>
                    <td style="padding: 8px 0; font-weight: 600;">{{ $payload['name'] }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #718096;">Email</td>
                    <td style="padding: 8px 0;">{{ $payload['email'] }}</td>
                </tr>
                @if (! empty($payload['order']))
                    <tr>
                        <td style="padding: 8px 0; color: #718096;">Order number</td>
                        <td style="padding: 8px 0;">{{ $payload['order'] }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="padding: 8px 0; color: #718096;">Inquiry</td>
                    <td style="padding: 8px 0;">{{ $payload['inquiry_label'] }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #718096; vertical-align: top;">Message</td>
                    <td style="padding: 8px 0; white-space: pre-line;">{{ $payload['message'] }}</td>
                </tr>
            </table>

            <p style="font-size: 13px; color: #718096; margin-top: 25px;">
                Reply directly to this email — it will reach {{ $payload['name'] }}.
            </p>
        </div>
    </div>
</body>
</html>
