<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Update</title>
</head>
<body style="font-family: 'Montserrat', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #FAF7F2; margin: 0; padding: 24px 12px; color: #1C1C1C;">
    @php
        $copy = match ($state) {
            'shipped' => [
                'label'  => 'Shipped',
                'title'  => 'Your Order Is On Its Way',
                'line'   => "Hello {$order->name}, your order has been packed and handed to our courier. It will arrive shortly — keep the order details below handy.",
                'accent' => '#9B1B30',
            ],
            'delivered' => [
                'label'  => 'Delivered',
                'title'  => 'Your Order Has Been Delivered',
                'line'   => "Hello {$order->name}, your order was delivered successfully. We hope you love it — you can share a review from your account.",
                'accent' => '#0A9051',
            ],
            'cancelled' => [
                'label'  => 'Cancelled',
                'title'  => 'Your Order Has Been Cancelled',
                'line'   => "Hello {$order->name}, your order has been cancelled. If you had already paid, the refund will be initiated to your original payment method.",
                'accent' => '#9B1B30',
            ],
            'returned' => [
                'label'  => 'Returned',
                'title'  => 'Your Return Has Been Approved',
                'line'   => "Hello {$order->name}, your return request for the order below has been approved. Please pack the item and keep it ready for pickup as per the instructions shared with you.",
                'accent' => '#B08D57',
            ],
            'refunded' => [
                'label'  => 'Refunded',
                'title'  => 'Your Refund Has Been Processed',
                'line'   => "Hello {$order->name}, your refund for the order below has been processed. The amount will reflect in your account within 3–5 business days, depending on your bank.",
                'accent' => '#0A9051',
            ],
            default => [
                'label'  => 'Update',
                'title'  => 'Order Update',
                'line'   => "Hello {$order->name}, there is an update on your order below.",
                'accent' => '#B08D57',
            ],
        };
    @endphp
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 4px; overflow: hidden; border: 1px solid rgba(176, 141, 87, 0.35);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #40111F 0%, #2A0C14 100%); padding: 34px 30px 28px; text-align: center; color: #F3EDE4;">
            @if($settings?->logo)
                <img src="{{ asset('storage/' . $settings->logo) }}" alt="{{ $settings->site_name ?? 'Shivayra' }}" style="height: 46px; border-radius: 6px; margin-bottom: 14px;">
            @endif
            <h1 style="margin: 0; font-family: Georgia, 'Times New Roman', serif; font-size: 26px; font-weight: 500; letter-spacing: 0.5px; color: #F3EDE4;">
                {{ $copy['title'] }}
            </h1>
            <div style="width: 44px; height: 1px; background: #B08D57; margin: 14px auto;"></div>
            <p style="margin: 0; font-size: 12px; letter-spacing: 2px; text-transform: uppercase; color: #C9A96A;">Order #{{ $order->id }}</p>
        </div>

        <!-- Body -->
        <div style="padding: 30px;">

            <!-- Status chip -->
            <div style="text-align: center; margin-bottom: 22px;">
                <span style="display: inline-block; background-color: {{ $copy['accent'] }}; color: #ffffff; font-size: 11px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; padding: 7px 18px; border-radius: 999px;">
                    Status: {{ $copy['label'] }}
                </span>
            </div>

            <p style="font-size: 15px; line-height: 1.6; color: #6B6B6B; margin: 0 0 25px;">
                {{ $copy['line'] }}
            </p>

            <!-- Order meta -->
            <div style="background-color: #FAF7F2; border-radius: 4px; padding: 16px 20px; margin-bottom: 25px; border: 1px solid rgba(176, 141, 87, 0.25); display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; font-size: 13px; color: #6B6B6B;">
                <div><strong style="color: #1C1C1C;">{{ $order->order_number ?? ('Order #' . $order->id) }}</strong></div>
                <div>Placed {{ $order->created_at->format('d M Y') }}</div>
                <div>Payment: {{ strtoupper($order->payment_method) }}</div>
                <div><strong style="color: #9B1B30;">₹{{ number_format($order->total, 2) }}</strong></div>
            </div>

            <!-- Items -->
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

            @if($state === 'shipped')
                <!-- Shipping address — most useful exactly now -->
                <div style="background-color: #FAF7F2; border-radius: 4px; padding: 15px; border: 1px solid rgba(176, 141, 87, 0.25); margin-bottom: 25px;">
                    <h4 style="margin: 0 0 8px 0; color: #6B6B6B; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">Shipping Address</h4>
                    <p style="margin: 0; font-size: 13px; line-height: 1.6; color: #1C1C1C;">
                        <strong>{{ $order->shipping_name }}</strong><br>
                        {{ $order->shipping_address }}<br>
                        {{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}<br>
                        Phone: {{ $order->shipping_phone }}
                    </p>
                </div>
            @endif

            <hr style="border: 0; border-top: 1px solid rgba(176, 141, 87, 0.25); margin: 10px 0 25px;">

            <div style="text-align: center;">
                <a href="{{ url('/order-detail/' . $order->order_number) }}" style="display: inline-block; background-color: #5C1A2E; color: #F3EDE4; padding: 12px 34px; border-radius: 2px; text-decoration: none; font-weight: 600; font-size: 13px; letter-spacing: 0.6px; text-transform: uppercase;">
                    View Order
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
