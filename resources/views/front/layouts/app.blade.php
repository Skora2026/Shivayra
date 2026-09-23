<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trim($__env->yieldContent('title')) ?: 'Home' }} - {{ $settings?->site_name ?? 'Shivayra' }}</title>

    @if($settings?->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $settings->favicon) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    @endif

    @if($settings?->logo)
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $settings->logo) }}">
    @endif

    {{-- Social sharing / SEO defaults --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $settings?->site_name ?? 'Shivayra' }}">
    <meta property="og:title" content="{{ trim($__env->yieldContent('title')) ?: 'Home' }} - {{ $settings?->site_name ?? 'Shivayra' }}">
    <meta property="og:image" content="{{ asset('images/og-card.png') }}">
    <meta name="twitter:card" content="summary_large_image">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    {{-- Per-page styles. Must stay after the vendor sheets above so a page can
         override them. Without this yield, every @section('styles') in the
         front views was silently dropped. --}}
    @yield('styles')
</head>

<body data-auth="{{ auth()->check() ? 1 : 0 }}">

    @include('front.layouts.partials.icons')

    @include('front.layouts.header')

    <main>

        @yield('content')

    </main>

    @include('front.layouts.footer')

    @stack('scripts')
</body>

</html>
