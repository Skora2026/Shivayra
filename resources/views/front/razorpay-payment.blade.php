@extends('front.layouts.app')

@section('title')
    Processing Payment
@endsection

@section('content')
<div class="container my-5 py-5">
    <div class="row justify-content-center py-5">
        <div class="col-md-6 text-center">
            <div class="spinner-border mb-4" style="color: #C5A880; width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h4 class="fw-600 text-dark mb-2" style="font-family: var(--font-display);">Processing Secure Payment...</h4>
            <p class="text-muted" style="font-size: 0.9rem;">Please do not close this window or click back. We are opening the secure Razorpay payment modal.</p>
            
            {{-- Hidden Verification Form --}}
            <form id="paymentForm" action="{{ route('checkout.verify') }}" method="POST">
                @csrf
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                <input type="hidden" name="razorpay_order_id" value="{{ $razorpayOrderId }}">
                <input type="hidden" name="razorpay_signature" id="razorpay_signature">
            </form>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var options = {
            "key": "{{ config('services.razorpay.key') }}",
            "amount": "{{ (int)round($order->total * 100) }}",
            "currency": "INR",
            "name": "{{ $settings?->site_name ?? 'Shivayra' }}",
            "description": "Payment for Order {{ $order->order_number }}",
            "image": "{{ $settings?->logo ? asset('storage/' . $settings->logo) : asset('images/logo2.jpeg') }}",
            "order_id": "{{ $razorpayOrderId }}",
            "handler": function (response){
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                document.getElementById('paymentForm').submit();
            },
            "prefill": {
                "name": "{{ $order->name }}",
                "email": "{{ $order->email }}",
                "contact": "{{ $order->phone }}"
            },
            "theme": {
                "color": "#C5A880" // Champagne Gold
            },
            "modal": {
                "ondismiss": function(){
                    window.location.href = "{{ route('checkout') }}?payment=cancelled&order_id={{ $order->id }}";
                }
            }
        };
        var rzp = new Razorpay(options);
        
        rzp.on('payment.failed', function (response){
            console.error('Payment failed', response.error);
            window.location.href = "{{ route('checkout') }}?payment=failed&order_id={{ $order->id }}";
        });
        
        rzp.open();
    });
</script>
@endsection
