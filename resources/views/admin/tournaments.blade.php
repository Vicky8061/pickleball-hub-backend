@extends('layouts.admin')

@section('title', 'Master Tournaments | Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-trophy-fill text-warning me-2"></i>Master Tournaments & Competitions Moderation
            </h3>
            <p class="text-muted small mb-0">View all tournaments hosted across partner venues, inspect participant schedules & prize pools, and moderate event statuses.</p>
        </div>
    </div>


    <!-- =========================================
         STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">TOTAL TOURNAMENTS</small>
                <h3 class="fw-extrabold text-dark my-1" id="statTotalTournaments">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-trophy me-1 text-primary"></i>All created events</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">UPCOMING COMPETITIONS</small>
                <h3 class="fw-extrabold text-info my-1" id="statUpcomingTournaments">0</h3>
                <small class="text-info fs-8 fw-semibold"><i class="bi bi-calendar-event me-1"></i>Open for registration</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">ONGOING ACTIVE EVENTS</small>
                <h3 class="fw-extrabold text-success my-1" id="statOngoingTournaments">0</h3>
                <small class="text-success fs-8 fw-semibold"><i class="bi bi-play-circle me-1"></i>Matches in progress</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-secondary">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">COMPLETED & CANCELLED</small>
                <h3 class="fw-extrabold text-secondary my-1" id="statCompletedTournaments">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-check-all me-1"></i>Concluded / Cancelled</small>
            </div>
        </div>
    </div>


    <!-- =========================================
         FILTERS & SEARCH BAR
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <div class="row g-3 align-items-center">

            <!-- STATUS TABS -->
            <div class="col-lg-7 col-12">
                <div class="nav nav-pills gap-1 p-1 bg-light rounded-pill d-inline-flex w-100 mw-100 border" id="tournamentStatusPillsNav">
                    <button class="nav-link active rounded-pill px-3.5 py-1.5 fs-8 fw-bold" data-status="">All Tournaments</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-info" data-status="upcoming">Upcoming</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-success" data-status="ongoing">Ongoing</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-secondary" data-status="completed">Completed</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-danger" data-status="cancelled">Cancelled</button>
                </div>
            </div>

            <!-- SEARCH INPUT & SORT -->
            <div class="col-lg-5 col-12 d-flex align-items-center gap-2 justify-content-lg-end">
                <div class="input-group search-input-group" style="max-width: 260px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="tournamentSearchInput" class="form-control border-start-0 ps-0" placeholder="Search tournament, court...">
                </div>

                <select id="tournamentSortSelect" class="form-select rounded-pill" style="width: 130px;">
                    <option value="latest">Latest</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>

        </div>
    </div>


    <!-- =========================================
         TOURNAMENTS MASTER TABLE
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4 py-3">TOURNAMENT TITLE</th>
                        <th class="py-3">ORGANIZING VENUE</th>
                        <th class="py-3">PRIZE POOL</th>
                        <th class="py-3">ENTRY FEE</th>
                        <th class="py-3">EVENT DATES</th>
                        <th class="py-3">STATUS</th>
                        <th class="text-end pe-4 py-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="tournamentsTbody">
                    <tr><td colspan="7" class="text-center py-5 text-muted">Loading master tournaments...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION CONTAINER -->
        <div class="p-3 border-top bg-light d-flex align-items-center justify-content-between" id="paginationContainer"></div>
    </div>

</div>


<!-- =========================================
     MODAL 1: INSPECT TOURNAMENT DETAILS
========================================== -->
<div class="modal fade" id="inspectTournamentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold" id="inspectTournamentModalTitle">
                    <i class="bi bi-trophy-fill me-2 text-warning"></i>Tournament Specifications
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="inspectTournamentModalBody">
                <div class="text-center py-5"><span class="spinner-border text-primary"></span></div>
            </div>
            <div class="modal-footer bg-light p-3 border-top d-flex justify-content-between" id="inspectTournamentModalFooter">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 2: CANCEL TOURNAMENT CONFIRMATION
========================================== -->
<div class="modal fade" id="cancelTournamentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger p-4 text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-slash-circle-fill me-2"></i>Cancel Tournament Event
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelTournamentForm">
                <div class="modal-body p-4">
                    <p class="text-dark mb-3" id="cancelTournamentPrompt">
                        Are you sure you want to cancel this tournament event?
                    </p>
                    <div class="alert alert-danger rounded-3 small mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Cancelling this tournament will update its status to "cancelled" and notify all registered players.
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                    <button type="submit" id="submitCancelTournamentBtn" class="btn btn-danger rounded-pill px-4 fw-bold">
                        Confirm Cancel Tournament
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/tournaments.js')
@endpush
