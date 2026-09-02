<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>@yield('title', 'Admin Panel | Pickleball Hub')</title>

    <!-- BOOTSTRAP 5 & ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/css/owner.css', 'resources/css/admin.css'])
    @stack('styles')
</head>

<body class="bg-light text-dark font-sans">

    <!-- =========================================
         ADMIN NAVBAR
    ========================================== -->
    <nav class="admin-navbar py-2">
        <div class="container-fluid px-3 px-lg-4 d-flex align-items-center justify-content-between">

            <!-- LEFT: HAMBURGER (MOBILE) + BRAND LOGO -->
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-light border d-lg-none rounded-circle p-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileMenu" aria-controls="adminMobileMenu">
                    <i class="bi bi-list fs-5"></i>
                </button>

                <a href="{{ route('admin.dashboard') }}" class="admin-brand d-flex align-items-center gap-2">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Pickleball Hub">
                    <span class="admin-brand-badge">ADMIN MASTER</span>
                </a>
            </div>

            <!-- DESKTOP NAV LINKS (DESKTOP / LARGE SCREENS) -->
            <div class="admin-nav-links-scroll d-none d-lg-flex align-items-center mx-3">
                <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.owner-applications') }}" class="admin-nav-link position-relative {{ request()->routeIs('admin.owner-applications') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-check"></i>
                    <span>Applications</span>
                    <span id="navPendingBadge" class="badge bg-warning text-dark rounded-pill ms-1 d-none">0</span>
                </a>
                <a href="{{ route('admin.courts') }}" class="admin-nav-link {{ request()->routeIs('admin.courts') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    <span>Courts</span>
                </a>
                <a href="{{ route('admin.users') }}" class="admin-nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Users & Owners</span>
                </a>
                <a href="{{ route('admin.banners') }}" class="admin-nav-link {{ request()->routeIs('admin.banners') ? 'active' : '' }}">
                    <i class="bi bi-images"></i>
                    <span>Banners</span>
                </a>
                <a href="{{ route('admin.bookings') }}" class="admin-nav-link {{ request()->routeIs('admin.bookings') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check-fill"></i>
                    <span>Bookings</span>
                </a>
                <a href="{{ route('admin.payouts') }}" class="admin-nav-link {{ request()->routeIs('admin.payouts') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Payouts</span>
                </a>
                <a href="{{ route('admin.reviews') }}" class="admin-nav-link {{ request()->routeIs('admin.reviews') ? 'active' : '' }}">
                    <i class="bi bi-star-fill text-warning"></i>
                    <span>Reviews</span>
                </a>
                <a href="{{ route('admin.tournaments') }}" class="admin-nav-link {{ request()->routeIs('admin.tournaments') ? 'active' : '' }}">
                    <i class="bi bi-trophy-fill text-warning"></i>
                    <span>Tournaments</span>
                </a>
            </div>

            <!-- RIGHT PROFILE & LOGOUT -->
            <div class="dropdown">
                <button class="btn admin-profile-btn dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="admin-avatar">
                        A
                    </div>
                    <div class="text-start d-none d-sm-block">
                        <span id="adminNavbarName" class="fw-bold d-block lh-1 text-dark small">System Admin</span>
                        <small class="text-muted fs-8">Master Administrator</small>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
                    <li>
                        <button id="adminLogoutBtn" class="dropdown-item py-2 text-danger" type="button">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </button>
                    </li>
                </ul>
            </div>

        </div>
    </nav>

    <!-- =========================================
         OFFCANVAS MOBILE DRAWER MENU
    ========================================== -->
    <div class="offcanvas offcanvas-start admin-offcanvas" tabindex="-1" id="adminMobileMenu" aria-labelledby="adminMobileMenuLabel">
        <div class="offcanvas-header border-bottom p-3">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Pickleball Hub" style="height: 38px;">
                <span class="admin-brand-badge">ADMIN</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-3">
            <div class="d-flex flex-column gap-1">
                <a href="{{ route('admin.dashboard') }}" class="nav-link-mobile {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.owner-applications') }}" class="nav-link-mobile justify-content-between {{ request()->routeIs('admin.owner-applications') ? 'active' : '' }}">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-check"></i>
                        <span>Owner Applications</span>
                    </div>
                    <span id="mobileNavPendingBadge" class="badge bg-warning text-dark rounded-pill d-none">0</span>
                </a>
                <a href="{{ route('admin.courts') }}" class="nav-link-mobile {{ request()->routeIs('admin.courts') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    <span>Master Courts</span>
                </a>
                <a href="{{ route('admin.users') }}" class="nav-link-mobile {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                    <i class="bi bi-people-fill"></i>
                    <span>Users & Owners</span>
                </a>
                <a href="{{ route('admin.banners') }}" class="nav-link-mobile {{ request()->routeIs('admin.banners') ? 'active' : '' }}">
                    <i class="bi bi-images"></i>
                    <span>Banners & Promos</span>
                </a>
                <a href="{{ route('admin.bookings') }}" class="nav-link-mobile {{ request()->routeIs('admin.bookings') ? 'active' : '' }}">
                    <i class="bi bi-calendar2-check-fill"></i>
                    <span>Master Bookings</span>
                </a>
                <a href="{{ route('admin.payouts') }}" class="nav-link-mobile {{ request()->routeIs('admin.payouts') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Payout Settlements</span>
                </a>
                <a href="{{ route('admin.reviews') }}" class="nav-link-mobile {{ request()->routeIs('admin.reviews') ? 'active' : '' }}">
                    <i class="bi bi-star-fill text-warning"></i>
                    <span>Master Reviews</span>
                </a>
                <a href="{{ route('admin.tournaments') }}" class="nav-link-mobile {{ request()->routeIs('admin.tournaments') ? 'active' : '' }}">
                    <i class="bi bi-trophy-fill text-warning"></i>
                    <span>Master Tournaments</span>
                </a>
            </div>
        </div>
    </div>

    <!-- =========================================
         MAIN CONTENT AREA
    ========================================== -->
    <main class="owner-main-content">
        @yield('content')
    </main>

    <!-- BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @vite(['resources/js/app.js', 'resources/js/api.js'])
    @stack('scripts')
</body>

</html>
