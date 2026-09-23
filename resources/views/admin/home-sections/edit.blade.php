@extends('admin.layouts.app')

@section('title', 'Home Sections')

@section('styles')
<style>
    .section-card {
        background: #fff;
        border: 1px solid var(--gold-hairline);
        border-radius: 4px;
        overflow: hidden;
    }
    .section-card-head {
        background: linear-gradient(135deg, #40111F 0%, #5C1A2E 100%);
        color: #fff;
        padding: 1.1rem 1.5rem;
    }
    .section-card-head h5 {
        margin: 0;
        color: #fff;
    }
    .prod-tile {
        border: 1px solid var(--gold-hairline);
        border-radius: 4px;
        padding: 0.8rem;
        text-align: center;
        background: #fffdf9;
        transition: all .25s ease;
        height: 100%;
    }
    .prod-tile img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border-radius: 4px;
        margin-bottom: .5rem;
        background: var(--champagne);
    }
    .prod-tile .name {
        font-family: var(--font-display);
        font-size: .95rem;
        font-weight: 600;
        margin-bottom: .6rem;
        line-height: 1.3;
        min-height: 2.5em;
    }
    .prod-tile.on {
        border-color: var(--warm-peach);
        box-shadow: 0 4px 14px rgba(176, 141, 87, .18);
    }
    .section-toggle {
        width: 100%;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .06em;
        text-transform: uppercase;
        border-radius: 2px;
    }
</style>
@endsection

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h2 class="mb-0" style="font-size: 1.5rem;"><i class="fa-solid fa-house-flag me-2" style="color: var(--warm-peach);"></i> Home Sections</h2>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- Limits form --}}
<div class="section-card mb-4">
    <div class="section-card-head d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5><i class="fa-solid fa-sliders me-2"></i> Cards per section</h5>
        <span class="badge" style="background: rgba(255,255,255,.15); font-weight: 500;">
            {{ $featuredCount }} featured · {{ $trendingCount }} trending currently selected
        </span>
    </div>
    <div class="p-4">
        <form action="{{ route('admin.home-sections.update') }}" method="POST" class="row g-3 align-items-end">
            @csrf
            @method('PUT')
            <div class="col-md-4">
                <label class="form-label fw-600">Featured section shows</label>
                <div class="input-group">
                    <input type="number" name="featured_limit" class="form-control" min="1" max="24"
                        value="{{ $setting?->featured_limit ?? 8 }}">
                    <span class="input-group-text">cards</span>
                </div>
                <small class="text-muted">First N selected products appear (by product name order within selection).</small>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-600">Trending section shows</label>
                <div class="input-group">
                    <input type="number" name="trending_limit" class="form-control" min="1" max="24"
                        value="{{ $setting?->trending_limit ?? 8 }}">
                    <span class="input-group-text">cards</span>
                </div>
                <small class="text-muted">Applied after the carousel — a "View All" button redirects to the shop.</small>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-custom-primary w-100">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Save limits
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Featured section --}}
<div class="section-card mb-4">
    <div class="section-card-head">
        <h5><i class="fa-solid fa-star me-2"></i> Featured Products</h5>
        <small class="opacity-75">Toggle which active products appear in the home-page Featured carousel</small>
    </div>
    <div class="p-4">
        <div class="row g-3" data-section="featured">
            @foreach($products as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="prod-tile {{ $product->is_featured ? 'on' : '' }}">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                        <div class="name">{{ $product->name }}</div>
                        <button type="button"
                            class="btn section-toggle {{ $product->is_featured ? 'btn-custom-primary' : 'btn-outline-secondary' }}"
                            data-product="{{ $product->id }}"
                            data-value="{{ $product->is_featured ? 1 : 0 }}">
                            {{ $product->is_featured ? '✓ In section' : 'Add to section' }}
                        </button>
                    </div>
                </div>
            @endforeach
            @if($products->isEmpty())
                <div class="col-12 text-center text-muted py-4">No active products yet.</div>
            @endif
        </div>
    </div>
</div>

{{-- Trending section --}}
<div class="section-card mb-4">
    <div class="section-card-head">
        <h5><i class="fa-solid fa-fire me-2"></i> Trending Products</h5>
        <small class="opacity-75">Toggle which active products appear in the home-page Trending carousel</small>
    </div>
    <div class="p-4">
        <div class="row g-3" data-section="trending">
            @foreach($products as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="prod-tile {{ $product->is_trending ? 'on' : '' }}">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                        <div class="name">{{ $product->name }}</div>
                        <button type="button"
                            class="btn section-toggle {{ $product->is_trending ? 'btn-custom-primary' : 'btn-outline-secondary' }}"
                            data-product="{{ $product->id }}"
                            data-value="{{ $product->is_trending ? 1 : 0 }}">
                            {{ $product->is_trending ? '✓ In section' : 'Add to section' }}
                        </button>
                    </div>
                </div>
            @endforeach
            @if($products->isEmpty())
                <div class="col-12 text-center text-muted py-4">No active products yet.</div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.section-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tile = btn.closest('.prod-tile');
            const section = btn.closest('[data-section]').dataset.section;
            const newValue = btn.dataset.value === '1' ? 0 : 1;

            btn.disabled = true;
            fetch('{{ route('admin.home-sections.toggle') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    product_id: parseInt(btn.dataset.product, 10),
                    section: section,
                    value: newValue === 1
                })
            })
            .then(r => r.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    btn.dataset.value = String(newValue);
                    tile.classList.toggle('on', newValue === 1);
                    btn.classList.toggle('btn-custom-primary', newValue === 1);
                    btn.classList.toggle('btn-outline-secondary', newValue !== 1);
                    btn.textContent = newValue === 1 ? '✓ In section' : 'Add to section';
                } else {
                    alert(data.message || 'Could not update.');
                }
            })
            .catch(() => { btn.disabled = false; alert('Network error.'); });
        });
    });
});
</script>
@endsection
