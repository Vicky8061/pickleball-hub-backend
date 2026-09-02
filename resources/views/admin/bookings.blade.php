@extends('layouts.admin')

@section('title', 'Master Bookings | Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-calendar2-check-fill text-success me-2"></i>Master Court Bookings & Reservation Inspection
            </h3>
            <p class="text-muted small mb-0">View, search, filter, and inspect player court reservations and booking invoices across all registered venue partners.</p>
        </div>
    </div>


    <!-- =========================================
         STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">TOTAL BOOKINGS</small>
                <h3 class="fw-extrabold text-dark my-1" id="statTotalBookings">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-journal-text me-1 text-primary"></i>All platform reservations</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">CONFIRMED BOOKINGS</small>
                <h3 class="fw-extrabold text-success my-1" id="statConfirmedBookings">0</h3>
                <small class="text-success fs-8 fw-semibold"><i class="bi bi-check-circle me-1"></i>Completed & active slots</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">PENDING BOOKINGS</small>
                <h3 class="fw-extrabold text-warning my-1" id="statPendingBookings">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-clock me-1 text-warning"></i>Awaiting payment/confirmation</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">CANCELLED BOOKINGS</small>
                <h3 class="fw-extrabold text-danger my-1" id="statCancelledBookings">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-x-circle text-danger me-1"></i>Cancelled by player/owner</small>
            </div>
        </div>
    </div>


    <!-- =========================================
         FILTERS & SEARCH BAR
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <div class="row g-3 align-items-center">

            <!-- STATUS TABS -->
            <div class="col-lg-6 col-12">
                <div class="nav nav-pills gap-1 p-1 bg-light rounded-pill d-inline-flex w-100 mw-100 border" id="bookingStatusPillsNav">
                    <button class="nav-link active rounded-pill px-3.5 py-1.5 fs-8 fw-bold" data-status="">All Bookings</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-success" data-status="confirmed">Confirmed</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-warning" data-status="pending">Pending</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-danger" data-status="cancelled">Cancelled</button>
                </div>
            </div>

            <!-- SEARCH INPUT & SORT -->
            <div class="col-lg-6 col-12 d-flex align-items-center gap-2 justify-content-lg-end">
                <div class="input-group search-input-group" style="max-width: 320px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="bookingSearchInput" class="form-control border-start-0 ps-0" placeholder="Search player, court, or #BK ID...">
                </div>

                <select id="bookingSortSelect" class="form-select rounded-pill" style="width: 140px;">
                    <option value="latest">Latest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>

        </div>
    </div>


    <!-- =========================================
         BOOKINGS MASTER TABLE
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4 py-3">REF ID</th>
                        <th class="py-3">PLAYER</th>
                        <th class="py-3">COURT VENUE</th>
                        <th class="py-3">RESERVATION DATE & TIME</th>
                        <th class="py-3">AMOUNT PAID</th>
                        <th class="py-3">STATUS</th>
                        <th class="text-end pe-4 py-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="bookingsTbody">
                    <tr><td colspan="7" class="text-center py-5 text-muted">Loading master bookings...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION CONTAINER -->
        <div class="p-3 border-top bg-light d-flex align-items-center justify-content-between" id="paginationContainer"></div>
    </div>

</div>


<!-- =========================================
     MODAL: INSPECT BOOKING INVOICE
========================================== -->
<div class="modal fade" id="inspectBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold" id="inspectBookingModalTitle">
                    <i class="bi bi-receipt-cutoff me-2 text-success"></i>Reservation Invoice & Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="inspectBookingModalBody">
                <div class="text-center py-5"><span class="spinner-border text-primary"></span></div>
            </div>
            <div class="modal-footer bg-light p-3 border-top">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/bookings.js')
@endpush
