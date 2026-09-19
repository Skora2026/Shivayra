@extends('front.layouts.app')

@section('title')
    Privacy Policy
@endsection

@section('content')
    <main class="container my-5 py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="fw-bold mb-4">Privacy Policy</h1>
                <p class="text-muted">Last updated: June 16, 2026</p>
                <hr class="my-4">
                
                <section class="mb-4">
                    <h4 class="fw-bold">1. Information We Collect</h4>
                    <p class="text-muted">
                        We collect personal information that you provide to us, such as your name, shipping address, email address, phone number, and payment details when making a purchase.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="fw-bold">2. How We Use Your Information</h4>
                    <p class="text-muted">
                        We use your information to process transactions, deliver orders, send shipping notifications, communicate about customer support, and send promotional offers (if opted in).
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="fw-bold">3. Data Protection</h4>
                    <p class="text-muted">
                        We implement strict security measures to protect your personal information. We do not sell or rent your data to third parties for marketing purposes.
                    </p>
                </section>
            </div>
        </div>
    </main>
@endsection
