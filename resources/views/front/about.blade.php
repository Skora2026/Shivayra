@extends('front.layouts.app')

@section('title')
    About Us
@endsection

@section('content')
    <main class="container my-5 py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <span class="badge bg-success mb-2 px-3 py-2 text-uppercase fw-semibold" style="background-color: var(--warm-peach) !important; color: #111111;">Our Story</span>
                <h1 class="display-4 fw-bold">About Shivayra</h1>
                <p class="lead text-muted">Your premier destination for high-quality, modern, and comfortable lifestyle products.</p>
            </div>
        </div>

        <div class="row align-items-center mb-5">
            <div class="col-md-6 mb-4 mb-md-0">
                <img src="/images/hero-banner-1.jpg" class="img-fluid rounded-4 shadow-sm" alt="Our craft">
            </div>
            <div class="col-md-6 ps-md-5">
                <h3 class="fw-bold mb-3">Crafted for Comfort & Elegance</h3>
                <p class="text-muted">
                    At Shivayra, we believe that fine jewelry and accessories should be both elegant and wearable. Founded in 2026, we curate premium pieces from dedicated designers to bring you high-value luxury at competitive prices.
                </p>
                <p class="text-muted">
                    We focus on sustainability, ethical production, and absolute customer satisfaction. From sportswear to casual attire, each item is tested for durability and comfort before it arrives at your doorstep.
                </p>
            </div>
        </div>

        <div class="row text-center mt-5 g-4">
            <div class="col-md-4">
                <div class="p-4 border rounded-4 bg-light shadow-sm">
                    <div class="mb-3"><svg class="icon" style="width:44px;height:44px;color:var(--gold-bright)"><use href="#i-truck"/></svg></div>
                    <h5 class="fw-bold">Free Shipping</h5>
                    <p class="text-muted mb-0">Get free delivery on all orders above ₹999 anywhere across the country.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border rounded-4 bg-light shadow-sm">
                    <div class="mb-3"><svg class="icon" style="width:44px;height:44px;color:var(--gold-bright)"><use href="#i-shield"/></svg></div>
                    <h5 class="fw-bold">Secure Checkout</h5>
                    <p class="text-muted mb-0">100% secure payments via multiple prepaid and credit methods.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border rounded-4 bg-light shadow-sm">
                    <div class="mb-3"><svg class="icon" style="width:44px;height:44px;color:var(--gold-bright)"><use href="#i-refresh"/></svg></div>
                    <h5 class="fw-bold">Easy Returns</h5>
                    <p class="text-muted mb-0">Not satisfied? Return or exchange items hassle-free within 7 days.</p>
                </div>
            </div>
        </div>
    </main>
@endsection
