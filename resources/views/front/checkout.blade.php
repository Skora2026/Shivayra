@extends('front.layouts.app')

@section('title')
    Checkout
@endsection




@section('content')

@php
    $taxPercent = $settings->tax_percent ?? 5;
    $deliveryFee = $settings->delivery_fee ?? 60;
    $minOrderFree = $settings->min_order_for_free_delivery ?? 1000;
    $isCodEnabled = isset($settings->is_cod_enabled) ? (bool)$settings->is_cod_enabled : true;
    $codDisabled = !$isCodEnabled;
@endphp

<script>
    window.taxPercent = {{ (float)$taxPercent }};
    window.deliveryFee = {{ (float)$deliveryFee }};
    window.minOrderFreeDelivery = {{ (float)$minOrderFree }};
    window.isCodEnabled = {{ $isCodEnabled ? 'true' : 'false' }};
</script>

    <div class="container my-5">

        <div class="text-center mb-5">

            <h2 class="main-heading">
                Checkout
            </h2>

            <p class="creative-subline">
                Hand-picked premium items just for you
            </p>

        </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert" style="background-color: #fdf2f2; color: #9b1c1c;">
            <i class="fa-solid fa-circle-exclamation me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert" style="background-color: #f0fdf4; color: #166534;">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form id="checkoutForm" action="{{ route('checkout.store') }}" method="POST">
        @csrf
        <input type="hidden" name="cart_data" id="cartDataInput">

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please fix the highlighted fields:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-warning" role="alert">{{ session('error') }}</div>
        @endif

        <div class="row g-4">

            <!-- LEFT -->
            <div class="col-lg-7">
                <div class="checkout-card">

                    <h5 class="section-title">Billing Details</h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="">Name*</label>
                            <input type="text" id="billName" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Full Name" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="">Email*</label>
                            <input type="email" id="billEmail" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="">Phone*</label>
                            <input type="text" id="billPhone" name="phone" class="form-control @error('phone') is-invalid @enderror" maxlength="10"
                                placeholder="Phone" value="{{ old('phone') }}" required>
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="">Address*</label>
                            <input type="text" id="billAddress" name="address" class="form-control @error('address') is-invalid @enderror" placeholder="Address" value="{{ old('address') }}" required>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="">City*</label>
                            <input type="text" id="billCity" name="city" class="form-control @error('city') is-invalid @enderror" placeholder="City" value="{{ old('city') }}" required>
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="">State*</label>
                            <input type="text" id="billState" name="state" class="form-control @error('state') is-invalid @enderror" placeholder="State" value="{{ old('state') }}" required>
                            @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="">Pincode*</label>
                            <input type="text" id="billPin" name="pincode" class="form-control @error('pincode') is-invalid @enderror" placeholder="Pincode" value="{{ old('pincode') }}" required>
                            @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" class="form-check-input" id="sameAddress" name="same_address" value="1" checked>
                        <label class="form-check-label">Shipping same as billing</label>
                    </div>

                    <h5 class="section-title mt-4" id="shippingTitle">Shipping Details</h5>

                    <div id="shippingSection">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="">Shipping Name*</label>
                                <input type="text" name="shipping_name" class="form-control shipping-field @error('shipping_name') is-invalid @enderror" placeholder="Shipping Name" readonly>
                                @error('shipping_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="">Shipping Email</label>
                                <input type="text" name="shipping_email" class="form-control shipping-field" placeholder="Shipping Email" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="">Shipping Phone</label>
                                <input type="text" name="shipping_phone" class="form-control shipping-field" maxlength="10"
                                    placeholder="Shipping Phone" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="">Shipping Address*</label>
                                <input type="text" name="shipping_address" class="form-control shipping-field @error('shipping_address') is-invalid @enderror"
                                    placeholder="Shipping Address" readonly>
                                @error('shipping_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="">City</label>
                                <input type="text" name="shipping_city" class="form-control shipping-field" placeholder="City" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="">State</label>
                                <input type="text" name="shipping_state" class="form-control shipping-field" placeholder="State" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="">Pincode</label>
                                <input type="text" name="shipping_pincode" class="form-control shipping-field" placeholder="Pincode" readonly>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- RIGHT -->
            <div class="col-lg-5">
                <div class="checkout-card summary-card">

                    <h5 class="section-title">Order Summary</h5>

                    <div class="summary-detail" id="orderItems"></div>

                    <hr>

                    <div class="total-box">
                        <p>Subtotal: ₹<span id="subtotal">0</span></p>
                        <p>Delivery Fee: ₹<span id="shipping">0</span></p>
                        <p>Tax ({{ (float)$taxPercent }}% GST): ₹<span id="tax">0</span></p>
                        <h5>Total: ₹<span id="total">0</span></h5>
                    </div>

                    <h5 class="section-title mt-4">Payment Method</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-check payment-box p-3 border rounded-3 d-flex align-items-center gap-2 m-0 {{ $codDisabled ? 'disabled opacity-50 bg-light' : '' }}" style="cursor: {{ $codDisabled ? 'not-allowed' : 'pointer' }};" onclick="selectPayment(this)">
                                <input class="form-check-input ms-2" type="radio" name="payment_method" id="codOption" value="cod" {{ $codDisabled ? 'disabled' : 'checked' }}>
                                <label class="form-check-label fw-600 m-0" style="cursor: {{ $codDisabled ? 'not-allowed' : 'pointer' }};" for="codOption">
                                    <svg class="icon"><use href="#i-cash"/></svg> Cash on Delivery
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check payment-box p-3 border rounded-3 d-flex align-items-center gap-2 m-0" style="cursor: pointer;" onclick="selectPayment(this)">
                                <input class="form-check-input ms-2" type="radio" name="payment_method" id="razorpayOption" value="razorpay" {{ $codDisabled ? 'checked' : '' }}>
                                <label class="form-check-label fw-600 m-0" style="cursor: pointer;" for="razorpayOption">
                                    <svg class="icon"><use href="#i-card"/></svg> Pay Online
                                </label>
                            </div>
                        </div>
                    </div>

                    @guest
                        <div class="alert alert-warning border-0 rounded-3 mb-3 text-start">
                            <i class="fa-solid fa-circle-exclamation me-2"></i>
                            Please <a href="{{ route('login') }}" class="fw-700 text-decoration-underline text-warning">Login</a> or <a href="{{ route('register') }}" class="fw-700 text-decoration-underline text-warning">Register</a> to place an order.
                        </div>
                        <button type="button" class="order-btn mt-2" disabled style="opacity:0.6; cursor:not-allowed;">Place Order</button>
                    @else
                        <button type="submit" class="order-btn mt-4">Place Order</button>
                    @endguest

                </div>
            </div>

        </div>
    </form>
    </div>


@endsection
