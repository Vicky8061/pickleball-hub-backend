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
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-danger rounded-pill px-3 py-1 fw-bold fs-8 shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> MASTER CONTROL PANEL
                    </span>
                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 rounded-pill px-3 py-1 fw-semibold fs-8">
                        <i class="bi bi-circle-fill text-success fs-9 me-1"></i> System Online
                    </span>
                </div>
                <h1 class="display-6 fw-bold text-white mb-2">Platform Master Overview</h1>
                <p class="fs-6 text-slate-300 mb-0">Track platform commission earnings, review court owner verification requests, and manage system operations seamlessly.</p>
            </div>
            <div class="col-lg-4 col-12 text-center text-lg-end mt-3 mt-lg-0">
                <a href="{{ route('admin.owner-applications') }}" class="btn btn-warning rounded-pill px-4 py-2.5 fw-bold shadow-sm text-dark">
                    <i class="bi bi-file-earmark-check me-1"></i> Review Pending Applications
                </a>
            </div>
        </div>
    </div>


    <!-- =========================================
         4 KPI STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">

        <!-- RETAINED COMMISSION REVENUE -->
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden card-hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">PLATFORM COMMISSION (10%)</span>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-extrabold text-success mb-1" id="kpiCommissionRevenue">
                    <span class="spinner-border spinner-border-sm text-success"></span>
                </h2>
                <div class="d-flex align-items-center gap-1 text-muted fs-8">
                    <i class="bi bi-graph-up-arrow text-success me-1"></i> Net retained revenue & fees
                </div>
            </div>
        </div>

        <!-- PENDING APPLICATIONS -->
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
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden card-hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">ACTIVE COURTS</span>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-grid-3x3-gap-fill fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-extrabold text-dark mb-1" id="kpiActiveCourts">
                    <span class="spinner-border spinner-border-sm text-primary"></span>
                </h2>
                <div class="d-flex align-items-center gap-1 text-muted fs-8">
                    <i class="bi bi-building text-primary me-1"></i> Verified court venues
                </div>
            </div>
        </div>

        <!-- TOTAL BOOKINGS -->
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100 position-relative overflow-hidden card-hover-lift">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="text-muted small fw-bold text-uppercase tracking-wider">PLAYER BOOKINGS</span>
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-calendar-check-fill fs-5"></i>
                    </div>
                </div>
                <h2 class="fw-extrabold text-info mb-1" id="kpiTotalBookings">
                    <span class="spinner-border spinner-border-sm text-info"></span>
                </h2>
                <div class="d-flex align-items-center gap-1 text-muted fs-8">
                    <i class="bi bi-check-circle text-info me-1"></i> Completed & active matches
                </div>
            </div>
        </div>

    </div>


    <!-- =========================================
         QUICK ACTIONS GRID
    ========================================== -->
    <div class="row g-3 mb-4">

        <div class="col-md-4 col-12">
            <a href="{{ route('admin.owner-applications') }}" class="card border-0 shadow-sm rounded-4 p-3.5 bg-white text-decoration-none card-hover-lift d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                    <i class="bi bi-file-earmark-check fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Owner Applications Queue</h6>
                    <small class="text-muted fs-8">Inspect documents & approve owners</small>
                </div>
            </a>
        </div>

        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white opacity-75 d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                    <i class="bi bi-shield-lock fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Master Court Moderation</h6>
                    <small class="text-muted fs-8">View & suspend courts across venue owners</small>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white opacity-75 d-flex flex-row align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                    <i class="bi bi-people fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-0">User & Partner Directory</h6>
                    <small class="text-muted fs-8">Manage player & court owner accounts</small>
                </div>
            </div>
        </div>

    </div>


    <!-- =========================================
         WIDGET: RECENT OWNER APPLICATIONS QUEUE
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        <div class="p-4 border-bottom bg-white d-flex align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-clock-history text-warning me-2"></i>Recent Owner Applications Queue
                </h5>
                <small class="text-muted">Latest court owner partner requests requiring review</small>
            </div>
            <a href="{{ route('admin.owner-applications') }}" class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-bold">
                View Full Queue <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="text-muted small">
                        <th class="ps-4 py-3">APPLICANT</th>
                        <th class="py-3">BUSINESS NAME</th>
                        <th class="py-3">LOCATION</th>
                        <th class="py-3">PHONE</th>
                        <th class="py-3">SUBMITTED ON</th>
                        <th class="text-end pe-4 py-3">STATUS</th>
                    </tr>
                </thead>
                <tbody id="recentApplicationsTbody">
                    <tr><td colspan="6" class="text-center py-4 text-muted">Loading applications...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
@vite('resources/js/admin/dashboard.js')
@endpush