@extends('layouts.auth')

@section('title', 'Login | Pickleball Hub')


@section('content')

<div class="auth-page">


    <!-- =========================================
         LEFT VISUAL
    ========================================== -->

    <section class="auth-visual">

        <div class="visual-content">

            <div class="brand-logo">

                <img
                    src="{{ asset('assets/images/logo.png') }}"
                    alt="Pickleball Hub"
                    class="brand-logo-img">
            </div>


            <h1>
                Your Game.<br>
                Your Court.<br>
                Your Community.
            </h1>


            <p>
                Book your favorite pickleball courts,
                discover tournaments and connect with
                the pickleball community.
            </p>


            <div class="feature-list">

                <div class="feature-item">

                    <div class="feature-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>

                    Easy court booking

                </div>


                <div class="feature-item">

                    <div class="feature-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>

                    Discover tournaments

                </div>


                <div class="feature-item">

                    <div class="feature-icon">
                        <i class="bi bi-check-lg"></i>
                    </div>

                    Find the best courts near you

                </div>

            </div>

        </div>

    </section>



    <!-- =========================================
         LOGIN FORM
    ========================================== -->

    <section class="auth-form-section">

        <div class="auth-card">


            <!-- Mobile Brand -->

            <div class="mobile-brand">

                <img
                    src="{{ asset('assets/images/logo.png') }}"
                    alt="Pickleball Hub"
                    class="brand-logo-img">
            </div>


            <div class="auth-heading">

                <h2>
                    Welcome back 👋
                </h2>

                <p>
                    Login to continue to your account.
                </p>

            </div>


            <!-- Alert -->

            <div
                id="loginAlert"
                class="alert auth-alert d-none">
            </div>


            <!-- Form -->

            <form id="loginForm">


                <!-- EMAIL -->

                <div class="mb-4">

                    <label
                        class="form-label"
                        for="email">

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

                <div class="mb-4">

                    <div class="d-flex justify-content-between">

                        <label
                            class="form-label"
                            for="password">

                            Password

                        </label>

                    </div>


                    <div class="input-group">

                        <input
                            type="password"
                            id="password"
                            class="form-control"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required>

                        <button
                            type="button"
                            class="password-toggle"
                            id="togglePassword">

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>


                    <div
                        id="passwordError"
                        class="text-danger small mt-1">
                    </div>

                </div>


                <!-- LOGIN -->

                <button
                    type="submit"
                    id="loginBtn"
                    class="btn auth-btn w-100">

                    <span id="loginText">
                        Login
                    </span>

                    <span
                        id="loginSpinner"
                        class="spinner-border spinner-border-sm d-none">
                    </span>

                </button>


            </form>


            <!-- REGISTER -->

            <div class="auth-switch">

                Don't have an account?

                <a href="{{ route('register') }}">
                    Create account
                </a>

            </div>


        </div>

    </section>

</div>

@endsection

@push('scripts')
    @vite('resources/js/auth/login.js')
@endpush


