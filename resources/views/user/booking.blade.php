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

                        <div class="booking-summary-row">
                            <span>Court Fee</span>
                            <strong id="summaryCourtPrice">₹0</strong>
                        </div>

                        <div class="booking-summary-row">
                            <span>Platform Fee</span>
                            <strong id="summaryPlatformFee" class="text-success">+ ₹50</strong>
                        </div>

                        <hr>

                        <div class="booking-total">
                            <span>Total Payable</span>
                            <strong id="summaryTotal">₹0</strong>
                        </div>
                        <small class="d-block text-muted text-end mb-3" style="font-size: 11px;">
                            <i class="bi bi-info-circle me-1"></i> Includes ₹50 platform service fee
                        </small>


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

                <!-- Status Icon -->
                <div class="booking-success-icon" id="bookingModalIcon">
                    <i class="bi bi-clock-history"></i>
                </div>

                <div class="modal-body text-center">

                    <h3 id="bookingSuccessModalLabel">
                        Court Slot Held!
                    </h3>

                    <p class="booking-success-message" id="bookingSuccessMessage">
                        Your court slot is held. Complete payment before the timer expires to confirm your reservation.
                    </p>

                    <!-- HOLD COUNTDOWN BANNER -->
                    <div id="bookingHoldBanner" class="booking-hold-banner mb-3">
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                            <span class="hold-countdown-timer">
                                <i class="bi bi-stopwatch"></i>
                                <span id="bookingHoldTimer">10:00</span>
                            </span>
                        </div>
                        <small class="text-muted d-block mt-2" id="bookingHoldNote">
                            <i class="bi bi-shield-lock me-1"></i> Slot reserved exclusively for you during this countdown.
                        </small>
                    </div>

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

                        <button type="button" class="btn btn-success fw-bold py-2" id="payNowModalBtn">
                            <i class="bi bi-credit-card me-1"></i>
                            Pay & Confirm Booking
                        </button>

                        <button type="button" class="btn booking-view-btn d-none" id="viewMyBookingsBtn">
                            <i class="bi bi-calendar-check me-1"></i>
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


    <!-- =========================================
             PAYMENT GATEWAY SIMULATOR MODAL
        ========================================= -->
    <div class="modal fade" id="paymentSimulatorModal" tabindex="-1" aria-labelledby="paymentSimulatorLabel" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header bg-dark text-white py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success px-2 py-1">LEARNING SANDBOX</span>
                        <h5 class="modal-title mb-0 fw-bold fs-6" id="paymentSimulatorLabel">Razorpay Payment Simulator</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        <strong>Learning Test Mode:</strong> No real money will be charged. This simulates the official Razorpay payment flow.
                    </div>

                    <div class="text-center py-2 mb-3">
                        <small class="text-muted text-uppercase fw-semibold" style="letter-spacing: 1px;">Amount Payable</small>
                        <h2 class="fw-bold text-success mt-1 mb-0" id="simModalAmount">₹0</h2>
                        <small class="text-muted" id="simModalOrderId">Order ID: --</small>
                    </div>

                    <div class="payment-method-selector mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Select Test Payment Method</label>
                        <div class="list-group">
                            <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3">
                                <input class="form-check-input flex-shrink-0" type="radio" name="simPaymentMethod" value="upi" checked>
                                <div>
                                    <div class="fw-bold"><i class="bi bi-qr-code-scan me-1 text-primary"></i> UPI (GPay / PhonePe / Paytm)</div>
                                    <small class="text-muted">Simulates instant UPI pin verification</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3">
                                <input class="form-check-input flex-shrink-0" type="radio" name="simPaymentMethod" value="card">
                                <div>
                                    <div class="fw-bold"><i class="bi bi-credit-card-2-front me-1 text-success"></i> Test Debit/Credit Card</div>
                                    <small class="text-muted">Simulates 4111-XXXX-XXXX-4242 with OTP</small>
                                </div>
                            </label>
                            <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3">
                                <input class="form-check-input flex-shrink-0" type="radio" name="simPaymentMethod" value="netbanking">
                                <div>
                                    <div class="fw-bold"><i class="bi bi-bank me-1 text-warning"></i> NetBanking</div>
                                    <small class="text-muted">Simulates mock bank gateway redirect</small>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success fw-bold py-2" id="simSuccessBtn">
                            <i class="bi bi-check-circle-fill me-1"></i> Simulate Successful Payment
                        </button>
                        <button type="button" class="btn btn-outline-danger fw-semibold py-2" id="simFailBtn">
                            <i class="bi bi-x-circle-fill me-1"></i> Simulate Payment Failure
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

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <script>

        window.BOOKING_COURT_ID = @json($id);
        window.MY_BOOKINGS_URL = "{{ route('user.bookings') }}";
    </script>

    @vite('resources/js/user/booking.js')

@endpush