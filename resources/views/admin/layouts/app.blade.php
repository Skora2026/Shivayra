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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            --burgundy-hover: #45121F;
            --vibrant-mint: #8C7355;
            --ember-brown: #1C1C1C;
            --bg-light: #FAF7F2;
            --sidebar-width: 260px;
            --header-height: 70px;
            --font-body: 'Montserrat', sans-serif;
            --font-display: 'Cormorant Garamond', Georgia, serif;
            --gold-hairline: rgba(176, 141, 87, 0.35);
        }

        * {
            font-family: var(--font-body);
        }

        body {
            font-family: var(--font-body);
            background-color: var(--bg-light);
            margin: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-display) !important;
            font-weight: 500;
            letter-spacing: 0.01em;
        }

        #wrapper {
            display: flex;
            min-height: 100vh;
        }

        #sidebar-wrapper {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #40111F 0%, #2A0C14 100%);
            color: #ffffff;
            transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            position: fixed;
            height: 100vh;
            z-index: 1000;
            overflow-y: auto;
            overflow-x: hidden;
            /* Wheel scrolling must stay inside the sidebar — chaining to the
               page behind while the pointer is over the menu feels broken. */
            overscroll-behavior: contain;
        }

        /* The dark scrollbar on the burgundy gradient is invisible — give it a
           visible, branded thumb so long menus can always be scrolled. */
        #sidebar-wrapper::-webkit-scrollbar {
            width: 6px;
        }

        #sidebar-wrapper::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.04);
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: rgba(201, 169, 110, 0.45);
            border-radius: 3px;
        }

        #sidebar-wrapper {
            scrollbar-width: thin;
            scrollbar-color: rgba(201, 169, 110, 0.45) rgba(255, 255, 255, 0.04);
        }

        #page-content-wrapper {
            width: 100%;
            min-width: 0;
            margin-left: var(--sidebar-width);
            transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-header {
            height: var(--header-height);
            background: #ffffff;
            border-bottom: 1px solid var(--gold-hairline);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .main-content {
            padding: 2rem 2.5rem;
            flex-grow: 1;
        }

        .footer {
            background-color: #ffffff;
            border-top: 1px solid var(--gold-hairline);
            padding: 1rem 2.5rem;
            text-align: center;
            font-size: 0.82rem;
            color: var(--vibrant-mint);
            letter-spacing: 0.5px;
        }

        /* ================================================
           SIDEBAR — refined navigation
           ================================================ */

        .sidebar-brand {
            padding: 1.8rem 1.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand a {
            color: #ffffff;
            text-decoration: none;
        }

        .sidebar-menu {
            list-style: none;
            padding: 1.2rem 0;
            margin: 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 0.85rem 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            border-left: 3px solid transparent;
            gap: 12px;
            font-size: 0.9rem;
            letter-spacing: 0.3px;
        }

        .sidebar-menu li a:hover,
        .sidebar-menu li.active > a {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.06);
            border-left-color: var(--warm-peach);
        }

        .sidebar-menu li a i {
            width: 20px;
            font-size: 1rem;
            color: var(--warm-peach);
        }

        .menu-header {
            padding: 0.6rem 1.5rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.35);
            font-weight: 700;
            margin-top: 1.2rem;
        }

        .sub-menu {
            list-style: none;
            padding-left: 0;
            background: rgba(0, 0, 0, 0.15);
        }

        .sub-menu li a {
            padding: 0.65rem 1.5rem 0.65rem 2.8rem !important;
            font-size: 0.85rem;
            border-left: none !important;
        }

        .sub-menu li a i {
            font-size: 0.9rem !important;
            color: rgba(255, 255, 255, 0.45) !important;
        }

        .sub-menu li.active > a,
        .sub-menu li a:hover {
            background: rgba(255, 255, 255, 0.06) !important;
            color: #ffffff !important;
        }

        .sub-menu li.active > a i {
            color: var(--warm-peach) !important;
        }

        .submenu-arrow {
            transition: transform 0.3s ease;
            font-size: 0.75rem;
        }

        a:not(.collapsed) .submenu-arrow {
            transform: rotate(180deg);
        }

        /* ================================================
           PREMIUM UI COMPONENTS
           ================================================ */

        .card-stats {
            border: none;
            border-radius: 4px;
            background: #ffffff;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
            transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
            border: 1px solid var(--gold-hairline);
        }

        .card-stats:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.06);
        }

        .btn-custom-primary {
            background: var(--burgundy);
            color: #ffffff;
            border: none;
            border-radius: 2px;
            padding: 0.6rem 1.8rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            box-shadow: 0 4px 12px rgba(64, 17, 31, 0.18);
            transition: all 0.3s ease;
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .btn-custom-primary:hover {
            background: var(--burgundy-hover);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(64, 17, 31, 0.25);
        }

        .badge-active {
            background-color: rgba(140, 115, 85, 0.12);
            color: var(--vibrant-mint);
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 2px;
            padding: 0.3rem 0.8rem;
        }

        .badge-inactive {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 2px;
            padding: 0.3rem 0.8rem;
        }

        /* ================================================
           TABLES — refined data presentation
           ================================================ */

        .table {
            border-color: var(--gold-hairline);
        }

        .table thead th {
            background: var(--bg-light);
            border-bottom: 2px solid var(--gold-hairline);
            font-weight: 600;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: var(--ember-brown);
            padding: 12px 16px;
        }

        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid rgba(176, 141, 87, 0.15);
            font-size: 0.88rem;
        }

        .table tbody tr:hover {
            background-color: rgba(176, 141, 87, 0.04);
        }

        /* ================================================
           ICON SAFETY + REFINEMENT
           ================================================ */

        /* The shared icon sprite is display:none by design — it must never
           take layout space or trigger a scrollbar in the admin panel. */
        body > svg[style*="display:none"],
        body > svg:not([class]) {
            width: 0 !important;
            height: 0 !important;
            position: absolute !important;
            overflow: hidden;
        }

        /* Admin has no .icon default — give sprite icons a sane size and
           vertical alignment wherever they appear (links, buttons, cells). */
        svg.icon {
            width: 1em;
            height: 1em;
            flex: 0 0 auto;
            vertical-align: -0.125em;
        }

        .icon-inline svg.icon {
            width: 15px;
            height: 15px;
            transition: transform 0.25s ease;
        }

        .icon-inline:hover svg.icon {
            transform: translateX(3px);
        }

        /* ================================================
           DATATABLES — refined chrome (sort arrows, pagination)
           ================================================ */

        /* Replace the clunky unicode ▲/▼ sort markers with refined chevrons */
        table.dataTable thead .sorting::before,
        table.dataTable thead .sorting_asc::before,
        table.dataTable thead .sorting_desc::before,
        table.dataTable thead .sorting::after,
        table.dataTable thead .sorting_asc::after,
        table.dataTable thead .sorting_desc::after {
            display: none !important;
            content: none !important;
        }

        table.dataTable thead .sorting,
        table.dataTable thead .sorting_asc,
        table.dataTable thead .sorting_desc {
            position: relative;
            padding-right: 26px !important;
            cursor: pointer;
        }

        table.dataTable thead .sorting::before,
        table.dataTable thead .sorting_asc::before,
        table.dataTable thead .sorting_desc::before {
            display: block !important;
            content: "" !important;
            position: absolute !important;
            right: 10px;
            top: 50%;
            width: 7px;
            height: 7px;
            border-right: 1.6px solid rgba(64, 17, 31, 0.35);
            border-bottom: 1.6px solid rgba(64, 17, 31, 0.35);
            transform: translateY(-50%) rotate(45deg);
            opacity: 0.55;
            transition: opacity 0.2s ease, border-color 0.2s ease;
        }

        table.dataTable thead .sorting_asc::before {
            transform: translateY(-80%) rotate(-135deg);
            border-color: var(--warm-peach);
            opacity: 1;
        }

        table.dataTable thead .sorting_desc::before {
            transform: translateY(-20%) rotate(45deg);
            border-color: var(--warm-peach);
            opacity: 1;
        }

        table.dataTable thead .sorting:hover::before {
            opacity: 1;
            border-color: rgba(64, 17, 31, 0.7);
        }

        /* Pagination: chevron arrows instead of Previous/Next text */
        .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next {
            color: transparent !important;
            position: relative;
            width: 34px;
            text-align: center;
            overflow: hidden;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.previous::before,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next::before {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            width: 8px;
            height: 8px;
            border-left: 1.6px solid currentColor;
            border-bottom: 1.6px solid currentColor;
            transform: translate(-50%, -50%) rotate(45deg);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.previous::before {
            transform: translate(-30%, -50%) rotate(45deg);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.next::before {
            transform: translate(-70%, -50%) rotate(225deg);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 2px !important;
            font-weight: 600;
            letter-spacing: 0.4px;
            min-width: 34px;
            text-align: center;
        }

        /* Touch targets: checkboxes/switches tappable-size on mobile */
        @media (max-width: 767.98px) {
            .form-check-input {
                width: 2.4rem;
                height: 1.4rem;
                flex: 0 0 auto;
            }

            .form-check-input[type="checkbox"] {
                width: 1.5rem;
                height: 1.5rem;
            }
        }

        /* Yajra DataTables tables render without a scroll wrapper on some
           versions — guarantee one so wide tables never clip the page. */
        .dt-card > .dt-card-body,
        .card-stats > .table-responsive {
            max-width: 100%;
        }

        .dt-card .table {
            min-width: 0;
        }

        /* ================================================
           FORMS — refined input treatment
           ================================================ */

        .form-control,
        .form-select {
            border-radius: 2px;
            border: 1px solid rgba(176, 141, 87, 0.3);
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--warm-peach);
            box-shadow: 0 0 0 3px rgba(176, 141, 87, 0.1);
        }

        /* ================================================
           ALERTS — refined
           ================================================ */

        .alert {
            border-radius: 4px;
            font-size: 0.9rem;
            border: none;
            padding: 1rem 1.2rem;
        }

        .alert-success {
            background: rgba(140, 115, 85, 0.1);
            color: var(--vibrant-mint);
            border-left: 3px solid var(--vibrant-mint);
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.08);
            color: #dc3545;
            border-left: 3px solid #dc3545;
        }

        /* ================================================
           INLINE SVG ICONS — sized (SVG default is 300x150!)
           ================================================ */

        .icon-inline {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            color: var(--burgundy);
            font-weight: 600;
            font-size: 0.85rem;
        }

        .icon-inline:hover {
            color: var(--burgundy-hover);
        }

        svg.icon,
        .icon-inline .icon {
            width: 16px;
            height: 16px;
            flex: 0 0 16px;
        }

        /* ================================================
           INPUT GROUPS — number inputs must stay readable
           (Bootstrap gives inputs width:1% + flex, so a long
           suffix like "days after delivery" can crush them)
           ================================================ */

        .input-group .form-control[type="number"] {
            flex: 1 1 auto;
            width: auto;
            min-width: 90px;
        }

        .input-group .input-group-text {
            white-space: nowrap;
        }

        /* ================================================
           VARIANT CARDS — two-row layout, every field fully visible
           ================================================ */

        .variant-labels-strip {
            display: grid;
            grid-template-columns: 1fr 1fr 1.8fr;
            gap: 1rem;
            background: var(--bg-light);
            border: 1px solid var(--gold-hairline);
            border-radius: 4px;
            padding: 0.9rem 1rem;
        }

        .variant-card {
            background: #ffffff;
            border: 1px solid var(--gold-hairline);
            border-radius: 4px;
            margin-bottom: 1rem;
            overflow: hidden;
            transition: box-shadow 0.25s ease;
        }

        .variant-card:hover {
            box-shadow: 0 8px 20px rgba(64, 17, 31, 0.06);
        }

        .variant-card__head {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.6rem 1rem;
            background: var(--bg-light);
            border-bottom: 1px solid var(--gold-hairline);
        }

        .variant-card__num {
            font-weight: 700;
            font-size: 0.78rem;
            color: var(--warm-peach);
            letter-spacing: 0.5px;
        }

        .variant-card__title {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--ember-brown);
            letter-spacing: 0.3px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .variant-card__row {
            display: grid;
            gap: 1rem;
            padding: 1rem;
            align-items: start;
        }

        .variant-card__row--identity {
            grid-template-columns: 1.2fr 1fr 1.2fr;
            border-bottom: 1px dashed rgba(176, 141, 87, 0.25);
        }

        .variant-card__row--pricing {
            grid-template-columns: 1fr 1fr 0.7fr 1.6fr;
            background: rgba(250, 247, 242, 0.5);
        }

        .vlabel {
            display: block;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--vibrant-mint);
            margin-bottom: 0.35rem;
        }

        .vfield--tight input {
            max-width: 110px;
        }

        .vfield--wide input[type="file"] {
            font-size: 0.8rem;
        }

        /* ================================================
           MOBILE RESPONSIVENESS
           ================================================ */

        #sidebarToggle { display: none; }

        #sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease;
            z-index: 999;
        }

        @media (max-width: 767.98px) {
            .variant-labels-strip {
                grid-template-columns: 1fr;
            }

            .variant-card__row--identity,
            .variant-card__row--pricing {
                grid-template-columns: 1fr;
                gap: 0.8rem;
            }

            .vfield--tight input {
                max-width: none;
            }
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
                font-size: 1.2rem;
                color: var(--deep-forest);
                min-width: 44px;
                min-height: 44px;
            }

            #sidebar-wrapper.mobile-open {
                left: 0;
                box-shadow: 8px 0 30px rgba(0, 0, 0, 0.2);
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

            .table-responsive,
            .dataTables_wrapper {
                max-width: 100%;
            }

            .card-stats .h3,
            .card-stats h3 {
                font-size: 1.3rem;
            }

            .main-header h4 {
                font-size: 1.05rem;
            }

            .main-header .btn-link span {
                max-width: 90px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
                display: inline-block;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 150px !important;
                margin-left: 0 !important;
            }

            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_paginate {
                text-align: left !important;
                justify-content: flex-start;
            }

            .table td .btn-group {
                flex-wrap: wrap;
                gap: 0.25rem;
            }

            .btn-custom-primary {
                width: 100%;
            }

            /* let items of wrapping flex rows shrink + wrap instead of overflowing */
            .main-content .d-flex.flex-wrap > * {
                min-width: 0;
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
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    <strong>Please fix the following before saving:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $err)
                            @if($loop->iteration <= 6)
                                <li>{{ $err }}</li>
                            @endif
                        @endforeach
                        @if($errors->count() > 6)
                            <li>…and {{ $errors->count() - 6 }} more</li>
                        @endif
                    </ul>
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

{{-- Drag & drop for every image input in the admin panel. Included here so any
     upload field on any admin page is a drop target with no per-page wiring. --}}
@include('admin.partials.dropzone-script')

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

        // When a submenu (e.g. Product Management) expands near the bottom of a
        // short window, keep its items on screen by scrolling them into view.
        sidebar.querySelectorAll('.sidebar-menu a[data-bs-toggle="collapse"]').forEach(function (t) {
            t.addEventListener('shown.bs.collapse', function () {
                var target = document.querySelector(t.getAttribute('href'));
                if (!target) return;
                var tb = target.getBoundingClientRect();
                var sb = sidebar.getBoundingClientRect();
                if (tb.bottom > sb.bottom) {
                    sidebar.scrollTo({ top: sidebar.scrollTop + (tb.bottom - sb.bottom) + 12, behavior: 'smooth' });
                }
            });
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 992) close();
        });
    })();
</script>
</body>
</html>
