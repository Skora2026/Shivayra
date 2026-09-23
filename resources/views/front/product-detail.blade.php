@extends('front.layouts.app')

@section('title')
    {{ $product->name }}
@endsection

@section('styles')
    <style>
        /* ── Gallery stage: museum mat + hairline frame around the main image ── */
        .pd-stage {
            position: relative;
            max-width: min(540px, 68vh);
            margin-inline: auto;
            padding: 14px;
            background: linear-gradient(165deg, #fffefc 0%, var(--champagne) 100%);
            border: 1px solid rgba(176, 141, 87, 0.55);
            border-radius: 8px;
            box-shadow: 0 28px 60px -32px rgba(64, 17, 31, 0.5);
        }
        .pd-stage::before {
            content: "";
            position: absolute;
            inset: 6px;
            border: 1px solid rgba(176, 141, 87, 0.35);
            border-radius: 5px;
            pointer-events: none;
            z-index: 4;
        }
        .pd-stage .pd-img-wrap { box-shadow: inset 0 0 0 1px rgba(176, 141, 87, 0.4); }

        /* ── Slide arrows: frosted glass discs with a gold setting ── */
        .pd-arrow {
            position: absolute; top: 50%; transform: translateY(-50%);
            width: 46px; height: 46px; border-radius: 50%;
            border: 1px solid rgba(176, 141, 87, 0.65);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: var(--burgundy);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            box-shadow: 0 12px 30px -14px rgba(64, 17, 31, 0.55),
                        inset 0 0 0 1px rgba(255, 255, 255, 0.7),
                        inset 0 0 0 4px rgba(176, 141, 87, 0.12);
            cursor: pointer; z-index: 6;
            transition: background .35s cubic-bezier(.22,1,.36,1),
                        color .35s cubic-bezier(.22,1,.36,1),
                        border-color .35s cubic-bezier(.22,1,.36,1),
                        transform .35s cubic-bezier(.22,1,.36,1),
                        box-shadow .35s cubic-bezier(.22,1,.36,1);
            animation: pdFadeIn .5s ease .15s both;
        }
        .pd-arrow:hover {
            background: linear-gradient(150deg, var(--burgundy) 0%, var(--burgundy-hover) 100%);
            border-color: var(--gold-bright);
            color: var(--gold-bright);
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 18px 44px -16px rgba(64, 17, 31, 0.7),
                        inset 0 0 0 1px rgba(201, 169, 110, 0.55);
        }
        .pd-arrow:active { transform: translateY(-50%) scale(0.96); }
        .pd-arrow-prev { left: 26px; }
        .pd-arrow-next { right: 26px; }
        @keyframes pdFadeIn { from { opacity: 0; } to { opacity: 1; } }
        .pd-stage.pd-single .pd-arrow { display: none; }

        /* Crossfade when the main image changes (arrows, thumb clicks, variant
           switches). Extends — never replaces — the transform transition the
           zoom code owns; .is-dragging still outranks it for pan easing. */
        .pd-img-wrap img { transition: opacity .2s ease, transform .3s ease; }
        .pd-img-wrap img.pd-fade-out { opacity: 0; }

        /* ── Swiper nav discs (mobile main carousel + desktop thumb strip) ── */
        .mainSwiper .swiper-button-prev, .mainSwiper .swiper-button-next {
            width: 44px; height: 44px; border-radius: 50%;
            top: 50%; transform: translateY(-50%); margin-top: 0;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(176, 141, 87, 0.65);
            color: var(--burgundy);
            box-shadow: 0 12px 30px -14px rgba(64, 17, 31, 0.55),
                        inset 0 0 0 1px rgba(255, 255, 255, 0.7);
            transition: background .3s ease, color .3s ease, border-color .3s ease;
        }
        .thumbSwiper .swiper-button-prev, .thumbSwiper .swiper-button-next {
            width: 30px; height: 30px; border-radius: 50%;
            top: 50%; transform: translateY(-50%); margin-top: 0;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(176, 141, 87, 0.55);
            color: var(--burgundy);
            box-shadow: 0 6px 18px -8px rgba(64, 17, 31, 0.45);
            transition: background .3s ease, color .3s ease, border-color .3s ease;
        }
        .mainSwiper .swiper-button-prev, .thumbSwiper .swiper-button-prev { left: 12px; right: auto; }
        .mainSwiper .swiper-button-next, .thumbSwiper .swiper-button-next { right: 12px; left: auto; }
        .mainSwiper .swiper-button-prev::after, .mainSwiper .swiper-button-next::after {
            font-size: 16px; font-weight: 700;
        }
        .thumbSwiper .swiper-button-prev::after, .thumbSwiper .swiper-button-next::after {
            font-size: 11px; font-weight: 700;
        }
        .mainSwiper .swiper-button-prev:hover, .mainSwiper .swiper-button-next:hover {
            background: linear-gradient(150deg, var(--burgundy) 0%, var(--burgundy-hover) 100%);
            border-color: var(--gold-bright);
            color: var(--gold-bright);
        }
        .swiper-button-disabled { opacity: .3; pointer-events: none; }
        .mainSwiper.pd-single .swiper-button-prev, .mainSwiper.pd-single .swiper-button-next,
        .thumbSwiper.pd-single .swiper-button-prev, .thumbSwiper.pd-single .swiper-button-next {
            display: none;
        }

        /* ── Thumbnail strip: dimmed prints, gold-ringed active state ── */
        .thumbSwiper { padding: 5px 0; }
        .thumbSwiper .swiper-slide img {
            opacity: 0.6;
            filter: saturate(0.85);
            box-shadow: 0 8px 20px -10px rgba(64, 17, 31, 0.45);
            transition: opacity .3s ease, filter .3s ease, box-shadow .3s ease;
        }
        .thumbSwiper .swiper-slide img:hover { opacity: 1; filter: none; }
        .thumbSwiper .swiper-slide img.active {
            opacity: 1;
            filter: none;
            box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px var(--warm-peach),
                        0 10px 24px -10px rgba(64, 17, 31, 0.5);
        }

        /* ── Mobile main slides: hairline gold edge, lifted from the page ── */
        .mainSwiper .swiper-slide {
            border-radius: 12px;
            box-shadow: 0 0 0 1px rgba(176, 141, 87, 0.45),
                        0 24px 54px -28px rgba(64, 17, 31, 0.55);
        }

        @media (prefers-reduced-motion: reduce) {
            .pd-arrow, .thumbSwiper .swiper-slide img { animation: none; transition: none; }
        }
    </style>
@endsection

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- LEFT: IMAGES -->
                <div class="col-lg-7 col-md-6 ">
                    <div class="sticky-col p-3 rounded-4">
                        @php $gallery = $product->gallery_images ?? []; @endphp

                        <!-- DESKTOP MAIN IMAGE + SLIDE ARROWS -->
                        <div class="pd-stage d-none d-md-block">
                            <div class="pd-img-wrap text-center">
                                <img id="mainImg"
                                    src="{{ $product->image_url }}">
                            </div>
                            <button type="button" class="pd-arrow pd-arrow-prev" aria-label="Previous image">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button type="button" class="pd-arrow pd-arrow-next" aria-label="Next image">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>

                        <div class="swiper thumbSwiper d-none d-lg-block mt-3">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide text-center">
                                    <img src="{{ $product->image_url }}" class="active shadow rounded-3" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                </div>
                                @foreach($gallery as $img)
                                    <div class="swiper-slide text-center">
                                        <img src="{{ asset('storage/' . $img) }}" class="shadow rounded-3" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                        </div>

                        <!-- MOBILE MAIN SWIPER -->
                        <div class="swiper mainSwiper d-md-none">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide text-center">
                                    <img src="{{ $product->image_url }}" class="rounded-4 w-100">
                                </div>
                                @foreach($gallery as $img)
                                    <div class="swiper-slide text-center">
                                        <img src="{{ asset('storage/' . $img) }}" class="rounded-4 w-100">
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT: DETAILS -->
                <div class="col-lg-5 col-md-6">
                    <div class="p-3 ">
                        <h3 class="fw-600 text-dark">{{ $product->name }}</h3>
                        <div class="d-flex justify-content-between mb-2 mt-2">
                            <div>Category: <span class="text-danger fw-600">{{ $product->category->name }}</span></div>
                        </div>

                        <hr>

                        <div class="my-3">
                            <h4 class="price-main fw-800 text-success d-inline">₹{{ number_format($product->sale_price ?? $product->price, 2) }}</h4>
                            @if($product->sale_price)
                                <del class="text-muted ms-2 fs-5">₹{{ number_format($product->price, 2) }}</del>
                            @endif
                        </div>

                        <hr>

                        @if($product->variants->isNotEmpty())
                            <!-- Dynamic Variant Selectors -->
                            @php
                                $uniqueVal1 = $product->variants->pluck('value_1')->filter()->unique();
                                $uniqueVal2 = $product->variants->pluck('value_2')->filter()->unique();
                            @endphp

                            @if($uniqueVal1->isNotEmpty())
                                <div class="mb-3">
                                    <p class="fw-600 mb-2">Select {{ $product->variant_name_1 ?? 'Size' }}</p>
                                    <div class="d-flex flex-wrap gap-2" id="val1-selector">
                                        @foreach($uniqueVal1 as $val1)
                                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 var-option-1 fw-600" data-val="{{ $val1 }}">{{ $val1 }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($uniqueVal2->isNotEmpty())
                                <div class="mb-3">
                                    <p class="fw-600 mb-2">Select {{ $product->variant_name_2 ?? 'Color' }}</p>
                                    <div class="d-flex flex-wrap gap-2 align-items-center" id="val2-selector">
                                        @foreach($uniqueVal2 as $val2)
                                            @if(str_starts_with($val2, '#'))
                                                <button type="button" class="rounded-circle border-2 border-white shadow-sm var-option-2 color-dot-btn" data-val="{{ $val2 }}" style="background-color: {{ $val2 }}; width: 32px; height: 32px; padding: 0; outline: none;" title="{{ $val2 }}"></button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 var-option-2 fw-600" data-val="{{ $val2 }}">{{ $val2 }}</button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @else
                            @if($product->color)
                            <div class="mb-3">
                                <p class="fw-600 mb-2">Color</p>
                                <span class="badge bg-secondary px-3 py-2 rounded-pill">{{ $product->color }}</span>
                            </div>
                            @endif
                            @if($product->size)
                            <div class="mb-3">
                                <p class="fw-600 mb-2">Size</p>
                                <span class="badge bg-dark px-3 py-2 rounded-pill">{{ $product->size }}</span>
                            </div>
                            @endif
                        @endif

                        @if($product->stock > 0)
                        <div class="d-flex align-items-center gap-3 my-4">
                            <label class="fw-600 mb-0" for="qtySelector">Qty</label>
                            <div class="qty-selector d-inline-flex align-items-center border rounded-pill overflow-hidden">
                                <button type="button" id="qtyMinus" class="btn btn-sm px-3 border-0" aria-label="Decrease quantity">−</button>
                                <input type="number" id="qtySelector" class="form-control text-center border-0 shadow-none p-0"
                                    value="1" min="1" max="{{ $product->stock }}" style="width: 56px;" aria-label="Quantity">
                                <button type="button" id="qtyPlus" class="btn btn-sm px-3 border-0" aria-label="Increase quantity">+</button>
                            </div>
                            <small class="text-muted">({{ $product->stock }} available)</small>
                        </div>
                        @endif

                        <div class="d-flex gap-2 my-4">
                            <button class="position-relative btn-box btn p-0" style="background: none; border: none;">
                                <a href="javascript:void(0);" class="add-to-cart" data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                    data-price="{{ $product->sale_price ?? $product->price }}" data-img="{{ $product->image_url }}">
                                    <div class="main-btn" style="overflow: hidden !important">
                                        <span>Add to cart </span>
                                        <span class="shine"></span>
                                    </div>
                                </a>
                            </button>
                            <button class="icon-btn add-to-wishlist" data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                data-price="{{ $product->sale_price ?? $product->price }}" data-was="{{ $product->price }}"
                                data-slug="{{ $product->slug }}" data-img="{{ $product->image_url }}"><i
                                    class="bi bi-heart"></i></button>
                        </div>

                        <hr>

                        <p>SKU: PROD-{{ $product->id }}</p>
                        @if($product->fabric)
                            <p>Metal / Material: {{ $product->fabric }}</p>
                        @endif
                        @if($product->neckline)
                            <p>Gemstone: {{ $product->neckline }}</p>
                        @endif
                        <p class="text-success fw-600">Availability: {{ $product->stock }} Items In Stock</p>
                    </div>
                    <hr>

                    <div class="product-tabs mt-4">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs" id="productTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc" type="button">
                                    Description
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#info" type="button">
                                    Additional Info
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews" type="button">
                                    Reviews
                                </button>
                            </li>
                        </ul>


                        <!-- Tab Contents -->
                        <div class="tab-content" id="productTabContent">
                            <!-- DESCRIPTION -->
                            <div class="tab-pane fade show active" id="desc" role="tabpanel">
                                <p class="mt-3">{{ $product->description ?? 'No description available for this product.' }}</p>
                            </div>

                            <!-- ADDITIONAL INFO -->
                            <div class="tab-pane fade" id="info" role="tabpanel">
                                <table class="table table-bordered mt-3">
                                    <tr>
                                        <th>Feature</th>
                                        <th>Details</th>
                                    </tr>
                                    @if($product->fabric)
                                    <tr>
                                        <td>Metal / Material</td>
                                        <td>{{ $product->fabric }}</td>
                                    </tr>
                                    @endif
                                    @if($product->neckline)
                                    <tr>
                                        <td>Gemstone</td>
                                        <td>{{ $product->neckline }}</td>
                                    </tr>
                                    @endif
                                    @if($product->pattern)
                                    <tr>
                                        <td>Purity / Carat</td>
                                        <td>{{ $product->pattern }}</td>
                                    </tr>
                                    @endif
                                    @if($product->color)
                                    <tr>
                                        <td>Metal Color</td>
                                        <td>{{ $product->color }}</td>
                                    </tr>
                                    @endif
                                    @if($product->size)
                                    <tr>
                                        <td>Size</td>
                                        <td>{{ $product->size }}</td>
                                    </tr>
                                    @endif
                                    @if($product->occasion)
                                    <tr>
                                        <td>Occasion</td>
                                        <td>{{ $product->occasion }}</td>
                                    </tr>
                                    @endif
                                    @php $specs = $product->specifications ?? []; @endphp
                                    @foreach($specs as $spec)
                                    <tr>
                                        <td>{{ $spec['name'] }}</td>
                                        <td>{{ $spec['value'] }}</td>
                                    </tr>
                                    @endforeach
                                </table>
                            </div>

                            <!-- REVIEWS -->
                            <div class="tab-pane fade" id="reviews" role="tabpanel">
                                @php
                                    $productReviews = \App\Models\ProductReview::with('user:id,name')
                                        ->where('product_id', $product->id)
                                        ->approved()->latest()->get();
                                    $avgRating = $productReviews->avg('rating');
                                @endphp

                                @if($productReviews->isNotEmpty())
                                    <div class="d-flex align-items-center gap-3 mt-3 mb-4 p-3" style="background:var(--champagne); border-radius:4px;">
                                        <div class="text-center px-2">
                                            <div style="font-family:var(--font-display); font-size:2rem; font-weight:600; color:var(--burgundy); line-height:1;">
                                                {{ number_format($avgRating, 1) }}</div>
                                            <div style="color:var(--warm-peach); font-size: .95rem; letter-spacing: 2px;">
                                                @for($i=1; $i<=5; $i++)
                                                    {{ $i <= round($avgRating) ? '★' : '☆' }}
                                                @endfor
                                            </div>
                                            <small class="text-muted">{{ $productReviews->count() }} review{{ $productReviews->count() === 1 ? '' : 's' }}</small>
                                        </div>
                                        <div class="flex-grow-1">
                                            @for($star = 5; $star >= 1; $star--)
                                                @php $cnt = $productReviews->where('rating', $star)->count(); $pct = $productReviews->count() ? intval($cnt * 100 / $productReviews->count()) : 0; @endphp
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <small style="width:32px; color:var(--text-muted);">{{ $star }}★</small>
                                                    <div class="flex-grow-1" style="height:6px; background:rgba(176,141,87,.15); border-radius:3px;">
                                                        <div style="height:6px; width:{{ $pct }}%; background:var(--warm-peach); border-radius:3px;"></div>
                                                    </div>
                                                    <small style="width:24px; color:var(--text-muted);">{{ $cnt }}</small>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>

                                    @foreach($productReviews as $review)
                                        <div class="review-card">
                                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                                <div>
                                                    <div style="color:var(--warm-peach); letter-spacing:2px; font-size:.95rem;">
                                                        @for($i=1; $i<=5; $i++) {{ $i <= $review->rating ? '★' : '☆' }} @endfor
                                                    </div>
                                                    @if($review->title)
                                                        <h6 class="mt-2 mb-1" style="font-family:var(--font-display); font-weight:600;">{{ $review->title }}</h6>
                                                    @endif
                                                    <p class="mb-2" style="color:var(--text-dark); line-height:1.7;">{{ $review->body }}</p>
                                                    <small class="text-muted">
                                                        {{ $review->user?->name ?? 'Verified Buyer' }}
                                                        · <span style="color:var(--warm-peach);">✓ Verified purchase</span>
                                                        · {{ $review->created_at->format('d M Y') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-5">
                                        <svg class="icon icon-lg" style="color: var(--gold-bright);"><use href="#i-star-o"/></svg>
                                        <h5 class="mt-3 mb-2" style="font-family: var(--font-display);">No reviews yet</h5>
                                        <p class="text-muted mb-0">Be the first to share your experience with this piece.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Thumbnail Image Selector
    document.querySelectorAll('.thumbSwiper img').forEach(img => {
        img.addEventListener('click', function() {
            document.querySelectorAll('.thumbSwiper img').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            
            swapMainImage(this.src);
        });
    });

    // 1a. Gallery carousels: instantiate BOTH swipers on page load. They were
    // previously only created inside the variant-click handler, so a page
    // without a variant click had a dead, non-sliding gallery.
    function initGallerySwipers() {
        if (typeof Swiper === 'undefined') return;
        try {
            if (!window.thumbSwiper && document.querySelector('.thumbSwiper')) {
                window.thumbSwiper = new Swiper('.thumbSwiper', {
                    slidesPerView: 4,
                    spaceBetween: 10,
                    speed: 450,
                    rewind: true,
                    navigation: { prevEl: '.thumbSwiper .swiper-button-prev', nextEl: '.thumbSwiper .swiper-button-next' },
                    breakpoints: {
                        0: { slidesPerView: 3 },
                        768: { slidesPerView: 4 }
                    }
                });
            }
            if (!window.mainSwiper && document.querySelector('.mainSwiper')) {
                window.mainSwiper = new Swiper('.mainSwiper', {
                    slidesPerView: 1,
                    spaceBetween: 10,
                    speed: 550,
                    rewind: true,
                    keyboard: { enabled: true, onlyInViewport: true },
                    navigation: { prevEl: '.mainSwiper .swiper-button-prev', nextEl: '.mainSwiper .swiper-button-next' }
                });
            }
            syncPdNav();
        } catch (err) {
            console.error('Gallery swiper init failed:', err);
        }
    }

    // Crossfade the desktop main image whenever it changes (arrows, thumb
    // clicks, variant switches): fade out, swap, fade back in. The thumb strip
    // already loaded every gallery image, so the swap never waits on the wire.
    let pdFadeTimer = null;
    function swapMainImage(src) {
        const el = document.getElementById('mainImg');
        if (!el || !src || el.src === src) return;
        el.classList.add('pd-fade-out');
        window.clearTimeout(pdFadeTimer);
        pdFadeTimer = window.setTimeout(() => {
            el.src = src;
            el.classList.remove('pd-fade-out');
        }, 150);
    }

    // 1b. Desktop arrows step the main image through the thumbnail list, so
    // they work even below lg where the thumb strip is hidden.
    function stepDesktopImage(dir) {
        const thumbs = Array.from(document.querySelectorAll('.thumbSwiper img'));
        if (thumbs.length < 2) return;
        let idx = thumbs.findIndex(t => t.classList.contains('active'));
        if (idx < 0) idx = 0;
        idx = (idx + dir + thumbs.length) % thumbs.length;
        thumbs[idx].click();
        if (window.thumbSwiper && typeof window.thumbSwiper.slideTo === 'function') {
            window.thumbSwiper.slideTo(idx);
        }
    }
    document.querySelector('.pd-arrow-prev')?.addEventListener('click', () => stepDesktopImage(-1));
    document.querySelector('.pd-arrow-next')?.addEventListener('click', () => stepDesktopImage(1));

    // Keyboard ← / → step the desktop gallery — never while typing in a field.
    document.addEventListener('keydown', (e) => {
        const t = e.target;
        if (t && typeof t.matches === 'function'
            && t.matches('input, textarea, select, [contenteditable]')) return;
        if (e.key === 'ArrowLeft') stepDesktopImage(-1);
        else if (e.key === 'ArrowRight') stepDesktopImage(1);
    });

    // 1c. Show/hide each carousel's arrows only when there is something to
    // slide to — single-image products get a clean, arrow-less panel. The
    // thumb strip only needs arrows past 4 thumbs (its widest slidesPerView).
    function syncPdNav() {
        const desktopImgs = document.querySelectorAll('.thumbSwiper img').length;
        const mobileSlides = document.querySelectorAll('.mainSwiper .swiper-slide').length;
        document.querySelector('.pd-stage')?.classList.toggle('pd-single', desktopImgs < 2);
        document.querySelector('.mainSwiper')?.classList.toggle('pd-single', mobileSlides < 2);
        document.querySelector('.thumbSwiper')?.classList.toggle('pd-single', desktopImgs <= 4);
    }
    initGallerySwipers();

    // 1b. Quantity selector
    const qtyInput = document.getElementById('qtySelector');
    if (qtyInput) {
        const clampQty = () => {
            let v = parseInt(qtyInput.value, 10);
            if (isNaN(v) || v < 1) v = 1;
            const max = parseInt(qtyInput.max, 10) || 999;
            if (v > max) v = max;
            qtyInput.value = v;
        };
        document.getElementById('qtyMinus')?.addEventListener('click', () => {
            qtyInput.value = Math.max(1, (parseInt(qtyInput.value, 10) || 1) - 1);
        });
        document.getElementById('qtyPlus')?.addEventListener('click', () => {
            qtyInput.value = (parseInt(qtyInput.value, 10) || 1) + 1;
            clampQty();
        });
        qtyInput.addEventListener('change', clampQty);
    }

    // 2. Dynamic Variant Matching
    const variants = {!! json_encode($product->variants) !!};
    const mainImg = document.getElementById('mainImg');
    const priceMain = document.querySelector('.price-main');
    const delPrice = document.querySelector('del');
    const addToCartBtn = document.querySelector('.add-to-cart');
    const availabilityText = document.querySelector('.text-success.fw-600');
    
    let selectedVal1 = null;
    let selectedVal2 = null;

    // Handle Option 1 selection
    document.querySelectorAll('.var-option-1').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.var-option-1').forEach(b => {
                b.classList.remove('active', 'btn-success');
                b.classList.add('btn-outline-success');
            });
            
            this.classList.remove('btn-outline-success');
            this.classList.add('active', 'btn-success');
            selectedVal1 = this.dataset.val;
            matchVariant();
        });
    });

    // Handle Option 2 selection
    document.querySelectorAll('.var-option-2').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.var-option-2').forEach(b => {
                b.classList.remove('active');
                if (b.classList.contains('color-dot-btn')) {
                    b.style.transform = 'none';
                    b.style.boxShadow = 'none';
                } else {
                     b.classList.remove('btn-success');
                     b.classList.add('btn-outline-success');
                }
            });

            this.classList.add('active');
            if (this.classList.contains('color-dot-btn')) {
                this.style.transform = 'scale(1.15)';
                this.style.boxShadow = '0 0 0 3px #0A9051';
            } else {
                this.classList.remove('btn-outline-success');
                this.classList.add('btn-success');
            }
            selectedVal2 = this.dataset.val;
            matchVariant();
        });
    });

    function matchVariant() {
        if (variants.length === 0) return;
        
        let match = variants.find(v => {
            let match1 = selectedVal1 ? v.value_1 === selectedVal1 : true;
            let match2 = selectedVal2 ? v.value_2 === selectedVal2 : true;
            return match1 && match2;
        });

        // Auto-select fallback for Value 2 if no direct match with current selection
        if (!match && selectedVal1) {
            let alternative = variants.find(v => v.value_1 === selectedVal1);
            if (alternative) {
                selectedVal2 = alternative.value_2;
                document.querySelectorAll('.var-option-2').forEach(b => {
                    b.classList.remove('active');
                    if (b.classList.contains('color-dot-btn')) {
                        b.style.transform = 'none';
                        b.style.boxShadow = 'none';
                    } else {
                        b.classList.remove('btn-success');
                        b.classList.add('btn-outline-success');
                    }

                    if (b.dataset.val === selectedVal2) {
                        b.classList.add('active');
                        if (b.classList.contains('color-dot-btn')) {
                            b.style.transform = 'scale(1.15)';
                            b.style.boxShadow = '0 0 0 3px #0A9051';
                        } else {
                            b.classList.remove('btn-outline-success');
                            b.classList.add('btn-success');
                        }
                    }
                });
                match = alternative;
            }
        }

        // Auto-select fallback for Value 1 if no direct match with current selection
        if (!match && selectedVal2) {
            let alternative = variants.find(v => v.value_2 === selectedVal2);
            if (alternative) {
                selectedVal1 = alternative.value_1;
                document.querySelectorAll('.var-option-1').forEach(b => {
                    b.classList.remove('active', 'btn-success');
                    b.classList.add('btn-outline-success');

                    if (b.dataset.val === selectedVal1) {
                        b.classList.remove('btn-outline-success');
                        b.classList.add('btn-success', 'active');
                    }
                });
                match = alternative;
            }
        }

        if (match) {
            // Update price
            const price = parseFloat(match.sale_price ?? match.price);
            if (priceMain) {
                priceMain.innerHTML = `₹${price.toFixed(2)}`;
            }
            if (delPrice) {
                if (match.sale_price) {
                    delPrice.style.display = 'inline';
                    delPrice.innerHTML = `₹${parseFloat(match.price).toFixed(2)}`;
                } else {
                    delPrice.style.display = 'none';
                }
            }

            // Update images dynamically from variant's multiple images
            const placeholderImgUrl = "{{ $product->image_url }}";
            let imagesToUse = match.image_urls && match.image_urls.length > 0 ? match.image_urls : [placeholderImgUrl];
            
            // Update desktop main image (crossfades)
            if (mainImg) {
                swapMainImage(imagesToUse[0]);
                if (addToCartBtn) addToCartBtn.dataset.img = imagesToUse[0];
            }

            // Rebuild desktop thumbnails
            const thumbWrapper = document.querySelector('.thumbSwiper .swiper-wrapper');
            if (thumbWrapper) {
                let thumbHtml = '';
                imagesToUse.forEach((url, i) => {
                    thumbHtml += `
                        <div class="swiper-slide text-center">
                            <img src="${url}" class="${i === 0 ? 'active' : ''} shadow rounded-3" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                        </div>
                    `;
                });
                thumbWrapper.innerHTML = thumbHtml;

                // Re-bind thumbnail click events
                document.querySelectorAll('.thumbSwiper img').forEach(img => {
                    img.addEventListener('click', function() {
                        document.querySelectorAll('.thumbSwiper img').forEach(i => i.classList.remove('active'));
                        this.classList.add('active');
                        swapMainImage(this.src);
                    });
                });
            }

            // Rebuild mobile main swiper
            const mobileWrapper = document.querySelector('.mainSwiper .swiper-wrapper');
            if (mobileWrapper) {
                let mobileHtml = '';
                imagesToUse.forEach((url) => {
                    mobileHtml += `
                        <div class="swiper-slide text-center">
                            <img src="${url}" class="rounded-4 w-100">
                        </div>
                    `;
                });
                mobileWrapper.innerHTML = mobileHtml;
            }

            // Re-initialize or update Swiper instances
            try {
                if (window.thumbSwiper && typeof window.thumbSwiper.update === 'function') {
                    window.thumbSwiper.update();
                } else if (typeof Swiper !== 'undefined') {
                    window.thumbSwiper = new Swiper(".thumbSwiper", {
                        slidesPerView: 4,
                        spaceBetween: 10,
                        loop: imagesToUse.length > 1,
                        navigation: { prevEl: '.thumbSwiper .swiper-button-prev', nextEl: '.thumbSwiper .swiper-button-next' },
                        breakpoints: {
                            0: { slidesPerView: 3 },
                            768: { slidesPerView: 4 }
                        }
                    });
                }

                if (window.mainSwiper && typeof window.mainSwiper.update === 'function') {
                    window.mainSwiper.update();
                } else if (typeof Swiper !== 'undefined') {
                    window.mainSwiper = new Swiper(".mainSwiper", {
                        slidesPerView: 1,
                        spaceBetween: 10,
                        loop: imagesToUse.length > 1,
                        navigation: { prevEl: '.mainSwiper .swiper-button-prev', nextEl: '.mainSwiper .swiper-button-next' }
                    });
                }
                syncPdNav();
            } catch (err) {
                console.error("Swiper update failed:", err);
            }

            // Update stock availability and cart button
            if (availabilityText) {
                if (match.stock > 0) {
                    availabilityText.innerHTML = `Availability: ${match.stock} Items In Stock`;
                    availabilityText.className = "text-success fw-600";
                    if (addToCartBtn) {
                        const mainBtnSpan = addToCartBtn.querySelector('.main-btn span');
                        if (mainBtnSpan) mainBtnSpan.innerText = "Add to cart";
                        addToCartBtn.style.pointerEvents = "auto";
                        addToCartBtn.style.opacity = "1";
                    }
                } else {
                    availabilityText.innerHTML = `Availability: Out of Stock`;
                    availabilityText.className = "text-danger fw-600";
                    if (addToCartBtn) {
                        const mainBtnSpan = addToCartBtn.querySelector('.main-btn span');
                        if (mainBtnSpan) mainBtnSpan.innerText = "Out of Stock";
                        addToCartBtn.style.pointerEvents = "none";
                        addToCartBtn.style.opacity = "0.6";
                    }
                }
            }

            // Update add to cart buttons dataset
            if (addToCartBtn) {
                addToCartBtn.dataset.price = price;
                addToCartBtn.dataset.variantId = match.id;
                addToCartBtn.dataset.variantValues = [selectedVal1, selectedVal2].filter(Boolean).join(', ');
            }
        } else {
            // No matching variant found
            if (priceMain) {
                priceMain.innerHTML = `N/A`;
            }
            if (delPrice) delPrice.style.display = 'none';
            if (availabilityText) {
                availabilityText.innerHTML = `Availability: Not Available`;
                availabilityText.className = "text-danger fw-600";
            }
            if (addToCartBtn) {
                const mainBtnSpan = addToCartBtn.querySelector('.main-btn span');
                if (mainBtnSpan) mainBtnSpan.innerText = "Unavailable";
                addToCartBtn.style.pointerEvents = "none";
                addToCartBtn.style.opacity = "0.6";
                addToCartBtn.dataset.variantId = "";
                addToCartBtn.dataset.variantValues = "";
            }
        }
    }
    
    // ---------- Image zoom + pan ----------
    // Pointer devices get a hover preview that follows the cursor; clicking locks the
    // zoom in so the image can be grabbed and dragged around. Touch has no hover, so a
    // tap locks the zoom in and a drag pans it.
    const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    // Levels live in product-detail.css so they can be tuned without touching this file.
    const zoomVars = getComputedStyle(document.documentElement);
    const HOVER_ZOOM = parseFloat(zoomVars.getPropertyValue('--pd-zoom-hover')) || 2;
    const PAN_ZOOM = parseFloat(zoomVars.getPropertyValue('--pd-zoom-active')) || 3;

    const DRAG_THRESHOLD = 6; // px of travel before a touch counts as a drag

    function originFrom(container, clientX, clientY) {
        const r = container.getBoundingClientRect();
        const x = Math.min(100, Math.max(0, ((clientX - r.left) / r.width) * 100));
        const y = Math.min(100, Math.max(0, ((clientY - r.top) / r.height) * 100));
        return x + '% ' + y + '%';
    }

    // Zoom/pan state for one image inside one container. The transform is built by hand
    // rather than read from CSS so scale and pan compose predictably: translate() is
    // applied in screen pixels first, then scale() about transform-origin.
    function makeZoomable(container, img) {
        const state = { level: 1, x: 0, y: 0 };

        function apply(animate) {
            img.style.transition = animate ? '' : 'none';
            img.style.transform =
                'translate(' + state.x + 'px, ' + state.y + 'px) scale(' + state.level + ')';
        }

        function maxPan() {
            const box = container.getBoundingClientRect();
            const nw = img.naturalWidth || box.width || 1;
            const nh = img.naturalHeight || box.height || 1;
            // object-fit: contain, so the picture is letterboxed inside its box. Only
            // the part that overflows the box is reachable by panning.
            const fit = Math.min(box.width / nw, box.height / nh) || 1;
            return {
                x: Math.max(0, (nw * fit * state.level - box.width) / 2),
                y: Math.max(0, (nh * fit * state.level - box.height) / 2),
            };
        }

        function clamp() {
            const m = maxPan();
            state.x = Math.min(m.x, Math.max(-m.x, state.x));
            state.y = Math.min(m.y, Math.max(-m.y, state.y));
        }

        function reset() {
            state.level = 1;
            state.x = 0;
            state.y = 0;
            img.style.transformOrigin = 'center center';
            apply(true);
        }

        return { state: state, apply: apply, clamp: clamp, reset: reset };
    }

    const zoomWrap = document.querySelector('.pd-img-wrap');
    const zoomImg = document.getElementById('mainImg');

    // One zoom/pan state per container. Touch registers containers lazily, which is
    // what lets it keep working when the mobile slides are rebuilt on variant change.
    const zoomables = new WeakMap();

    if (zoomWrap && zoomImg) {
        const zoom = makeZoomable(zoomWrap, zoomImg);
        zoomables.set(zoomWrap, { img: zoomImg, zoom: zoom });

        if (canHover) {
            let mode = 'idle'; // idle | hover | pan
            let dragging = false;
            let dragMoved = false;
            let startX = 0;
            let startY = 0;
            let baseX = 0;
            let baseY = 0;

            function enterHover(e) {
                mode = 'hover';
                zoom.state.level = HOVER_ZOOM;
                zoom.state.x = 0;
                zoom.state.y = 0;
                zoomImg.style.transformOrigin = originFrom(zoomWrap, e.clientX, e.clientY);
                zoomWrap.classList.remove('is-panning');
                zoomWrap.classList.add('is-zoomed');
                zoom.apply(true);
            }

            function exit() {
                mode = 'idle';
                dragging = false;
                dragMoved = false;
                zoomWrap.classList.remove('is-zoomed', 'is-panning', 'is-dragging');
                zoom.reset();
            }

            zoomWrap.addEventListener('mouseenter', function (e) {
                if (mode !== 'pan') enterHover(e);
            });

            // Only the hover preview tracks the cursor. Once locked in, the image holds
            // still so it can actually be inspected, then dragged.
            zoomWrap.addEventListener('mousemove', function (e) {
                if (mode !== 'hover' || dragging) return;
                zoomImg.style.transformOrigin = originFrom(zoomWrap, e.clientX, e.clientY);
            });

            zoomWrap.addEventListener('mouseleave', exit);

            zoomWrap.addEventListener('click', function (e) {
                // Every drag ends with a click; don't let a pan cancel the zoom.
                if (dragMoved) {
                    dragMoved = false;
                    return;
                }

                if (mode === 'pan') {
                    // Fall back to the cursor-following preview, since the pointer is
                    // still over the image after the click that released the lock.
                    zoomWrap.classList.remove('is-panning', 'is-dragging');
                    enterHover(e);
                    return;
                }

                mode = 'pan';
                zoom.state.level = PAN_ZOOM;
                zoom.state.x = 0;
                zoom.state.y = 0;
                // A centred origin keeps the pan range symmetrical, so clamping works.
                zoomImg.style.transformOrigin = 'center center';
                zoomWrap.classList.remove('is-zoomed');
                zoomWrap.classList.add('is-panning');
                zoom.apply(true);
            });

            zoomWrap.addEventListener('pointerdown', function (e) {
                if (mode !== 'pan') return;
                dragging = true;
                dragMoved = false;
                startX = e.clientX;
                startY = e.clientY;
                baseX = zoom.state.x;
                baseY = zoom.state.y;
                if (zoomWrap.setPointerCapture) {
                    try { zoomWrap.setPointerCapture(e.pointerId); } catch (err) { /* ignore */ }
                }
            });

            zoomWrap.addEventListener('pointermove', function (e) {
                if (!dragging) return;
                if (Math.abs(e.clientX - startX) > 2 || Math.abs(e.clientY - startY) > 2) {
                    dragMoved = true;
                }
                zoomWrap.classList.add('is-dragging');
                zoom.state.x = baseX + (e.clientX - startX);
                zoom.state.y = baseY + (e.clientY - startY);
                zoom.clamp();
                zoom.apply(false);
            });

            function endDrag() {
                if (!dragging) return;
                dragging = false;
                zoomWrap.classList.remove('is-dragging');
                zoom.apply(true);
            }
            zoomWrap.addEventListener('pointerup', endDrag);
            zoomWrap.addEventListener('pointercancel', endDrag);

            // Switching thumbnail or variant swaps the photo — drop the zoom so the new
            // image starts clean instead of inheriting the previous pan offset.
            if (window.MutationObserver) {
                new MutationObserver(exit).observe(zoomImg, {
                    attributes: true,
                    attributeFilter: ['src'],
                });
            }
        } else {
            // Touch is handled by the delegated listener further down, which also
            // covers the mobile slides. Only the reset-on-new-photo hook is needed here.
            if (window.MutationObserver) {
                new MutationObserver(function () {
                    zoomWrap.classList.remove('is-zoomed', 'is-dragging', 'swiper-no-swiping');
                    if (window.mainSwiper) window.mainSwiper.allowTouchMove = true;
                    zoom.reset();
                }).observe(zoomImg, { attributes: true, attributeFilter: ['src'] });
            }
        }
    }

    // Touch: tap to lock the zoom in, then drag to pan.
    //
    // Delegated from document rather than bound per element. A tap only becomes
    // distinguishable from a drag on release, and the mobile slides are thrown away
    // and rebuilt whenever a variant is selected — which would orphan any listener
    // bound directly to them (the thumbnail strip has the same problem, and re-binds
    // by hand after each rebuild).
    if (!canHover) {
        const TOUCH_TARGET = '.mainSwiper .swiper-slide, .pd-img-wrap';
        const gestures = new WeakMap();

        function touchTargetFor(target) {
            return target && target.closest ? target.closest(TOUCH_TARGET) : null;
        }

        function zoomableFor(container) {
            const existing = zoomables.get(container);
            if (existing) return existing;

            const img = container.querySelector('img');
            if (!img) return null;

            const entry = { img: img, zoom: makeZoomable(container, img) };
            zoomables.set(container, entry);
            return entry;
        }

        // The gesture has to be recorded even before the image is zoomed, otherwise
        // the tap that turns zooming ON can never be recognised.
        document.addEventListener('pointerdown', function (e) {
            const container = touchTargetFor(e.target);
            if (!container) return;

            const entry = zoomableFor(container);
            if (!entry) return;

            gestures.set(container, {
                zoomed: container.classList.contains('is-zoomed'),
                moved: false,
                x: e.clientX,
                y: e.clientY,
                baseX: entry.zoom.state.x,
                baseY: entry.zoom.state.y,
            });
        });

        document.addEventListener('pointermove', function (e) {
            const container = touchTargetFor(e.target);
            if (!container) return;

            const gesture = gestures.get(container);
            const entry = zoomableFor(container);
            // Until the image is zoomed the gesture belongs to the page / Swiper.
            if (!gesture || !entry || !gesture.zoomed) return;

            const dx = e.clientX - gesture.x;
            const dy = e.clientY - gesture.y;
            if (!gesture.moved && Math.abs(dx) < DRAG_THRESHOLD && Math.abs(dy) < DRAG_THRESHOLD) return;

            gesture.moved = true;
            container.classList.add('is-dragging');
            entry.zoom.state.x = gesture.baseX + dx;
            entry.zoom.state.y = gesture.baseY + dy;
            entry.zoom.clamp();
            entry.zoom.apply(false);
        });

        document.addEventListener('pointerup', function (e) {
            const container = touchTargetFor(e.target);
            if (!container) return;

            const gesture = gestures.get(container);
            const entry = zoomableFor(container);
            if (!gesture || !entry) return;

            gestures.delete(container);
            container.classList.remove('is-dragging');

            if (gesture.moved) {
                entry.zoom.apply(true);
                return;
            }

            // Nothing moved, so it was a tap: toggle the zoom.
            if (gesture.zoomed) {
                container.classList.remove('is-zoomed', 'swiper-no-swiping');
                if (window.mainSwiper) window.mainSwiper.allowTouchMove = true;
                entry.zoom.reset();
                return;
            }

            entry.zoom.state.level = PAN_ZOOM;
            entry.zoom.state.x = 0;
            entry.zoom.state.y = 0;
            entry.img.style.transformOrigin = originFrom(container, e.clientX, e.clientY);
            // Stop Swiper turning the pan that follows into a slide change.
            container.classList.add('is-zoomed', 'swiper-no-swiping');
            if (window.mainSwiper) window.mainSwiper.allowTouchMove = false;
            entry.zoom.apply(true);
        });

        document.addEventListener('pointercancel', function (e) {
            const container = touchTargetFor(e.target);
            if (!container) return;

            gestures.delete(container);
            container.classList.remove('is-dragging');

            const entry = zoomableFor(container);
            if (entry) entry.zoom.apply(true);
        });
    }

    // Auto-select first variant on load if exists
    const firstVal1 = document.querySelector('.var-option-1');
    const firstVal2 = document.querySelector('.var-option-2');
    if (firstVal1) firstVal1.click();
    if (firstVal2) firstVal2.click();
});
</script>
@endpush
