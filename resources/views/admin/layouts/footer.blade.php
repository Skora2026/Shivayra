<footer class="footer mt-auto">
    <div class="container-fluid">
        <div class="row align-items-center justify-content-between">
            <div class="col-md-6 text-center text-md-start">
                &copy; {{ date('Y') }} {{ $settings?->site_name ?? 'Shivayra' }}. All rights reserved.
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <span>Made with <i class="fa-solid fa-heart text-danger"></i> for {{ $settings?->site_name ?? 'Shivayra' }}.</span>
            </div>
        </div>
    </div>
</footer>
