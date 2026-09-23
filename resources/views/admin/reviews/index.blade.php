@extends('admin.layouts.app')

@section('title', 'Customer Reviews')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h2 class="mb-0" style="font-size: 1.5rem;"><i class="fa-solid fa-star me-2" style="color: var(--warm-peach);"></i> Customer Reviews</h2>
    <div class="d-flex gap-3">
        <span class="badge badge-active">Total: {{ $stats['total'] }}</span>
        <span class="badge badge-inactive">Hidden: {{ $stats['pending'] }}</span>
        <span class="badge badge-active">Avg: ★ {{ $stats['avg'] }}</span>
    </div>
</div>

<div class="card-stats p-3 mb-4">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-auto">
            <select name="rating" class="form-select form-select-sm">
                <option value="">All ratings</option>
                @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                @endfor
            </select>
        </div>
        <div class="col-auto">
            <select name="status" class="form-select form-select-sm">
                <option value="">All statuses</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Visible</option>
                <option value="hidden" {{ request('status') === 'hidden' ? 'selected' : '' }}>Hidden</option>
            </select>
        </div>
        <div class="col-auto">
            <button class="btn btn-sm btn-outline-secondary">Filter</button>
        </div>
    </form>
</div>

<div class="card-stats">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Customer</th>
                    <th style="width:110px;">Rating</th>
                    <th>Review</th>
                    <th style="width:110px;">Status</th>
                    <th style="width:210px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr>
                        <td style="max-width:180px;">
                            <div class="fw-600 text-truncate">{{ $review->product?->name }}</div>
                            <small class="text-muted">{{ $review->order?->order_number }}</small>
                        </td>
                        <td>{{ $review->user?->name }}</td>
                        <td style="color: var(--warm-peach); letter-spacing:1px;">
                            @for($i=1; $i<=5; $i++) {{ $i <= $review->rating ? '★' : '☆' }} @endfor
                        </td>
                        <td style="max-width:320px;">
                            @if($review->title)<div class="fw-600">{{ $review->title }}</div>@endif
                            <div class="text-muted" style="font-size:.85rem;">{{ Str::limit($review->body, 120) }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $review->is_approved ? 'badge-active' : 'badge-inactive' }}">
                                {{ $review->is_approved ? 'Visible' : 'Hidden' }}
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('admin.reviews.approval', $review) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="is_approved" value="{{ $review->is_approved ? 0 : 1 }}">
                                <button class="btn btn-sm {{ $review->is_approved ? 'btn-outline-secondary' : 'btn-custom-primary' }}">
                                    {{ $review->is_approved ? 'Hide' : 'Approve' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('Delete this review permanently?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">No reviews yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $reviews->links() }}</div>
</div>
@endsection
