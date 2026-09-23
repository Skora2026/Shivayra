@extends('front.layouts.app')

@section('title', 'Write a Review')

@section('content')
<section class="py-5">
    <div class="container" style="max-width: 720px;">
        <a href="{{ route('my-account') }}" class="d-inline-block mb-3 text-muted">
            <svg class="icon"><use href="#i-arrow"/></svg> Back to My Account
        </a>

        <div class="card border-0" style="background:#fffdf9; border:1px solid var(--gold-hairline) !important; border-radius:4px;">
            <div class="p-4 p-md-5">
                <h2 class="main-heading mb-1" style="font-size: 1.8rem;">Write a Review</h2>
                <p class="text-muted mb-4">Share your experience with this piece — verified purchase.</p>

                <div class="d-flex align-items-center gap-3 mb-4 p-3" style="background:var(--champagne); border-radius:4px;">
                    <img src="{{ $item->product?->image_url }}" alt="{{ $item->product_name }}"
                        style="width:64px;height:64px;object-fit:cover;border-radius:4px;">
                    <div>
                        <div class="fw-600" style="font-family:var(--font-display); font-size:1.05rem;">{{ $item->product_name }}</div>
                        <small class="text-muted">Order {{ $item->order->order_number }}</small>
                    </div>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger" style="border-radius:4px;">{{ $errors->first() }}</div>
                @endif

                <form action="{{ route('review.store', $item->id) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-600 d-block mb-2">Your rating <span class="text-danger">*</span></label>
                        <div id="starPicker" class="d-flex gap-2" style="font-size: 2rem; cursor: pointer;">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="star-btn" data-val="{{ $i }}" width="34" height="34"
                                    style="color: {{ old('rating', 0) >= $i ? 'var(--warm-peach)' : '#ddd6c8' }};">
                                    <use href="#i-star"/>
                                </svg>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="ratingInput" value="{{ old('rating', 0) }}">
                        <div class="text-danger mt-1" style="font-size:.8rem;">@error('rating'){{ $message }}@enderror</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600" for="rev-title">Headline (optional)</label>
                        <input type="text" id="rev-title" name="title" class="form-control" maxlength="120"
                            value="{{ old('title') }}" placeholder="e.g. Exceeded expectations">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-600" for="rev-body">Your review <span class="text-danger">*</span></label>
                        <textarea id="rev-body" name="body" class="form-control" rows="5" minlength="10" maxlength="2000"
                            placeholder="What did you like about the craftsmanship, finish, or delivery?">{{ old('body') }}</textarea>
                        <div class="text-danger mt-1" style="font-size:.8rem;">@error('body'){{ $message }}@enderror</div>
                    </div>

                    <button type="submit" class="main-btn"><span>Publish Review</span><span class="shine"></span></button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const picker = document.getElementById('starPicker');
    const input = document.getElementById('ratingInput');
    const stars = picker.querySelectorAll('.star-btn');

    function paint(n) {
        stars.forEach(s => {
            s.style.color = parseInt(s.dataset.val, 10) <= n ? 'var(--warm-peach)' : '#ddd6c8';
        });
    }

    stars.forEach(s => {
        s.addEventListener('click', () => {
            input.value = s.dataset.val;
            paint(parseInt(s.dataset.val, 10));
        });
        s.addEventListener('mouseenter', () => paint(parseInt(s.dataset.val, 10)));
    });

    picker.addEventListener('mouseleave', () => paint(parseInt(input.value, 10)));
});
</script>
@endpush
