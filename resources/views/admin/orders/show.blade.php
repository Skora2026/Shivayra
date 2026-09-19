@extends('admin.layouts.app')

@section('styles')
<style>
    .page-header-card {
        background: linear-gradient(135deg, #40111F 0%, #6E5A42 100%);
        border-radius: 4px;
        padding: 2rem 2.5rem;
        color: #fff;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    .page-header-card::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 180px; height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .page-header-card::after {
        content: '';
        position: absolute;
        bottom: -60px; right: 80px;
        width: 250px; height: 250px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
    .info-card {
        background: #fff;
        border-radius: 1.25rem;
        box-shadow: 0 4px 24px rgba(0,0,0,0.05);
        border: none;
        margin-bottom: 1.5rem;
    }
    .info-card-header {
        background: transparent;
        border-bottom: 1px solid rgba(176, 141, 87, 0.18);
        padding: 1.25rem 1.5rem;
        font-weight: 700;
        color: #40111F;
        font-size: 1.05rem;
    }
    .status-stepper {
        display: flex;
        align-items: center;
        gap: 0;
        margin: .5rem 0 0;
        flex-wrap: wrap;
    }
    .step {
        display: flex;
        align-items: center;
        gap: .45rem;
        font-size: .8rem;
        font-weight: 600;
        color: #B9AFA5;
    }
    .step .dot {
        width: 22px; height: 22px;
        border-radius: 50%;
        border: 2px solid #E3D9CB;
        background: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: .65rem;
        color: transparent;
    }
    .step.done .dot, .step.current .dot {
        border-color: #5C1A2E;
        background: #5C1A2E;
        color: #F3EDE4;
    }
    .step.done, .step.current { color: #40111F; }
    .step-bar { flex: 1 1 26px; height: 2px; background: #E3D9CB; min-width: 18px; }
    .step-bar.done { background: #5C1A2E; }
    @media print {
        #sidebar-wrapper, .main-header, .footer, #sidebar-backdrop, .no-print, .page-header-card .btn { display: none !important; }
        #page-content-wrapper { margin-left: 0 !important; }
        body { background: #fff !important; }
        .info-card { box-shadow: none !important; border: 1px solid #eee !important; }
    }
    .info-card-body {
        padding: 1.5rem;
    }
    .invoice-table th {
        background: #f8f9fa;
        font-weight: 700;
        font-size: 0.85rem;
        color: #495057;
        text-transform: uppercase;
        border-bottom: 2px solid #e9ecef;
    }
    .invoice-table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }
</style>
@endsection

@section('content')
@php
    $steps = ['pending', 'processing', 'completed'];
    $currentStep = array_search($order->order_status, $steps, true);
    $isCancelled = $order->order_status === 'cancelled';
@endphp
<div class="page-header-card">
    <div class="d-flex justify-content-between align-items-center position-relative flex-wrap gap-3" style="z-index:1;">
        <div>
            <h1 class="fw-800 mb-1" style="font-size:1.75rem;">
                <i class="fa-solid fa-file-invoice me-2 opacity-75"></i>
                {{ $order->order_number }}
            </h1>
            <p class="mb-0 opacity-75">Placed {{ $order->created_at->format('d M Y, h:i A') }} · via {{ strtoupper($order->payment_method) }}</p>
            <div class="mt-3">
                @if($isCancelled)
                    <span class="badge bg-danger px-3 py-2">CANCELLED</span>
                @else
                    <div class="status-stepper">
                        @foreach($steps as $i => $s)
                            @if($i > 0)<span class="step-bar {{ $currentStep !== false && $i <= $currentStep ? 'done' : '' }}"></span>@endif
                            <span class="step {{ $currentStep !== false && $i < $currentStep ? 'done' : '' }} {{ $currentStep === $i ? 'current' : '' }}">
                                <span class="dot"><i class="fa-solid fa-check"></i></span>
                                {{ ucfirst($s) }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap no-print">
            <button type="button" onclick="window.print()" class="btn btn-light fw-600 rounded-pill px-4 py-2">
                <i class="fa-solid fa-print me-2"></i>Print Invoice
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-light fw-600 rounded-pill px-4 py-2">
                <i class="fa-solid fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>
</div>

<div class="row">
    {{-- Left: Details --}}
    <div class="col-lg-8">
        {{-- Items Card --}}
        <div class="card info-card">
            <div class="card-header info-card-header">
                <i class="fa-solid fa-box-open me-2 text-success"></i> Ordered Items
            </div>
            <div class="card-body info-card-body p-0">
                <div class="table-responsive">
                    <table class="table invoice-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Product</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end pe-4">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item->product && $item->product->image_url)
                                                <img src="{{ $item->product->image_url }}" width="45" height="45" class="rounded" style="object-fit: cover;">
                                            @endif
                                            <div>
                                                <strong class="text-dark d-block">{{ $item->product_name }}</strong>
                                                <small class="text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">₹{{ number_format($item->price, 2) }}</td>
                                    <td class="text-center">{{ $item->qty }}</td>
                                    <td class="text-end pe-4">₹{{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Shipping and Billing Details --}}
        <div class="row">
            <div class="col-md-6">
                <div class="card info-card">
                    <div class="card-header info-card-header">
                        <i class="fa-solid fa-receipt me-2 text-success"></i> Billing Information
                    </div>
                    <div class="card-body info-card-body">
                        <p class="mb-1"><strong>Name:</strong> {{ $order->name }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $order->email }}</p>
                        <p class="mb-1"><strong>Phone:</strong> {{ $order->phone }}</p>
                        <p class="mb-0"><strong>Address:</strong> {{ $order->address }}, {{ $order->city }}, {{ $order->state }} - {{ $order->pincode }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card info-card">
                    <div class="card-header info-card-header">
                        <i class="fa-solid fa-truck me-2 text-success"></i> Shipping Information
                    </div>
                    <div class="card-body info-card-body">
                        <p class="mb-1"><strong>Name:</strong> {{ $order->shipping_name }}</p>
                        <p class="mb-1"><strong>Email:</strong> {{ $order->shipping_email }}</p>
                        <p class="mb-1"><strong>Phone:</strong> {{ $order->shipping_phone }}</p>
                        <p class="mb-0"><strong>Address:</strong> {{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transaction Details --}}
        <div class="card info-card">
            <div class="card-header info-card-header">
                <i class="fa-solid fa-credit-card me-2 text-success"></i> Transaction Gateway Records
            </div>
            <div class="card-body info-card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-uppercase text-muted fw-600 d-block">Payment Method</small>
                        <strong class="text-dark text-uppercase fs-6">{{ $order->payment_method }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-uppercase text-muted fw-600 d-block">Payment Status</small>
                        @php $pstatus = strtolower($order->payment_status); @endphp
                        @if($pstatus === 'paid')
                            <span class="badge bg-success fs-7">Paid</span>
                        @elseif($pstatus === 'failed')
                            <span class="badge bg-danger fs-7">Failed</span>
                        @else
                            <span class="badge bg-warning text-dark fs-7">Pending</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-uppercase text-muted fw-600 d-block">Razorpay Order ID</small>
                        <code class="text-secondary">{{ $order->razorpay_order_id ?? 'N/A' }}</code>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-uppercase text-muted fw-600 d-block">Razorpay Payment ID</small>
                        <code class="text-secondary">{{ $order->razorpay_payment_id ?? 'N/A' }}</code>
                    </div>
                    <div class="col-12">
                        <small class="text-uppercase text-muted fw-600 d-block">Signature Verification hash</small>
                        <code class="text-muted" style="word-break: break-all;">{{ $order->razorpay_signature ?? 'N/A' }}</code>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Actions and Summary --}}
    <div class="col-lg-4">
        {{-- Status update card --}}
        <div class="card info-card">
            <div class="card-header info-card-header">
                <i class="fa-solid fa-list-check me-2 text-success"></i> Order Actions
            </div>
            <div class="card-body info-card-body">
                @if($order->order_status === 'cancelled')
                    <div class="alert alert-secondary py-3 px-3 mb-3" role="alert">
                        <i class="fa-solid fa-lock me-2"></i><strong>This order is cancelled and final.</strong><br>
                        <span class="font-sm">Cancelled orders cannot be reopened. Create a new order for the customer if needed.</span>
                    </div>
                @else
                <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-600">Order Status</label>
                        <select name="order_status" class="form-select">
                            <option value="pending" {{ $order->order_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ $order->order_status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-600">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="pending" {{ $order->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $order->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="failed" {{ $order->payment_status === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <div class="alert alert-warning py-2 px-3 font-sm mb-3 no-print" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>Cancelling restocks all items automatically and is final.
                    </div>

                    <button type="submit" class="btn btn-custom-primary w-100 fw-600 py-2.5">
                        <i class="fa-solid fa-save me-1"></i> Update Statuses
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Invoice Summary --}}
        <div class="card info-card">
            <div class="card-header info-card-header">
                <i class="fa-solid fa-file-invoice-dollar me-2 text-success"></i> Invoice Summary
            </div>
            <div class="card-body info-card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Subtotal</span>
                    <strong class="text-dark">₹{{ number_format($order->subtotal, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Shipping</span>
                    <strong class="text-dark">₹{{ number_format($order->shipping_charge, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Tax (5%)</span>
                    <strong class="text-dark">₹{{ number_format($order->tax, 2) }}</strong>
                </div>
                @if($order->discount > 0)
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Discount</span>
                        <strong>-₹{{ number_format($order->discount, 2) }}</strong>
                    </div>
                @endif
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-dark fw-700">Total Invoice Amount</span>
                    <h4 class="fw-800 mb-0" style="color:#5C1A2E;">₹{{ number_format($order->total, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
