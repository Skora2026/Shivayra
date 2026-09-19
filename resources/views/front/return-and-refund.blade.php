@extends('front.layouts.app')

@section('title')
    Return & Refund Policy
@endsection

@section('content')
    <main class="container my-5 py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="fw-bold mb-4">Return & Refund Policy</h1>
                <p class="text-muted">Last updated: June 16, 2026</p>
                <hr class="my-4">
                
                <section class="mb-4">
                    <h4 class="fw-bold">1. Return Window</h4>
                    <p class="text-muted">
                        We accept returns and exchanges on eligible products within 7 days of delivery. The items must be unused, in their original packaging, and with all tags intact.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="fw-bold">2. Non-Returnable Items</h4>
                    <p class="text-muted">
                        Certain products, such as innerwear, custom garments, and sale items, are non-returnable and non-exchangeable for hygiene and policy reasons.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="fw-bold">3. Refund Process</h4>
                    <p class="text-muted">
                        Once we receive and inspect the returned item, we will notify you. Approved refunds will be processed to your original payment method or as store credits within 5-7 business days.
                    </p>
                </section>

                <section class="mb-4">
                    <h4 class="fw-bold">4. Shipping Costs</h4>
                    <p class="text-muted">
                        Customers are responsible for return shipping costs unless the return is due to our error (e.g. damaged or incorrect product delivered).
                    </p>
                </section>
            </div>
        </div>
    </main>
@endsection
