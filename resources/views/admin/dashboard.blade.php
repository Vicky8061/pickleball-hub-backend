@extends('layouts.admin')

@section('title', 'Admin Master Dashboard | Pickleball Hub')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         HERO DASHBOARD HEADER
    ========================================== -->
    <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 mb-4 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-lg-8 col-12">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-danger rounded-pill px-3 py-1 fw-bold fs-8 shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> MASTER CONTROL PANEL
                    </span>
                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 rounded-pill px-3 py-1 fw-semibold fs-8">
                        <i class="bi bi-circle-fill text-success fs-9 me-1"></i> System Online & Operations Active
                    </span>
                </div>
                <h1 class="display-6 fw-bold text-white mb-2">Platform Master Dashboard</h1>
                <p class="fs-6 text-slate-300 mb-0">Monitor platform commission revenues, approve partner venue applications, inspect court bookings, and oversee global system performance.</p>
            </div>
            <div class="col-lg-4 col-12 text-center text-lg-end mt-3 mt-lg-0">
                <a href="{{ route('admin.owner-applications') }}" class="btn btn-warning rounded-pill px-4 py-2.5 fw-bold shadow-sm text-dark">
                    <i class="bi bi-file-earmark-check me-1"></i> Review Applications Queue
                </a>
            </div>
        </div>
    </div>


    <!-- =========================================
         4 PRIMARY KPI STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">

        <!-- RETAINED COMMISSION REVENUE -->
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden card-hover-lift border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">PLATFORM COMMISSION</span>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-extrabold text-success mb-1" id="kpiCommissionRevenue">
                    <span class="spinner-border spinner-border-sm text-success"></span>
                </h2>
                <div class="d-flex align-items-center gap-1 text-muted fs-8" id="kpiGrossRevenue">
                    <i class="bi bi-graph-up-arrow text-success me-1"></i> Net retained revenue (10% + fees)
                </div>
            </div>
        </div>

        <!-- PENDING OWNER APPLICATIONS -->
        <div class="col-xl-3 col-md-6 col-12">
            <a href="{{ route('admin.owner-applications') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden card-hover-lift border-start border-4 border-warning">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-bold text-uppercase tracking-wider">PENDING APPLICATIONS</span>
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-hourglass-split fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-extrabold text-warning mb-1" id="kpiPendingApplications">
                        <span class="spinner-border spinner-border-sm text-warning"></span>
                    </h2>
                    <div class="d-flex align-items-center gap-1 text-warning fs-8 fw-semibold">
                        Awaiting admin review <i class="bi bi-arrow-right ms-1"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- ACTIVE COURTS -->
        <div class="col-xl-3 col-md-6 col-12">
            <a href="{{ route('admin.courts') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden card-hover-lift border-start border-4 border-primary">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-bold text-uppercase tracking-wider">ACTIVE COURTS</span>
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-extrabold text-dark mb-1" id="kpiActiveCourts">
                        <span class="spinner-border spinner-border-sm text-primary"></span>
                    </h2>
                    <div class="d-flex align-items-center gap-1 text-muted fs-8" id="kpiTotalCourts">
                        <i class="bi bi-building text-primary me-1"></i> Verified court venues
                    </div>
                </div>
            </a>
        </div>

        <!-- PLAYER BOOKINGS -->
        <div class="col-xl-3 col-md-6 col-12">
            <a href="{{ route('admin.bookings') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden card-hover-lift border-start border-4 border-info">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-muted small fw-bold text-uppercase tracking-wider">PLAYER BOOKINGS</span>
                        <div class="rounded-circle bg-info bg-opacity-10 text-info p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="bi bi-calendar-check-fill fs-5"></i>
                        </div>
                    </div>
                    <h2 class="fw-extrabold text-info mb-1" id="kpiTotalBookings">
                        <span class="spinner-border spinner-border-sm text-info"></span>
                    </h2>
                    <div class="d-flex align-items-center gap-1 text-muted fs-8" id="kpiCompletedBookings">
                        <i class="bi bi-check-circle text-info me-1"></i> Active reservations
                    </div>
                </div>
            </a>
        </div>

    </div>


    <!-- =========================================
         SECONDARY PLATFORM METRICS SUMMARY
    ========================================== -->
    <div class="row g-3 mb-4">

        <!-- USERS BREAKDOWN -->
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Account Directory</h6>
                    <a href="{{ route('admin.users') }}" class="small text-primary text-decoration-none fw-bold">Manage <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                    <span class="text-muted small"><i class="bi bi-person me-1 text-info"></i> Registered Players</span>
                    <strong class="text-dark" id="statTotalPlayers">0</strong>
                </div>
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                    <span class="text-muted small"><i class="bi bi-briefcase me-1 text-success"></i> Court Owners</span>
                    <strong class="text-dark" id="statTotalOwners">0</strong>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2">
                    <span class="text-muted small"><i class="bi bi-shield-check me-1 text-danger"></i> System Administrators</span>
                    <strong class="text-dark" id="statTotalAdmins">0</strong>
                </div>
            </div>
        </div>

        <!-- REVIEWS & SATISFACTION -->
        <div class="col-lg-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-star-fill text-warning me-2"></i>Feedback & Quality</h6>
                    <a href="{{ route('admin.reviews') }}" class="small text-primary text-decoration-none fw-bold">Moderate <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                    <span class="text-muted small"><i class="bi bi-emoji-smile me-1 text-warning"></i> Platform Average Rating</span>
                    <strong class="text-warning fw-bold fs-6" id="statAverageRating">0.0 / 5</strong>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2">
                    <span class="text-muted small"><i class="bi bi-chat-left-text me-1 text-primary"></i> Total Submitted Reviews</span>
                    <strong class="text-dark" id="statTotalReviews">0</strong>
                </div>
            </div>
        </div>

        <!-- TOURNAMENTS & EVENTS -->
        <div class="col-lg-4 col-md-12 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>Tournaments & Events</h6>
                    <a href="{{ route('admin.tournaments') }}" class="small text-primary text-decoration-none fw-bold">Inspect <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                    <span class="text-muted small"><i class="bi bi-play-circle me-1 text-success"></i> Active / Ongoing Events</span>
                    <strong class="text-success" id="statActiveTournaments">0</strong>
                </div>
                <div class="d-flex align-items-center justify-content-between pt-2">
                    <span class="text-muted small"><i class="bi bi-trophy me-1 text-dark"></i> Total Created Tournaments</span>
                    <strong class="text-dark" id="statTotalTournaments">0</strong>
                </div>
            </div>
        </div>

    </div>


    <!-- =========================================
         DIRECT MANAGEMENT QUICK ACTIONS GRID
    ========================================== -->
    <h6 class="fw-bold text-dark mb-3">
        <i class="bi bi-grid-fill text-primary me-2"></i>Master Management Modules
    </h6>
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6 col-12">
            <a href="{{ route('admin.owner-applications') }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white text-decoration-none card-hover-lift d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-file-earmark-check fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0 fs-7">Applications Queue</h6>
                    <small class="text-muted fs-8">Approve court owners</small>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <a href="{{ route('admin.courts') }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white text-decoration-none card-hover-lift d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0 fs-7">Master Courts</h6>
                    <small class="text-muted fs-8">Suspend & verify venues</small>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <a href="{{ route('admin.users') }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white text-decoration-none card-hover-lift d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0 fs-7">User & Owner Directory</h6>
                    <small class="text-muted fs-8">Manage user accounts</small>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <a href="{{ route('admin.banners') }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white text-decoration-none card-hover-lift d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-images fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0 fs-7">Banners & Promos</h6>
                    <small class="text-muted fs-8">Manage hero carousels</small>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <a href="{{ route('admin.bookings') }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white text-decoration-none card-hover-lift d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-indigo bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-calendar2-check-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0 fs-7">Master Bookings</h6>
                    <small class="text-muted fs-8">Inspect reservations</small>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <a href="{{ route('admin.payouts') }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white text-decoration-none card-hover-lift d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-emerald bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-wallet2 fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0 fs-7">Payout Settlements</h6>
                    <small class="text-muted fs-8">Settle owner earnings</small>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <a href="{{ route('admin.reviews') }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white text-decoration-none card-hover-lift d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-star-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0 fs-7">Master Reviews</h6>
                    <small class="text-muted fs-8">Moderate feedback</small>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 col-12">
            <a href="{{ route('admin.tournaments') }}" class="card border-0 shadow-sm rounded-4 p-3 bg-white text-decoration-none card-hover-lift d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-amber bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-trophy-fill fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0 fs-7">Master Tournaments</h6>
                    <small class="text-muted fs-8">Oversee events</small>
                </div>
            </a>
        </div>

    </div>


    <!-- =========================================
         DUAL LIVE ACTIVITY FEED WIDGETS
    ========================================== -->
    <div class="row g-4 mb-4">

        <!-- WIDGET 1: RECENT OWNER APPLICATIONS QUEUE -->
        <div class="col-lg-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                <div class="p-4 border-bottom bg-white d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bi bi-clock-history text-warning me-2"></i>Pending Owner Requests
                        </h6>
                        <small class="text-muted">Latest court owner partner applications</small>
                    </div>
                    <a href="{{ route('admin.owner-applications') }}" class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-bold">
                        View Queue <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small">
                                <th class="ps-4 py-3">APPLICANT</th>
                                <th class="py-3">BUSINESS NAME</th>
                                <th class="py-3">LOCATION</th>
                                <th class="text-end pe-4 py-3">STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="recentApplicationsTbody">
                            <tr><td colspan="4" class="text-center py-4 text-muted">Loading applications...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- WIDGET 2: RECENT PLAYER BOOKINGS -->
        <div class="col-lg-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white h-100">
                <div class="p-4 border-bottom bg-white d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bi bi-calendar2-check-fill text-info me-2"></i>Recent Court Bookings
                        </h6>
                        <small class="text-muted">Latest player court reservations across venues</small>
                    </div>
                    <a href="{{ route('admin.bookings') }}" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-bold">
                        View Bookings <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small">
                                <th class="ps-4 py-3">PLAYER</th>
                                <th class="py-3">COURT VENUE</th>
                                <th class="py-3">AMOUNT</th>
                                <th class="text-end pe-4 py-3">STATUS</th>
                            </tr>
                        </thead>
                        <tbody id="recentBookingsTbody">
                            <tr><td colspan="4" class="text-center py-4 text-muted">Loading recent bookings...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
@vite('resources/js/admin/dashboard.js')
@endpush