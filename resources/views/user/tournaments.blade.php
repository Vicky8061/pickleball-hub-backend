@extends('layouts.user')

@section('title', 'Tournaments & Competitions | Pickleball Hub')

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
                        <i class="bi bi-trophy-fill me-1"></i> PLAYER COMPETITIONS
                    </span>
                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 rounded-pill px-3 py-1 fw-semibold fs-8">
                        <i class="bi bi-fire text-warning me-1"></i> Live Registration
                    </span>
                </div>
                <h1 class="display-6 fw-bold text-white mb-2">Pickleball Tournaments</h1>
                <p class="fs-6 text-slate-300 mb-0">Browse upcoming venue tournaments, compete for lucrative prize pools, register your entry pass, and track your active competition passes.</p>
            </div>
        </div>
    </div>


    <!-- =========================================
         NAVIGATION TABS
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 p-2 mb-4 bg-white">
        <ul class="nav nav-pills nav-pills-responsive gap-2" id="tournamentMainTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4 py-2 fw-bold fs-8" id="explore-tab" data-bs-toggle="pill" data-bs-target="#explorePane" type="button" role="tab">
                    <i class="bi bi-grid me-1"></i> Explore Tournaments
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill px-4 py-2 fw-bold fs-8 position-relative" id="joined-tab" data-bs-toggle="pill" data-bs-target="#joinedPane" type="button" role="tab">
                    <i class="bi bi-ticket-perforated me-1"></i> My Joined Passes
                    <span id="joinedCountBadge" class="badge bg-danger rounded-circle ms-1 d-none">0</span>
                </button>
            </li>
        </ul>
    </div>


    <!-- TAB CONTENT -->
    <div class="tab-content" id="tournamentTabContent">

        <!-- =========================================
             TAB 1: EXPLORE TOURNAMENTS
        ========================================== -->
        <div class="tab-pane fade show active" id="explorePane" role="tabpanel">

            <!-- FILTERS & SEARCH -->
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-6 col-12">
                        <div class="input-group search-input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" id="tournamentSearchInput" class="form-control border-start-0 ps-0" placeholder="Search by tournament title or court venue...">
                        </div>
                    </div>
                    <div class="col-lg-6 col-12 d-flex align-items-center justify-content-lg-end gap-2">
                        <div class="nav nav-pills gap-1 p-1 bg-light rounded-pill border" id="statusFilterNav">
                            <button class="nav-link active rounded-pill px-3 py-1 fs-8 fw-bold" data-status="">All</button>
                            <button class="nav-link rounded-pill px-3 py-1 fs-8 fw-bold text-info" data-status="upcoming">Upcoming</button>
                            <button class="nav-link rounded-pill px-3 py-1 fs-8 fw-bold text-success" data-status="ongoing">Ongoing</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOURNAMENTS CARDS GRID -->
            <div class="row g-4" id="tournamentsGrid">
                <div class="col-12 text-center py-5 text-muted">
                    <span class="spinner-border text-primary me-2"></span>Loading available tournaments...
                </div>
            </div>

        </div>


        <!-- =========================================
             TAB 2: MY JOINED PASSES
        ========================================== -->
        <div class="tab-pane fade" id="joinedPane" role="tabpanel">

            <div class="row g-4" id="myJoinedContainer">
                <div class="col-12 text-center py-5 text-muted">
                    <span class="spinner-border text-primary me-2"></span>Loading your joined tournament passes...
                </div>
            </div>

        </div>

    </div>

</div>


<!-- =========================================
     MODAL: TOURNAMENT DETAILS & REGISTRATION
========================================== -->
<div class="modal fade" id="tournamentDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold" id="modalTournamentTitle">
                    <i class="bi bi-trophy-fill me-2 text-warning"></i>Tournament Specifications
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalTournamentBody">
                <div class="text-center py-5"><span class="spinner-border text-primary"></span></div>
            </div>
            <div class="modal-footer bg-light p-3 border-top d-flex justify-content-between" id="modalTournamentFooter">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/user/tournaments.js')
@endpush