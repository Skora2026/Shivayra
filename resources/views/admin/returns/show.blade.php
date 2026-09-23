@extends('admin.layouts.app')

@section('title', 'Return Request')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h2 class="mb-0" style="font-size: 1.5rem;">
        <i class="fa-solid fa-rotate-left me-2" style="color: var(--warm-peach);"></i>
        {{ $returnRequest->request_number }}
    </h2>
    <a href="{{ route('admin.returns.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> All returns
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-stats p-4 h-100">
            <h5 class="mb-3" style="font-size:1.05rem;">Request details</h5>
            <dl class="row mb-0">
                <dt class="col-sm-4">Customer</dt>
                <dd class="col-sm-8">{{ $returnRequest->user?->name }} · {{ $returnRequest->user?->email }}</dd>

                <dt class="col-sm-4">Order</dt>
                <dd class="col-sm-8">{{ $returnRequest->order?->order_number }} ({{ $returnRequest->order?->payment_method }}, {{ $returnRequest->order?->payment_status }})</dd>

                <dt class="col-sm-4">Item</dt>
                <dd class="col-sm-8">{{ $returnRequest->orderItem?->product_name }} — Qty {{ $returnRequest->orderItem?->qty }} · ₹{{ number_format($returnRequest->orderItem?->total ?? 0, 2) }}</dd>

                <dt class="col-sm-4">Filed on</dt>
                <dd class="col-sm-8">{{ $returnRequest->requested_at->format('d M Y, h:i A') }}</dd>

                <dt class="col-sm-4">Status</dt>
                <dd class="col-sm-8"><span class="badge badge-active">{{ ucfirst($returnRequest->status) }}</span></dd>

                <dt class="col-sm-4">Customer reason</dt>
                <dd class="col-sm-8" style="white-space: pre-line;">{{ $returnRequest->reason }}</dd>

                @if($returnRequest->admin_note)
                    <dt class="col-sm-4">Your note</dt>
                    <dd class="col-sm-8">{{ $returnRequest->admin_note }}</dd>
                @endif
            </dl>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-stats p-4">
            <h5 class="mb-3" style="font-size:1.05rem;">Decide</h5>

            @if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif

            @if($returnRequest->status === 'refunded')
                <p class="text-muted mb-0">This request is refunded and final.</p>
            @else
                <form action="{{ route('admin.returns.status', $returnRequest) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-600">Decision</label>
                        <select name="status" class="form-select" required>
                            <option value="approved" {{ $returnRequest->status === 'approved' ? 'selected' : '' }}>Approve (restores stock)</option>
                            <option value="rejected" {{ $returnRequest->status === 'rejected' ? 'selected' : '' }}>Reject</option>
                            @if($returnRequest->status === 'approved')
                                <option value="refunded">Mark refunded (completes loop)</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Note to customer (optional)</label>
                        <textarea name="admin_note" class="form-control" rows="3"
                            placeholder="e.g. Pickup scheduled — refund in 3-5 business days.">{{ $returnRequest->admin_note }}</textarea>
                    </div>
                    <button class="btn btn-custom-primary w-100"><i class="fa-solid fa-check me-1"></i> Save decision</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
