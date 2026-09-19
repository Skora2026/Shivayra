<!-- Top Bar -->
<section class="nav-top-section">
    <div class="nav-top">
        <div class="marquee">
            <span><svg class="icon"><use href="#i-bag"/></svg> Mega Shopping Sale</span>
            <span><svg class="icon"><use href="#i-fire"/></svg> Flat 50% OFF</span>
            <span><svg class="icon"><use href="#i-truck"/></svg> Free Shipping Above ₹999</span>
            <span><svg class="icon"><use href="#i-sparkle"/></svg> New Arrivals</span>
            <span><svg class="icon"><use href="#i-star"/></svg> Extra 10% on Prepaid</span>
        </div>
    </div>
    <div id="toast"><svg class="icon"><use href="#i-check"/></svg> Added to cart</div>
    <div id="wishToast"><svg class="icon"><use href="#i-heart-fill"/></svg> Wishlist Updated</div>
</section>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg bg-white shadow-sm py-2" id="mainNavbar">
    <div class="container">
        <!-- Logo -->
        <a href="{{ url('/') }}" class="navbar-brand">
            <img src="{{ $settings?->logo ? asset('storage/' . $settings->logo) : asset('images/logo2.jpeg') }}" height="55" alt="{{ $settings?->site_name ?? 'Shivayra' }} logo">
        </a>


        <!-- Mobile Icons + Menu Toggle -->
        <div class="mobile-only d-flex align-items-center gap-3">
            <div class="cart-fab position-relative" id="mobileCartIcon">
                <i class="bi bi-cart-fill fs-5 text-dark"></i>
                <span id="mcartCount" class="badge-count">0</span>
            </div>
            <div class="position-relative" id="mobileWishlistIcon">
                <i class="bi bi-heart-fill fs-5" style="color: var(--ruby);"></i>
                <span id="mwishlistCount" class="badge-count">0</span>
            </div>
            <button class="navbar-toggler border-0" type="button" id="mobileMenuToggle">
                <i class="bi bi-list fs-2"></i>
            </button>
        </div>

        <!-- Desktop Menu -->
        <div class="collapse navbar-collapse desktop-only" id="navbarMain">
            <ul class="navbar-nav mx-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Home</a></li>
                <li class="nav-item dropdown mega-dropdown">
                    <a class="nav-link dropdown-toggle" href="#">Categories</a>
                    <div class="dropdown-menu mega-menu p-4">
                        <div class="row g-4">
                            @foreach($headerCategories as $cat)
                                <div class="col-md-3 mega-category-col">
                                    <div class="mega-category-title">{{ $cat->name }}</div>
                                    <div class="subcat-group">
                                        @foreach($cat->subCategories as $sub)
                                            <a class="dropdown-item" href="{{ route('products') }}?subcategory={{ $sub->slug }}">
                                                {{ $sub->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </li>
                <li class="nav-item"><a class="nav-link" href="{{ route('products') }}?filter=new_arrivals">New Arrivals</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('products') }}">Shop</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <form action="{{ route('products') }}" method="GET" class="search-form d-none d-lg-block ms-3">
                    <div class="search-container d-flex align-items-center rounded-pill px-3 py-2" 
                         style="width: 320px; background-color: #f8fafc; border: 1.5px solid rgba(10, 144, 81, 0.2); transition: all 0.3s ease;">
                        <i class="bi bi-search me-2" style="font-size: 0.95rem; color: var(--warm-peach);"></i>
                        <input type="text" name="search" id="headerSearchInput" class="form-control border-0 bg-transparent p-0 m-0" 
                               placeholder="Search products..." 
                               value="{{ request()->get('search') }}"
                               style="font-size: 0.9rem; outline: none; box-shadow: none; width: 100%; color: #1a2e24;">
                    </div>
                </form>
                <div class="cart-fab position-relative" id="desktopCartIcon"><i class="bi bi-cart-fill fs-5"></i><span
                        id="lcartCount" class="badge-count">0</span></div>
                <a href="{{ route('wishlist') }}" class="position-relative" id="desktopWishlistIcon"><i
                        class="bi bi-heart-fill fs-5" style="color: var(--ruby);"></i><span id="lwishlistCount"
                        class="badge-count">0</span></a>
                @guest
                    <a href="{{ route('login') }}" class="btn btn-outline-success rounded-pill px-3" id="accountBtn">Sign In</a>
                @else
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary rounded-pill px-3 dropdown-toggle user-chip" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-fill me-1"></i> {{ auth()->user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2" aria-labelledby="userMenu">
                            @if(auth()->user()->hasRole('admin'))
                                <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge me-2"></i> Admin Panel</a></li>
                                <li><hr class="dropdown-divider"></li>
                            @endif
                            <li><a class="dropdown-item py-2" href="{{ route('my-account') }}"><i class="bi bi-person-circle me-2"></i> My Account</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </div>
                @endguest
            </div>
        </div>
    </div>

    <!-- Mobile Search -->
    <div class="mobile-only w-100 px-3 pb-2">
        <div class="search-wrapper w-100">
            <i class="bi bi-search"></i>
            <input type="text" placeholder="Search products..." id="mobileSearch">
        </div>
    </div>
</nav>

<!-- Mobile Offcanvas Menu -->
<div class="offcanvas-overlay" id="offcanvasOverlay"></div>
<div class="offcanvas-mobile" id="mobileOffcanvas">
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <h5 class="mb-0 icon-inline"><svg class="icon"><use href="#i-bag"/></svg> Menu</h5>
        <i class="bi bi-x-lg fs-4" id="closeOffcanvasBtn" style="cursor:pointer"></i>
    </div>
    <ul class="mobile-menu-list">
        <li><a href="{{ url('/') }}"><span><i class="bi bi-house-door"></i> Home</span></a></li>
        <li><a href="{{ route('about_us') }}"><span><i class="bi bi-info-circle"></i> About</span></a></li>

        <!-- Shop by Category - DROPDOWN MENU (Mega menu on mobile) -->
        <li class="mobile-dropdown-item">
            <div class="mobile-dropdown-header" id="categoryDropdownHeader">
                <span><i class="bi bi-grid-3x3-gap-fill"></i>Categories</span>
                <i class="bi bi-chevron-down"></i>
            </div>
            <ul class="mobile-submenu" id="categorySubmenu">
                @foreach($headerCategories as $cat)
                    <li class="mobile-parent">
                        <div class="mobile-parent-head">
                            <span>{{ $cat->name }}</span>
                            <i class="bi bi-plus"></i>
                        </div>
                        <ul class="mobile-child">
                            @foreach($cat->subCategories as $sub)
                                <li><a href="{{ route('products') }}?subcategory={{ $sub->slug }}">{{ $sub->name }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </li>

        <li><a href="{{ route('products') }}?filter=new_arrivals"><span><i class="bi bi-stars"></i> New Arrivals</span></a></li>
        <li><a href="{{ route('products') }}"><span><i class="bi bi-bag"></i> Shop All</span></a></li>
        <li><a href="{{ route('wishlist') }}"><span><i class="bi bi-heart"></i> Wishlist</span></a></li>
        @guest
            <li><a href="{{ route('login') }}"><span><i class="bi bi-person-circle"></i> Sign In</span></a></li>
        @else
            @if(auth()->user()->hasRole('admin'))
                <li><a href="{{ route('admin.dashboard') }}"><span><i class="bi bi-shield-lock"></i> Admin Panel</span></a></li>
            @endif
            <li><a href="{{ route('my-account') }}"><span><i class="bi bi-person-circle"></i> My Account</span></a></li>
            <li>
                <a href="javascript:void(0);" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
                    <span><i class="bi bi-box-arrow-right text-danger"></i> Logout</span>
                </a>
                <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </li>
        @endguest
        </li>
    </ul>
</div>

<!-- Cart Panel -->
<div class="cart-panel" id="cartPanel">
    <div class="cart-header">
        <h5 class="m-0 icon-inline"><svg class="icon"><use href="#i-cart"/></svg> Your Cart</h5>
        <span class="cart-close" onclick="closeCart()" aria-label="Close cart"><svg class="icon" style="width:1.2em;height:1.2em;vertical-align:-.25em"><use href="#i-close"/></svg></span>
    </div>
    <div id="cartItems"></div>
    <div class="cart-footer mt-auto pt-3">
        <div class="d-flex justify-content-between fw-bold mb-2">
            <span>Total:</span>
            <span>₹ <span id="cartTotal">0</span></span>
        </div>
        <a href="{{ route('checkout') }}"><button class="checkout-btn">Checkout <svg class="icon"><use href="#i-arrow"/></svg></button></a>
    </div>
</div>
