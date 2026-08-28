@extends('layouts.owner')

@section('title', 'Owner Dashboard | Pickleball Hub')

@section('content')
<div class="container-fluid py-4 px-lg-4">

    <!-- =========================================
         WELCOME BANNER
    ========================================== -->
    <div class="owner-welcome-banner mb-4">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <span class="owner-welcome-eyebrow">
                    <i class="bi bi-speedometer2 me-1"></i> COURT OWNER DASHBOARD
                </span>
                <h1 id="welcomeOwnerName">Ready to manage your courts?</h1>
                <p>Track your net revenue, court bookings, tournaments, and player reviews in real time.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a href="#" class="btn btn-light rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
                        <i class="bi bi-plus-circle-fill text-success me-1"></i> Add Court
                    </a>
                    <a href="#" class="btn btn-success rounded-pill px-4 py-2 fw-bold border-0 shadow-sm" style="background-color: #20c997;">
                        <i class="bi bi-trophy-fill me-1"></i> Create Tournament
                    </a>
                </div>
            </div>
        </div>
    </div>


    <!-- =========================================
         KPI STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">

        <!-- REVENUE -->
        <div class="col-6 col-md-3">
            <div class="owner-kpi-card">
                <div class="kpi-icon-box green">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <div class="kpi-label">Net Earnings</div>
                    <div class="kpi-value" id="kpiRevenue">
                        <span class="spinner-border spinner-border-sm text-success"></span>
                    </div>
                    <div class="kpi-subtext">90% net court payout</div>
                </div>
            </div>
        </div>

        <!-- TODAY BOOKINGS -->
        <div class="col-6 col-md-3">
            <div class="owner-kpi-card">
                <div class="kpi-icon-box blue">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div>
                    <div class="kpi-label">Today's Bookings</div>
                    <div class="kpi-value" id="kpiTodayBookings">
                        <span class="spinner-border spinner-border-sm text-primary"></span>
                    </div>
                    <div class="kpi-subtext">Scheduled today</div>
                </div>
            </div>
        </div>

        <!-- ACTIVE COURTS -->
        <div class="col-6 col-md-3">
            <div class="owner-kpi-card">
                <div class="kpi-icon-box purple">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </div>
                <div>
                    <div class="kpi-label">Active Courts</div>
                    <div class="kpi-value" id="kpiActiveCourts">
                        <span class="spinner-border spinner-border-sm text-purple"></span>
                    </div>
                    <div class="kpi-subtext" id="kpiCourtsSubtext">0 total courts</div>
                </div>
            </div>
        </div>

        <!-- RATING -->
        <div class="col-6 col-md-3">
            <div class="owner-kpi-card">
                <div class="kpi-icon-box warning">
                    <i class="bi bi-star-fill"></i>
                </div>
                <div>
                    <div class="kpi-label">Avg Rating</div>
                    <div class="kpi-value" id="kpiAverageRating">
                        <span class="spinner-border spinner-border-sm text-warning"></span>
                    </div>
                    <div class="kpi-subtext" id="kpiReviewsSubtext">0 reviews</div>
                </div>
            </div>
        </div>

    </div>


    <!-- =========================================
         QUICK ACTIONS
    ========================================== -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <a href="#" class="owner-action-card">
                <div class="action-icon">
                    <i class="bi bi-plus-square"></i>
                </div>
                <div>
                    <h6>Manage Courts</h6>
                    <p>Add, edit or disable courts</p>
                </div>
                <i class="bi bi-chevron-right ms-auto text-muted small"></i>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="#" class="owner-action-card">
                <div class="action-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <h6>Time Slots</h6>
                    <p>Set court hours & pricing</p>
                </div>
                <i class="bi bi-chevron-right ms-auto text-muted small"></i>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="#" class="owner-action-card">
                <div class="action-icon">
                    <i class="bi bi-trophy"></i>
                </div>
                <div>
                    <h6>Tournaments</h6>
                    <p>Host leagues & events</p>
                </div>
                <i class="bi bi-chevron-right ms-auto text-muted small"></i>
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="#" class="owner-action-card">
                <div class="action-icon">
                    <i class="bi bi-star"></i>
                </div>
                <div>
                    <h6>Player Reviews</h6>
                    <p>View customer feedback</p>
                </div>
                <i class="bi bi-chevron-right ms-auto text-muted small"></i>
            </a>
        </div>
    </div>


    <!-- =========================================
         RECENT BOOKINGS TABLE CARD
    ========================================== -->
    <div class="owner-card mb-4">
        <div class="owner-card-header">
            <div>
                <h5 class="owner-card-title">Recent Court Bookings</h5>
                <p class="text-muted small mb-0">Overview of recent court reservations across your venues.</p>
            </div>
            <a href="#" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold">
                View All Bookings <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="table owner-table align-middle">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Player</th>
                        <th>Court</th>
                        <th>Date & Time</th>
                        <th>Court Fee</th>
                        <th>Payout (90%)</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="ownerRecentBookingsBody">
                    <!-- Skeleton Rows -->
                    @for ($i = 0; $i < 4; $i++)
                    <tr>
                        <td><div class="skeleton skeleton-text w-50"></div></td>
                        <td><div class="skeleton skeleton-text w-75"></div></td>
                        <td><div class="skeleton skeleton-text w-75"></div></td>
                        <td><div class="skeleton skeleton-text w-75"></div></td>
                        <td><div class="skeleton skeleton-text w-50"></div></td>
                        <td><div class="skeleton skeleton-text w-50"></div></td>
                        <td><div class="skeleton skeleton-badge"></div></td>
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script type="module">
    import "{{ Vite::asset('resources/js/owner/dashboard.js') }}";
</script>
@endpush