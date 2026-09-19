@extends('front.layouts.app')

@section('title')
    {{ $product->name }}
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
@endpush

@section('content')
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- LEFT: IMAGES -->
                <div class="col-lg-7 col-md-6 ">
                    <div class="sticky-col p-3 rounded-4">
                        <!-- DESKTOP MAIN IMAGE -->
                        <div class="pd-img-wrap d-none d-md-block text-center">
                            <img id="mainImg"
                                src="{{ $product->image_url }}"
                                class="shadow rounded-4" style="max-height: 450px; object-fit: cover; width: 100%;">
                        </div>

                        <div class="swiper thumbSwiper d-none d-lg-block mt-3">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide text-center">
                                    <img src="{{ $product->image_url }}" class="active shadow rounded-3" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                </div>
                                @php $gallery = $product->gallery_images ?? []; @endphp
                                @foreach($gallery as $img)
                                    <div class="swiper-slide text-center">
                                        <img src="{{ asset('storage/' . $img) }}" class="shadow rounded-3" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- MOBILE MAIN SWIPER -->
                        <div class="swiper mainSwiper d-md-none">
                            <div class="swiper-wrapper">
                                <div class="swiper-slide text-center">
                                    <img src="{{ $product->image_url }}" class="rounded-4 w-100" style="max-height: 350px; object-fit: cover;">
                                </div>
                                @foreach($gallery as $img)
                                    <div class="swiper-slide text-center">
                                        <img src="{{ asset('storage/' . $img) }}" class="rounded-4 w-100" style="max-height: 350px; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
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
                                <div class="text-center py-5">
                                    <svg class="icon icon-lg" style="color: var(--gold-bright);"><use href="#i-star-o"/></svg>
                                    <h5 class="mt-3 mb-2" style="font-family: var(--font-display);">No reviews yet</h5>
                                    <p class="text-muted mb-0">Be the first to share your experience with this piece.</p>
                                </div>
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
            
            const mainImg = document.getElementById('mainImg');
            if (mainImg) {
                mainImg.src = this.src;
            }
        });
    });

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
            
            // Update desktop main image
            if (mainImg) {
                mainImg.src = imagesToUse[0];
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
                        if (mainImg) mainImg.src = this.src;
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
                            <img src="${url}" class="rounded-4 w-100" style="max-height: 350px; object-fit: cover;">
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
                        loop: imagesToUse.length > 1
                    });
                }
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
    
    // Auto-select first variant on load if exists
    const firstVal1 = document.querySelector('.var-option-1');
    const firstVal2 = document.querySelector('.var-option-2');
    if (firstVal1) firstVal1.click();
    if (firstVal2) firstVal2.click();
});
</script>
@endpush
