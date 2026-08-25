<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token"
        content="{{ csrf_token() }}">

    <meta
        http-equiv="Cache-Control"
        content="no-cache, no-store, must-revalidate">

    <meta
        http-equiv="Pragma"
        content="no-cache">

    <meta
        http-equiv="Expires"
        content="0">

    <title>
        @yield('title', 'Pickleball Hub')
    </title>


    <!-- =========================================
         BOOTSTRAP 5
    ========================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- =========================================
         BOOTSTRAP ICONS
    ========================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">


    <!-- =========================================
         GOOGLE FONT
    ========================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">


    <!-- =========================================
         APPLICATION CSS
    ========================================== -->

    @vite([
    'resources/css/app.css',
    'resources/css/user.css',
    'resources/css/courts.css',
    'resources/css/court-details.css',
    'resources/css/booking.css',
    'resources/css/bookings.css',
    'resources/css/wishlist.css'    
    ])

    @stack('styles')

</head>


<body>


    <!-- =========================================
         NAVBAR
    ========================================== -->

    <nav class="user-navbar">

        <div class="container-fluid">


            <!-- BRAND -->

            <a
                href="{{ route('user.dashboard') }}"
                class="user-brand">

                <img
                    src="{{ asset('assets/images/logo.png') }}"
                    alt="Pickleball Hub">

            </a>


            <!-- DESKTOP NAVIGATION -->

            <div class="user-nav-links">


                <a
                    href="{{ route('user.dashboard') }}"
                    class="user-nav-link">

                    <i class="bi bi-house"></i>

                    <span>Home</span>

                </a>


                <a
                    href="{{ route('user.courts') }}"
                    class="user-nav-link">

                    <i class="bi bi-grid"></i>

                    <span>Courts</span>

                </a>


                <a
                    href="{{ route('user.tournaments') }}"
                    class="user-nav-link">

                    <i class="bi bi-trophy"></i>

                    <span>Tournaments</span>

                </a>


                <a
                    href="{{ route('user.wishlist') }}"
                    class="user-nav-link">

                    <i class="bi bi-heart"></i>

                    <span>Wishlist</span>

                </a>


                <a
                    href="{{ route('user.bookings') }}"
                    class="user-nav-link">

                    <i class="bi bi-calendar-check"></i>

                    <span>My Bookings</span>

                </a>

            </div>


            <!-- RIGHT SIDE -->

            <div class="user-nav-right">


                <!-- NOTIFICATION -->

                <button
                    type="button"
                    class="user-icon-btn"
                    id="notificationBtn">

                    <i class="bi bi-bell"></i>

                </button>


                <!-- PROFILE -->

                <a
                    href="{{ route('user.profile') }}"
                    class="user-profile">

                    <div class="user-avatar">

                        <i class="bi bi-person"></i>

                    </div>

                    <div class="user-profile-info">

                        <span
                            id="navbarUserName"
                            class="user-name">

                            User

                        </span>

                        <small>
                            Player
                        </small>

                    </div>

                </a>


                <!-- MOBILE MENU -->

                <button
                    type="button"
                    class="user-menu-btn"
                    id="userMenuBtn">

                    <i class="bi bi-list"></i>

                </button>

            </div>

        </div>

    </nav>


    <!-- =========================================
         MOBILE MENU
    ========================================== -->

    <div
        class="user-mobile-menu"
        id="userMobileMenu">


        <a
            href="{{ route('user.dashboard') }}"
            class="user-mobile-link">

            <i class="bi bi-house"></i>

            Home

        </a>


        <a
            href="{{ route('user.courts') }}"
            class="user-mobile-link">

            <i class="bi bi-grid"></i>

            Courts

        </a>


        <a
            href="{{ route('user.tournaments') }}"
            class="user-mobile-link">

            <i class="bi bi-trophy"></i>

            Tournaments

        </a>


        <a
            href="{{ route('user.wishlist') }}"
            class="user-mobile-link">

            <i class="bi bi-heart"></i>

            Wishlist

        </a>


        <a
            href="{{ route('user.bookings') }}"
            class="user-mobile-link">

            <i class="bi bi-calendar-check"></i>

            My Bookings

        </a>


        <a
            href="{{ route('user.profile') }}"
            class="user-mobile-link">

            <i class="bi bi-person"></i>

            Profile

        </a>


        <button
            type="button"
            id="mobileLogoutBtn"
            class="user-mobile-link user-logout">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </button>

    </div>


    <!-- =========================================
         MAIN CONTENT
    ========================================== -->

    <main class="user-main">

        @yield('content')

    </main>


    <!-- =========================================
         BOOTSTRAP JS
    ========================================== -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


    <!-- =========================================
         USER JS
    ========================================== -->

    @vite([
    'resources/js/app.js',
    'resources/js/api.js',
    'resources/js/user/user-layout.js'
    ])


    @stack('scripts')

</body>

</html>