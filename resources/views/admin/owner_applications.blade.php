@extends('layouts.admin')

@section('title', 'Owner Applications Queue | Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-file-earmark-check text-warning me-2"></i>Owner Verification Applications
            </h3>
            <p class="text-muted small mb-0">Review submitted court owner partner verification applications, inspect document proof, and approve or reject requests.</p>
        </div>
    </div>


    <!-- =========================================
         STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">TOTAL APPLICATIONS</small>
                <h3 class="fw-extrabold text-dark my-1" id="statTotalApps">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-file-text me-1 text-primary"></i>All submitted requests</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">PENDING REVIEW</small>
                <h3 class="fw-extrabold text-warning my-1" id="statPendingApps">0</h3>
                <small class="text-warning fs-8 fw-semibold"><i class="bi bi-clock-history me-1"></i>Awaiting decision</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">APPROVED OWNERS</small>
                <h3 class="fw-extrabold text-success my-1" id="statApprovedApps">0</h3>
                <small class="text-success fs-8 fw-semibold"><i class="bi bi-check-circle me-1"></i>Active venue partners</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">REJECTED APPLICATIONS</small>
                <h3 class="fw-extrabold text-danger my-1" id="statRejectedApps">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-x-circle text-danger me-1"></i>Requires revision</small>
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
                <div class="nav nav-pills gap-1 p-1 bg-light rounded-pill d-inline-flex w-100 mw-100 border" id="statusPillsNav">
                    <button class="nav-link active rounded-pill px-3.5 py-1.5 fs-8 fw-bold" data-status="">All Applications</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-warning" data-status="pending">Pending</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-success" data-status="approved">Approved</button>
                    <button class="nav-link rounded-pill px-3.5 py-1.5 fs-8 fw-bold text-danger" data-status="rejected">Rejected</button>
                </div>
            </div>

            <!-- SEARCH INPUT & SORT -->
            <div class="col-lg-6 col-12 d-flex align-items-center gap-2 justify-content-lg-end">
                <div class="input-group search-input-group" style="max-width: 320px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="appSearchInput" class="form-control border-start-0 ps-0" placeholder="Search applicant, email, business...">
                </div>

                <select id="appSortSelect" class="form-select rounded-pill" style="width: 140px;">
                    <option value="latest">Latest First</option>
                    <option value="oldest">Oldest First</option>
                </select>
            </div>

        </div>
    </div>


    <!-- =========================================
         APPLICATIONS TABLE
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4 py-3">APPLICANT</th>
                        <th class="py-3">BUSINESS NAME</th>
                        <th class="py-3">LOCATION</th>
                        <th class="py-3">PHONE</th>
                        <th class="py-3">DOCUMENT PROOF</th>
                        <th class="py-3">SUBMITTED ON</th>
                        <th class="py-3">STATUS</th>
                        <th class="text-end pe-4 py-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="applicationsTbody">
                    <tr><td colspan="8" class="text-center py-5 text-muted">Loading applications queue...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION CONTAINER -->
        <div class="p-3 border-top bg-light d-flex align-items-center justify-content-between" id="paginationContainer"></div>
    </div>

</div>


<!-- =========================================
     MODAL 1: INSPECT APPLICATION & DOCUMENT
========================================== -->
<div class="modal fade" id="inspectDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold" id="inspectModalTitle">
                    <i class="bi bi-file-earmark-person me-2 text-warning"></i>Owner Verification Proof Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="inspectModalBody">
                <div class="text-center py-4"><span class="spinner-border text-primary"></span></div>
            </div>
            <div class="modal-footer bg-light p-3 border-top d-flex justify-content-between" id="inspectModalFooter">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 2: APPROVE APPLICATION CONFIRMATION
========================================== -->
<div class="modal fade" id="approveApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white p-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-check-circle me-2"></i>Approve Owner Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveApplicationForm">
                <div class="modal-body p-4">
                    <p class="text-dark mb-3">
                        Are you sure you want to approve <strong id="approveModalBusinessName">this application</strong>?
                    </p>
                    <div class="alert alert-success rounded-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Approving this request will automatically promote the user's role to <strong>Owner</strong> and grant full access to court management.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Admin Approval Note (Optional)</label>
                        <textarea id="approveAdminNote" class="form-control" rows="2" placeholder="Optional notes or instructions for the venue owner..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitApproveBtn" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Approve & Promote User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 3: REJECT APPLICATION REASON
========================================== -->
<div class="modal fade" id="rejectApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white p-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-x-circle me-2"></i>Reject Owner Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectApplicationForm">
                <div class="modal-body p-4">
                    <p class="text-dark mb-3">
                        Please provide the reason for rejecting <strong id="rejectModalBusinessName">this application</strong>:
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Rejection Reason Feedback *</label>
                        <textarea id="rejectAdminNote" class="form-control" rows="3" placeholder="Explain what document or details need revision..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitRejectBtn" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="bi bi-x-lg me-1"></i> Reject Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/owner_applications.js')
@endpush
