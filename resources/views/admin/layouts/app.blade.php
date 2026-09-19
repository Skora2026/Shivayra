<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - {{ $settings?->site_name ?? 'Shivayra' }}</title>
    @if($settings?->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $settings->favicon) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    @endif
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --warm-peach: #C9A96A;
            --deep-forest: #40111F;
            --burgundy: #5C1A2E;
            --vibrant-mint: #8C7355;
            --ember-brown: #1C1C1C;
            --bg-light: #FAF7F2;
            --sidebar-width: 260px;
            --header-height: 70px;
            --font-family: 'Montserrat', sans-serif;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--bg-light);
            margin: 0;
            overflow-x: hidden;
        }

        #wrapper {
            display: flex;
            min-height: 100vh;
        }

        #sidebar-wrapper {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #40111F 0%, #2A0C14 100%);
            color: #ffffff;
            transition: all 0.3s ease;
            position: fixed;
            height: 100vh;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #page-content-wrapper {
            width: 100%;
            margin-left: var(--sidebar-width);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-header {
            height: var(--header-height);
            background: #ffffff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .main-content {
            padding: 2rem;
            flex-grow: 1;
        }

        .footer {
            background-color: #ffffff;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 2rem;
            text-align: center;
            font-size: 0.85rem;
            color: #6c757d;
        }

        /* Sidebar Navigation Styles */
        .sidebar-brand {
            padding: 1.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand a {
            color: #ffffff;
            text-decoration: none;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1rem 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
            border-left: 4px solid transparent;
            gap: 10px;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li.active > a {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
            border-left-color: var(--warm-peach);
        }

        .sidebar-menu li a i {
            width: 20px;
            font-size: 1.1rem;
            color: var(--warm-peach);
        }

        .menu-header {
            padding: 0.5rem 1.5rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255, 255, 255, 0.4);
            font-weight: 700;
            margin-top: 1rem;
        }

        .sub-menu {
            list-style: none;
            padding-left: 0;
            background: rgba(0, 0, 0, 0.12);
        }

        .sub-menu li a {
            padding: 0.6rem 1.5rem 0.6rem 2.8rem !important;
            font-size: 0.88rem;
            border-left: none !important;
        }

        .sub-menu li a i {
            font-size: 0.95rem !important;
            color: rgba(255, 255, 255, 0.5) !important;
        }

        .sub-menu li.active > a,
        .sub-menu li a:hover {
            background: rgba(255, 255, 255, 0.05) !important;
            color: #ffffff !important;
        }

        .sub-menu li.active > a i {
            color: var(--warm-peach) !important;
        }

        .submenu-arrow {
            transition: transform 0.3s ease;
            font-size: 0.8rem;
        }

        a:not(.collapsed) .submenu-arrow {
            transform: rotate(180deg);
        }

        /* Premium UI Components */
        .card-stats {
            border: none;
            border-radius: 1.5rem;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
        }

        .btn-custom-primary {
            background: linear-gradient(100deg, #5C1A2E 0%, #8C7355 100%);
            color: #ffffff;
            border: none;
            border-radius: 2px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            box-shadow: 0 4px 10px rgba(64, 17, 31, 0.18);
            transition: all 0.2s;
        }

        .btn-custom-primary:hover {
            background: linear-gradient(100deg, #45121F 0%, #6E5A42 100%);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(64, 17, 31, 0.25);
        }

        .badge-active {
            background-color: rgba(140, 115, 85, 0.12);
            color: #6E5A42;
            font-weight: 600;
        }

        .badge-inactive {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            font-weight: 600;
        }

        /* ===== Mobile responsiveness ===== */
        #sidebarToggle { display: none; }

        #sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
            z-index: 999;
        }

        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                left: calc(-1 * var(--sidebar-width));
            }

            #page-content-wrapper {
                margin-left: 0;
                width: 100%;
            }

            #sidebarToggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                color: var(--deep-forest);
                min-width: 40px;
                min-height: 40px;
            }

            #sidebar-wrapper.mobile-open {
                left: 0;
                box-shadow: 8px 0 30px rgba(0, 0, 0, 0.25);
            }

            body.sidebar-open #sidebar-backdrop {
                opacity: 1;
                visibility: visible;
            }

            .main-header {
                padding: 0 1rem;
                height: 60px;
            }

            .main-content {
                padding: 1rem;
            }

            .footer {
                padding: 1rem;
            }

            /* keep data tables usable: horizontal scroll inside their card */
            .table-responsive,
            .dataTables_wrapper {
                max-width: 100%;
            }

            .card-stats .h3,
            .card-stats h3 {
                font-size: 1.35rem;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    @include('front.layouts.partials.icons')


<div id="sidebar-backdrop"></div>

<div id="wrapper">
    <!-- Sidebar -->
    @include('admin.layouts.sidebar')

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <!-- Top Navbar -->
        @include('admin.layouts.header')

        <!-- Main Content -->
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 card-custom-shadow mb-4" role="alert">
                    <i class="fa-solid fa-circle-check text-success me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 card-custom-shadow mb-4" role="alert">
                    <i class="fa-solid fa-circle-exclamation text-danger me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Footer -->
        @include('admin.layouts.footer')
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<!-- DataTables Bootstrap 5 JS -->
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
@yield('scripts')
<script>
    (function () {
        var sidebar = document.getElementById('sidebar-wrapper');
        var toggle = document.getElementById('sidebarToggle');
        var backdrop = document.getElementById('sidebar-backdrop');
        if (!sidebar || !toggle) return;

        function close() {
            sidebar.classList.remove('mobile-open');
            document.body.classList.remove('sidebar-open');
        }

        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('mobile-open');
            document.body.classList.toggle('sidebar-open');
        });

        if (backdrop) backdrop.addEventListener('click', close);
        sidebar.addEventListener('click', function (e) {
            if (e.target.closest('a')) close();
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) close();
        });
    })();
</script>
</body>
</html>