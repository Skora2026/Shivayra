<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Password Reset OTP</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f8; margin: 0; padding: 20px; color: #333;">
    <div style="max-width: 500px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); border: 1px solid #eef2f5;">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #004727 0%, #0A9051 100%); padding: 25px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 22px; font-weight: 700; letter-spacing: 0.5px;">Password Reset Request</h1>
        </div>

        <!-- Body -->
        <div style="padding: 30px; text-align: center;">
            <p style="font-size: 16px; line-height: 1.5; color: #4a5568; margin-top: 0; margin-bottom: 20px; text-align: left;">
                Hello,
            </p>
            <p style="font-size: 15px; line-height: 1.6; color: #4a5568; margin-bottom: 25px; text-align: left;">
                We received a request to reset the password for your account. Please use the following 6-digit OTP to complete your password reset. This code is valid for 15 minutes.
            </p>

            <!-- OTP Code Box -->
            <div style="display: inline-block; background-color: #f0fdf4; border: 2px dashed #0A9051; border-radius: 8px; padding: 15px 35px; margin-bottom: 25px;">
                <span style="font-size: 28px; font-weight: 800; color: #004727; letter-spacing: 4px;">{{ $otp }}</span>
            </div>

            <p style="font-size: 13px; line-height: 1.5; color: #718096; margin-bottom: 20px; text-align: left;">
                If you did not request this change, you can safely ignore this email. Your password will remain unchanged.
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8fafc; padding: 15px; text-align: center; border-top: 1px solid #edf2f7; font-size: 12px; color: #718096;">
            <p style="margin: 0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>
