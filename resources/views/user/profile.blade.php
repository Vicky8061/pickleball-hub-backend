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