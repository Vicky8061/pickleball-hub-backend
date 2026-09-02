@extends('layouts.user')

@section('title', 'Dashboard | Pickleball Hub')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         HERO DASHBOARD HEADER
    ========================================== -->
    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 mb-4 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-lg-8 col-12">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold fs-8 shadow-sm">
                        <i class="bi bi-controller me-1"></i> PLAYER DASHBOARD
                    </span>
                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 rounded-pill px-3 py-1 fw-semibold fs-8">
                        <i class="bi bi-circle-fill text-success fs-9 me-1"></i> Active Session
                    </span>
                </div>
                <h1 class="display-6 fw-bold text-white mb-2" id="dashboardUserName">Ready to play?</h1>
                <p class="fs-6 text-slate-300 mb-0">Discover top pickleball courts near you, reserve time slots, compete in local tournaments, and manage your match bookings.</p>
            </div>
            <div class="col-lg-4 col-12 text-center text-lg-end mt-3 mt-lg-0 d-flex flex-column flex-sm-row justify-content-lg-end gap-2">
                <a href="{{ route('user.courts') }}" class="btn btn-warning rounded-pill px-4 py-2.5 fw-bold shadow-sm text-dark">
                    <i class="bi bi-search me-1"></i> Find a Court
                </a>
                <a href="{{ route('user.tournaments') }}" class="btn btn-outline-light rounded-pill px-4 py-2.5 fw-bold">
                    <i class="bi bi-trophy me-1"></i> Tournaments
                </a>
            </div>
        </div>
    </div>


    <!-- =========================================
         DYNAMIC BANNER CAROUSEL
    ========================================== -->
    <div id="dashboardBannerContainer" class="mb-4 d-none">
        <div id="dashboardBannerCarousel" class="carousel slide carousel-fade rounded-4 overflow-hidden shadow-sm" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-indicators" id="bannerCarouselIndicators"></div>
            <div class="carousel-inner" id="bannerCarouselInner"></div>
            <button class="carousel-control-prev" type="button" data-bs-target="#dashboardBannerCarousel" data-bs-slide="prev">
                <i class="bi bi-chevron-left text-white fs-5"></i>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#dashboardBannerCarousel" data-bs-slide="next">
                <i class="bi bi-chevron-right text-white fs-5"></i>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>


    <!-- =========================================
         QUICK ACTIONS GRID
    ========================================== -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6 col-12">
            <a href="{{ route('user.courts') }}" class="quick-card text-decoration-none">
                <div class="quick-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Find Courts</h5>
                    <p class="text-muted fs-8 mb-0">Discover & reserve venues near you</p>
                </div>
                <i class="bi bi-arrow-right ms-auto text-muted fs-5"></i>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 col-12">
            <a href="{{ route('user.tournaments') }}" class="quick-card text-decoration-none border-start border-4 border-warning">
                <div class="quick-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-trophy-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Tournaments & Events</h5>
                    <p class="text-muted fs-8 mb-0">Compete for prize pools & player passes</p>
                </div>
                <i class="bi bi-arrow-right ms-auto text-warning fs-5"></i>
            </a>
        </div>

        <div class="col-lg-4 col-md-6 col-12">
            <a href="{{ route('user.bookings') }}" class="quick-card text-decoration-none">
                <div class="quick-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-dark">My Bookings</h5>
                    <p class="text-muted fs-8 mb-0">Track active & past match invoices</p>
                </div>
                <i class="bi bi-arrow-right ms-auto text-muted fs-5"></i>
            </a>
        </div>
    </div>


    <!-- =========================================
         FEATURED COURTS
    ========================================== -->
    <div class="section-header">
        <div>
            <h3>Featured Courts</h3>
            <p>Explore popular pickleball courts available for booking</p>
        </div>
        <a href="{{ route('user.courts') }}" class="view-all">
            View all <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row g-4 mb-4" id="featuredCourts">
        @for ($i = 0; $i < 3; $i++)
        <div class="col-md-6 col-xl-4">
            <div class="skeleton-card">
                <div class="skeleton skeleton-img"></div>
                <div class="skeleton-card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="skeleton skeleton-badge"></div>
                        <div class="skeleton skeleton-text w-25 mb-0"></div>
                    </div>
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text w-75 mb-3"></div>
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                        <div class="skeleton skeleton-text w-50 mb-0"></div>
                        <div class="skeleton skeleton-badge" style="width: 100px;"></div>
                    </div>
                </div>
            </div>
        </div>
        @endfor
    </div>


    <!-- =========================================
         TOP RATED COURTS
    ========================================== -->
    <div class="section-header mt-5">
        <div>
            <h3><i class="bi bi-star-fill text-warning me-1"></i> Top Rated Courts</h3>
            <p>Highest rated courts according to player reviews</p>
        </div>
        <a href="{{ route('user.courts') }}" class="view-all">
            View all <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row g-4 mb-4" id="topRatedCourts">
        @for ($i = 0; $i < 3; $i++)
        <div class="col-md-6 col-xl-4">
            <div class="skeleton-card">
                <div class="skeleton skeleton-img"></div>
                <div class="skeleton-card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="skeleton skeleton-badge"></div>
                        <div class="skeleton skeleton-text w-25 mb-0"></div>
                    </div>
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text w-75 mb-3"></div>
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                        <div class="skeleton skeleton-text w-50 mb-0"></div>
                        <div class="skeleton skeleton-badge" style="width: 100px;"></div>
                    </div>
                </div>
            </div>
        </div>
        @endfor
    </div>


    <!-- =========================================
         UPCOMING TOURNAMENTS
    ========================================== -->
    <div class="section-header mt-5">
        <div>
            <h3><i class="bi bi-trophy-fill text-warning me-1"></i> Upcoming Tournaments</h3>
            <p>Compete in local events and earn prize pools</p>
        </div>
        <a href="{{ route('user.tournaments') }}" class="view-all">
            View all <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row g-4 mb-4" id="upcomingTournaments">
        @for ($i = 0; $i < 3; $i++)
        <div class="col-md-6 col-xl-4">
            <div class="skeleton-card">
                <div class="skeleton skeleton-img"></div>
                <div class="skeleton-card-body">
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text w-100"></div>
                    <div class="skeleton skeleton-text w-75 mb-4"></div>
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                        <div class="skeleton skeleton-text w-50 mb-0"></div>
                        <div class="skeleton skeleton-badge"></div>
                    </div>
                </div>
            </div>
        </div>
        @endfor
    </div>

</div>
@endsection

@push('scripts')
<script type="module">
    import "{{ Vite::asset('resources/js/user/dashboard.js') }}";
</script>
@endpush