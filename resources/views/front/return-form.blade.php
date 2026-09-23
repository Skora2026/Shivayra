@extends('front.layouts.app')

@section('title', 'Request a Return')

@section('content')
<section class="py-5">
    <div class="container" style="max-width: 720px;">
        <a href="{{ route('my-account') }}" class="d-inline-block mb-3 text-muted">
            <svg class="icon"><use href="#i-arrow"/></svg> Back to My Account
        </a>

        <div class="card border-0" style="background:#fffdf9; border:1px solid var(--gold-hairline) !important; border-radius:4px;">
            <div class="p-4 p-md-5">
                <h2 class="main-heading mb-1" style="font-size: 1.8rem;">Request a Return</h2>
                <p class="text-muted mb-4">Tell us why this piece isn't right — we'll review it promptly.</p>

                <div class="d-flex align-items-center gap-3 mb-4 p-3" style="background:var(--champagne); border-radius:4px;">
                    <img src="{{ $item->product?->image_url }}" alt="{{ $item->product_name }}"
                        style="width:64px;height:64px;object-fit:cover;border-radius:4px;">
                    <div>
                        <div class="fw-600" style="font-family:var(--font-display); font-size:1.05rem;">{{ $item->product_name }}</div>
                        <small class="text-muted">Order {{ $item->order->order_number }} · Qty {{ $item->qty }} · ₹{{ number_format($item->total, 2) }}</small>
                    </div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger" style="border-radius:4px;">{{ $errors->first() }}</div>
                @endif

                <form action="{{ route('return.store', $item->id) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-600" for="ret-reason">Reason for return <span class="text-danger">*</span></label>
                        <textarea id="ret-reason" name="reason" class="form-control" rows="5" minlength="10" maxlength="1000"
                            placeholder="e.g. The ring size doesn't fit — I'd like to exchange it for a different size.">{{ old('reason') }}</textarea>
                        <div class="text-danger mt-1" style="font-size:.8rem;">@error('reason'){{ $message }}@enderror</div>
                    </div>

                    <div class="p-3 mb-4" style="background:var(--ruby-soft); border-left:3px solid var(--ruby); border-radius:2px; font-size:.85rem;">
                        Once approved, our team will arrange the pickup and process your refund to the original payment method.
                    </div>

                    <button type="submit" class="main-btn"><span>Submit Return Request</span><span class="shine"></span></button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
