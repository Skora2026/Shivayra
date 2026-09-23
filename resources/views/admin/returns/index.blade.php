@extends('admin.layouts.app')

@section('title', 'Returns & Refunds')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h2 class="mb-0" style="font-size: 1.5rem;"><i class="fa-solid fa-rotate-left me-2" style="color: var(--warm-peach);"></i> Returns & Refunds</h2>
    <div class="d-flex gap-3 flex-wrap">
        <span class="badge badge-active">Pending: {{ $stats['pending'] }}</span>
        <span class="badge badge-active">Approved: {{ $stats['approved'] }}</span>
        <span class="badge badge-inactive">Rejected: {{ $stats['rejected'] }}</span>
        <span class="badge badge-active">Refunded: {{ $stats['refunded'] }}</span>
    </div>
</div>

{{-- Policy settings --}}
<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card-stats h-100">
            <div class="p-4">
                <h5 class="mb-3" style="font-size:1.05rem;"><i class="fa-solid fa-calendar-days me-2" style="color:var(--warm-peach);"></i> Return window</h5>
                <form action="{{ route('admin.return-window.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="input-group mb-2" style="max-width: 320px;">
                        <input type="number" name="return_window_days" class="form-control" min="0" max="90" style="min-width: 90px;"
                            value="{{ $setting?->return_window_days ?? 7 }}">
                        <span class="input-group-text">days after delivery</span>
                    </div>
                    <small class="text-muted d-block mb-3">Customers can file a return for completed orders within this many days of delivery.</small>
                    <button class="btn btn-custom-primary btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card-stats h-100">
            <div class="p-4">
                <h5 class="mb-3" style="font-size:1.05rem;"><i class="fa-solid fa-box me-2" style="color:var(--warm-peach);"></i> Returnable products</h5>
                <p class="mb-2 text-muted" style="font-size:.9rem;">
                    Set per product under <strong>Catalog → Products → edit</strong> ("Returnable" toggle).
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <span class="badge badge-active">{{ $returnableCount }} returnable</span>
                    <span class="badge badge-inactive">{{ $nonReturnableCount }} non-returnable</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Requests table --}}
<div class="card-stats">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Customer</th>
                    <th>Item</th>
                    <th>Reason</th>
                    <th style="width:110px;">Status</th>
                    <th style="width:120px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $ret)
                    <tr>
                        <td>
                            <div class="fw-600">{{ $ret->request_number }}</div>
                            <small class="text-muted">{{ $ret->order?->order_number }} · {{ $ret->requested_at->format('d M Y') }}</small>
                        </td>
                        <td>{{ $ret->user?->name }}</td>
                        <td style="max-width:170px;">
                            <div class="text-truncate">{{ $ret->orderItem?->product_name }}</div>
                            <small class="text-muted">Qty {{ $ret->orderItem?->qty }} · ₹{{ number_format($ret->orderItem?->total ?? 0, 2) }}</small>
                        </td>
                        <td style="max-width:240px;"><small class="text-muted">{{ Str::limit($ret->reason, 90) }}</small></td>
                        <td>
                            @php
                                $badge = ['pending' => 'badge-active', 'approved' => 'badge-active', 'refunded' => 'badge-active', 'rejected' => 'badge-inactive'][$ret->status];
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($ret->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.returns.show', $ret) }}" class="btn btn-sm btn-outline-secondary">
                                Review
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">No return requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $returns->links() }}</div>
</div>
@endsection
