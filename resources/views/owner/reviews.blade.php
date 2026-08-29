@extends('layouts.owner')

@section('title', 'Player Reviews & Ratings | Owner Portal')

@section('content')
<div class="container-fluid px-4 py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">
                <i class="bi bi-star-fill text-warning me-2"></i>Player Reviews & Ratings
            </h3>
            <p class="text-muted small mb-0">Track customer feedback, analyze star rating distributions, and monitor player experience across your courts.</p>
        </div>
    </div>


    <!-- =========================================
         RATING OVERVIEW & BREAKDOWN CARD
    ========================================== -->
    <div class="owner-card p-4 mb-4">
        <div class="row align-items-center g-4">
            
            <!-- LEFT: BIG AVERAGE SCORE -->
            <div class="col-md-4 col-12 border-end-md text-center py-2">
                <div class="display-3 fw-bold text-dark mb-0 lh-1" id="reviewAvgScore">0.0</div>
                <div class="fs-4 text-warning my-2" id="reviewAvgStars">
                    <i class="bi bi-star"></i><i class="bi bi-star"></i><i class="bi bi-star"></i><i class="bi bi-star"></i><i class="bi bi-star"></i>
                </div>
                <p class="text-muted small mb-0">Based on <strong class="text-dark" id="reviewTotalCount">0</strong> player reviews</p>
            </div>

            <!-- RIGHT: 5-STAR PROGRESS BARS -->
            <div class="col-md-8 col-12">
                <div class="d-flex flex-column gap-2" id="starBreakdownBars">
                    @for ($star = 5; $star >= 1; $star--)
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-semibold text-muted small" style="width: 55px;">${star} Stars</span>
                        <div class="progress flex-grow-1" style="height: 8px;">
                            <div class="progress-bar bg-warning" id="barStar{{ $star }}" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <span class="text-muted fs-8 text-end" id="countStar{{ $star }}" style="width: 35px;">0</span>
                    </div>
                    @endfor
                </div>
            </div>

        </div>
    <!-- =========================================
         COURT-WISE RATING SUMMARY CARDS
    ========================================== -->
    <div class="row g-3 mb-4" id="courtWiseRatingCards">
        <!-- Loaded dynamically -->
    </div>


    <!-- =========================================
         FILTERS & SEARCH BAR
    ========================================== -->
    <div class="owner-card p-4 mb-4">
        
        <!-- STAR RATING PILLS -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 border-bottom pb-3">
            <div class="nav nav-pills gap-2" id="ratingStarTabs">
                <button type="button" class="nav-link active rounded-pill px-3 py-1 text-dark fw-semibold small border" data-rating="">
                    All Reviews
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-warning fw-semibold small border" data-rating="5">
                    5 Stars ★
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-warning fw-semibold small border" data-rating="4">
                    4 Stars ★
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-warning fw-semibold small border" data-rating="3">
                    3 Stars ★
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-warning fw-semibold small border" data-rating="2">
                    2 Stars ★
                </button>
                <button type="button" class="nav-link rounded-pill px-3 py-1 text-warning fw-semibold small border" data-rating="1">
                    1 Star ★
                </button>
            </div>

            <button type="button" id="resetReviewFiltersBtn" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
            </button>
        </div>

        <div class="row g-3">
            <!-- SEARCH INPUT -->
            <div class="col-md-6 col-12">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchReviewInput" class="form-control bg-light border-start-0" placeholder="Search by player name or comment keywords...">
                </div>
            </div>

            <!-- COURT VENUE FILTER -->
            <div class="col-md-6 col-12">
                <select id="filterCourtReviewSelect" class="form-select bg-light">
                    <option value="">All Court Venues</option>
                </select>
            </div>
        </div>

    </div>


    <!-- =========================================
         PLAYER REVIEWS LIST GRID
    ========================================== -->
    <div class="row g-4" id="ownerReviewsListGrid">
        <!-- Skeleton Loaders -->
        @for ($i = 0; $i < 3; $i++)
        <div class="col-12">
            <div class="owner-card p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="skeleton rounded-circle" style="width: 48px; height: 48px;"></div>
                    <div class="w-50">
                        <div class="skeleton skeleton-title w-50 mb-2"></div>
                        <div class="skeleton skeleton-text w-25"></div>
                    </div>
                </div>
                <div class="skeleton skeleton-text w-100 mb-2"></div>
                <div class="skeleton skeleton-text w-75"></div>
            </div>
        </div>
        @endfor
    </div>

</div>
@endsection

@push('scripts')
@vite('resources/js/owner/reviews.js')
@endpush
