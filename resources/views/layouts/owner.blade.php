<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>@yield('title', 'Owner Portal | Pickleball Hub')</title>

    <!-- BOOTSTRAP 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- BOOTSTRAP ICONS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- GOOGLE FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- LEAFLET MAP CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- APPLICATION CSS -->
    @vite([
        'resources/css/app.css',
        'resources/css/owner.css'
    ])

    @stack('styles')
</head>

<body>

    <!-- =========================================
         NAVBAR
    ========================================== -->
    <nav class="owner-navbar">
        <div class="container-fluid">

            <!-- BRAND & BADGE -->
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('owner.dashboard') }}" class="owner-brand">
                    <img src="{{ asset('assets/images/logo.png') }}" alt="Pickleball Hub">
                </a>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold fs-7 d-none d-sm-inline-flex align-items-center gap-1">
                    <i class="bi bi-building"></i> Owner Portal
                </span>
            </div>

            <!-- DESKTOP NAVIGATION -->
            <div class="owner-nav-links d-none d-md-flex">

                <a href="{{ route('owner.dashboard') }}" class="owner-nav-link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('owner.courts') }}" class="owner-nav-link {{ request()->routeIs('owner.courts') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <span>My Courts</span>
                </a>

                <a href="{{ route('owner.time-slots') }}" class="owner-nav-link {{ request()->routeIs('owner.time-slots') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i>
                    <span>Time Slots</span>
                </a>

                <a href="{{ route('owner.bookings') }}" class="owner-nav-link {{ request()->routeIs('owner.bookings') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i>
                    <span>Bookings</span>
                </a>

                <a href="{{ route('owner.tournaments') }}" class="owner-nav-link {{ request()->routeIs('owner.tournaments') ? 'active' : '' }}">
                    <i class="bi bi-trophy"></i>
                    <span>Tournaments</span>
                </a>

            </div>

            <!-- RIGHT SIDE / USER MENU & MOBILE TOGGLE -->
            <div class="owner-nav-right d-flex align-items-center gap-2">

                <div class="dropdown">
                    <button class="btn owner-profile-btn dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="owner-avatar">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div class="text-start d-none d-sm-block">
                            <span id="ownerNavbarName" class="fw-bold d-block lh-1 text-dark small">Owner</span>
                            <small class="text-muted fs-8">Court Owner</small>
                        </div>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2">
                        <li>
                            <button id="ownerLogoutBtn" class="dropdown-item py-2 text-danger" type="button">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- MOBILE HAMBURGER BUTTON -->
                <button type="button" class="owner-menu-btn d-md-none" id="ownerMenuBtn" aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>

            </div>

        </div>
    </nav>

    <!-- MOBILE NAVIGATION MENU -->
    <div class="owner-mobile-menu" id="ownerMobileMenu">
        <div class="owner-mobile-menu-inner">
            <a href="{{ route('owner.dashboard') }}" class="owner-mobile-link {{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('owner.courts') }}" class="owner-mobile-link {{ request()->routeIs('owner.courts') ? 'active' : '' }}">
                <i class="bi bi-grid-3x3-gap"></i>
                <span>My Courts</span>
            </a>
            <a href="{{ route('owner.time-slots') }}" class="owner-mobile-link {{ request()->routeIs('owner.time-slots') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>Time Slots</span>
            </a>
            <a href="{{ route('owner.bookings') }}" class="owner-mobile-link {{ request()->routeIs('owner.bookings') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i>
                <span>Bookings</span>
            </a>
            <a href="{{ route('owner.tournaments') }}" class="owner-mobile-link {{ request()->routeIs('owner.tournaments') ? 'active' : '' }}">
                <i class="bi bi-trophy"></i>
                <span>Tournaments</span>
            </a>
        </div>
    </div>


    <!-- =========================================
         MAIN CONTENT
    ========================================== -->
    <main class="owner-main-content">
        @yield('content')
    </main>


    <!-- BOOTSTRAP JS BUNDLE -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    @vite('resources/js/api.js')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userStr = localStorage.getItem('auth_user');
            if (userStr) {
                try {
                    const user = JSON.parse(userStr);
                    const nameEl = document.getElementById('ownerNavbarName');
                    if (nameEl && user.name) {
                        nameEl.textContent = user.name;
                    }
                } catch (e) {}
            }

            // Mobile Menu Toggle
            const menuBtn = document.getElementById('ownerMenuBtn');
            const mobileMenu = document.getElementById('ownerMobileMenu');
            if (menuBtn && mobileMenu) {
                menuBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    mobileMenu.classList.toggle('active');
                });
                document.addEventListener('click', (e) => {
                    if (mobileMenu.classList.contains('active') && !mobileMenu.contains(e.target) && !menuBtn.contains(e.target)) {
                        mobileMenu.classList.remove('active');
                    }
                });
            }

            const logoutBtn = document.getElementById('ownerLogoutBtn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', async () => {
                    try {
                        await fetch('/auth/logout', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            }
                        });
                        localStorage.removeItem('auth_token');
                        localStorage.removeItem('auth_user');
                        window.location.href = '/login';
                    } catch (e) {
                        window.location.href = '/login';
                    }
                });
            }
        });
    </script>

    <!-- LEAFLET MAP JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('scripts')

</body>

</html>
