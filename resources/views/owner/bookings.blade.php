@extends('layouts.owner')

@section('title', 'Booking Management | Owner Portal')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER & FINANCIAL SUMMARY
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-calendar-check text-success me-2"></i>Booking Management & Reservations
            </h3>
            <p class="text-muted small mb-0">Track player reservations, update booking statuses, and view net owner payout statements.</p>
        </div>
    </div>

    <!-- KPI STATS CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-12">
            <div class="owner-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-semibold d-block fs-8">NET OWNER PAYOUT (90%)</small>
                    <h4 class="fw-bold text-success mb-0" id="kpiNetEarnings">₹0</h4>
                </div>
                <div class="owner-kpi-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="owner-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-semibold d-block fs-8">CONFIRMED BOOKINGS</small>
                    <h4 class="fw-bold text-primary mb-0" id="kpiConfirmedBookings">0</h4>
                </div>
                <div class="owner-kpi-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-check-circle fs-4"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="owner-card p-3 d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted fw-semibold d-block fs-8">PENDING APPROVALS</small>
                    <h4 class="fw-bold text-warning mb-0" id="kpiPendingBookings">0</h4>
                </div>
                <div class="owner-kpi-icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
            </div>
        </div>
    </div>


    <!-- =========================================
         FILTERS & SEARCH BAR
    ========================================== -->
    <div class="owner-card p-4 mb-4">
        
        <!-- STATUS TABS -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 border-bottom pb-3">
            <div class="nav nav-pills gap-2" id="bookingStatusTabs">
                <button type="button" class="nav-link active rounded-pill px-3 py-1 text-dark fw-semibold small border" data-status="">
                    All Bookings (<span id="countAll">0</span>)
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-warning fw-semibold small border" data-status="pending">
                    Pending (<span id="countPending">0</span>)
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-primary fw-semibold small border" data-status="confirmed">
                    Confirmed (<span id="countConfirmed">0</span>)
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-success fw-semibold small border" data-status="completed">
                    Completed (<span id="countCompleted">0</span>)
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-danger fw-semibold small border" data-status="cancelled">
                    Cancelled (<span id="countCancelled">0</span>)
                </button>
            </div>

            <button type="button" id="resetBookingFiltersBtn" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
            </button>
        </div>

        <div class="row g-3">
            <!-- SEARCH INPUT -->
            <div class="col-md-5 col-12">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchBookingInput" class="form-control bg-light border-start-0" placeholder="Search by Player Name, Email, or Booking ID...">
                </div>
            </div>

            <!-- COURT FILTER -->
            <div class="col-md-4 col-6">
                <select id="filterCourtSelect" class="form-select bg-light">
                    <option value="">All Court Venues</option>
                </select>
            </div>

            <!-- DATE FILTER -->
            <div class="col-md-3 col-6">
                <input type="date" id="filterBookingDate" class="form-control bg-light">
            </div>
        </div>

    </div>


    <!-- =========================================
         BOOKINGS TABLE
    ========================================== -->
    <div class="owner-card p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="ownerBookingsTable">
                <thead class="bg-light">
                    <tr class="text-muted small">
                        <th class="ps-4">BOOKING ID</th>
                        <th>PLAYER DETAILS</th>
                        <th>COURT VENUE</th>
                        <th>DATE & TIME SLOT</th>
                        <th>PAYOUT (90%)</th>
                        <th>STATUS</th>
                        <th class="text-end pe-4">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="ownerBookingsTbody">
                    <!-- Loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>

</div>


<!-- =========================================
     MODAL: BOOKING DETAILS MODAL
========================================== -->
<div class="modal fade" id="bookingDetailsModal" tabindex="-1" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="bookingDetailsModalLabel">
                    <i class="bi bi-receipt me-2 text-success"></i> Reservation Details - <span id="modalBookingId">#000</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                
                <div class="row g-4">
                    <!-- PLAYER INFO -->
                    <div class="col-md-6 border-end">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person me-1 text-primary"></i> Player Information</h6>
                        <table class="table table-sm table-borderless small">
                            <tr>
                                <td class="text-muted">Player Name:</td>
                                <td class="fw-semibold text-dark text-end" id="modalPlayerName">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td class="fw-semibold text-dark text-end" id="modalPlayerEmail">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Role:</td>
                                <td class="fw-semibold text-dark text-end">Player / Customer</td>
                            </tr>
                        </table>

                        <h6 class="fw-bold text-dark mb-3 mt-4"><i class="bi bi-geo-alt me-1 text-danger"></i> Venue & Time</h6>
                        <table class="table table-sm table-borderless small">
                            <tr>
                                <td class="text-muted">Court Venue:</td>
                                <td class="fw-semibold text-dark text-end" id="modalCourtName">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Address:</td>
                                <td class="fw-semibold text-dark text-end" id="modalCourtAddress">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Booking Date:</td>
                                <td class="fw-semibold text-dark text-end" id="modalBookingDate">-</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Time Slot:</td>
                                <td class="fw-semibold text-success text-end" id="modalTimeSlot">-</td>
                            </tr>
                        </table>
                    </div>

                    <!-- FINANCIAL SPLIT -->
                    <div class="col-md-6">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-wallet2 me-1 text-success"></i> Payment & Revenue Breakdown</h6>
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Court Hourly Fee:</span>
                                <span class="fw-semibold" id="modalCourtPrice">₹0</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Platform Fee (Paid by User):</span>
                                <span class="fw-semibold text-muted" id="modalPlatformFee">₹50</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Admin Commission (10%):</span>
                                <span class="fw-semibold text-danger" id="modalAdminCommission">-₹0</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between fw-bold text-success fs-6">
                                <span>Net Owner Payout (90%):</span>
                                <span id="modalOwnerPayout">₹0</span>
                            </div>
                        </div>

                        <div class="p-3 rounded-3 border bg-opacity-10" id="modalStatusContainer">
                            <small class="text-muted d-block mb-1">Reservation Status:</small>
                            <span class="badge rounded-pill px-3 py-1 fs-7" id="modalStatusBadge">Pending</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@vite('resources/js/owner/bookings.js')
@endpush
