@extends('layouts.user')

@section('title', 'Dashboard | Pickleball Hub')


@section('content')

<div class="container-fluid py-4">


    <!-- =========================================
         WELCOME
    ========================================== -->

    <div class="user-welcome mb-4">

        <div>

            <span class="welcome-label">
                Welcome back 👋
            </span>


            <h1 id="dashboardUserName">
                Ready to play?
            </h1>


            <p>
                Find a court, book your next game,
                and enjoy pickleball.
            </p>

        </div>


        <a
            href="{{ route('user.courts') }}"
            class="btn user-primary-btn">

            <i class="bi bi-search"></i>

            Find a Court

        </a>

    </div>


    <!-- =========================================
         DYNAMIC BANNER CAROUSEL
    ========================================== -->

    <div id="dashboardBannerContainer" class="mb-4 d-none">

        <div id="dashboardBannerCarousel" class="carousel slide rounded-4 overflow-hidden shadow-sm" data-bs-ride="carousel" data-bs-interval="4000">

            <div class="carousel-indicators" id="bannerCarouselIndicators"></div>

            <div class="carousel-inner" id="bannerCarouselInner"></div>

            <button class="carousel-control-prev" type="button" data-bs-target="#dashboardBannerCarousel" data-bs-slide="prev">

                <span class="carousel-control-prev-icon" aria-hidden="true"></span>

                <span class="visually-hidden">Previous</span>

            </button>

            <button class="carousel-control-next" type="button" data-bs-target="#dashboardBannerCarousel" data-bs-slide="next">

                <span class="carousel-control-next-icon" aria-hidden="true"></span>

                <span class="visually-hidden">Next</span>

            </button>

        </div>

    </div>



    <!-- =========================================
         QUICK ACTIONS
    ========================================== -->

    <div class="row g-3 mb-4">


        <!-- COURTS -->

        <div class="col-md-4">

            <a
                href="{{ route('user.courts') }}"
                class="quick-card">

                <div class="quick-icon">

                    <i class="bi bi-grid"></i>

                </div>


                <div>

                    <h5>
                        Find Courts
                    </h5>

                    <p>
                        Discover courts near you
                    </p>

                </div>


                <i
                    class="bi bi-arrow-right ms-auto"></i>

            </a>

        </div>



        <!-- TOURNAMENTS -->

        <div class="col-md-4">

            <a
                href="{{ route('user.tournaments') }}"
                class="quick-card">

                <div class="quick-icon">

                    <i class="bi bi-trophy"></i>

                </div>


                <div>

                    <h5>
                        Tournaments
                    </h5>

                    <p>
                        Join exciting tournaments
                    </p>

                </div>


                <i
                    class="bi bi-arrow-right ms-auto"></i>

            </a>

        </div>



        <!-- BOOKINGS -->

        <div class="col-md-4">

            <a
                href="{{ route('user.bookings') }}"
                class="quick-card">

                <div class="quick-icon">

                    <i class="bi bi-calendar-check"></i>

                </div>


                <div>

                    <h5>
                        My Bookings
                    </h5>

                    <p>
                        Manage your bookings
                    </p>

                </div>


                <i
                    class="bi bi-arrow-right ms-auto"></i>

            </a>

        </div>

    </div>



    <!-- =========================================
         FEATURED COURTS
    ========================================== -->

    <div class="section-header">

        <div>

            <h3>
                Featured Courts
            </h3>

            <p>
                Explore popular pickleball courts
            </p>

        </div>


        <a
            href="{{ route('user.courts') }}"
            class="view-all">

            View all

            <i class="bi bi-arrow-right"></i>

        </a>

    </div>



    <div
        class="row g-4"
        id="featuredCourts">

        <!-- Loading -->

        <div class="col-12">

            <div
                class="empty-state"
                id="courtsLoading">

                <div
                    class="spinner-border text-success"></div>


                <p>
                    Loading courts...
                </p>

            </div>

        </div>

    </div>



    <!-- =========================================
         TOP RATED COURTS
    ========================================== -->

    <div class="section-header mt-5">

        <div>

            <h3>
                <i class="bi bi-star-fill text-warning me-1"></i> Top Rated Courts
            </h3>

            <p>
                Highest rated courts according to player reviews
            </p>

        </div>


        <a
            href="{{ route('user.courts') }}"
            class="view-all">

            View all

            <i class="bi bi-arrow-right"></i>

        </a>

    </div>


    <div
        class="row g-4 mb-4"
        id="topRatedCourts">

        <!-- Loading -->

        <div class="col-12">

            <div class="empty-state">

                <div class="spinner-border text-success"></div>

                <p>
                    Loading top rated courts...
                </p>

            </div>

        </div>

    </div>



    <!-- =========================================
         UPCOMING TOURNAMENTS
    ========================================== -->

    <div class="section-header mt-5">

        <div>

            <h3>
                Upcoming Tournaments
            </h3>

            <p>
                Compete, connect and have fun
            </p>

        </div>


        <a
            href="{{ route('user.tournaments') }}"
            class="view-all">

            View all

            <i class="bi bi-arrow-right"></i>

        </a>

    </div>



    <div
        class="row g-4"
        id="upcomingTournaments">

        <div class="col-12">

            <div class="empty-state">

                <div
                    class="spinner-border text-success"></div>


                <p>
                    Loading tournaments...
                </p>

            </div>

        </div>

    </div>

</div>

@endsection



@push('scripts')

<script type="module">
    import "{{ Vite::asset('resources/js/user/dashboard.js') }}";
</script>

@endpush