@extends('front.layouts.app')

@section('title')
    Order Details - #{{ $order->order_number }}
@endsection

@section('content')
<div class="container my-5">

    {{-- Top Navigation & Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3 no-print">
        <div>
            <a href="{{ route('my-account') }}" class="text-decoration-none text-muted small fw-600 mb-2 d-inline-block">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to My Account
            </a>
            <h2 class="fw-800 text-dark mb-0 font-outfit" style="font-size: 1.75rem; letter-spacing: -0.02em;">
                Order <span class="text-custom-primary">#{{ $order->order_number }}</span>
            </h2>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 py-2 fw-600 shadow-sm d-flex align-items-center gap-2" style="font-size: 0.88rem; transition: all 0.2s;">
                <i class="fa-solid fa-file-arrow-down fs-6"></i> Download / Print Invoice
            </button>
        </div>
    </div>

    {{-- Header Status Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden no-print" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        {{-- Order Number Badge --}}
                        <span class="badge rounded-pill px-3 py-2 fw-600 shadow-sm" style="background-color: #1e293b !important; color: #ffffff !important; font-size: 0.82rem; letter-spacing: 0.03em; border: 1px solid #334155;">
                            #{{ $order->order_number }}
                        </span>
                        
                        {{-- Payment Status Badge --}}
                        @php
                            $pStatus = strtolower($order->payment_status);
                            $pBg = '#15803d'; // Green for Paid
                            $pIcon = 'fa-circle-check';
                            if ($pStatus === 'pending') {
                                $pBg = '#d97706'; // Amber for Pending
                                $pIcon = 'fa-clock';
                            } elseif ($pStatus === 'failed') {
                                $pBg = '#dc2626'; // Red for Failed
                                $pIcon = 'fa-circle-xmark';
                            }
                        @endphp
                        <span class="badge rounded-pill px-3 py-2 fw-600 d-inline-flex align-items-center gap-1.5 shadow-sm" style="background-color: {{ $pBg }} !important; color: #ffffff !important; font-size: 0.82rem;">
                            <i class="fa-solid {{ $pIcon }}"></i> Payment {{ ucfirst($order->payment_status) }}
                        </span>

                        {{-- Order Status Badge --}}
                        @php
                            $oStatus = strtolower($order->order_status);
                            $oBg = '#2563eb'; // Blue for processing
                            $oIcon = 'fa-rotate';
                            if ($oStatus === 'completed' || $oStatus === 'delivered') {
                                $oBg = '#059669'; // Emerald
                                $oIcon = 'fa-circle-check';
                            } elseif ($oStatus === 'cancelled') {
                                $oBg = '#dc2626'; // Red
                                $oIcon = 'fa-circle-xmark';
                            } elseif ($oStatus === 'pending') {
                                $oBg = '#d97706'; // Amber
                                $oIcon = 'fa-clock';
                            }
                        @endphp
                        <span class="badge rounded-pill px-3 py-2 fw-600 d-inline-flex align-items-center gap-1.5 shadow-sm" style="background-color: {{ $oBg }} !important; color: #ffffff !important; font-size: 0.82rem;">
                            <i class="fa-solid {{ $oIcon }}"></i> Order Status: {{ ucfirst($order->order_status) }}
                        </span>
                    </div>
                    <p class="text-muted mb-0 small">
                        <i class="fa-regular fa-calendar-check me-1 text-custom-primary"></i> Placed on {{ $order->created_at->format('d M Y') }} at {{ $order->created_at->format('h:i A') }}
                    </p>
                </div>
                <div class="col-md-5 text-md-end">
                    <div class="d-inline-block text-md-end">
                        <span class="text-muted d-block small uppercase fw-600 letter-spacing-1">Total Amount</span>
                        <h3 class="fw-800 text-dark mb-0 font-outfit" style="color: #0A9051 !important;">
                            ₹{{ number_format($order->total, 2) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Order Shipment Status Timeline --}}
    @php
        $status = strtolower($order->order_status);
        $isCancelled = ($status === 'cancelled');
        
        $step1 = true;
        $step2 = in_array($status, ['processing', 'shipped', 'completed', 'delivered']);
        $step3 = in_array($status, ['shipped', 'completed', 'delivered']);
        $step4 = in_array($status, ['completed', 'delivered']);

        $progressWidth = '0%';
        if ($step4) $progressWidth = '100%';
        elseif ($step3) $progressWidth = '66.6%';
        elseif ($step2) $progressWidth = '33.3%';
    @endphp

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 no-print">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-700 text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 1.1rem;">
                <i class="fa-solid fa-truck-ramp-box text-custom-primary fs-5"></i> Shipment Status
            </h5>
            @if($isCancelled)
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-600">
                    <i class="fa-solid fa-circle-exclamation me-1"></i> Order Cancelled
                </span>
            @endif
        </div>

        @if($isCancelled)
            <div class="alert alert-danger border-0 rounded-3 p-3 mb-0 d-flex align-items-center gap-3" style="background-color: #fef2f2; color: #991b1b;">
                <i class="fa-solid fa-triangle-exclamation fs-4 text-danger"></i>
                <div>
                    <strong class="d-block mb-1">This order has been cancelled</strong>
                    <span class="small">If you have any questions or were charged for this transaction, please contact our support team.</span>
                </div>
            </div>
        @else
            <div class="timeline-wrapper py-3">
                <div class="timeline-track">
                    <div class="timeline-bar" style="width: {{ $progressWidth }};"></div>
                </div>

                <div class="row text-center position-relative g-0">
                    <div class="col-3 timeline-item">
                        <div class="timeline-icon {{ $step1 ? 'active' : '' }}">
                            <i class="fa-solid fa-clipboard-check"></i>
                        </div>
                        <div class="timeline-content mt-3">
                            <strong class="d-block text-dark fw-700" style="font-size: 0.88rem;">Placed</strong>
                            <small class="text-muted d-block" style="font-size: 0.76rem;">Order Confirmed</small>
                            <small class="text-success fw-600 d-block mt-1" style="font-size: 0.72rem;">{{ $order->created_at->format('d M, h:i A') }}</small>
                        </div>
                    </div>

                    <div class="col-3 timeline-item">
                        <div class="timeline-icon {{ $step2 ? 'active' : '' }}">
                            <i class="fa-solid fa-boxes-packing"></i>
                        </div>
                        <div class="timeline-content mt-3">
                            <strong class="d-block text-dark fw-700" style="font-size: 0.88rem;">Processing</strong>
                            <small class="text-muted d-block" style="font-size: 0.76rem;">Packing Items</small>
                        </div>
                    </div>

                    <div class="col-3 timeline-item">
                        <div class="timeline-icon {{ $step3 ? 'active' : '' }}">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <div class="timeline-content mt-3">
                            <strong class="d-block text-dark fw-700" style="font-size: 0.88rem;">Shipped</strong>
                            <small class="text-muted d-block" style="font-size: 0.76rem;">In Transit</small>
                        </div>
                    </div>

                    <div class="col-3 timeline-item">
                        <div class="timeline-icon {{ $step4 ? 'active' : '' }}">
                            <i class="fa-solid fa-house-circle-check"></i>
                        </div>
                        <div class="timeline-content mt-3">
                            <strong class="d-block text-dark fw-700" style="font-size: 0.88rem;">Delivered</strong>
                            <small class="text-muted d-block" style="font-size: 0.76rem;">Order Completed</small>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Screen Main Content Grid --}}
    <div class="row g-4 no-print">
        {{-- Left Column: Items & Addresses --}}
        <div class="col-lg-8">
            {{-- Ordered Items Card --}}
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-700 text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 1.1rem;">
                        <i class="fa-solid fa-bag-shopping text-custom-primary"></i> Order Items ({{ $order->items->count() }})
                    </h5>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle custom-order-table mb-0">
                        <thead>
                            <tr class="text-uppercase text-secondary border-bottom" style="font-size: 0.78rem; letter-spacing: 0.05em;">
                                <th style="min-width: 220px;">Product</th>
                                <th class="text-center" style="width: 100px;">Price</th>
                                <th class="text-center" style="width: 80px;">Qty</th>
                                <th class="text-end" style="width: 120px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item->product && !empty($item->product->image_url))
                                                <img src="{{ $item->product->image_url }}" 
                                                     alt="{{ $item->product_name }}" 
                                                     class="rounded-3 border shadow-sm flex-shrink-0" 
                                                     style="width: 56px; height: 56px; object-fit: cover;">
                                            @else
                                                <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center flex-shrink-0" 
                                                     style="width: 56px; height: 56px;">
                                                    <i class="fa-solid fa-image text-muted fs-5"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <h6 class="fw-700 text-dark mb-1" style="font-size: 0.92rem; line-height: 1.3;">
                                                    {{ $item->product_name }}
                                                </h6>
                                                <small class="text-muted d-block" style="font-size: 0.78rem;">
                                                    Product ID: #{{ $item->product_id }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-dark fw-600 py-3" style="font-size: 0.9rem;">
                                        ₹{{ number_format($item->price, 2) }}
                                    </td>
                                    <td class="text-center py-3">
                                        <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-600" style="font-size: 0.85rem;">
                                            x{{ $item->qty }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-700 text-dark py-3" style="font-size: 0.95rem;">
                                        ₹{{ number_format($item->total, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Addresses Grid --}}
            <div class="row g-4">
                {{-- Billing Address --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative overflow-hidden">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="p-2 rounded-3 bg-success-subtle text-success">
                                <i class="fa-solid fa-receipt fs-5"></i>
                            </div>
                            <h5 class="fw-700 text-dark mb-0" style="font-size: 1.05rem;">Billing Address</h5>
                        </div>
                        
                        <div class="address-details text-secondary" style="font-size: 0.88rem; line-height: 1.7;">
                            <div class="fw-700 text-dark fs-6 mb-1">{{ $order->name }}</div>
                            <div class="mb-1"><i class="fa-regular fa-envelope me-2 text-muted"></i>{{ $order->email }}</div>
                            <div class="mb-1"><i class="fa-solid fa-phone me-2 text-muted"></i>{{ $order->phone }}</div>
                            <div class="mt-2 pt-2 border-top">
                                <i class="fa-solid fa-location-dot me-2 text-muted"></i>
                                {{ $order->address }}, {{ $order->city }}, {{ $order->state }} - <strong>{{ $order->pincode }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Shipping Address --}}
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 position-relative overflow-hidden">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="p-2 rounded-3 bg-primary-subtle text-primary">
                                <i class="fa-solid fa-truck-arrow-right fs-5"></i>
                            </div>
                            <h5 class="fw-700 text-dark mb-0" style="font-size: 1.05rem;">Shipping Address</h5>
                        </div>

                        <div class="address-details text-secondary" style="font-size: 0.88rem; line-height: 1.7;">
                            <div class="fw-700 text-dark fs-6 mb-1">{{ $order->shipping_name ?? $order->name }}</div>
                            <div class="mb-1"><i class="fa-regular fa-envelope me-2 text-muted"></i>{{ $order->shipping_email ?? $order->email }}</div>
                            <div class="mb-1"><i class="fa-solid fa-phone me-2 text-muted"></i>{{ $order->shipping_phone ?? $order->phone }}</div>
                            <div class="mt-2 pt-2 border-top">
                                <i class="fa-solid fa-location-dot me-2 text-muted"></i>
                                {{ $order->shipping_address ?? $order->address }}, {{ $order->shipping_city ?? $order->city }}, {{ $order->shipping_state ?? $order->state }} - <strong>{{ $order->shipping_pincode ?? $order->pincode }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Payment & Order Summary --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 90px; background: #ffffff;">
                <h5 class="fw-700 text-dark mb-4 d-flex align-items-center gap-2" style="font-size: 1.1rem;">
                    <i class="fa-solid fa-file-invoice-dollar text-custom-primary"></i> Order Summary
                </h5>

                <div class="d-flex justify-content-between mb-2 text-secondary" style="font-size: 0.9rem;">
                    <span>Items Subtotal:</span>
                    <span class="fw-600 text-dark">₹{{ number_format($order->subtotal, 2) }}</span>
                </div>

                <div class="d-flex justify-content-between mb-2 text-secondary" style="font-size: 0.9rem;">
                    <span>Delivery Fee:</span>
                    @if($order->shipping_charge > 0)
                        <span class="fw-600 text-dark">₹{{ number_format($order->shipping_charge, 2) }}</span>
                    @else
                        <span class="badge bg-success-subtle text-success fw-700">FREE</span>
                    @endif
                </div>

                <div class="d-flex justify-content-between mb-3 text-secondary" style="font-size: 0.9rem;">
                    <span>Tax (GST):</span>
                    <span class="fw-600 text-dark">₹{{ number_format($order->tax, 2) }}</span>
                </div>

                @if($order->discount > 0)
                    <div class="d-flex justify-content-between mb-3 text-success" style="font-size: 0.9rem;">
                        <span>Discount Applied:</span>
                        <span class="fw-700">-₹{{ number_format($order->discount, 2) }}</span>
                    </div>
                @endif

                <hr class="my-3 border-secondary-subtle">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="fw-700 text-dark d-block" style="font-size: 1.05rem;">Grand Total</span>
                        <small class="text-muted" style="font-size: 0.75rem;">Includes all taxes & fees</small>
                    </div>
                    <span class="fw-800 text-success font-outfit" style="font-size: 1.45rem;">
                        ₹{{ number_format($order->total, 2) }}
                    </span>
                </div>

                {{-- Payment Information Container --}}
                <div class="p-3 rounded-4 border bg-light">
                    <h6 class="fw-700 text-dark mb-3 d-flex align-items-center gap-2" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-credit-card text-muted"></i> Payment Details
                    </h6>

                    <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.85rem;">
                        <span class="text-muted">Payment Method:</span>
                        <span class="fw-700 text-uppercase text-dark badge bg-white border text-dark px-2 py-1">
                            @if(strtolower($order->payment_method) === 'cod')
                                <svg class="icon"><use href="#i-cash"/></svg> Cash on Delivery
                            @else
                                <svg class="icon"><use href="#i-card"/></svg> Online (Razorpay)
                            @endif
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2" style="font-size: 0.85rem;">
                        <span class="text-muted">Payment Status:</span>
                        @php
                            $pStatus = strtolower($order->payment_status);
                            $badgeBg = '#fef3c7'; // Light amber
                            $badgeFg = '#92400e';
                            if ($pStatus === 'paid') {
                                $badgeBg = '#dcfce7'; // Light green
                                $badgeFg = '#15803d';
                            } elseif ($pStatus === 'failed') {
                                $badgeBg = '#fee2e2'; // Light red
                                $badgeFg = '#b91c1c';
                            }
                        @endphp
                        <span class="badge px-2.5 py-1.5 rounded-pill fw-700 text-uppercase" style="background-color: {{ $badgeBg }} !important; color: {{ $badgeFg }} !important; border: 1px solid {{ $badgeFg }}33; font-size: 0.78rem;">
                            {{ $order->payment_status }}
                        </span>
                    </div>

                    @if($order->razorpay_payment_id)
                        <div class="mt-3 pt-2 border-top border-secondary-subtle" style="font-size: 0.8rem;">
                            <span class="text-muted d-block mb-1">Transaction Ref:</span>
                            <code class="user-select-all bg-white px-2 py-1 rounded border text-dark d-inline-block font-monospace" style="font-size: 0.78rem;">
                                {{ $order->razorpay_payment_id }}
                            </code>
                        </div>
                    @endif
                </div>

                <div class="text-center mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-outline-dark w-100 rounded-pill py-2 fw-600 d-flex align-items-center justify-content-center gap-2" style="font-size: 0.88rem;">
                        <i class="fa-solid fa-print"></i> Download / Print Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DEDICATED PRINTABLE INVOICE CONTAINER (Only visible when printing / window.print()) --}}
<div id="printableInvoiceSection">
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
@endsection

@section('styles')
<style>
    /* Custom Styling for Order Detail Screen */
    .font-outfit {
        font-family: 'Outfit', 'Instrument Sans', sans-serif;
    }

    /* Timeline Stepper Styling */
    .timeline-wrapper {
        position: relative;
        padding: 10px 0;
    }
    .timeline-track {
        position: absolute;
        top: 26px;
        left: 12.5%;
        right: 12.5%;
        height: 4px;
        background: #e2e8f0;
        z-index: 1;
        border-radius: 4px;
    }
    .timeline-bar {
        position: absolute;
        height: 100%;
        background: linear-gradient(90deg, #0A9051 0%, #10b981 100%);
        border-radius: 4px;
        transition: width 0.4s ease-in-out;
    }
    .timeline-item {
        position: relative;
        z-index: 2;
    }
    .timeline-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #ffffff;
        border: 3px solid #cbd5e1;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .timeline-icon.active {
        background: #0A9051;
        border-color: #0A9051;
        color: #ffffff;
        box-shadow: 0 0 0 5px rgba(10, 144, 81, 0.2);
    }

    /* Custom Order Table */
    .custom-order-table tbody tr:last-child td {
        border-bottom: 0;
    }
    .custom-order-table tbody tr:hover {
        background-color: #f8fafc;
    }

    /* Hidden on screen by default */
    #printableInvoiceSection {
        display: none !important;
    }

    /* Strict Media Print Formatting (PDF / Window.print) */
    @media print {
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        /* Explicitly hide top ticker, navbar, search bar, footer, floating elements without touching layout files */
        .nav-top-section, .nav-top, .marquee, #mainNavbar, .navbar, .search-form, .search-container,
        #mobileSearch, .search-wrapper, #mobileOffcanvas, .offcanvas-mobile, #offcanvasOverlay,
        .offcanvas-overlay, #cartPanel, .cart-panel, .footer-section, footer, header, nav,
        .no-print, .cart-fab, .badge-count, #toast, #wishToast, .btn, button, .alert {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            max-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
            opacity: 0 !important;
        }

        html, body {
            background: #ffffff !important;
            color: #000000 !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            font-family: Arial, Helvetica, sans-serif !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        main, #app, .container, .container-fluid {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            box-shadow: none !important;
            border: none !important;
        }

        /* Hide screen cards & timeline on print */
        .card, .timeline-wrapper {
            display: none !important;
        }

        /* Show ONLY printable invoice section */
        #printableInvoiceSection {
            display: block !important;
            visibility: visible !important;
            position: relative !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #ffffff !important;
            color: #000000 !important;
        }

        #printableInvoiceSection * {
            visibility: visible !important;
        }
    }
</style>
@endsection
