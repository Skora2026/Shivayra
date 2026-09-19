<footer class="footer-section">

    <div class="container">

        <!-- Top Footer -->
        <div class="row gy-5">

            <!-- About -->
            <div class="col-lg-4">
                {{-- <a href="#" class="footer-logo">
                    Shivayra
                </a> --}}

                <a href="{{ url('/') }}">
                    <img src="{{ $settings?->logo ? asset('storage/' . $settings->logo) : asset('images/logo2.jpeg') }}" class="bg-white rounded-3"
                        height="60" alt="">
                </a>

                <p class="footer-text mt-3">
                    Your trusted online shopping destination for quality products,
                    secure payments, and fast delivery. Shop smarter with {{ $settings?->site_name ?? 'Shivayra' }}.
                </p>

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
                

            </div>

            <!-- Links -->

            <div class="col-lg-2 col-6">

                <h5>Quick Links</h5>

                <ul>

                    <li><a href="{{url('/')}}">Home</a></li>


                    <li><a href="{{route('contact-us')}}">Contact Us</a></li>

                </ul>

            </div>

            <!-- Policies -->

            <div class="col-lg-3 col-6">

                <h5>Our Policies</h5>

                <ul>
                    

                    <li><a href="{{route('privacy_policy')}}">Privacy Policy</a></li>

                    <li><a href="{{route('terms&conditions')}}">Terms & Conditions</a></li>

                    {{-- <li><a href="{{route('privacy_policy')}}">Delivery & Shipping</a></li> --}}

                    <li><a href="{{route('return&refund')}}">Return & Exchange</a></li>

                </ul>

            </div>

            <!-- Newsletter -->

            <div class="col-lg-3">

                <h5>Stay Connected</h5>

                {{-- <p class="footer-text">
                    Subscribe for latest offers and updates.
                </p> --}}

                <div class="mt-4">
                    @if($settings?->phone)
                        <div class="footer-contact">
                            <i class="bi bi-telephone-fill"></i>
                            {{ $settings->phone }}
                        </div>
                    @endif

                    @if($settings?->email)
                        <div class="footer-contact">
                            <i class="bi bi-envelope-fill"></i>
                            <a href="mailto:{{ $settings->email }}">{{ $settings->email }} </a>
                        </div>
                    @endif

                    @if($settings?->address)
                        <div class="footer-contact">
                            <i class="bi bi-geo-alt-fill"></i>
                            {{ $settings->address }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Trust -->

        <div class="trust-box">

            <div>
                <i class="bi bi-shield-check"></i>
                Secure Payment
            </div>

            <div>
                <i class="bi bi-truck"></i>
                Fast Delivery
            </div>

            <div>
                <i class="bi bi-arrow-repeat"></i>
                Easy Returns
            </div>

            <div>
                <i class="bi bi-patch-check"></i>
                Quality Products
            </div>

        </div>

        <!-- Bottom -->

        <div class="footer-bottom">

            <div>
                © {{ date('Y') }} {{ $settings?->site_name ?? 'Shivayra' }}. All Rights Reserved.
            </div>

            <div>
                Designed & Maintained by
                <a href="https://www.skorainfotech.com/" target="_blank">SkoraInfotech</a>
            </div>

        </div>

    </div>

</footer>
