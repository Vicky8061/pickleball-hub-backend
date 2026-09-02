@extends('layouts.admin')

@section('title', 'User & Owner Directory | Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-people-fill text-success me-2"></i>User & Owner Directory Management
            </h3>
            <p class="text-muted small mb-0">View all registered accounts, inspect user profiles & roles, and manage active/blocked access statuses.</p>
        </div>
    </div>


    <!-- =========================================
         STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">TOTAL ACCOUNTS</small>
                <h3 class="fw-extrabold text-dark my-1" id="statTotalUsers">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-person-badge me-1 text-primary"></i>All registered members</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">REGISTERED PLAYERS</small>
                <h3 class="fw-extrabold text-info my-1" id="statTotalPlayers">0</h3>
                <small class="text-info fs-8 fw-semibold"><i class="bi bi-person me-1"></i>User role accounts</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">VERIFIED COURT OWNERS</small>
                <h3 class="fw-extrabold text-success my-1" id="statTotalOwners">0</h3>
                <small class="text-success fs-8 fw-semibold"><i class="bi bi-patch-check me-1"></i>Venue partner owners</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">BLOCKED ACCOUNTS</small>
                <h3 class="fw-extrabold text-danger my-1" id="statBlockedAccounts">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-slash-circle text-danger me-1"></i>Access suspended</small>
            </div>
        </div>
    </div>


    <!-- =========================================
         FILTERS & SEARCH BAR
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <div class="row g-3 align-items-center">

            <!-- ROLE TABS -->
            <div class="col-lg-6 col-12">
                <div class="nav nav-pills gap-1 p-1 bg-light rounded-pill d-inline-flex w-100 mw-100 border" id="userRolePillsNav">
                    <button class="nav-link active rounded-pill px-3.5 py-1.5 fs-8 fw-bold" data-role="">All Accounts</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-info" data-role="user">Players</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-success" data-role="owner">Court Owners</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-danger" data-role="admin">Admins</button>
                </div>
            </div>

            <!-- SEARCH INPUT & SORT -->
            <div class="col-lg-6 col-12 d-flex align-items-center gap-2 justify-content-lg-end">
                <div class="input-group search-input-group" style="max-width: 320px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="userSearchInput" class="form-control border-start-0 ps-0" placeholder="Search name, email, phone...">
                </div>

                <select id="userSortSelect" class="form-select rounded-pill" style="width: 140px;">
                    <option value="latest">Latest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>

        </div>
    </div>


    <!-- =========================================
         USERS DIRECTORY TABLE
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4 py-3">MEMBER</th>
                        <th class="py-3">EMAIL ADDRESS</th>
                        <th class="py-3">PHONE</th>
                        <th class="py-3">ROLE</th>
                        <th class="py-3">JOINED ON</th>
                        <th class="py-3">STATUS</th>
                        <th class="text-end pe-4 py-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="usersTbody">
                    <tr><td colspan="7" class="text-center py-5 text-muted">Loading user directory...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION CONTAINER -->
        <div class="p-3 border-top bg-light d-flex align-items-center justify-content-between" id="paginationContainer"></div>
    </div>

</div>


<!-- =========================================
     MODAL 1: INSPECT USER DETAILS
========================================== -->
<div class="modal fade" id="inspectUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold" id="inspectUserModalTitle">
                    <i class="bi bi-person-bounding-box me-2 text-success"></i>Member Profile Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="inspectUserModalBody">
                <div class="text-center py-4"><span class="spinner-border text-primary"></span></div>
            </div>
            <div class="modal-footer bg-light p-3 border-top d-flex justify-content-between" id="inspectUserModalFooter">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 2: TOGGLE BLOCK ACCOUNT CONFIRMATION
========================================== -->
<div class="modal fade" id="toggleBlockUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header p-4 text-white" id="blockModalHeader">
                <h5 class="modal-title fw-bold" id="blockModalTitle">Update Account Access</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="toggleBlockUserForm">
                <div class="modal-body p-4">
                    <p class="text-dark mb-3" id="blockModalPrompt">
                        Are you sure you want to change the status of this user account?
                    </p>
                    <div id="blockModalAlert" class="alert rounded-3 small mb-0"></div>
                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitBlockUserBtn" class="btn rounded-pill px-4 fw-bold">
                        Confirm Account Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/users.js')
@endpush
