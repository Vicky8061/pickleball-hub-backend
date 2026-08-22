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