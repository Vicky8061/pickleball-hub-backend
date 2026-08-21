@extends('layouts.auth')

@section('title', 'Create Account | Pickleball Hub')


@section('content')

<div class="auth-page">


    <!-- =========================================
         LEFT VISUAL
    ========================================== -->

    <section class="auth-visual">

        <div class="visual-content">


            <!-- BRAND LOGO -->

            <div class="brand-logo">

                <img
                    src="{{ asset('assets/images/logo.png') }}"
                    alt="Pickleball Hub"
                    class="brand-logo-img">

            </div>


            <!-- HEADING -->

            <h1>

                Start Your<br>
                Pickleball<br>
                Journey.

            </h1>


            <!-- DESCRIPTION -->

            <p>

                Create your account and discover
                courts, tournaments and a community
                built around the game you love.

            </p>


            <!-- FEATURES -->

            <div class="feature-list">


                <div class="feature-item">

                    <div class="feature-icon">

                        <i class="bi bi-check-lg"></i>

                    </div>

                    Book courts easily

                </div>


                <div class="feature-item">

                    <div class="feature-icon">

                        <i class="bi bi-check-lg"></i>

                    </div>

                    Join exciting tournaments

                </div>


                <div class="feature-item">

                    <div class="feature-icon">

                        <i class="bi bi-check-lg"></i>

                    </div>

                    Save your favorite courts

                </div>


            </div>

        </div>

    </section>



    <!-- =========================================
         REGISTER FORM
    ========================================== -->

    <section class="auth-form-section">

        <div class="auth-card">


            <!-- =====================================
                 MOBILE BRAND
            ====================================== -->

            <div class="mobile-brand">

                <img
                    src="{{ asset('assets/images/logo.png') }}"
                    alt="Pickleball Hub"
                    class="brand-logo-img">

            </div>



            <!-- =====================================
                 HEADING
            ====================================== -->

            <div class="auth-heading">

                <h2>

                    Create your account

                </h2>

                <p>

                    Join Pickleball Hub today.

                </p>

            </div>



            <!-- =====================================
                 ALERT
            ====================================== -->

            <div
                id="registerAlert"
                class="alert auth-alert d-none">
            </div>



            <!-- =====================================
                 REGISTER FORM
            ====================================== -->

            <form id="registerForm">


                <!-- NAME -->

                <div class="mb-3">

                    <label
                        for="name"
                        class="form-label">

                        Full name

                    </label>

                    <input
                        type="text"
                        id="name"
                        class="form-control"
                        placeholder="Enter your full name"
                        autocomplete="name"
                        required>

                    <div
                        id="nameError"
                        class="text-danger small mt-1">
                    </div>

                </div>



                <!-- EMAIL -->

                <div class="mb-3">

                    <label
                        for="email"
                        class="form-label">

                        Email address

                    </label>

                    <input
                        type="email"
                        id="email"
                        class="form-control"
                        placeholder="you@example.com"
                        autocomplete="email"
                        required>

                    <div
                        id="emailError"
                        class="text-danger small mt-1">
                    </div>

                </div>



                <!-- PASSWORD -->

                <div class="mb-2">

                    <label
                        for="password"
                        class="form-label">

                        Password

                    </label>


                    <div class="input-group">

                        <input
                            type="password"
                            id="password"
                            class="form-control"
                            placeholder="Create a strong password"
                            autocomplete="new-password"
                            required>

                        <button
                            type="button"
                            class="password-toggle"
                            id="togglePassword">

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>


                    <!-- PASSWORD STRENGTH -->

                    <div class="password-strength">

                        <div
                            id="strengthBar"
                            class="password-strength-bar">
                        </div>

                    </div>


                    <div
                        id="strengthText"
                        class="password-strength-text">
                    </div>


                    <div
                        id="passwordError"
                        class="text-danger small mt-1">
                    </div>

                </div>



                <!-- CONFIRM PASSWORD -->

                <div class="mb-4">

                    <label
                        for="password_confirmation"
                        class="form-label">

                        Confirm password

                    </label>


                    <div class="input-group">

                        <input
                            type="password"
                            id="password_confirmation"
                            class="form-control"
                            placeholder="Confirm your password"
                            autocomplete="new-password"
                            required>

                        <button
                            type="button"
                            class="password-toggle"
                            id="toggleConfirmPassword">

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>


                    <div
                        id="passwordConfirmationError"
                        class="text-danger small mt-1">
                    </div>

                </div>



                <!-- REGISTER BUTTON -->

                <button
                    type="submit"
                    id="registerBtn"
                    class="btn auth-btn w-100">

                    <span id="registerText">

                        Create account

                    </span>

                    <span
                        id="registerSpinner"
                        class="spinner-border spinner-border-sm d-none">
                    </span>

                </button>


            </form>



            <!-- =====================================
                 LOGIN SWITCH
            ====================================== -->

            <div class="auth-switch">

                Already have an account?

                <a href="{{ route('login') }}">

                    Login

                </a>

            </div>


        </div>

    </section>

</div>

@endsection

@push('scripts')
@vite('resources/js/auth/register.js')
@endpush