@extends('front.layouts.app')

@section('title')
    Order Success
@endsection

@section('content')
<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow rounded-4 p-4 text-center" style="background: #ffffff;">
                <div class="mb-3">
                    {{-- Confetti Popper Icon --}}
                    <img src="/images/empty-state.png" alt="Order placed" width="120" class="mb-2">
                </div>
                
                <h2 class="fw-800 mb-2" style="color: #0A9051; font-size: 1.6rem;">Order Placed Successfully!</h2>
                <p class="text-muted mb-4" style="font-size: 0.9rem; line-height: 1.5;">
                    Thank you for your purchase. Your order has been registered in our system and is currently being processed.
                </p>
                
                {{-- Order Number Card --}}
                <div class="p-3 mb-4 rounded-3 border-0 text-start" style="background: #f8fafc; border: 1px solid #eef2f6 !important;">
                    <div class="text-center border-bottom pb-3 mb-3">
                        <small class="text-uppercase text-muted fw-600" style="letter-spacing: 0.05em; font-size: 0.75rem;">Order Number</small>
                        <h4 class="fw-800 text-dark mt-1 mb-0" style="font-size: 1.35rem; letter-spacing: 0.5px;">{{ $order->order_number }}</h4>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary fw-500" style="font-size: 0.9rem;">Total Amount:</span>
                        <span class="fw-800 text-success" style="font-size: 1.1rem;">₹{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>

                {{-- Delivery Details --}}
                <div class="text-start mb-4 p-3 rounded-3" style="background: #ffffff; border: 1px solid #eef2f6;">
                    <h6 class="fw-700 text-dark mb-3" style="font-size: 0.95rem;">Delivery Details</h6>
                    <div class="mb-2" style="font-size: 0.85rem;">
                        <span class="text-muted d-block">Recipient:</span>
                        <strong class="text-dark">{{ $order->shipping_name }}</strong>
                    </div>
                    <div class="mb-2" style="font-size: 0.85rem;">
                        <span class="text-muted d-block">Phone:</span>
                        <strong class="text-dark">{{ $order->shipping_phone }}</strong>
                    </div>
                    <div style="font-size: 0.85rem;">
                        <span class="text-muted d-block">Address:</span>
                        <strong class="text-dark">{{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}</strong>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="d-grid gap-2">
                    <a href="{{ route('order.details', $order->order_number) }}" class="btn btn-outline-success rounded-pill fw-600 py-2.5" style="border-width: 1.5px; font-size: 0.9rem;">
                        View / Download Invoice
                    </a>
                    <a href="{{ url('/') }}" class="btn text-white rounded-pill fw-600 py-2.5" style="background-color: #0A9051; font-size: 0.9rem;">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Clear the local cart upon successful order placement; for logged-in
    // customers the server-side cart is cleared too (best effort — the
    // order is already recorded either way).
    localStorage.removeItem("cart");
    if (document.body.dataset.auth === "1") {
        fetch("/cart/clear", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content || "",
                "Accept": "application/json",
            },
            keepalive: true,
        }).catch(() => {});
    }
    
    // Dispatch an event to update header cart counters instantly
    document.addEventListener("DOMContentLoaded", function() {
        let lcart = document.getElementById("lcartCount");
        let mcart = document.getElementById("mcartCount");
        if (lcart) lcart.innerText = 0;
        if (mcart) mcart.innerText = 0;
        
        let cartItems = document.getElementById("cartItems");
        if (cartItems) cartItems.innerHTML = "<div class='text-center py-4'>Your cart is empty</div>";
        
        let cartTotal = document.getElementById("cartTotal");
        if (cartTotal) cartTotal.innerText = "0.00";
    });
</script>
@endsection
