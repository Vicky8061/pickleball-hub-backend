@extends('layouts.admin')

@section('title', 'Master Reviews | Admin Panel')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-star-fill text-warning me-2"></i>Master Court Reviews & Ratings Moderation
            </h3>
            <p class="text-muted small mb-0">Inspect player ratings, review feedback comments across all venue partners, and moderate/delete abusive reviews.</p>
        </div>
    </div>


    <!-- =========================================
         STAT CARDS
    ========================================== -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">TOTAL REVIEWS</small>
                <h3 class="fw-extrabold text-dark my-1" id="statTotalReviews">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-chat-left-quote me-1 text-primary"></i>All player feedback</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">AVERAGE RATING</small>
                <h3 class="fw-extrabold text-warning my-1" id="statAvgRating">0.0 <small class="fs-6 text-muted">/ 5</small></h3>
                <small class="text-warning fs-8 fw-semibold"><i class="bi bi-star-fill me-1"></i>Platform average score</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">5-STAR REVIEWS</small>
                <h3 class="fw-extrabold text-success my-1" id="stat5StarReviews">0</h3>
                <small class="text-success fs-8 fw-semibold"><i class="bi bi-emoji-smile me-1"></i>Top rated court feedback</small>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
                <small class="text-muted fw-bold d-block fs-8 text-uppercase">FLAGGED / LOW RATINGS</small>
                <h3 class="fw-extrabold text-danger my-1" id="statLowRatingReviews">0</h3>
                <small class="text-muted fs-8"><i class="bi bi-exclamation-triangle text-danger me-1"></i>1 to 2 star reviews</small>
            </div>
        </div>
    </div>


    <!-- =========================================
         FILTERS & SEARCH BAR
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white">
        <div class="row g-3 align-items-center">

            <!-- RATING TABS -->
            <div class="col-lg-7 col-12">
                <div class="nav nav-pills gap-1 p-1 bg-light rounded-pill d-inline-flex border" id="reviewRatingPillsNav">
                    <button class="nav-link active rounded-pill px-3 py-1.5 fs-8 fw-bold" data-rating="">All Ratings</button>
                    <button class="nav-link rounded-pill px-3 py-1.5 fs-8 fw-bold text-warning" data-rating="5">★ 5 Stars</button>
                    <button class="nav-link rounded-pill px-3 py-1.5 fs-8 fw-bold text-warning" data-rating="4">★ 4 Stars</button>
                    <button class="nav-link rounded-pill px-3 py-1.5 fs-8 fw-bold text-warning" data-rating="3">★ 3 Stars</button>
                    <button class="nav-link rounded-pill px-3 py-1.5 fs-8 fw-bold text-danger" data-rating="2">★ 2 Stars</button>
                    <button class="nav-link rounded-pill px-3 py-1.5 fs-8 fw-bold text-danger" data-rating="1">★ 1 Star</button>
                </div>
            </div>

            <!-- SEARCH INPUT & SORT -->
            <div class="col-lg-5 col-12 d-flex align-items-center gap-2 justify-content-lg-end">
                <div class="input-group search-input-group" style="max-width: 260px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" id="reviewSearchInput" class="form-control border-start-0 ps-0" placeholder="Search feedback, player...">
                </div>

                <select id="reviewSortSelect" class="form-select rounded-pill" style="width: 130px;">
                    <option value="latest">Latest</option>
                    <option value="oldest">Oldest</option>
                </select>
            </div>

        </div>
    </div>


    <!-- =========================================
         REVIEWS DIRECTORY TABLE
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4 py-3">PLAYER / REVIEWER</th>
                        <th class="py-3">COURT VENUE</th>
                        <th class="py-3">RATING</th>
                        <th class="py-3" style="width: 40%;">FEEDBACK COMMENT</th>
                        <th class="py-3">SUBMITTED ON</th>
                        <th class="text-end pe-4 py-3">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="reviewsTbody">
                    <tr><td colspan="6" class="text-center py-5 text-muted">Loading master reviews...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION CONTAINER -->
        <div class="p-3 border-top bg-light d-flex align-items-center justify-content-between" id="paginationContainer"></div>
    </div>

</div>


<!-- =========================================
     MODAL 1: INSPECT REVIEW DETAILS
========================================== -->
<div class="modal fade" id="inspectReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white p-4">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-star-fill text-warning me-2"></i>Review Feedback Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="inspectReviewModalBody">
                <div class="text-center py-4"><span class="spinner-border text-primary"></span></div>
            </div>
            <div class="modal-footer bg-light p-3 border-top d-flex justify-content-between" id="inspectReviewModalFooter">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- =========================================
     MODAL 2: DELETE REVIEW CONFIRMATION
========================================== -->
<div class="modal fade" id="deleteReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger p-4 text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-trash-fill me-2"></i>Delete Player Review
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteReviewForm">
                <div class="modal-body p-4">
                    <p class="text-dark mb-3" id="deleteReviewPrompt">
                        Are you sure you want to delete this review?
                    </p>
                    <div class="alert alert-danger rounded-3 small mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Deleting this review will permanently remove it from the platform and automatically recalculate the venue's average rating.
                    </div>
                </div>
                <div class="modal-footer bg-light p-3 border-top">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="submitDeleteReviewBtn" class="btn btn-danger rounded-pill px-4 fw-bold">
                        Confirm Delete Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/admin/reviews.js')
@endpush
