<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
</head>
<body style="font-family: 'Montserrat', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #FAF7F2; margin: 0; padding: 24px 12px; color: #1C1C1C;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 4px; overflow: hidden; border: 1px solid rgba(176, 141, 87, 0.35);">

        {{-- Inline icon set: emails can't reference page SVG sprites, so the two
             needed glyphs are embedded directly with currentColor strokes. --}}
        <div style="background: linear-gradient(135deg, #40111F 0%, #2A0C14 100%); padding: 34px 30px 28px; text-align: center; color: #F3EDE4;">
            @if($settings?->logo)
                <img src="{{ asset('storage/' . $settings->logo) }}" alt="{{ $settings->site_name ?? 'Shivayra' }}" style="height: 46px; border-radius: 6px; margin-bottom: 14px;">
            @endif
            <h1 style="margin: 0; font-family: Georgia, 'Times New Roman', serif; font-size: 26px; font-weight: 500; letter-spacing: 0.5px; color: #F3EDE4;">
                @if($isAdmin)
                    New Order Received
                @else
                    Thank You For Your Order
                @endif
            </h1>
            <div style="width: 44px; height: 1px; background: #B08D57; margin: 14px auto;"></div>
            <p style="margin: 0; font-size: 12px; letter-spacing: 2px; text-transform: uppercase; color: #C9A96A;">Order #{{ $order->id }}</p>
        </div>

        <!-- Body Content -->
        <div style="padding: 30px;">
            <p style="font-size: 15px; line-height: 1.6; color: #6B6B6B; margin: 0 0 25px;">
                @if($isAdmin)
                    A new order has been placed on your store. Here are the order details:
                @else
                    Hello {{ $order->name }}, your order has been placed successfully. We are preparing your items for shipment.
                @endif
            </p>

            <!-- Order Table -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
                <thead>
                    <tr style="background-color: #FAF7F2; border-bottom: 1px solid rgba(176, 141, 87, 0.35);">
                        <th style="padding: 12px; text-align: left; font-size: 11px; font-weight: 600; color: #6B6B6B; text-transform: uppercase; letter-spacing: 1px;">Product</th>
                        <th style="padding: 12px; text-align: center; font-size: 11px; font-weight: 600; color: #6B6B6B; text-transform: uppercase; letter-spacing: 1px;">Qty</th>
                        <th style="padding: 12px; text-align: right; font-size: 11px; font-weight: 600; color: #6B6B6B; text-transform: uppercase; letter-spacing: 1px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr style="border-bottom: 1px solid #F1E8DC;">
                            <td style="padding: 12px; font-size: 14px; color: #1C1C1C;">{{ $item->product_name }}</td>
                            <td style="padding: 12px; text-align: center; font-size: 14px; color: #6B6B6B;">x{{ $item->qty }}</td>
                            <td style="padding: 12px; text-align: right; font-size: 14px; font-weight: 600; color: #9B1B30;">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Total Calculations -->
            <div style="background-color: #FAF7F2; border-radius: 4px; padding: 20px; margin-bottom: 25px; border: 1px solid rgba(176, 141, 87, 0.25);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: #6B6B6B;">
                    <span>Subtotal</span>
                    <span style="font-weight: 600; color: #1C1C1C;">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: #6B6B6B;">
                    <span>Shipping</span>
                    <span style="font-weight: 600; color: #1C1C1C;">₹{{ number_format($order->shipping_charge, 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: #6B6B6B;">
                    <span>Tax</span>
                    <span style="font-weight: 600; color: #1C1C1C;">₹{{ number_format($order->tax, 2) }}</span>
                </div>
                <hr style="border: 0; border-top: 1px solid rgba(176, 141, 87, 0.35); margin: 12px 0;">
                <div style="display: flex; justify-content: space-between; font-size: 16px;">
                    <span style="font-weight: 600; color: #1C1C1C;">Total</span>
                    <span style="font-weight: 700; color: #9B1B30;">₹{{ number_format($order->total, 2) }}</span>
                </div>
            </div>

            <!-- Address -->
            <div style="background-color: #FAF7F2; border-radius: 4px; padding: 15px; border: 1px solid rgba(176, 141, 87, 0.25); margin-bottom: 25px;">
                <h4 style="margin: 0 0 8px 0; color: #6B6B6B; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Shipping Address</h4>
                <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #1C1C1C;">
                    <strong>{{ $order->shipping_name }}</strong><br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}<br>
                    Phone: {{ $order->shipping_phone }}
                </p>
            </div>

            <hr style="border: 0; border-top: 1px solid rgba(176, 141, 87, 0.25); margin: 25px 0;">

            <div style="text-align: center;">
                <a href="{{ url('/') }}" style="display: inline-block; background-color: #5C1A2E; color: #F3EDE4; padding: 12px 34px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; letter-spacing: 0.6px; text-transform: uppercase;">
                    Visit Store
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div style="background: #FAF7F2; padding: 16px 30px; text-align: center;">
            <p style="margin: 0; font-size: 12px; color: #6B6B6B;">
                &copy; {{ date('Y') }} {{ $settings?->site_name ?? 'Shivayra' }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
