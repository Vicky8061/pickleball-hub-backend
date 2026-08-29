@extends('layouts.user')

@section('title', 'My Profile | Pickleball Hub')

@section('content')

<div class="container-fluid py-4">

    <!-- =========================================
         PROFILE HEADER
    ========================================== -->

    <div class="profile-header mb-4">

        <div class="profile-header-left">

            <div class="profile-avatar-large">

                <i class="bi bi-person"></i>

            </div>

            <div>

                <span class="profile-label">
                    My Profile
                </span>

                <h1 id="profileName">
                    Loading...
                </h1>

                <p id="profileEmail">
                    Loading...
                </p>

            </div>

        </div>

        <div>

            <span
                id="profileStatus"
                class="profile-status">

                Loading...

            </span>

        </div>

    </div>


    <!-- =========================================
         BECOME COURT OWNER BANNER CARD
    ========================================== -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-dark text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #111827 0%, #1f2937 100%);">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 position-relative" style="z-index: 2;">
            <div>
                <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold mb-2">PARTNER WITH US</span>
                <h4 class="fw-bold mb-1">Host Your Pickleball Courts & Earn 90% Net Revenue</h4>
                <p class="text-white-50 small mb-0">List your courts, set custom hourly slots, host local tournaments, and manage player bookings seamlessly.</p>
            </div>
            <a href="{{ route('user.become-owner') }}" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm">
                <i class="bi bi-patch-check-fill me-1"></i> Become a Court Owner
            </a>
        </div>
    </div>


    <!-- =========================================
         PLAYER STATS OVERVIEW GRID
    ========================================== -->

    <div class="row g-3 mb-4">

        <!-- Total Matches -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; font-size: 24px;">
                        <i class="bi bi-calendar2-event-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Matches</span>
                        <h3 class="fw-bold mb-0 text-dark" id="statTotalMatches">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Matches -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; font-size: 24px;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Upcoming Games</span>
                        <h3 class="fw-bold mb-0 text-dark" id="statUpcomingMatches">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tournaments Joined -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; font-size: 24px;">
                        <i class="bi bi-trophy-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Tournaments</span>
                        <h3 class="fw-bold mb-0 text-dark" id="statTournamentsJoined">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Written -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; font-size: 24px;">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Reviews Given</span>
                        <h3 class="fw-bold mb-0 text-dark" id="statReviewsCount">0</h3>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <!-- =========================================
         PROFILE CONTENT
    ========================================== -->

    <div class="row g-4">


        <!-- =====================================
             PERSONAL INFORMATION
        ====================================== -->

        <div class="col-lg-8">

            <div class="profile-card">

                <div class="profile-card-header">

                    <div>

                        <h4>
                            Personal Information
                        </h4>

                        <p>
                            Your account information
                        </p>

                    </div>

                    <button
                        type="button"
                        class="btn profile-edit-btn"
                        id="editProfileBtn">

                        <i class="bi bi-pencil"></i>

                        Edit Profile

                    </button>

                </div>


                <div class="profile-card-body">

                    <div class="row g-4">


                        <!-- NAME -->

                        <div class="col-md-6">

                            <div class="profile-info-item">

                                <span>
                                    Full Name
                                </span>

                                <strong id="infoName">
                                    Loading...
                                </strong>

                            </div>

                        </div>


                        <!-- EMAIL -->

                        <div class="col-md-6">

                            <div class="profile-info-item">

                                <span>
                                    Email Address
                                </span>

                                <strong id="infoEmail">
                                    Loading...
                                </strong>

                            </div>

                        </div>


                        <!-- ROLE -->

                        <div class="col-md-6">

                            <div class="profile-info-item">

                                <span>
                                    Account Type
                                </span>

                                <strong id="infoRole">
                                    Loading...
                                </strong>

                            </div>

                        </div>


                        <!-- STATUS -->

                        <div class="col-md-6">

                            <div class="profile-info-item">

                                <span>
                                    Account Status
                                </span>

                                <strong id="infoStatus">
                                    Loading...
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================
                 EDIT PROFILE
            ====================================== -->

            <div
                class="profile-card mt-4 d-none"
                id="editProfileCard">

                <div class="profile-card-header">

                    <div>

                        <h4>
                            Edit Profile
                        </h4>

                        <p>
                            Update your personal information
                        </p>

                    </div>

                </div>


                <div class="profile-card-body">

                    <div
                        id="profileAlert"
                        class="alert d-none">
                    </div>


                    <form id="profileForm">

                        <div class="row g-3">


                            <!-- NAME -->

                            <div class="col-md-6">

                                <label
                                    for="editName"
                                    class="form-label">

                                    Full Name

                                </label>

                                <input
                                    type="text"
                                    id="editName"
                                    class="form-control"
                                    placeholder="Enter your name">

                                <div
                                    id="nameError"
                                    class="profile-error">
                                </div>

                            </div>


                            <!-- EMAIL -->

                            <div class="col-md-6">

                                <label
                                    for="editEmail"
                                    class="form-label">

                                    Email Address

                                </label>

                                <input
                                    type="email"
                                    id="editEmail"
                                    class="form-control"
                                    placeholder="Enter your email">

                                <div
                                    id="emailError"
                                    class="profile-error">
                                </div>

                            </div>


                            <!-- BUTTONS -->

                            <div class="col-12">

                                <div class="profile-form-actions">

                                    <button
                                        type="button"
                                        class="btn profile-cancel-btn"
                                        id="cancelEditBtn">

                                        Cancel

                                    </button>

                                    <button
                                        type="submit"
                                        class="btn profile-save-btn"
                                        id="saveProfileBtn">

                                        <span id="saveProfileText">
                                            Save Changes
                                        </span>

                                        <span
                                            id="saveProfileSpinner"
                                            class="spinner-border spinner-border-sm d-none"
                                            role="status">
                                        </span>

                                    </button>

                                </div>

                            </div>

                        </div>

                    </form>

                </div>

            </div>


            <!-- =====================================
                 FAVORITE COURT HIGHLIGHT CARD
            ====================================== -->

            <div class="profile-card mt-4 d-none" id="favoriteCourtContainer">

                <div class="profile-card-header">

                    <div>

                        <h4><i class="bi bi-heart-fill text-danger me-1"></i> Favorite Court</h4>

                        <p>Your #1 most frequently booked court</p>

                    </div>

                </div>

                <div class="profile-card-body" id="favoriteCourtContent">

                    <!-- Populated dynamically by profile.js -->

                </div>

            </div>


            <!-- =====================================
                 PLAYER ACHIEVEMENT BADGES
            ====================================== -->

            <div class="profile-card mt-4">

                <div class="profile-card-header">

                    <div>

                        <h4><i class="bi bi-award-fill text-warning me-1"></i> Player Badges & Achievements</h4>

                        <p>Earn badges by playing matches and participating in the community</p>

                    </div>

                </div>

                <div class="profile-card-body">

                    <div class="row g-3" id="playerBadgesGrid">

                        <!-- Populated dynamically by profile.js -->

                    </div>

                </div>

            </div>


            <!-- =====================================
                 RECENT ACTIVITY TIMELINE
            ====================================== -->

            <div class="profile-card mt-4">

                <div class="profile-card-header">

                    <div>

                        <h4><i class="bi bi-activity text-success me-1"></i> Recent Activity</h4>

                        <p>Your recent match bookings and actions</p>

                    </div>

                </div>

                <div class="profile-card-body" id="recentActivityList">

                    <!-- Populated dynamically by profile.js -->

                </div>

            </div>

        </div>


        <!-- =====================================
             ACCOUNT SIDEBAR
        ====================================== -->

        <div class="col-lg-4">


            <!-- ACCOUNT CARD -->

            <div class="profile-card mb-4">

                <div class="profile-card-header">

                    <div>

                        <h4>
                            Account
                        </h4>

                        <p>
                            Manage your account
                        </p>

                    </div>

                </div>


                <div class="profile-menu">

                    <a
                        href="{{ route('user.bookings') }}"
                        class="profile-menu-item">

                        <div class="profile-menu-icon">

                            <i class="bi bi-calendar-check"></i>

                        </div>

                        <div>

                            <strong>
                                My Bookings
                            </strong>

                            <small>
                                View your court bookings
                            </small>

                        </div>

                        <i class="bi bi-chevron-right ms-auto"></i>

                    </a>


                    <a
                        href="{{ route('user.tournaments') }}"
                        class="profile-menu-item">

                        <div class="profile-menu-icon">

                            <i class="bi bi-trophy"></i>

                        </div>

                        <div>

                            <strong>
                                Tournaments
                            </strong>

                            <small>
                                Manage your tournaments
                            </small>

                        </div>

                        <i class="bi bi-chevron-right ms-auto"></i>

                    </a>


                    <a
                        href="{{ route('user.wishlist') }}"
                        class="profile-menu-item">

                        <div class="profile-menu-icon">

                            <i class="bi bi-heart"></i>

                        </div>

                        <div>

                            <strong>
                                Wishlist
                            </strong>

                            <small>
                                Your saved courts
                            </small>

                        </div>

                        <i class="bi bi-chevron-right ms-auto"></i>

                    </a>

                </div>

            </div>


            <!-- LOGOUT CARD -->

            <div class="profile-card">

                <div class="profile-logout">

                    <div class="profile-logout-icon">

                        <i class="bi bi-box-arrow-right"></i>

                    </div>

                    <div>

                        <h5>
                            Logout
                        </h5>

                        <p>
                            Sign out from your account
                        </p>

                    </div>

                    <button
                        type="button"
                        id="profileLogoutBtn"
                        class="btn btn-outline-danger ms-auto">

                        Logout

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script type="module">
    import "{{ Vite::asset('resources/js/user/profile.js') }}";
</script>

@endpush