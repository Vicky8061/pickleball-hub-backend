@extends('layouts.user')

@section('title', 'Book Court | Pickleball Hub')

@section('content')

    <div class="container-fluid py-4">

        <!-- =========================================
                     BACK
                ========================================== -->

        <div class="booking-back mb-3">

            <a href="{{ route('user.courts-details', $id) }}">

                <i class="bi bi-arrow-left"></i>

                Back to Court

            </a>

        </div>


        <!-- =========================================
                     PAGE HEADER
                ========================================== -->

        <div class="booking-header mb-4">

            <div>

                <span class="booking-eyebrow">
                    BOOK YOUR GAME
                </span>

                <h1>
                    Book Court
                </h1>

                <p>
                    Select your date and preferred time slot.
                </p>

            </div>

        </div>


        <!-- =========================================
                     LOADING
                ========================================== -->

        <div id="bookingLoading" class="booking-loading">

            <div class="spinner-border text-success"></div>

            <p>
                Loading court information...
            </p>

        </div>


        <!-- =========================================
                     ERROR
                ========================================== -->

        <div id="bookingError" class="booking-message d-none">

            <div class="booking-message-icon">

                <i class="bi bi-exclamation-circle"></i>

            </div>

            <h4>
                Unable to load booking information
            </h4>

            <p id="bookingErrorMessage">
                Something went wrong.
            </p>

            <button type="button" id="retryBooking" class="btn user-primary-btn">

                <i class="bi bi-arrow-repeat"></i>

                Try Again

            </button>

        </div>


        <!-- =========================================
                     BOOKING CONTENT
                ========================================== -->

        <div id="bookingContent" class="d-none">

            <div class="row g-4">


                <!-- =====================================
                             LEFT SIDE
                        ====================================== -->

                <div class="col-lg-8">


                    <!-- COURT CARD -->

                    <div class="booking-court-card mb-4">

                        <div class="booking-court-image">

                            <img id="bookingCourtImage" src="" alt="Court">

                            <div id="bookingCourtImagePlaceholder" class="booking-image-placeholder d-none">

                                <i class="bi bi-image"></i>

                            </div>

                        </div>


                        <div class="booking-court-info">

                            <span id="bookingCourtType" class="booking-type">

                                Court

                            </span>

                            <h2 id="bookingCourtName">
                                Pickleball Court
                            </h2>

                            <p>

                                <i class="bi bi-geo-alt-fill"></i>

                                <span id="bookingCourtAddress">
                                    Location unavailable
                                </span>

                            </p>

                            <div class="booking-court-price">

                                <strong id="bookingCourtPrice">
                                    ₹0
                                </strong>

                                <span>
                                    / hour
                                </span>

                            </div>

                        </div>

                    </div>


                    <!-- DATE -->

                    <div class="booking-section-card">

                        <div class="booking-section-title">

                            <div class="booking-section-icon">

                                <i class="bi bi-calendar3"></i>

                            </div>

                            <div>

                                <h3>
                                    Select Date
                                </h3>

                                <p>
                                    Choose when you want to play.
                                </p>

                            </div>

                        </div>


                        <div class="booking-date-wrapper">

                            <label for="bookingDate">
                                Booking Date
                            </label>

                            <input type="date" id="bookingDate" class="form-control booking-date-input">

                        </div>

                    </div>


                    <!-- TIME SLOTS -->

                    <div class="booking-section-card mt-4">

                        <div class="booking-section-title">

                            <div class="booking-section-icon">

                                <i class="bi bi-clock"></i>

                            </div>

                            <div>

                                <h3>
                                    Select Time Slot
                                </h3>

                                <p>
                                    Choose an available time slot.
                                </p>

                            </div>

                        </div>


                        <!-- SLOT LOADING -->

                        <div id="slotLoading" class="slot-loading d-none">

                            <div class="spinner-border spinner-border-sm text-success"></div>

                            <span>
                                Loading available slots...
                            </span>

                        </div>


                        <!-- SLOT ERROR -->

                        <div id="slotError" class="slot-error d-none">

                            <i class="bi bi-exclamation-circle"></i>

                            <span id="slotErrorMessage">
                                Unable to load time slots.
                            </span>

                        </div>


                        <!-- NO SLOTS -->

                        <div id="noSlots" class="no-slots d-none">

                            <i class="bi bi-calendar-x"></i>

                            <h4>
                                No slots available
                            </h4>

                            <p>
                                Please select another date.
                            </p>

                        </div>


                        <!-- SLOTS -->

                        <div id="timeSlots" class="time-slots-grid">

                        </div>

                    </div>

                </div>


                <!-- =====================================
                             RIGHT SIDE - SUMMARY
                        ====================================== -->

                <div class="col-lg-4">

                    <div class="booking-summary-card">

                        <div class="booking-summary-header">

                            <i class="bi bi-receipt"></i>

                            <h3>
                                Booking Summary
                            </h3>

                        </div>


                        <div class="booking-summary-court">

                            <span>
                                Court
                            </span>

                            <strong id="summaryCourtName">
                                --
                            </strong>

                        </div>


                        <div class="booking-summary-row">

                            <span>
                                Date
                            </span>

                            <strong id="summaryDate">
                                Not selected
                            </strong>

                        </div>


                        <div class="booking-summary-row">

                            <span>
                                Time
                            </span>

                            <strong id="summaryTime">
                                Not selected
                            </strong>

                        </div>


                        <div class="booking-summary-row">

                            <span>
                                Duration
                            </span>

                            <strong id="summaryDuration">
                                1 hour
                            </strong>

                        </div>


                        <hr>


                        <div class="booking-total">

                            <span>
                                Total
                            </span>

                            <strong id="summaryTotal">
                                ₹0
                            </strong>

                        </div>


                        <button type="button" id="confirmBookingBtn" class="btn booking-confirm-btn" disabled>

                            <i class="bi bi-calendar-check"></i>

                            Confirm Booking

                        </button>


                        <p class="booking-note">

                            <i class="bi bi-shield-check"></i>

                            Your booking is secure and confirmed instantly.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =========================================
             BOOKING SUCCESS MODAL
        ========================================= -->

    <div class="modal fade" id="bookingSuccessModal" tabindex="-1" aria-labelledby="bookingSuccessModalLabel">

        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content booking-success-modal">

                <!-- Success Icon -->
                <div class="booking-success-icon">
                    <i class="bi bi-check-lg"></i>
                </div>

                <div class="modal-body text-center">

                    <h3 id="bookingSuccessModalLabel">
                        Booking Confirmed!
                    </h3>

                    <p class="booking-success-message">
                        Your court has been booked successfully.
                    </p>

                    <!-- Booking ID -->
                    <div class="booking-success-id">
                        <span>Booking ID</span>
                        <strong id="successBookingId">
                            --
                        </strong>
                    </div>

                    <!-- Details -->
                    <div class="booking-success-details">

                        <div class="booking-success-detail">
                            <div class="booking-success-detail-icon">
                                <i class="bi bi-calendar3"></i>
                            </div>

                            <div>
                                <span>Date</span>
                                <strong id="successBookingDate">
                                    --
                                </strong>
                            </div>
                        </div>

                        <div class="booking-success-detail">
                            <div class="booking-success-detail-icon">
                                <i class="bi bi-clock"></i>
                            </div>

                            <div>
                                <span>Time</span>
                                <strong id="successBookingTime">
                                    --
                                </strong>
                            </div>
                        </div>

                        <div class="booking-success-detail">
                            <div class="booking-success-detail-icon">
                                <i class="bi bi-currency-rupee"></i>
                            </div>

                            <div>
                                <span>Amount</span>
                                <strong id="successBookingAmount">
                                    ₹0
                                </strong>
                            </div>
                        </div>

                    </div>

                    <!-- Buttons -->
                    <div class="booking-success-actions">

                        <button type="button" class="btn booking-view-btn" id="viewMyBookingsBtn">

                            <i class="bi bi-calendar-check"></i>

                            View My Bookings

                        </button>

                        <button type="button" class="btn booking-close-btn" data-bs-dismiss="modal">

                            Close

                        </button>

                    </div>

                </div>

            </div>
        </div>
    </div>

@endsection


@push('styles')

    @vite('resources/css/booking.css')

@endpush


@push('scripts')

    <script>

        window.BOOKING_COURT_ID = @json($id);

    </script>

    <script>
        window.BOOKING_COURT_ID = @json($id);

        window.MY_BOOKINGS_URL =
            "{{ route('user.bookings') }}";
    </script>

    <script type="module">

        import "{{ Vite::asset('resources/js/user/booking.js') }}";

    </script>

@endpush