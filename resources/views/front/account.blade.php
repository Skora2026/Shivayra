@extends('front.layouts.app')

@section('title')
    My Account
@endsection

@section('content')
<div class="container my-5">
    <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #ffffff;">
        {{-- Welcome Header --}}
        <div class="mb-4">
            <h2 class="fw-800 text-dark mb-1">Hello, {{ Auth::user()->name }}!</h2>
            <p class="text-muted mb-0">Manage your profile and track your orders.</p>
        </div>

        {{-- Session Messages --}}
        @if(session('success'))
            <div class="alert alert-success border-0 rounded-3 mb-4">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Nav Tabs --}}
        <ul class="nav nav-tabs border-bottom mb-4" id="accountTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-600 px-4 py-2 border-0" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-pane" type="button" role="tab" aria-controls="orders-pane" aria-selected="true" style="color: #0A9051;">
                    <i class="fa-solid fa-bag-shopping me-2"></i> My Orders
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-600 px-4 py-2 border-0 text-secondary" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-pane" type="button" role="tab" aria-controls="profile-pane" aria-selected="false">
                    <i class="fa-solid fa-user me-2"></i> Profile Settings
                </button>
            </li>
        </ul>

        {{-- Tab Panes --}}
        <div class="tab-content" id="accountTabsContent">
            {{-- Pane 1: Orders --}}
            <div class="tab-pane fade show active" id="orders-pane" role="tabpanel" aria-labelledby="orders-tab" tabindex="0">
                @php
                    $reviewableItems = collect();
                    $returnWindowDays = \App\Models\Setting::first()?->return_window_days ?? 7;
                    foreach ($orders as $o) {
                        if ($o->order_status === 'completed') {
                            foreach ($o->items as $it) { $reviewableItems->push($it->loadMissing('product')); }
                        }
                    }
                @endphp

                @if($reviewableItems->isNotEmpty())
                    <div class="p-3 mb-4" style="background:#fffdf9; border:1px solid var(--gold-hairline); border-radius:4px;">
                        <h5 class="mb-3" style="font-family:var(--font-display); font-weight:600;">
                            <svg class="icon" style="color:var(--warm-peach);"><use href="#i-star"/></svg>
                            Rate your purchases
                        </h5>
                        <div class="row g-3">
                            @foreach($reviewableItems as $it)
                                @php
                                    $alreadyReviewed = $it->review()->exists();
                                    $hasReturn = (bool) $it->returnRequest()->exists();
                                    $inWindow = ($it->order->updated_at ?? $it->order->created_at)->copy()->addDays($returnWindowDays)->isFuture();
                                    $isReturnable = $it->product?->is_returnable ?? true;
                                @endphp
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center gap-3 p-2" style="border:1px solid rgba(176,141,87,.2); border-radius:4px;">
                                        <img src="{{ $it->product?->image_url }}" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:4px;">
                                        <div class="flex-grow-1" style="min-width:0;">
                                            <div class="text-truncate fw-600" style="font-size:.9rem;">{{ $it->product_name }}</div>
                                            <small class="text-muted">{{ $it->order->order_number }}</small>
                                            <div class="d-flex gap-2 mt-1 flex-wrap">
                                                @if($alreadyReviewed)
                                                    <span class="badge" style="background:rgba(176,141,87,.15); color:#6E5A42; font-weight:600;">
                                                        <svg class="icon"><use href="#i-check"/></svg> Reviewed
                                                    </span>
                                                @else
                                                    <a href="{{ route('review.form', $it->id) }}" class="badge text-decoration-none" style="background:var(--burgundy); color:#F3EDE4; font-weight:600; letter-spacing:.05em;">
                                                        <svg class="icon"><use href="#i-star"/></svg> Write review
                                                    </a>
                                                @endif

                                                @if($hasReturn)
                                                    @php $ret = $it->returnRequest; @endphp
                                                    <span class="badge" style="background:rgba(155,27,48,.1); color:var(--ruby-deep); font-weight:600;">
                                                        Return: {{ $ret->status }}
                                                    </span>
                                                @elseif($isReturnable && $inWindow)
                                                    <a href="{{ route('return.form', $it->id) }}" class="badge text-decoration-none" style="background:transparent; border:1px solid var(--ruby-hairline); color:var(--ruby); font-weight:600;">
                                                        <svg class="icon"><use href="#i-refresh"/></svg> Return item
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($orders->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <img src="/images/empty-state.png" alt="No orders" width="120" class="mb-2" style="opacity: 0.9;">
                        </div>
                        <h4 class="fw-700 text-dark mb-2">You haven't placed any orders yet.</h4>
                        <p class="text-muted mb-4">Start shopping and place your first order!</p>
                        <a href="{{ route('products') }}" class="btn text-white rounded-pill px-4 py-2" style="background-color: #0A9051;">
                            Shop Now
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr class="text-muted" style="font-size: 0.85rem;">
                                    <th>Order Number</th>
                                    <th>Date Placed</th>
                                    <th>Total</th>
                                    <th>Payment Method</th>
                                    <th>Payment Status</th>
                                    <th class="text-center">Order Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr style="font-size: 0.9rem;">
                                        <td class="fw-700 text-dark">{{ $order->order_number }}</td>
                                        <td class="text-secondary">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                        <td class="fw-600 text-dark">₹{{ number_format($order->total, 2) }}</td>
                                        <td class="text-uppercase text-secondary" style="font-size: 0.8rem;">{{ $order->payment_method }}</td>
                                        <td>
                                            @php
                                                $pBadge = 'bg-secondary-subtle text-secondary';
                                                if($order->payment_status === 'paid') $pBadge = 'bg-success-subtle text-success';
                                                elseif($order->payment_status === 'failed') $pBadge = 'bg-danger-subtle text-danger';
                                            @endphp
                                            <span class="badge rounded-pill px-3 py-1.5 {{ $pBadge }} text-uppercase" style="font-size: 0.75rem; font-weight: 600;">
                                                {{ $order->payment_status }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $badgeClass = 'bg-secondary-subtle text-secondary';
                                                if($order->order_status === 'completed') $badgeClass = 'bg-success-subtle text-success';
                                                elseif($order->order_status === 'processing') $badgeClass = 'bg-warning-subtle text-warning';
                                                elseif($order->order_status === 'cancelled') $badgeClass = 'bg-danger-subtle text-danger';
                                            @endphp
                                            <span class="badge rounded-pill px-3 py-1.5 {{ $badgeClass }} text-uppercase" style="font-size: 0.75rem; font-weight: 600;">
                                                {{ $order->order_status }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('order.details', $order->order_number) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-600">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>

            {{-- Pane 2: Profile Settings --}}
            <div class="tab-pane fade" id="profile-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                <form action="{{ route('profile.update') }}" method="POST" class="needs-validation" novalidate style="max-width: 600px;">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-600" for="prof-name">Full Name</label>
                        <input type="text" id="prof-name" name="name" class="form-control rounded-3" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600" for="prof-email">Email Address</label>
                        <input type="email" id="prof-email" class="form-control rounded-3 bg-light" value="{{ $user->email }}" disabled readonly>
                        <small class="text-muted">Email cannot be changed.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600" for="prof-phone">Phone Number</label>
                        <input type="text" id="prof-phone" name="phone" class="form-control rounded-3" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-4">
                    <h5 class="fw-700 mb-3">Change Password</h5>

                    <div class="mb-3">
                        <label class="form-label fw-600" for="prof-pass">Current Password</label>
                        <input type="password" id="prof-pass-current" name="current_password" class="form-control rounded-3" placeholder="Required to change password">
                        @error('current_password')
                            <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600" for="prof-pass">New Password</label>
                        <input type="password" id="prof-pass" name="password" class="form-control rounded-3" placeholder="Leave blank to keep current">
                        @error('password')
                            <div class="text-danger mt-1" style="font-size: 0.8rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600" for="prof-pass-confirm">Confirm New Password</label>
                        <input type="password" id="prof-pass-confirm" name="password_confirmation" class="form-control rounded-3" placeholder="Leave blank to keep current">
                    </div>

                    <button type="submit" class="btn text-white rounded-pill px-4 mt-3" style="background-color: #0A9051;">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .nav-tabs .nav-link.active {
        border-bottom: 3px solid #0A9051 !important;
        background: transparent !important;
    }
    .nav-tabs .nav-link {
        font-size: 0.95rem;
    }
    .table th {
        font-weight: 600;
        letter-spacing: 0.5px;
    }
</style>
@endsection
