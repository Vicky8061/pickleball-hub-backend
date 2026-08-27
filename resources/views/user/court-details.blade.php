@extends('layouts.user')

@section('title', 'Court Details | Pickleball Hub')

@section('content')

<div class="container-fluid py-4">

    <!-- =========================================
         BACK BUTTON
    ========================================== -->

    <div class="court-details-back mb-3">

        <a href="{{ route('user.courts') }}">

            <i class="bi bi-arrow-left"></i>

            Back to Courts

        </a>

    </div>


    <!-- =========================================
         LOADING
    ========================================== -->

    <div id="courtDetailsLoading">

        <div class="court-details-loading">

            <div class="spinner-border text-success"></div>

            <p>
                Loading court details...
            </p>

        </div>

    </div>


    <!-- =========================================
         ERROR
    ========================================== -->

    <div
        id="courtDetailsError"
        class="d-none">

        <div class="court-details-message">

            <div class="court-details-message-icon">

                <i class="bi bi-exclamation-circle"></i>

            </div>

            <h4>
                Unable to load court
            </h4>

            <p id="courtDetailsErrorMessage">
                Something went wrong while loading court details.
            </p>

            <button
                type="button"
                id="retryCourtDetails"
                class="btn user-primary-btn">

                <i class="bi bi-arrow-repeat"></i>

                Try Again

            </button>

        </div>

    </div>


    <!-- =========================================
         COURT DETAILS
    ========================================== -->

    <div
        id="courtDetails"
        class="d-none">


        <div class="row g-4">


            <!-- =====================================
                 LEFT - IMAGES
            ====================================== -->

            <div class="col-lg-7">

                <div class="court-details-image-card">


                    <!-- MAIN IMAGE -->

                    <div class="court-details-main-image">

                        <img
                            id="courtMainImage"
                            src=""
                            alt="Court"
                            class="court-main-image">

                        <div
                            id="courtImagePlaceholder"
                            class="court-image-placeholder d-none">

                            <i class="bi bi-image"></i>

                            <span>
                                No image available
                            </span>

                        </div>


                        <!-- IMAGE COUNTER -->

                        <div
                            id="courtImageCounter"
                            class="court-image-counter">

                            1 / 1

                        </div>


                        <!-- PREVIOUS -->

                        <button
                            type="button"
                            id="courtImagePrevious"
                            class="court-image-control court-image-prev">

                            <i class="bi bi-chevron-left"></i>

                        </button>


                        <!-- NEXT -->

                        <button
                            type="button"
                            id="courtImageNext"
                            class="court-image-control court-image-next">

                            <i class="bi bi-chevron-right"></i>

                        </button>

                    </div>


                    <!-- THUMBNAILS -->

                    <div
                        id="courtImageThumbnails"
                        class="court-image-thumbnails">

                    </div>

                </div>

            </div>


            <!-- =====================================
                 RIGHT - INFORMATION
            ====================================== -->

            <div class="col-lg-5">

                <div class="court-details-card">


                    <!-- TYPE -->

                    <span
                        id="courtTypeBadge"
                        class="court-details-type">

                        Court

                    </span>


                    <!-- NAME -->

                    <h1
                        id="courtName"
                        class="court-details-name">

                        Pickleball Court

                    </h1>


                    <!-- LOCATION -->

                    <div class="court-details-location">

                        <i class="bi bi-geo-alt-fill"></i>

                        <span id="courtAddress">
                            Location unavailable
                        </span>

                    </div>


                    <!-- PRICE -->

                    <div class="court-details-price-box">

                        <div>

                            <span>
                                Starting from
                            </span>

                            <strong id="courtPrice">
                                ₹0
                            </strong>

                            <small>
                                / hour
                            </small>

                        </div>

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="court-details-section">

                        <h4>
                            About this court
                        </h4>

                        <p id="courtDescription">
                            No description available.
                        </p>

                    </div>


                    <!-- DETAILS -->

                    <div class="court-details-section">

                        <h4>
                            Court Information
                        </h4>


                        <div class="court-info-grid">


                            <div class="court-info-item">

                                <div class="court-info-icon">

                                    <i class="bi bi-clock"></i>

                                </div>

                                <div>

                                    <span>
                                        Opening Time
                                    </span>

                                    <strong id="courtOpeningTime">
                                        --
                                    </strong>

                                </div>

                            </div>


                            <div class="court-info-item">

                                <div class="court-info-icon">

                                    <i class="bi bi-clock-history"></i>

                                </div>

                                <div>

                                    <span>
                                        Closing Time
                                    </span>

                                    <strong id="courtClosingTime">
                                        --
                                    </strong>

                                </div>

                            </div>


                            <div class="court-info-item">

                                <div class="court-info-icon">

                                    <i class="bi bi-geo"></i>

                                </div>

                                <div>

                                    <span>
                                        Latitude
                                    </span>

                                    <strong id="courtLatitude">
                                        --
                                    </strong>

                                </div>

                            </div>


                            <div class="court-info-item">

                                <div class="court-info-icon">

                                    <i class="bi bi-pin-map"></i>

                                </div>

                                <div>

                                    <span>
                                        Longitude
                                    </span>

                                    <strong id="courtLongitude">
                                        --
                                    </strong>

                                </div>

                            </div>


                        </div>

                    </div>


                    <!-- BOOK BUTTON -->

                    <div class="court-details-book">

                        <button
                            type="button"
                            id="bookCourtBtn"
                            class="btn court-book-btn">

                            <i class="bi bi-calendar-check"></i>

                            Book This Court

                        </button>

                    </div>


                </div>

            </div>

        </div>


        <!-- =====================================
             REVIEWS & RATING SECTION
        ====================================== -->

        <div class="mt-5 border-top pt-4">

            <h3 class="fw-bold mb-4">
                <i class="bi bi-star-fill text-warning me-2"></i>Court Reviews & Ratings
            </h3>

            <div class="row g-4">

                <!-- RATING SUMMARY CARD -->
                <div class="col-lg-4">

                    <div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100 bg-white">

                        <div class="display-4 fw-bold text-dark mb-1" id="averageRatingText">0.0</div>

                        <div class="star-rating-display mb-2 fs-5 text-warning" id="averageRatingStars">
                            <i class="bi bi-star"></i><i class="bi bi-star"></i><i class="bi bi-star"></i><i class="bi bi-star"></i><i class="bi bi-star"></i>
                        </div>

                        <p class="text-muted small mb-3" id="totalReviewsCountText">Based on 0 reviews</p>

                        <!-- Rating Distribution Bars -->
                        <div class="rating-distribution-bars text-start">

                            <div class="d-flex align-items-center mb-1 small">
                                <span class="me-2 text-muted" style="width: 35px;">5 <i class="bi bi-star-fill text-warning"></i></span>
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-warning" id="barStar5" style="width: 0%"></div>
                                </div>
                                <span class="ms-2 text-muted text-end" style="width: 35px;" id="countStar5">0</span>
                            </div>

                            <div class="d-flex align-items-center mb-1 small">
                                <span class="me-2 text-muted" style="width: 35px;">4 <i class="bi bi-star-fill text-warning"></i></span>
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-warning" id="barStar4" style="width: 0%"></div>
                                </div>
                                <span class="ms-2 text-muted text-end" style="width: 35px;" id="countStar4">0</span>
                            </div>

                            <div class="d-flex align-items-center mb-1 small">
                                <span class="me-2 text-muted" style="width: 35px;">3 <i class="bi bi-star-fill text-warning"></i></span>
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-warning" id="barStar3" style="width: 0%"></div>
                                </div>
                                <span class="ms-2 text-muted text-end" style="width: 35px;" id="countStar3">0</span>
                            </div>

                            <div class="d-flex align-items-center mb-1 small">
                                <span class="me-2 text-muted" style="width: 35px;">2 <i class="bi bi-star-fill text-warning"></i></span>
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-warning" id="barStar2" style="width: 0%"></div>
                                </div>
                                <span class="ms-2 text-muted text-end" style="width: 35px;" id="countStar2">0</span>
                            </div>

                            <div class="d-flex align-items-center mb-1 small">
                                <span class="me-2 text-muted" style="width: 35px;">1 <i class="bi bi-star-fill text-warning"></i></span>
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-warning" id="barStar1" style="width: 0%"></div>
                                </div>
                                <span class="ms-2 text-muted text-end" style="width: 35px;" id="countStar1">0</span>
                            </div>

                        </div>

                    </div>

                </div>

                <!-- WRITE REVIEW FORM & REVIEWS LIST -->
                <div class="col-lg-8">

                    <!-- WRITE REVIEW FORM CARD -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white" id="writeReviewCard">

                        <h5 class="fw-bold mb-3" id="reviewFormTitle">Write a Review</h5>

                        <div id="reviewAlert" class="alert d-none"></div>

                        <form id="reviewForm">

                            <input type="hidden" id="editReviewId" value="">

                            <div class="mb-3">
                                <label class="form-label fw-semibold mb-1">Your Rating</label>
                                <div class="star-rating-input d-flex gap-1 fs-3 text-secondary" id="starRatingInput">
                                    <i class="bi bi-star star-btn" data-value="1" style="cursor: pointer;"></i>
                                    <i class="bi bi-star star-btn" data-value="2" style="cursor: pointer;"></i>
                                    <i class="bi bi-star star-btn" data-value="3" style="cursor: pointer;"></i>
                                    <i class="bi bi-star star-btn" data-value="4" style="cursor: pointer;"></i>
                                    <i class="bi bi-star star-btn" data-value="5" style="cursor: pointer;"></i>
                                </div>
                                <input type="hidden" id="reviewRatingValue" name="rating" value="0">
                            </div>

                            <div class="mb-3">
                                <label for="reviewTextInput" class="form-label fw-semibold">Your Review</label>
                                <textarea class="form-control rounded-3" id="reviewTextInput" rows="3" placeholder="Describe court quality, lighting, facilities..." required></textarea>
                            </div>

                            <div class="d-flex gap-2">

                                <button type="submit" id="submitReviewBtn" class="btn user-primary-btn px-4">
                                    <i class="bi bi-send me-1"></i> Submit Review
                                </button>

                                <button type="button" id="cancelEditReviewBtn" class="btn btn-outline-secondary d-none">
                                    Cancel
                                </button>

                            </div>

                        </form>

                    </div>

                    <!-- REVIEWS LIST CONTAINER -->
                    <div id="courtReviewsList" class="d-flex flex-column gap-3">

                        <div class="text-center py-4 text-muted">
                            <div class="spinner-border spinner-border-sm text-success me-2"></div>
                            Loading reviews...
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('styles')

@vite('resources/css/court-details.css')

@endpush


@push('scripts')

<script>

    window.COURT_ID = @json($id);

</script>

<script type="module">

    import "{{ Vite::asset('resources/js/user/court-details.js') }}";

</script>

@endpush