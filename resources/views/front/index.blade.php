@extends('front.layouts.app')

@section('title')
    Home
@endsection

@section('content')
    @php
        $desktopBanners = $banners->filter(function($b) {
            return in_array($b->device_type, ['desktop', 'both']);
        })->values();

        $mobileBanners = $banners->filter(function($b) {
            return in_array($b->device_type, ['mobile', 'both']);
        })->values();
    @endphp

    <!-- HERO SECTION -->
    <section class="hero-section position-relative overflow-hidden">
        <div class="container-fluid p-0">
            @if($banners->isEmpty())
                <!-- Default Carousel -->
                <div id="heroCarouselDefault" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#heroCarouselDefault" data-bs-slide-to="0" class="active"
                            aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#heroCarouselDefault" data-bs-slide-to="1"
                            aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#heroCarouselDefault" data-bs-slide-to="2"
                            aria-label="Slide 3"></button>
                    </div>

                    <div class="carousel-inner">
                        <!-- Slide 1 -->
                        <div class="carousel-item active hero-slide">
                            <div class="hero-image-wrapper position-relative">
                                <img src="{{ asset('images/hero-banner-1.jpg') }}" class="hero-img"
                                    alt="Luxury Jewelry Collection Banner 1">
                                <div class="hero-overlay d-flex align-items-center">
                                    <div class="container px-4 px-md-5">
                                        <div class="col-lg-8 col-xl-7 text-white text-start">
                                            <span
                                                class="badge bg-gold mb-3 px-3 py-2 text-uppercase fw-semibold tracking-wider animate__animated animate__fadeInDown"
                                                style="background-color: var(--warm-peach) !important; color: #111111;">
                                                <svg class="icon"><use href="#i-sparkle"/></svg> LUXURY DEMI-FINE JEWELRY
                                            </span>
                                            <h1
                                                class="display-3 fw-bold mb-3 text-shadow animate__animated animate__fadeInUp animate__delay-1s">
                                                Handcrafted Elegance For Every Occasion
                                            </h1>
                                            <p
                                                class="lead mb-4 text-white-50 text-shadow max-width-600 animate__animated animate__fadeInUp animate__delay-2s">
                                                Explore our collection of ethical, premium-quality gold and diamond jewelry
                                                designed for modern everyday luxury.
                                            </p>
                                            <div
                                                class="d-flex flex-wrap gap-3 animate__animated animate__fadeInUp animate__delay-3s">
                                                <a href="{{ route('products') }}"
                                                    class="btn btn-gold btn-lg rounded-pill px-4 py-3 fw-bold shadow-lg hover-lift"
                                                    style="background-color: var(--burgundy) !important; color: #F3EDE4; border: none;">
                                                    Shop Collection <i class="bi bi-arrow-right ms-2"></i>
                                                </a>
                                                <a href="{{ route('about_us') }}"
                                                    class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-bold hover-lift">
                                                    Our Story
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 2 -->
                        <div class="carousel-item hero-slide">
                            <div class="hero-image-wrapper position-relative">
                                <img src="{{ asset('images/hero-banner-2.jpg') }}" class="hero-img"
                                    alt="Luxury Jewelry Collection Banner 2">
                                <div class="hero-overlay d-flex align-items-center">
                                    <div class="container px-4 px-md-5">
                                        <div class="col-lg-8 col-xl-7 text-white text-start">
                                            <span
                                                class="badge bg-gold mb-3 px-3 py-2 text-uppercase fw-semibold tracking-wider"
                                                style="background-color: var(--warm-peach) !important; color: #111111;">
                                                <svg class="icon"><use href="#i-gem"/></svg> THE ESSENTIALS
                                            </span>
                                            <h1 class="display-3 fw-bold mb-3 text-shadow">
                                                Time-Transcendental Designs
                                            </h1>
                                            <p class="lead mb-4 text-white-50 text-shadow max-width-600">
                                                Beautifully simple, modern jewelry that elevates any outfit. From office
                                                styling to dinner dates.
                                            </p>
                                            <div class="d-flex flex-wrap gap-3">
                                                <a href="{{ route('products') }}"
                                                    class="btn btn-gold btn-lg rounded-pill px-4 py-3 fw-bold shadow-lg hover-lift"
                                                    style="background-color: var(--burgundy) !important; color: #F3EDE4; border: none;">
                                                    View Best Sellers <i class="bi bi-gem ms-2"></i>
                                                </a>
                                                <a href="{{ route('products') }}"
                                                    class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-bold hover-lift">
                                                    Explore All
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Slide 3 -->
                        <div class="carousel-item hero-slide">
                            <div class="hero-image-wrapper position-relative">
                                <img src="{{ asset('images/hero-banner-1.jpg') }}" class="hero-img"
                                    alt="Luxury Jewelry Collection Banner 3">
                                <div class="hero-overlay d-flex align-items-center">
                                    <div class="container px-4 px-md-5">
                                        <div class="col-lg-8 col-xl-7 text-white text-start">
                                            <span
                                                class="badge bg-gold mb-3 px-3 py-2 text-uppercase fw-semibold tracking-wider"
                                                style="background-color: var(--warm-peach) !important; color: #111111;">
                                                <svg class="icon"><use href="#i-gift"/></svg> GIFTING REDEFINED
                                            </span>
                                            <h1 class="display-3 fw-bold mb-3 text-shadow">
                                                A Celebration of Special Moments
                                            </h1>
                                            <p class="lead mb-4 text-white-50 text-shadow max-width-600">
                                                Surprise your loved ones with our custom gift-wrapped selections. Get up to
                                                50% off during our festive sale.
                                            </p>
                                            <div class="d-flex flex-wrap gap-3">
                                                <a href="{{ route('products') }}?sale=1"
                                                    class="btn btn-gold btn-lg rounded-pill px-4 py-3 fw-bold shadow-lg hover-lift"
                                                    style="background-color: var(--burgundy) !important; color: #F3EDE4; border: none;">
                                                    Shop Festive Gifts <i class="bi bi-gift ms-2"></i>
                                                </a>
                                                <a href="{{ route('contact-us') }}"
                                                    class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-bold hover-lift">
                                                    Contact Stylist
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Controls -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarouselDefault"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#heroCarouselDefault"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            @else
                <!-- Desktop Carousel (Visible on md screens and up) -->
                @if($desktopBanners->isNotEmpty())
                    <div id="heroCarouselDesktop" class="carousel slide carousel-fade d-none d-md-block" data-bs-ride="carousel" data-bs-interval="5000">
                        <div class="carousel-indicators">
                            @foreach($desktopBanners as $index => $banner)
                                <button type="button" data-bs-target="#heroCarouselDesktop" data-bs-slide-to="{{ $index }}"
                                    class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-label="Slide {{ $index + 1 }}"></button>
                            @endforeach
                        </div>

                        <div class="carousel-inner">
                            @foreach($desktopBanners as $index => $banner)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }} hero-slide">
                                    <div class="hero-image-wrapper position-relative">
                                        <img src="{{ $banner->image_url }}" class="hero-img" alt="{{ $banner->title }}">
                                        <div class="hero-overlay d-flex align-items-center">
                                            <div class="container px-4 px-md-5">
                                                <div class="col-lg-8 col-xl-7 text-white text-start">
                                                    @if ($banner->badge)
                                                        <span
                                                            class="badge bg-gold mb-3 px-3 py-2 text-uppercase fw-semibold tracking-wider animate__animated animate__fadeInDown"
                                                            style="background-color: var(--warm-peach) !important; color: #111111;">
                                                            {{ $banner->badge }}
                                                        </span>
                                                    @endif
                                                    <h1
                                                        class="display-3 fw-bold mb-3 text-shadow animate__animated animate__fadeInUp animate__delay-1s">
                                                        {{ $banner->title }}
                                                    </h1>
                                                    @if ($banner->description)
                                                        <p
                                                            class="lead mb-4 text-white-50 text-shadow max-width-600 animate__animated animate__fadeInUp animate__delay-2s">
                                                            {{ $banner->description }}
                                                        </p>
                                                    @endif
                                                    <div
                                                        class="d-flex flex-wrap gap-3 animate__animated animate__fadeInUp animate__delay-3s">
                                                        @if ($banner->button_text && $banner->button_link)
                                                            <a href="{{ $banner->button_link }}"
                                                                class="btn btn-gold btn-lg rounded-pill px-4 py-3 fw-bold shadow-lg hover-lift"
                                                                style="background-color: var(--burgundy) !important; color: #F3EDE4; border: none;">
                                                                {{ $banner->button_text }} <i class="bi bi-arrow-right ms-2"></i>
                                                            </a>
                                                        @endif
                                                        @if ($banner->secondary_button_text && $banner->secondary_button_link)
                                                            <a href="{{ $banner->secondary_button_link }}"
                                                                class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-bold hover-lift">
                                                                {{ $banner->secondary_button_text }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarouselDesktop"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#heroCarouselDesktop"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                @endif

                <!-- Mobile Carousel (Visible on screens below md) -->
                @if($mobileBanners->isNotEmpty())
                    <div id="heroCarouselMobile" class="carousel slide carousel-fade d-block d-md-none" data-bs-ride="carousel" data-bs-interval="5000">
                        <div class="carousel-indicators">
                            @foreach($mobileBanners as $index => $banner)
                                <button type="button" data-bs-target="#heroCarouselMobile" data-bs-slide-to="{{ $index }}"
                                    class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-label="Slide {{ $index + 1 }}"></button>
                            @endforeach
                        </div>

                        <div class="carousel-inner">
                            @foreach($mobileBanners as $index => $banner)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }} hero-slide">
                                    <div class="hero-image-wrapper position-relative">
                                        <img src="{{ $banner->mobile_image_url }}" class="hero-img" alt="{{ $banner->title }}">
                                        <div class="hero-overlay d-flex align-items-center">
                                            <div class="container px-4 px-md-5">
                                                <div class="col-lg-8 col-xl-7 text-white text-start">
                                                    @if ($banner->badge)
                                                        <span
                                                            class="badge bg-gold mb-3 px-3 py-2 text-uppercase fw-semibold tracking-wider animate__animated animate__fadeInDown"
                                                            style="background-color: var(--warm-peach) !important; color: #111111;">
                                                            {{ $banner->badge }}
                                                        </span>
                                                    @endif
                                                    <h1
                                                        class="display-3 fw-bold mb-3 text-shadow animate__animated animate__fadeInUp animate__delay-1s">
                                                        {{ $banner->title }}
                                                    </h1>
                                                    @if ($banner->description)
                                                        <p
                                                            class="lead mb-4 text-white-50 text-shadow max-width-600 animate__animated animate__fadeInUp animate__delay-2s">
                                                            {{ $banner->description }}
                                                        </p>
                                                    @endif
                                                    <div
                                                        class="d-flex flex-wrap gap-3 animate__animated animate__fadeInUp animate__delay-3s">
                                                        @if ($banner->button_text && $banner->button_link)
                                                            <a href="{{ $banner->button_link }}"
                                                                class="btn btn-gold btn-lg rounded-pill px-4 py-3 fw-bold shadow-lg hover-lift"
                                                                style="background-color: var(--burgundy) !important; color: #F3EDE4; border: none;">
                                                                {{ $banner->button_text }} <i class="bi bi-arrow-right ms-2"></i>
                                                            </a>
                                                        @endif
                                                        @if ($banner->secondary_button_text && $banner->secondary_button_link)
                                                            <a href="{{ $banner->secondary_button_link }}"
                                                                class="btn btn-outline-light btn-lg rounded-pill px-4 py-3 fw-bold hover-lift">
                                                                {{ $banner->secondary_button_text }}
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarouselMobile"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#heroCarouselMobile"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </section>

    <!-- CATEGORIES SECTION -->
    <section class="categories py-5">
        <div class="container-fluid">
            <div class="text-center mb-4">
                <h2 class="main-heading">Shop By Categories</h2>
                <p class="creative-subline">Trending Collections</p>
            </div>

            <div class="category-slider">
                @foreach ($categories as $category)
                    <a href="{{ route('products') }}?category={{ $category->slug }}" class="cat-card ">
                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}">
                        <span>{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- <section class="trust-section py-5">
        <div class="container-fluid py-4">
            <div class="trust-wrapper">

                <div class="trust-card">
                    <div class="trust-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div class="trust-content">
                        <h5>925 Fine</h5>
                        <p>Silver</p>
                    </div>
                </div>

                <div class="trust-card">
                    <div class="trust-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="trust-content">
                        <h5>6-Month</h5>
                        <p>Warranty</p>
                    </div>
                </div>

                <div class="trust-card">
                    <div class="trust-icon">
                        <i class="fas fa-ring"></i>
                    </div>
                    <div class="trust-content">
                        <h5>Lifetime</h5>
                        <p>Plating</p>
                    </div>
                </div>

                <div class="trust-card">
                    <div class="trust-icon">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <div class="trust-content">
                        <h5>Easy 15 Days</h5>
                        <p>Return</p>
                    </div>
                </div>

            </div>
        </div>
    </section> --}}

    <!-- PRODUCTS SECTION -->
    <section class="position-relative slider-section mb-3 py-4">
        <div class="container-fluid slider-container">
            <div class="text-center mb-4">
                <h2 class="main-heading">Featured Products</h2>
                <p class="creative-subline">Hand-picked premium items just for you</p>
            </div>

            <div class="swiper productSwiper py-5">
                <div class="swiper-wrapper">
                    @foreach ($featuredProducts as $product)
                        <div class="swiper-slide">
                            <div class="product-card">
                                <a href="{{ route('product_detail', $product->slug) }}">
                                    <span class="product-badge hot"><svg class="icon"><use href="#i-sparkle"/></svg>Hot</span>
                                    <div class="product-img">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                    </div>
                                </a>
                                <div class="product-body">
                                    <h6>{{ $product->name }}</h6>
                                    <div class="pp-price">
                                        <strong
                                            style="color: var(--warm-peach);">₹{{ number_format($product->sale_price ?? $product->price, 0) }}</strong>
                                        @if ($product->sale_price)
                                            <del>₹{{ number_format($product->price, 0) }}</del>
                                        @endif
                                    </div>
                                </div>
                                <div class="pp-hover">
                                    <a href="{{ route('product_detail', $product->slug) }}" class="pp-hover-btn">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="pp-hover-btn add-to-wishlist"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        data-price="{{ $product->sale_price ?? $product->price }}"
                                        data-was="{{ $product->price }}"
                                        data-slug="{{ $product->slug }}"
                                        data-img="{{ $product->image_url }}">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    @php
                                        // Quick-add from the card: first in-stock variant;
                                        // all sold out -> fall back to the product page.
                                        $inStockVariants = $product->variants->filter(fn ($v) => $v->stock > 0);
                                        $quickVariant = $inStockVariants->first();
                                    @endphp
                                    @if($quickVariant)
                                        <button type="button" class="pp-hover-btn add-to-cart"
                                            data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                            data-variant-id="{{ $quickVariant->id }}"
                                            data-variant-values="{{ collect([$quickVariant->value_1, $quickVariant->value_2])->filter()->implode(', ') }}"
                                            data-price="{{ $quickVariant->sale_price ?? $quickVariant->price }}"
                                            data-img="{{ $product->image_url }}">
                                            <i class="bi bi-bag-plus"></i>
                                        </button>
                                        @if($inStockVariants->count() > 1)
                                            {{-- Tiny variant chips: pick size/weight straight from the card --}}
                                            <div class="variant-chips" data-for="{{ $product->id }}">
                                                @foreach ($inStockVariants as $v)
                                                    <button type="button" class="variant-chip add-to-cart"
                                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                                        data-variant-id="{{ $v->id }}"
                                                        data-variant-values="{{ collect([$v->value_1, $v->value_2])->filter()->implode(', ') }}"
                                                        data-price="{{ $v->sale_price ?? $v->price }}"
                                                        data-img="{{ $product->image_url }}">{{ $v->value_1 }}</button>
                                                @endforeach
                                            </div>
                                        @endif
                                    @else
                                        <a href="{{ route('product_detail', $product->slug) }}" class="pp-hover-btn">
                                            <i class="bi bi-bag-plus"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pp-prev">‹</div>
            <div class="pp-next">›</div>

            <div class="text-center mt-4">
                <a href="{{ route('products') }}" class="main-btn">
                    <span>View All Products</span>
                    <span class="shine"></span>
                </a>
            </div>
        </div>
    </section>

    <!-- PRODUCTS SECTION -->
    <section class="position-relative slider-section mb-3 py-4">
        <div class="container-fluid slider-container">
            <div class="text-center mb-4">
                <h2 class="main-heading">Trending Products</h2>
                <p class="creative-subline">Most popular premium items of the week</p>
            </div>

            <div class="swiper productSwiper py-5">
                <div class="swiper-wrapper">
                    @foreach ($trendingProducts as $product)
                        <div class="swiper-slide">
                            <div class="product-card">
                                <a href="{{ route('product_detail', $product->slug) }}">
                                    <span class="product-badge hot"><svg class="icon"><use href="#i-sparkle"/></svg>Hot</span>
                                    <div class="product-img">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                    </div>
                                </a>
                                <div class="product-body">
                                    <h6>{{ $product->name }}</h6>
                                    <div class="pp-price">
                                        <strong
                                            style="color: var(--warm-peach);">₹{{ number_format($product->sale_price ?? $product->price, 0) }}</strong>
                                        @if ($product->sale_price)
                                            <del>₹{{ number_format($product->price, 0) }}</del>
                                        @endif
                                    </div>
                                </div>
                                <div class="pp-hover">
                                    <a href="{{ route('product_detail', $product->slug) }}" class="pp-hover-btn">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="pp-hover-btn add-to-wishlist"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        data-price="{{ $product->sale_price ?? $product->price }}"
                                        data-was="{{ $product->price }}"
                                        data-slug="{{ $product->slug }}"
                                        data-img="{{ $product->image_url }}">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    @php
                                        // Quick-add from the card: first in-stock variant;
                                        // all sold out -> fall back to the product page.
                                        $inStockVariants = $product->variants->filter(fn ($v) => $v->stock > 0);
                                        $quickVariant = $inStockVariants->first();
                                    @endphp
                                    @if($quickVariant)
                                        <button type="button" class="pp-hover-btn add-to-cart"
                                            data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                            data-variant-id="{{ $quickVariant->id }}"
                                            data-variant-values="{{ collect([$quickVariant->value_1, $quickVariant->value_2])->filter()->implode(', ') }}"
                                            data-price="{{ $quickVariant->sale_price ?? $quickVariant->price }}"
                                            data-img="{{ $product->image_url }}">
                                            <i class="bi bi-bag-plus"></i>
                                        </button>
                                        @if($inStockVariants->count() > 1)
                                            {{-- Tiny variant chips: pick size/weight straight from the card --}}
                                            <div class="variant-chips" data-for="{{ $product->id }}">
                                                @foreach ($inStockVariants as $v)
                                                    <button type="button" class="variant-chip add-to-cart"
                                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                                        data-variant-id="{{ $v->id }}"
                                                        data-variant-values="{{ collect([$v->value_1, $v->value_2])->filter()->implode(', ') }}"
                                                        data-price="{{ $v->sale_price ?? $v->price }}"
                                                        data-img="{{ $product->image_url }}">{{ $v->value_1 }}</button>
                                                @endforeach
                                            </div>
                                        @endif
                                    @else
                                        <a href="{{ route('product_detail', $product->slug) }}" class="pp-hover-btn">
                                            <i class="bi bi-bag-plus"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pp-prev">‹</div>
            <div class="pp-next">›</div>

            <div class="text-center mt-4">
                <a href="{{ route('products') }}" class="main-btn">
                    <span>View All Products</span>
                    <span class="shine"></span>
                </a>
            </div>
        </div>
    </section>

    <!-- OUR PRODUCTS SECTION -->
    <section class="position-relative slider-section mb-3 py-4">
        <div class="container-fluid slider-container">
            <div class="text-center mb-4">
                <h2 class="main-heading">Our Products</h2>
                <p class="creative-subline">Explore our complete range of exquisite fine jewelry</p>
            </div>

            <div class="swiper productSwiper py-5">
                <div class="swiper-wrapper">
                    @foreach ($allProducts as $product)
                        <div class="swiper-slide">
                            <div class="product-card">
                                <a href="{{ route('product_detail', $product->slug) }}">
                                    <span class="product-badge hot"><svg class="icon"><use href="#i-sparkle"/></svg>New</span>
                                    <div class="product-img">
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                    </div>
                                </a>
                                <div class="product-body">
                                    <h6>{{ $product->name }}</h6>
                                    <div class="pp-price">
                                        <strong style="color: var(--warm-peach);">₹{{ number_format($product->sale_price ?? $product->price, 0) }}</strong>
                                        @if ($product->sale_price)
                                            <del>₹{{ number_format($product->price, 0) }}</del>
                                        @endif
                                    </div>
                                </div>
                                <div class="pp-hover">
                                    <a href="{{ route('product_detail', $product->slug) }}" class="pp-hover-btn">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="pp-hover-btn add-to-wishlist"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        data-price="{{ $product->sale_price ?? $product->price }}"
                                        data-was="{{ $product->price }}"
                                        data-slug="{{ $product->slug }}"
                                        data-img="{{ $product->image_url }}">
                                        <i class="bi bi-heart"></i>
                                    </button>
                                    @php
                                        // Quick-add from the card: first in-stock variant;
                                        // all sold out -> fall back to the product page.
                                        $inStockVariants = $product->variants->filter(fn ($v) => $v->stock > 0);
                                        $quickVariant = $inStockVariants->first();
                                    @endphp
                                    @if($quickVariant)
                                        <button type="button" class="pp-hover-btn add-to-cart"
                                            data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                            data-variant-id="{{ $quickVariant->id }}"
                                            data-variant-values="{{ collect([$quickVariant->value_1, $quickVariant->value_2])->filter()->implode(', ') }}"
                                            data-price="{{ $quickVariant->sale_price ?? $quickVariant->price }}"
                                            data-img="{{ $product->image_url }}">
                                            <i class="bi bi-bag-plus"></i>
                                        </button>
                                        @if($inStockVariants->count() > 1)
                                            {{-- Tiny variant chips: pick size/weight straight from the card --}}
                                            <div class="variant-chips" data-for="{{ $product->id }}">
                                                @foreach ($inStockVariants as $v)
                                                    <button type="button" class="variant-chip add-to-cart"
                                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                                        data-variant-id="{{ $v->id }}"
                                                        data-variant-values="{{ collect([$v->value_1, $v->value_2])->filter()->implode(', ') }}"
                                                        data-price="{{ $v->sale_price ?? $v->price }}"
                                                        data-img="{{ $product->image_url }}">{{ $v->value_1 }}</button>
                                                @endforeach
                                            </div>
                                        @endif
                                    @else
                                        <a href="{{ route('product_detail', $product->slug) }}" class="pp-hover-btn">
                                            <i class="bi bi-bag-plus"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pp-prev">‹</div>
            <div class="pp-next">›</div>
        </div>
    </section>



    <!-- Additional Custom CSS for Hero overlays and animations -->
    <style>

        .text-shadow {
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.5);
        }

        .max-width-600 {
            max-width: 600px;
        }

        .hover-lift {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hover-lift:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3) !important;
        }

        .tracking-wider {
            letter-spacing: 0.08em;
        }

        /* Hero text responsive */
        @media (max-width: 991px) {
            .hero-overlay .display-3 {
                font-size: 1.8rem;
            }
            .hero-overlay .lead {
                font-size: 0.95rem;
            }
            .hero-overlay .btn {
                padding: 0.5rem 1.2rem;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .hero-overlay h1.display-3 {
                font-size: 1.35rem !important;
                margin-bottom: 0.5rem !important;
            }
            .hero-overlay .lead {
                font-size: 0.8rem;
                margin-bottom: 0.6rem !important;
            }
            .hero-overlay .badge {
                font-size: 0.65rem;
                padding: 4px 10px;
                margin-bottom: 0.4rem !important;
            }
            .hero-overlay .d-flex.flex-wrap {
                gap: 8px !important;
            }
            .hero-overlay .btn {
                padding: 0.4rem 1rem;
                font-size: 0.75rem;
            }
            .hero-overlay .col-lg-8 {
                padding: 0 12px;
            }
        }
    </style>
@endsection
