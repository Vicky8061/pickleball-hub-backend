@extends('layouts.owner')

@section('title', 'Tournament Manager | Owner Portal')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER & ACTION BUTTON
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-trophy text-warning me-2"></i>Tournaments & Leagues Host Panel
            </h3>
            <p class="text-muted small mb-0">Host pickleball tournaments, set entry fees & prize pools, and track participant player registrations.</p>
        </div>

        <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createTournamentModal">
            <i class="bi bi-plus-lg me-1"></i> Host New Tournament
        </button>
    </div>


    <!-- =========================================
         FILTERS & SEARCH BAR
    ========================================== -->
    <div class="owner-card p-4 mb-4">
        
        <!-- STATUS TABS -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 border-bottom pb-3">
            <div class="nav nav-pills gap-2" id="tournamentStatusTabs">
                <button type="button" class="nav-link active rounded-pill px-3 py-1 text-dark fw-semibold small border" data-status="">
                    All Events (<span id="countAll">0</span>)
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-primary fw-semibold small border" data-status="upcoming">
                    Upcoming (<span id="countUpcoming">0</span>)
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-warning fw-semibold small border" data-status="ongoing">
                    Ongoing (<span id="countOngoing">0</span>)
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-success fw-semibold small border" data-status="completed">
                    Completed (<span id="countCompleted">0</span>)
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-danger fw-semibold small border" data-status="cancelled">
                    Cancelled (<span id="countCancelled">0</span>)
                </button>
            </div>

            <button type="button" id="resetTournamentFiltersBtn" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
            </button>
        </div>

        <div class="row g-3">
            <!-- SEARCH INPUT -->
            <div class="col-md-6 col-12">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchTournamentInput" class="form-control bg-light border-start-0" placeholder="Search by Tournament Title or Court Name...">
                </div>
            </div>

            <!-- COURT FILTER -->
            <div class="col-md-6 col-12">
                <select id="filterCourtSelect" class="form-select bg-light">
                    <option value="">All Court Venues</option>
                </select>
            </div>
        </div>

    </div>


    <!-- =========================================
         TOURNAMENTS GRID
    ========================================== -->
    <div class="row g-4" id="ownerTournamentsGrid">
        <!-- Skeleton Cards -->
        @for ($i = 0; $i < 3; $i++)
        <div class="col-lg-4 col-md-6">
            <div class="owner-card h-100 p-3">
                <div class="skeleton skeleton-img w-100 rounded-3 mb-3" style="height: 180px;"></div>
                <div class="skeleton skeleton-title w-75 mb-2"></div>
                <div class="skeleton skeleton-text w-50 mb-3"></div>
                <div class="skeleton skeleton-text w-100 mb-2"></div>
            </div>
        </div>
        @endfor
    </div>

</div>


<!-- =========================================
     MODAL 1: CREATE TOURNAMENT MODAL
========================================== -->
<div class="modal fade" id="createTournamentModal" tabindex="-1" aria-labelledby="createTournamentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="createTournamentModalLabel">
                    <i class="bi bi-trophy-fill text-warning me-2"></i> Host New Pickleball Tournament
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createTournamentForm" enctype="multipart/form-data">
                <div class="modal-body py-4">
                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-muted">Tournament Title *</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Surat Open Pickleball League 2026" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Select Court Venue *</label>
                            <select name="court_id" id="createTournamentCourtId" class="form-select" required>
                                <option value="">Loading courts...</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Event Description & Rules *</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Specify format (Singles/Doubles), rules, eligibility..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Tournament Banner Image</label>
                            <input type="file" name="banner" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Tournament Date *</label>
                            <input type="date" name="tournament_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Registration Last Date *</label>
                            <input type="date" name="registration_last_date" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Start Time *</label>
                            <input type="time" name="start_time" class="form-control" value="09:00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">End Time *</label>
                            <input type="time" name="end_time" class="form-control" value="18:00" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Entry Fee (₹) *</label>
                            <input type="number" name="entry_fee" class="form-control" placeholder="500" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Max Participants *</label>
                            <input type="number" name="max_participants" class="form-control" placeholder="32" min="2" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Prize Pool *</label>
                            <input type="text" name="prize" class="form-control" placeholder="e.g. ₹20,000 + Trophy" required>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="submitCreateTournamentBtn">
                        Create Tournament
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 2: EDIT TOURNAMENT MODAL
========================================== -->
<div class="modal fade" id="editTournamentModal" tabindex="-1" aria-labelledby="editTournamentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="editTournamentModalLabel">
                    <i class="bi bi-pencil-square text-success me-2"></i> Edit Tournament Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTournamentForm">
                <input type="hidden" id="editTournamentId">
                <div class="modal-body py-4">
                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label fw-semibold small text-muted">Tournament Title *</label>
                            <input type="text" name="title" id="editTournamentTitle" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Tournament Status *</label>
                            <select name="status" id="editTournamentStatus" class="form-select" required>
                                <option value="upcoming">Upcoming</option>
                                <option value="ongoing">Ongoing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted">Description *</label>
                            <textarea name="description" id="editTournamentDescription" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Tournament Date *</label>
                            <input type="date" name="tournament_date" id="editTournamentDate" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Registration Last Date *</label>
                            <input type="date" name="registration_last_date" id="editTournamentRegDate" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Start Time *</label>
                            <input type="time" name="start_time" id="editTournamentStartTime" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">End Time *</label>
                            <input type="time" name="end_time" id="editTournamentEndTime" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Entry Fee (₹) *</label>
                            <input type="number" name="entry_fee" id="editTournamentEntryFee" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Max Participants *</label>
                            <input type="number" name="max_participants" id="editTournamentMaxParticipants" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small text-muted">Prize Pool *</label>
                            <input type="text" name="prize" id="editTournamentPrize" class="form-control" required>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm" id="submitEditTournamentBtn">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 3: PARTICIPANTS LIST MODAL
========================================== -->
<div class="modal fade" id="tournamentParticipantsModal" tabindex="-1" aria-labelledby="tournamentParticipantsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="tournamentParticipantsModalLabel">
                    <i class="bi bi-people-fill text-primary me-2"></i> Registered Participants - <span id="participantsModalTitle">Tournament</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr class="text-muted small">
                                <th>#</th>
                                <th>PLAYER NAME</th>
                                <th>EMAIL ADDRESS</th>
                                <th>PAYMENT STATUS</th>
                                <th class="text-end">JOINED DATE</th>
                            </tr>
                        </thead>
                        <tbody id="participantsTableBody">
                            <!-- Loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@vite('resources/js/owner/tournaments.js')
@endpush
