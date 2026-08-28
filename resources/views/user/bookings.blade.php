@extends('layouts.user')

@section('title', 'My Bookings')

@section('content')

<div class="container py-5">

    {{-- PAGE HEADER --}}
    <div class="bookings-header mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-calendar-check me-2"></i>
                My Bookings
            </h2>

            <p class="text-muted mb-0">
                View and manage your court bookings.
            </p>
        </div>

    </div>


    {{-- LOADING --}}
    <div id="bookingsLoading" class="row g-3">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="skeleton-card p-3">
                <div class="skeleton skeleton-title"></div>
                <div class="skeleton skeleton-text w-75"></div>
                <div class="skeleton skeleton-text w-50 mb-3"></div>
                <div class="skeleton skeleton-badge mt-auto"></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="skeleton-card p-3">
                <div class="skeleton skeleton-title"></div>
                <div class="skeleton skeleton-text w-75"></div>
                <div class="skeleton skeleton-text w-50 mb-3"></div>
                <div class="skeleton skeleton-badge mt-auto"></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="skeleton-card p-3">
                <div class="skeleton skeleton-title"></div>
                <div class="skeleton skeleton-text w-75"></div>
                <div class="skeleton skeleton-text w-50 mb-3"></div>
                <div class="skeleton skeleton-badge mt-auto"></div>
            </div>
        </div>
    </div>


    {{-- ERROR --}}
    <div id="bookingsError" class="alert alert-danger d-none">

        <div class="d-flex align-items-center gap-3">

            <i class="bi bi-exclamation-circle fs-4"></i>

            <div class="flex-grow-1">

                <strong>Unable to load bookings</strong>

                <div
                    id="bookingsErrorMessage"
                    class="small mt-1"
                ></div>

            </div>

            <button
                type="button"
                id="retryBookings"
                class="btn btn-outline-danger btn-sm"
            >
                Retry
            </button>

        </div>

    </div>


    {{-- EMPTY STATE --}}
    <div id="noBookings" class="d-none">

        <div class="empty-bookings">

            <div class="empty-icon">

                <i class="bi bi-calendar-x"></i>

            </div>

            <h4 class="fw-bold">
                No Bookings Yet
            </h4>

            <p class="text-muted">
                You haven't booked any pickleball courts yet.
            </p>

            <a
                href="/user/courts"
                class="btn btn-success px-4"
            >
                <i class="bi bi-search me-2"></i>
                Find a Court
            </a>

        </div>

    </div>


    {{-- BOOKINGS --}}
    <div id="bookingsContent" class="d-none">

        <div
            id="bookingsList"
            class="row g-4"
        ></div>

    </div>

</div>


{{-- =========================================
     BOOKING DETAILS MODAL
========================================= --}}

<div
    class="modal fade"
    id="bookingDetailsModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header">

                <h5 class="modal-title fw-bold">

                    <i class="bi bi-calendar-check me-2"></i>

                    Booking Details

                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div class="modal-body">

                {{-- COURT --}}
                <div class="detail-court mb-4">

                    <div class="detail-court-icon">

                        <i class="bi bi-dribbble"></i>

                    </div>

                    <div>

                        <h6
                            id="detailCourtName"
                            class="fw-bold mb-1"
                        >
                            --
                        </h6>

                        <small
                            id="detailCourtAddress"
                            class="text-muted"
                        >
                            --
                        </small>

                    </div>

                </div>


                {{-- DETAILS --}}
                <div class="detail-box">

                    <div class="detail-row">

                        <span>Booking ID</span>

                        <strong id="detailBookingId">
                            --
                        </strong>

                    </div>


                    <div class="detail-row">

                        <span>Date</span>

                        <strong id="detailBookingDate">
                            --
                        </strong>

                    </div>


                    <div class="detail-row">

                        <span>Time</span>

                        <strong id="detailBookingTime">
                            --
                        </strong>

                    </div>


                    <div class="detail-row">
                        <span>Court Price</span>
                        <strong id="detailCourtPrice">₹0</strong>
                    </div>

                    <div class="detail-row">
                        <span>Platform Fee</span>
                        <strong id="detailPlatformFee" class="text-success">+ ₹50</strong>
                    </div>

                    <div class="detail-row">
                        <span>Total Paid</span>
                        <strong
                            id="detailBookingAmount"
                            class="text-success"
                        >
                            ₹0
                        </strong>
                    </div>


                    <div class="detail-row">

                        <span>Payment</span>

                        <span id="detailPaymentStatus">
                            --
                        </span>

                    </div>


                    <div class="detail-row">

                        <span>Booking Status</span>

                        <span id="detailBookingStatus">
                            --
                        </span>

                    </div>

                </div>

            </div>


            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>

            </div>

        </div>

    </div>

</div>


{{-- =========================================
     CANCEL CONFIRMATION MODAL
========================================= --}}

<div
    class="modal fade"
    id="cancelBookingModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg">

            <div class="modal-body text-center p-4 p-md-5">

                <div class="cancel-icon mb-3">

                    <i class="bi bi-exclamation-triangle"></i>

                </div>


                <h5 class="fw-bold mb-2">
                    Cancel Booking?
                </h5>


                <p class="text-muted mb-4">

                    Are you sure you want to cancel this booking?

                </p>


                <div class="d-flex gap-2">

                    <button
                        type="button"
                        class="btn btn-light w-50"
                        data-bs-dismiss="modal"
                    >
                        No, Keep It
                    </button>


                    <button
                        type="button"
                        id="confirmCancelBookingBtn"
                        class="btn btn-danger w-50"
                    >
                        Yes, Cancel
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>


@endsection


@push('styles')

@vite('resources/css/bookings.css')



@push('scripts')

@vite('resources/js/user/bookings.js')

@endpush