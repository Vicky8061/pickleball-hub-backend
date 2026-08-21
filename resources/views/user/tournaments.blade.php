@extends('layouts.user')

@section('title', 'Tournaments | Pickleball Hub')

@section('content')

<div class="container-fluid py-4">

    <!-- =========================================
         PAGE HEADER
    ========================================== -->

    <div class="tournament-page-header">

        <div>
            <span class="page-label">
                <i class="bi bi-trophy"></i>
                Pickleball Events
            </span>

            <h1>
                Tournaments
            </h1>

            <p>
                Find tournaments, compete with players,
                and show your skills.
            </p>
        </div>

    </div>


    <!-- =========================================
         FILTERS
    ========================================== -->

    <div class="tournament-filters mb-4">

        <div class="row g-3 align-items-center">

            <div class="col-lg-5">

                <div class="search-box">

                    <i class="bi bi-search"></i>

                    <input
                        type="text"
                        id="tournamentSearch"
                        class="form-control"
                        placeholder="Search tournaments..."
                    >

                </div>

            </div>


            <div class="col-lg-3">

                <select
                    id="tournamentStatus"
                    class="form-select">

                    <option value="all">
                        All Tournaments
                    </option>

                    <option value="upcoming">
                        Upcoming
                    </option>

                    <option value="ongoing">
                        Ongoing
                    </option>

                    <option value="completed">
                        Completed
                    </option>

                    <option value="cancelled">
                        Cancelled
                    </option>

                </select>

            </div>

        </div>

    </div>


    <!-- =========================================
         TOURNAMENTS
    ========================================== -->

    <div
        class="row g-4"
        id="tournamentsContainer">

        <!-- Loading -->

        <div class="col-12">

            <div class="empty-state">

                <div class="spinner-border text-success"></div>

                <p>
                    Loading tournaments...
                </p>

            </div>

        </div>

    </div>


    <!-- =========================================
         NO RESULTS
    ========================================== -->

    <div
        id="noTournaments"
        class="empty-state d-none">

        <i class="bi bi-trophy"></i>

        <h5>
            No tournaments found
        </h5>

        <p>
            Try changing your search or filter.
        </p>

    </div>


</div>

@endsection


@push('scripts')

<script type="module">
    import "{{ Vite::asset('resources/js/user/tournaments.js') }}";
</script>

@endpush