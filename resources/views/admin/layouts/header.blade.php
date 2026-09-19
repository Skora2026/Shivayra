<header class="main-header shadow-sm">
    <div class="d-flex align-items-center gap-2">
        <button id="sidebarToggle" class="btn btn-link text-decoration-none p-1" type="button" aria-label="Toggle sidebar" aria-controls="sidebar-wrapper">
            <i class="fa-solid fa-bars"></i>
        </button>
        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-dark">
            <h4 class="mb-0 fw-700 text-dark">{{ __('labels.admin_panel') }}</h4>
        </a>
    </div>
    
    <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <button class="btn btn-link text-decoration-none dropdown-toggle text-dark d-flex align-items-center gap-2 fw-600" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                @if(Auth::user() && Auth::user()->profile_pic)
                    <img src="{{ asset('storage/' . Auth::user()->profile_pic) }}" alt="Profile" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;">
                @else
                    <i class="fa-solid fa-circle-user fs-4" style="color: var(--warm-peach);"></i>
                @endif
                <span>{{ Auth::user() ? Auth::user()->name : 'Admin' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 rounded-3" aria-labelledby="userMenuDropdown">
                <li>
                    <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="{{ route('admin.profile.edit') }}">
                        <i class="fa-solid fa-user-gear text-secondary"></i>
                        <span>{{ __('labels.edit_profile') }}</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2" href="/">
                        <i class="fa-solid fa-globe text-secondary"></i>
                        <span>{{ __('labels.visit_website') }}</span>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item py-2 px-3 text-danger d-flex align-items-center gap-2" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>{{ config('button.logout') }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

<form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
    @csrf
</form>
