@extends('front.layouts.app')

@section('title')
    Contact Us
@endsection

@section('content')
    <main class="container">
        <div class="contact-hero">
            <h1>We’d love to hear from you</h1>
            <p>Questions about your order, sustainable sourcing, or just want to say hello? Our team is here to help.</p>
        </div>

        <div class="contact-grid">
            <!-- LEFT: CONTACT INFO & STORE -->
            <div class="info-card">
                <h3>Connect with us</h3>
                <div class="contact-details">
                    {{-- <div class="detail-item">
                        <div class="detail-icon"><svg class="icon" style="width:1.4em;height:1.4em;vertical-align:-.4em"><use href="#i-pin"/></svg></div>
                        <div class="detail-text">
                            <h4>Visit our flagship</h4>
                            <p>142 Green Valley Road, Eco District,<br>San Francisco, CA 94107</p>
                        </div>
                    </div> --}}
                    <div class="detail-item">
                        <div class="detail-icon"><svg class="icon" style="width:1.4em;height:1.4em;vertical-align:-.4em"><use href="#i-phone"/></svg></div>
                        <div class="detail-text">
                            <h4>Call or message</h4>
                            <p>
                                @if($settings?->phone)
                                    <a href="tel:{{ preg_replace('/\D/', '', $settings->phone) }}">{{ $settings->phone }}</a>
                                @endif
                                @if($settings?->email)
                                    <br>
                                    {{ $settings->email }}
                                @endif
                            </p>
                        </div>
                    </div>
                    {{-- <div class="detail-item">
                        <div class="detail-icon">⏱️</div>
                        <div class="detail-text">
                            <h4>Support hours</h4>
                            <p>Mon-Fri: 9am – 7pm PST<br>Saturday: 10am – 4pm PST</p>
                        </div>
                    </div> --}}
                </div>

                <div class="store-hours">
                    <h4 class="icon-inline"><svg class="icon"><use href="#i-box"/></svg> Customer care & returns</h4>
                    <p>Our team replies within 24h on business days. For returns, please include your order number for
                        faster processing. Zero-waste packaging always.</p>
                </div>


                <div class="social-links">
                    @if($settings?->facebook)
                        <a href="{{ $settings->facebook }}" target="_blank"><i class="bi bi-facebook"></i></a>
                    @endif

                    @if($settings?->instagram)
                        <a href="{{ $settings->instagram }}" target="_blank"><i class="bi bi-instagram"></i></a>
                    @endif

                    @if($settings?->whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings->whatsapp) }}" target="_blank"><i class="bi bi-whatsapp"></i></a>
                    @endif

                    @if($settings?->twitter)
                        <a href="{{ $settings->twitter }}" target="_blank"><i class="bi bi-twitter-x"></i></a>
                    @endif

                    @if($settings?->youtube)
                        <a href="{{ $settings->youtube }}" target="_blank"><i class="bi bi-youtube"></i></a>
                    @endif
                </div>
                

                {{-- <div class="social-links">
                    <a href="#" class="social-icon" aria-label="Facebook"><svg class="icon"><use href="#i-facebook"/></svg></a>
                    <a href="#" class="social-icon" aria-label="Instagram"><svg class="icon"><use href="#i-instagram"/></svg></a>
                    <a href="#" class="social-icon" aria-label="X"><svg class="icon"><use href="#i-x"/></svg></a>
                    <a href="#" class="social-icon" aria-label="TikTok"><svg class="icon"><use href="#i-music"/></svg></a>
                </div> --}}
            </div>

            <!-- RIGHT: CONTACT FORM -->
            <div class="form-card">
                <h3>Send a message <span class="badge">2h reply avg</span></h3>
                <form id="contactForm" action="{{ route('contact.store') }}" method="POST">
                    @csrf
                    @if (session('contact_success'))
                        <div class="alert alert-success" role="alert">{{ session('contact_success') }}</div>
                    @endif
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
                    <div class="input-group">
                        <label for="name">Full name *</label>
                        <input type="text" id="name" name="name" placeholder="Jamie Rivera" value="{{ old('name') }}" required>
                    </div>
                    <div class="input-group">
                        <label for="email">Email address *</label>
                        <input type="email" id="email" name="email" placeholder="hello@example.com" value="{{ old('email') }}" required>
                    </div>
                    <div class="input-group">
                        <label for="order">Order number (optional)</label>
                        <input type="text" id="order" name="order" placeholder="#EC-12345" value="{{ old('order') }}">
                    </div>
                    <div class="input-group">
                        <label for="inquiry">Inquiry type *</label>
                        <select id="inquiry" name="inquiry" required>
                            <option value="">Select topic</option>
                            @foreach (['orders' => 'Order & Shipping', 'returns' => 'Returns & Refunds', 'product' => 'Product information', 'wholesale' => 'Wholesale / Partnership', 'other' => 'Other'] as $value => $label)
                                <option value="{{ $value }}" {{ old('inquiry') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="input-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" rows="4" placeholder="Tell us how we can help you..." required>{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="btn-submit icon-inline">Send message <svg class="icon"><use href="#i-arrow"/></svg></button>
                    <p style="font-size: 0.7rem; margin-top: 1rem; text-align: center; color: #5f6c66;">By submitting, you
                        agree to our privacy policy. We’ll never share your data.</p>
                </form>
            </div>
        </div>
    </main>
@endsection
