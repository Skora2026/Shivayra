<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }} - {{ $settings?->site_name ?? config('app.name', 'Shivayra') }}</title>

    {{-- Deliberately standalone: this page is reached from the dashboard and must
         carry no site chrome (nav, footer, cart) so printing yields only the
         invoice. All styling is inlined so it renders before/without the bundle. --}}
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 28px 18px 48px;
            background: #eef1f5;
            color: #1e293b;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .invoice-shell {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
        }

        .invoice-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .invoice-toolbar__note {
            font-size: 12px;
            color: #64748b;
        }

        .invoice-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .invoice-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: 0;
            border-radius: 999px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .invoice-btn--primary {
            background: #0A9051;
            color: #ffffff;
        }

        .invoice-btn--primary:hover {
            background: #087a45;
        }

        .invoice-btn--ghost {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        .invoice-btn--ghost:hover {
            background: #f1f5f9;
        }

        .invoice-sheet {
            background: #ffffff;
            padding: 34px 32px 28px;
            border-radius: 4px;
            box-shadow: 0 10px 40px rgba(15, 23, 42, 0.12);
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 12mm;
            }

            body {
                background: #ffffff;
                padding: 0;
            }

            /* The toolbar is the only non-invoice element on this page */
            .invoice-toolbar {
                display: none !important;
            }

            .invoice-sheet {
                padding: 0;
                border-radius: 0;
                box-shadow: none;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>

<body>

    <div class="invoice-shell">

        {{-- Print controls. Hidden on paper so only the invoice is produced. --}}
        <div class="invoice-toolbar">
            <span class="invoice-toolbar__note">
                Invoice #{{ $order->order_number }} &mdash; choose
                <strong>&ldquo;Save as PDF&rdquo;</strong> in the print dialog to keep a copy.
            </span>
            <span class="invoice-actions">
                <a class="invoice-btn invoice-btn--ghost"
                   href="{{ route('order.details', $order->order_number) }}">&larr; Back to Order</a>
                <button type="button" class="invoice-btn invoice-btn--primary" onclick="window.print()">
                    Print / Save as PDF
                </button>
            </span>
        </div>

        <div class="invoice-sheet">
            {{-- Header Section --}}
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border-bottom: 2px solid #1e293b; padding-bottom: 12px;">
                <tr>
                    <td style="vertical-align: top; width: 60%;">
                        @if($settings && $settings->logo)
                            <img src="{{ asset('storage/' . $settings->logo) }}" alt="{{ config('app.name', 'Shivayra') }}" style="max-height: 50px; margin-bottom: 8px;">
                        @else
                            <h1 style="font-size: 28px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.5px;">{{ config('app.name', 'SHIVAYRA') }}</h1>
                        @endif
                        <div style="font-size: 11px; color: #475569; margin-top: 4px; line-height: 1.4;">
                            <strong>{{ $settings->site_name ?? config('app.name', 'Shivayra') }}</strong><br>
                            {{ $settings->address ?? 'Main Road, India' }}<br>
                            Email: {{ $settings->email ?? 'support@shivayra.in' }} | Phone: {{ $settings->phone ?? 'N/A' }}
                        </div>
                    </td>
                    <td style="vertical-align: top; text-align: right; width: 40%;">
                        <div style="font-size: 22px; font-weight: 900; color: #0A9051; letter-spacing: 1px;">TAX INVOICE</div>
                        <div style="font-size: 13px; font-weight: 700; color: #1e293b; margin-top: 4px;">Invoice No: <span style="color: #0f172a;">#{{ $order->order_number }}</span></div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Date: {{ $order->created_at->format('d M Y, h:i A') }}</div>
                        <div style="margin-top: 8px;">
                            <span style="display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 800; border-radius: 4px; text-transform: uppercase; background-color: {{ strtolower($order->payment_status) === 'paid' ? '#dcfce7' : '#fef3c7' }}; color: {{ strtolower($order->payment_status) === 'paid' ? '#15803d' : '#92400e' }}; border: 1px solid {{ strtolower($order->payment_status) === 'paid' ? '#86efac' : '#fde68a' }};">
                                PAYMENT {{ strtoupper($order->payment_status) }}
                            </span>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Billing & Shipping Details Grid --}}
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                <tr>
                    <td style="width: 49%; vertical-align: top; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #f8fafc;">
                        <div style="font-size: 11px; font-weight: 800; color: #0A9051; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">Billed To (Customer)</div>
                        <div style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $order->name }}</div>
                        <div style="font-size: 11px; color: #334155; margin-top: 4px; line-height: 1.5;">
                            <strong>Email:</strong> {{ $order->email }}<br>
                            <strong>Phone:</strong> {{ $order->phone }}<br>
                            <strong>Address:</strong> {{ $order->address }}, {{ $order->city }}, {{ $order->state }} - <strong>{{ $order->pincode }}</strong>
                        </div>
                    </td>
                    <td style="width: 2%;"></td>
                    <td style="width: 49%; vertical-align: top; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; background-color: #f8fafc;">
                        <div style="font-size: 11px; font-weight: 800; color: #0A9051; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">Shipped To (Destination)</div>
                        <div style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $order->shipping_name ?? $order->name }}</div>
                        <div style="font-size: 11px; color: #334155; margin-top: 4px; line-height: 1.5;">
                            <strong>Email:</strong> {{ $order->shipping_email ?? $order->email }}<br>
                            <strong>Phone:</strong> {{ $order->shipping_phone ?? $order->phone }}<br>
                            <strong>Address:</strong> {{ $order->shipping_address ?? $order->address }}, {{ $order->shipping_city ?? $order->city }}, {{ $order->shipping_state ?? $order->state }} - <strong>{{ $order->shipping_pincode ?? $order->pincode }}</strong>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Order Metadata Bar --}}
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid #cbd5e1; background-color: #ffffff;">
                <tr style="background-color: #f1f5f9; text-transform: uppercase; font-size: 10px; font-weight: 800; color: #475569; letter-spacing: 0.5px;">
                    <td style="padding: 8px 12px; border-right: 1px solid #cbd5e1; width: 33%;">Payment Method</td>
                    <td style="padding: 8px 12px; border-right: 1px solid #cbd5e1; width: 33%;">Order Status</td>
                    <td style="padding: 8px 12px; width: 34%;">Transaction Reference</td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; font-size: 12px; font-weight: 700; color: #0f172a; border-right: 1px solid #cbd5e1;">
                        {{ strtoupper($order->payment_method) === 'COD' ? 'Cash on Delivery (COD)' : 'Online (Razorpay)' }}
                    </td>
                    <td style="padding: 8px 12px; font-size: 12px; font-weight: 700; color: #0f172a; border-right: 1px solid #cbd5e1;">
                        {{ strtoupper($order->order_status) }}
                    </td>
                    <td style="padding: 8px 12px; font-size: 11px; font-family: monospace; color: #0f172a;">
                        {{ $order->razorpay_payment_id ?? 'N/A' }}
                    </td>
                </tr>
            </table>

            {{-- Itemized Products Table --}}
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid #cbd5e1;">
                <thead>
                    <tr style="background-color: #1e293b; color: #ffffff; text-transform: uppercase; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">
                        <th style="padding: 10px 12px; text-align: center; width: 6%; border-right: 1px solid #334155;">#</th>
                        <th style="padding: 10px 12px; text-align: left; width: 54%; border-right: 1px solid #334155;">Item Description</th>
                        <th style="padding: 10px 12px; text-align: center; width: 10%; border-right: 1px solid #334155;">Qty</th>
                        <th style="padding: 10px 12px; text-align: right; width: 15%; border-right: 1px solid #334155;">Unit Price</th>
                        <th style="padding: 10px 12px; text-align: right; width: 15%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $index => $item)
                        <tr style="border-bottom: 1px solid #e2e8f0; font-size: 12px;">
                            <td style="padding: 10px 12px; text-align: center; color: #64748b; border-right: 1px solid #e2e8f0;">{{ $index + 1 }}</td>
                            <td style="padding: 10px 12px; font-weight: 600; color: #0f172a; border-right: 1px solid #e2e8f0;">
                                {{ $item->product_name }}
                                <div style="font-size: 10px; color: #64748b; font-weight: 400; margin-top: 2px;">Product Code: #{{ $item->product_id }}</div>
                            </td>
                            <td style="padding: 10px 12px; text-align: center; font-weight: 700; color: #0f172a; border-right: 1px solid #e2e8f0;">{{ $item->qty }}</td>
                            <td style="padding: 10px 12px; text-align: right; color: #334155; border-right: 1px solid #e2e8f0;">₹{{ number_format($item->price, 2) }}</td>
                            <td style="padding: 10px 12px; text-align: right; font-weight: 700; color: #0f172a;">₹{{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Financial Totals & Terms --}}
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 24px; page-break-inside: avoid;">
                <tr>
                    <td style="width: 52%; vertical-align: top; padding-right: 16px;">
                        <div style="border: 1px solid #cbd5e1; padding: 12px; border-radius: 6px; background-color: #f8fafc;">
                            <div style="font-size: 11px; font-weight: 800; color: #0f172a; margin-bottom: 6px; text-transform: uppercase;">Declarations & Notes:</div>
                            <ul style="margin: 0; padding-left: 14px; font-size: 11px; color: #475569; line-height: 1.6;">
                                <li>This is a computer-generated Tax Invoice and requires no signature.</li>
                                <li>Goods once sold can be returned per our standard return policy.</li>
                                <li>For support, email: {{ $settings->email ?? 'support@shivayra.in' }}</li>
                            </ul>
                        </div>
                    </td>
                    <td style="width: 48%; vertical-align: top;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px; border: 1px solid #cbd5e1;">
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 8px 12px; color: #475569;">Items Subtotal:</td>
                                <td style="padding: 8px 12px; text-align: right; font-weight: 600; color: #0f172a;">₹{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 8px 12px; color: #475569;">Delivery / Shipping Fee:</td>
                                <td style="padding: 8px 12px; text-align: right; font-weight: 600; color: #0f172a;">
                                    {{ $order->shipping_charge > 0 ? '₹' . number_format($order->shipping_charge, 2) : 'FREE' }}
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 8px 12px; color: #475569;">GST Tax:</td>
                                <td style="padding: 8px 12px; text-align: right; font-weight: 600; color: #0f172a;">₹{{ number_format($order->tax, 2) }}</td>
                            </tr>
                            @if($order->discount > 0)
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 8px 12px; color: #15803d;">Discount:</td>
                                    <td style="padding: 8px 12px; text-align: right; font-weight: 700; color: #15803d;">-₹{{ number_format($order->discount, 2) }}</td>
                                </tr>
                            @endif
                            <tr style="background-color: #f1f5f9; border-top: 2px solid #0f172a;">
                                <td style="padding: 10px 12px; font-size: 13px; font-weight: 800; color: #0f172a;">Grand Total Paid:</td>
                                <td style="padding: 10px 12px; text-align: right; font-size: 16px; font-weight: 800; color: #0A9051;">₹{{ number_format($order->total, 2) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- Invoice Footer --}}
            <div style="text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 12px;">
                Thank you for shopping with <strong>{{ config('app.name', 'Shivayra') }}</strong>! Visit us at {{ url('/') }}
            </div>
        </div>
    </div>

    <script>
        // Open the print dialog on arrival so the "Download Invoice" button behaves
        // like a download: the customer only has to confirm "Save as PDF".
        // Remove this listener if you'd rather they open the dialog themselves.
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 250);
        });
    </script>

</body>

</html>
