@extends('layouts.user')

@section('title', 'Courts | Pickleball Hub')

@section('content')

<div class="container-fluid py-4">


<!-- =========================================
     PAGE HEADER
========================================== -->

<div class="courts-page-header mb-4">

    <div>
        <span class="page-label">
            <i class="bi bi-grid"></i>
            PICKLEBALL COURTS
        </span>

        <h1>
            Find Your Perfect Court
        </h1>

        <p>
            Discover available pickleball courts and book your next game.
        </p>
    </div>

</div>


<!-- =========================================
     SEARCH + FILTERS
========================================== -->

<div class="court-filters mb-4">

    <div class="row g-3 align-items-end">

        <!-- SEARCH -->

        <div class="col-lg-4 col-md-6">

            <label class="court-filter-label">
                Search Court
            </label>

            <div class="court-search-box">

                <i class="bi bi-search"></i>

                <input
                    type="text"
                    id="courtSearch"
                    class="form-control"
                    placeholder="Search by court name..."
                >

            </div>

        </div>


        <!-- COURT TYPE -->

        <div class="col-lg-2 col-md-6">

            <label class="court-filter-label">
                Court Type
            </label>

            <select
                id="courtType"
                class="form-select">

                <option value="">
                    All Types
                </option>

                <option value="Indoor">
                    Indoor
                </option>

                <option value="Outdoor">
                    Outdoor
                </option>

            </select>

        </div>


        <!-- CITY -->

        <div class="col-lg-2 col-md-6">

            <label class="court-filter-label">
                City
            </label>

            <input
                type="text"
                id="courtCity"
                class="form-control"
                placeholder="e.g. Surat"
            >

        </div>


        <!-- MIN PRICE -->

        <div class="col-lg-1 col-md-3">

            <label class="court-filter-label">
                Min ₹
            </label>

            <input
                type="number"
                id="priceMin"
                class="form-control"
                min="0"
                placeholder="200"
            >

        </div>


        <!-- MAX PRICE -->

        <div class="col-lg-1 col-md-3">

            <label class="court-filter-label">
                Max ₹
            </label>

            <input
                type="number"
                id="priceMax"
                class="form-control"
                min="0"
                placeholder="1000"
            >

        </div>


        <!-- SORT -->

        <div class="col-lg-2 col-md-6">

            <label class="court-filter-label">
                Sort By
            </label>

            <select
                id="courtSort"
                class="form-select">

                <option value="latest">
                    Latest
                </option>

                <option value="price_low">
                    Price: Low to High
                </option>

                <option value="price_high">
                    Price: High to Low
                </option>

            </select>

        </div>


        <!-- BUTTONS -->

        <div class="col-12">

            <div class="court-filter-actions">

                <button
                    type="button"
                    id="applyFilters"
                    class="btn user-primary-btn">

                    <i class="bi bi-funnel"></i>

                    Apply Filters

                </button>


                <button
                    type="button"
                    id="resetFilters"
                    class="btn court-reset-btn">

                    <i class="bi bi-arrow-counterclockwise"></i>

                    Reset

                </button>

            </div>

        </div>

    </div>

</div>


<!-- =========================================
     COURTS HEADER
========================================== -->

<div class="section-header">

    <div>

        <h3>
            Available Courts
        </h3>

        <p id="courtResultText">
            Find a court for your next game
        </p>

    </div>

</div>


<!-- =========================================
     COURTS LIST
========================================== -->

<div
    class="row g-4"
    id="courtsContainer">

    <!-- Loading state -->

    <div class="col-12">

        <div
            class="empty-state"
            id="courtsLoading">

            <div class="spinner-border text-success"></div>

            <p>
                Loading courts...
            </p>

        </div>

    </div>

</div>


<!-- =========================================
     EMPTY STATE
========================================== -->

<div
    id="courtsEmpty"
    class="d-none">

    <div class="empty-state">

        <div class="court-empty-icon">

            <i class="bi bi-search"></i>

        </div>

        <h5>
            No courts found
        </h5>

        <p>
            Try changing your search or filters.
        </p>

    </div>

</div>


<!-- =========================================
     ERROR STATE
========================================== -->

<div
    id="courtsError"
    class="d-none">

    <div class="empty-state court-error-state">

        <div class="court-empty-icon">

            <i class="bi bi-exclamation-circle"></i>

        </div>

        <h5>
            Unable to load courts
        </h5>

        <p id="courtsErrorMessage">
            Something went wrong while loading courts.
        </p>

        <button
            type="button"
            id="retryCourts"
            class="btn user-primary-btn mt-2">

            <i class="bi bi-arrow-repeat"></i>

            Try Again

        </button>

    </div>

</div>


<!-- =========================================
     PAGINATION
========================================== -->

<div
    id="courtsPagination"
    class="court-pagination d-none">

</div>
```

</div>

@endsection

@push('scripts')

<script type="module">
    import "{{ Vite::asset('resources/js/user/courts.js') }}";
</script>

@endpush
