@extends('layouts.user')

@section('title', 'My Wishlist')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="wishlist-header mb-4">

        <div>
            <div class="page-label">
                <i class="bi bi-heart-fill"></i>
                <span>MY FAVORITES</span>
            </div>

            <h2 class="fw-bold mb-1">
                My Wishlist
            </h2>

            <p class="text-muted mb-0">
                Your favorite courts in one place.
            </p>
        </div>

        <a href="/user/courts" class="user-primary-btn text-decoration-none mt-3 mt-md-0">
            <i class="bi bi-search me-1"></i>
            Explore Courts
        </a>

    </div>


    {{-- Loading --}}
    <div id="wishlistLoading" class="text-center py-5">

        <div class="spinner-border text-success" role="status"></div>

        <p class="text-muted mt-3 mb-0">
            Loading your wishlist...
        </p>

    </div>


    {{-- Error --}}
    <div id="wishlistError" class="d-none">

        <div class="alert alert-danger d-flex align-items-center">

            <i class="bi bi-exclamation-circle me-2"></i>

            <span id="wishlistErrorMessage">
                Unable to load wishlist.
            </span>

        </div>

        <button
            type="button"
            id="retryWishlist"
            class="btn btn-outline-success"
        >
            <i class="bi bi-arrow-clockwise me-1"></i>
            Try Again
        </button>

    </div>


    {{-- Wishlist Content --}}
    <div id="wishlistContent" class="d-none">

        {{-- Wishlist Count --}}
        <div class="mb-4">

            <span
                id="wishlistCount"
                class="text-muted fw-bold"
            >
                0 courts
            </span>

        </div>


        {{-- Wishlist Cards --}}
        <div
            id="wishlistGrid"
            class="row g-4"
        ></div>


        {{-- Empty Wishlist --}}
        <div
            id="emptyWishlist"
            class="d-none empty-wishlist-box py-5"
        >

            <div class="empty-wishlist-icon">
                <i class="bi bi-heart"></i>
            </div>

            <h3 class="fw-bold mb-2">
                Your Wishlist is Empty
            </h3>

            <p class="text-muted mb-4">
                Save your favorite courts here and book them whenever you want.
            </p>

            <a
                href="/user/courts"
                class="user-primary-btn text-decoration-none px-4"
            >
                <i class="bi bi-search me-1"></i>
                Explore Courts
            </a>

        </div>

    </div>

</div>


{{-- Remove Wishlist Confirmation Modal --}}
<div
    class="modal fade"
    id="removeWishlistModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow">

            <div class="modal-body text-center p-4">

                <div
                    class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                    style="
                        width: 65px;
                        height: 65px;
                        border-radius: 50%;
                        background: #fff3cd;
                        color: #856404;
                        font-size: 30px;
                    "
                >
                    <i class="bi bi-heartbreak"></i>
                </div>

                <h5 class="fw-bold mb-2">
                    Remove from Wishlist?
                </h5>

                <p
                    id="removeWishlistMessage"
                    class="text-muted mb-4"
                >
                    Are you sure you want to remove this court?
                </p>

                <div class="d-flex gap-2">

                    <button
                        type="button"
                        class="btn btn-light w-50"
                        data-bs-dismiss="modal"
                    >
                        Keep
                    </button>

                    <button
                        type="button"
                        id="confirmRemoveWishlist"
                        class="btn btn-danger w-50"
                    >
                        Remove
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>


@endsection


@push('scripts')

@vite('resources/js/user/wishlist.js')

@endpush