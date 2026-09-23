<div id="sidebar-wrapper">
    <div class="sidebar-brand d-flex align-items-center justify-content-center">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
            @if($settings?->logo)
                <img src="{{ asset('storage/' . $settings->logo) }}" width="132" alt="Logo" class="rounded-1">
            @else
                <img src="{{ asset('images/logo2.jpeg') }}" width="132" alt="Logo" class="rounded-1">
            @endif
            {{-- <span>{{ $settings?->site_name ?? 'Shivayra' }}</span> --}}
        </a>
    </div>

    <ul class="sidebar-menu">
        {{-- Dashboard --}}
        <li class="{{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-chart-line"></i>
                <span>{{ __('labels.dashboard') }}</span>
            </a>
        </li>

        {{-- Sales --}}
        <li class="{{ Request::routeIs('admin.orders.*') ? 'active' : '' }}">
            <a href="{{ route('admin.orders.index') }}">
                <i class="fa-solid fa-cart-shopping"></i>
                <span>Orders</span>
            </a>
        </li>

        {{-- Catalog --}}
        @php
            $productActive = Request::routeIs('admin.categories.*') || Request::routeIs('admin.sub-categories.*') || Request::routeIs('admin.products.*');
        @endphp
        <li class="{{ $productActive ? 'active' : '' }}">
            <a href="#product-menu" data-bs-toggle="collapse" class="d-flex align-items-center justify-content-between {{ $productActive ? '' : 'collapsed' }}" aria-expanded="{{ $productActive ? 'true' : 'false' }}">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-boxes-stacked"></i>
                    <span>{{ __('labels.product_management') }}</span>
                </div>
                <i class="fa-solid fa-chevron-down submenu-arrow"></i>
            </a>
            <div class="collapse {{ $productActive ? 'show' : '' }}" id="product-menu">
                <ul class="sub-menu">
                    <li class="{{ Request::routeIs('admin.products.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.products.index') }}">
                            <i class="fa-solid fa-box-open"></i>
                            <span>{{ __('labels.products') }}</span>
                            @php
                                $lowStockCount = \App\Models\Product::where('status', 'active')->where('stock', '<=', 5)->count();
                            @endphp
                            @if($lowStockCount > 0)
                                <span class="badge rounded-pill ms-auto" style="background:#C9A96A;color:#40111F;font-size:.62rem;">{{ $lowStockCount }} low</span>
                            @endif
                        </a>
                    </li>
                    <li class="{{ Request::routeIs('admin.categories.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.categories.index') }}">
                            <i class="fa-solid fa-tags"></i>
                            <span>{{ __('labels.categories') }}</span>
                        </a>
                    </li>
                    <li class="{{ Request::routeIs('admin.sub-categories.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.sub-categories.index') }}">
                            <i class="fa-solid fa-sitemap"></i>
                            <span>{{ __('labels.sub_categories') }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        {{-- Customers --}}
        <li class="{{ Request::routeIs('admin.users.*') ? 'active' : '' }}">
            <a href="{{ route('admin.users.index') }}">
                <i class="fa-solid fa-users"></i>
                <span>{{ __('labels.users') }}</span>
            </a>
        </li>

        {{-- Site Content --}}
        <li class="{{ Request::routeIs('admin.banners.*') ? 'active' : '' }}">
            <a href="{{ route('admin.banners.index') }}">
                <i class="fa-solid fa-images"></i>
                <span>{{ __('labels.banners') }}</span>
            </a>
        </li>
        <li class="{{ Request::routeIs('admin.home-sections.*') ? 'active' : '' }}">
            <a href="{{ route('admin.home-sections.edit') }}">
                <i class="fa-solid fa-house-flag"></i>
                <span>Home Sections</span>
            </a>
        </li>
        <li class="{{ Request::routeIs('admin.reviews.*') ? 'active' : '' }}">
            <a href="{{ route('admin.reviews.index') }}">
                <i class="fa-solid fa-star"></i>
                <span>Reviews</span>
            </a>
        </li>
        <li class="{{ Request::routeIs('admin.returns.*') ? 'active' : '' }}">
            <a href="{{ route('admin.returns.index') }}">
                <i class="fa-solid fa-rotate-left"></i>
                <span>Returns</span>
                @php
                    // Awaiting owner action: pending = decide, approved = refund.
                    $actionableReturns = \App\Models\ReturnRequest::whereIn('status', ['pending', 'approved'])->count();
                @endphp
                @if($actionableReturns > 0)
                    <span class="badge rounded-pill ms-auto" style="background:#C9A96A;color:#40111F;font-size:.62rem;">{{ $actionableReturns }} to do</span>
                @endif
            </a>
        </li>
        {{-- Settings --}}
        <li class="{{ Request::routeIs('admin.settings.*') ? 'active' : '' }}">
            <a href="{{ route('admin.settings.index') }}">
                <i class="fa-solid fa-gear"></i>
                <span>{{ __('labels.settings') }}</span>
            </a>
        </li>

        <div class="menu-header">{{ __('labels.quick_access') }}</div>
        <li>
            <a href="/" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>{{ __('labels.storefront') }}</span>
            </a>
        </li>

        
    </ul>
</div>
