@extends('layouts.admin')

@section('title', 'Master Courts Moderation | Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-grid-3x3-gap-fill text-primary me-2"></i>Master Courts & Venue Moderation
            </h3>
            <p class="text-muted small mb-0">View all registered pickleball courts across venue owners, inspect court specifications, and toggle active/suspended status.</p>
        </div>
    </div>


    <!-- =========================================
         STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">TOTAL COURTS</small>
                <h3 class="fw-extrabold text-dark my-1" id="statTotalCourts">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-building me-1 text-primary"></i>All registered venues</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">ACTIVE COURTS</small>
                <h3 class="fw-extrabold text-success my-1" id="statActiveCourts">0</h3>
                <small class="text-success fs-8 fw-semibold"><i class="bi bi-check-circle me-1"></i>Available for booking</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">SUSPENDED / INACTIVE</small>
                <h3 class="fw-extrabold text-danger my-1" id="statInactiveCourts">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-slash-circle text-danger me-1"></i>Hidden from search</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">AVG HOURLY RATE</small>
                <h3 class="fw-extrabold text-info my-1" id="statAvgPrice">₹0</h3>
                <small class="text-muted fs-8"><i class="bi bi-cash me-1 text-info"></i>Across all venues</small>
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
                <div class="nav nav-pills gap-1 p-1 bg-light rounded-pill d-inline-flex w-100 mw-100 border" id="courtStatusPillsNav">
                    <button class="nav-link active rounded-pill px-3.5 py-1.5 fs-8 fw-bold" data-status="">All Courts</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-success" data-status="active">Active</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-danger" data-status="inactive">Inactive / Suspended</button>
                </div>
            </div>

            <!-- SEARCH INPUT & SORT -->
            <div class="col-lg-6 col-12 d-flex align-items-center gap-2 justify-content-lg-end">
                <div class="input-group search-input-group" style="max-width: 320px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="courtSearchInput" class="form-control border-start-0 ps-0" placeholder="Search court name, address, owner...">
                </div>

                <select id="courtSortSelect" class="form-select rounded-pill" style="width: 140px;">
                    <option value="latest">Latest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>

        </div>
    </div>


    <!-- =========================================
         COURTS MASTER TABLE
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4 py-3">COURT VENUE</th>
                        <th class="py-3">OWNER / PARTNER</th>
                        <th class="py-3">LOCATION</th>
                        <th class="py-3">TYPE</th>
                        <th class="py-3">HOURLY RATE</th>
                        <th class="py-3">STATUS</th>
                        <th class="text-end pe-4 py-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="courtsTbody">
                    <tr><td colspan="7" class="text-center py-5 text-muted">Loading court inventory...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION CONTAINER -->
        <div class="p-3 border-top bg-light d-flex align-items-center justify-content-between" id="paginationContainer"></div>
    </div>

</div>


<!-- =========================================
     MODAL 1: INSPECT COURT DETAILS
========================================== -->
<div class="modal fade" id="inspectCourtModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold" id="inspectModalTitle">
                    <i class="bi bi-building me-2 text-primary"></i>Court Venue Specifications
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="inspectCourtModalBody">
                <div class="text-center py-4"><span class="spinner-border text-primary"></span></div>
            </div>
            <div class="modal-footer bg-light p-3 border-top d-flex justify-content-between" id="inspectCourtModalFooter">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 2: TOGGLE COURT STATUS CONFIRMATION
========================================== -->
<div class="modal fade" id="toggleCourtStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header p-4 text-white" id="toggleModalHeader">
                <h5 class="modal-title fw-bold" id="toggleModalTitle">Update Court Status</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="toggleCourtStatusForm">
                <div class="modal-body p-4">
                    <p class="text-dark mb-3" id="toggleModalPrompt">
                        Are you sure you want to change the status of this court venue?
                    </p>
                    <div id="toggleModalAlert" class="alert rounded-3 small mb-0"></div>
                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitToggleStatusBtn" class="btn rounded-pill px-4 fw-bold">
                        Confirm Status Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/courts.js')
@endpush
