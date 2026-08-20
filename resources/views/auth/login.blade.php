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

<script>
    document.addEventListener('DOMContentLoaded', function() {


        const form =
            document.getElementById('loginForm');

        const alertBox =
            document.getElementById('loginAlert');

        const loginBtn =
            document.getElementById('loginBtn');

        const loginText =
            document.getElementById('loginText');

        const spinner =
            document.getElementById('loginSpinner');


        // =========================================
        // PASSWORD TOGGLE
        // =========================================

        document
            .getElementById('togglePassword')
            .addEventListener('click', function() {

                const password =
                    document.getElementById('password');

                const icon =
                    this.querySelector('i');


                if (password.type === 'password') {

                    password.type = 'text';

                    icon.className =
                        'bi bi-eye-slash';

                } else {

                    password.type = 'password';

                    icon.className =
                        'bi bi-eye';

                }

            });


        // =========================================
        // LOGIN
        // =========================================

        form.addEventListener('submit', async function(e) {

            e.preventDefault();

            clearErrors();

            setLoading(true);


            const email =
                document.getElementById('email').value.trim();

            const password =
                document.getElementById('password').value;


            try {

                const response = await fetch('/api/login', {

                    method: 'POST',

                    headers: {

                        'Content-Type': 'application/json',

                        'Accept': 'application/json'

                    },

                    body: JSON.stringify({

                        email: email,

                        password: password

                    })

                });


                const result =
                    await response.json();


                // =================================
                // VALIDATION ERROR
                // =================================

                if (response.status === 422) {

                    showValidationErrors(
                        result.errors
                    );

                    return;

                }


                // =================================
                // OTHER ERROR
                // =================================

                if (!response.ok) {

                    showAlert(
                        result.message ||
                        'Invalid email or password.',
                        'danger'
                    );

                    return;

                }


                // =================================
                // SUCCESS
                // =================================

                const token =
                    result.data?.token ??
                    result.token;


                const user =
                    result.data?.user ??
                    result.user;


                if (!token) {

                    showAlert(
                        'Login successful but authentication token was not received.',
                        'danger'
                    );

                    return;

                }


                // Store token

                localStorage.setItem(
                    'auth_token',
                    token
                );


                // Store user

                if (user) {

                    localStorage.setItem(
                        'auth_user',
                        JSON.stringify(user)
                    );

                }


                showAlert(
                    result.message ||
                    'Login successful!',
                    'success'
                );


                // =================================
                // REDIRECT
                // =================================

                setTimeout(() => {

                    if (user?.role === 'admin') {

                        window.location.href =
                            '/admin/dashboard';

                    } else if (user?.role === 'owner') {

                        window.location.href =
                            '/owner/dashboard';

                    } else {

                        window.location.href =
                            '/user/dashboard';

                    }

                }, 700);


            } catch (error) {

                console.error(error);

                showAlert(
                    'Unable to connect to the server.',
                    'danger'
                );

            } finally {

                setLoading(false);

            }

        });


        // =========================================
        // LOADING
        // =========================================

        function setLoading(loading) {

            loginBtn.disabled = loading;

            if (loading) {

                loginText.textContent =
                    'Logging in...';

                spinner.classList.remove(
                    'd-none'
                );

            } else {

                loginText.textContent =
                    'Login';

                spinner.classList.add(
                    'd-none'
                );

            }

        }


        // =========================================
        // ALERT
        // =========================================

        function showAlert(message, type) {

            alertBox.className =
                `alert alert-${type} auth-alert`;

            alertBox.textContent =
                message;

        }


        // =========================================
        // VALIDATION
        // =========================================

        function showValidationErrors(errors) {

            if (!errors) return;


            if (errors.email) {

                document.getElementById(
                        'emailError'
                    ).textContent =
                    errors.email[0];

            }


            if (errors.password) {

                document.getElementById(
                        'passwordError'
                    ).textContent =
                    errors.password[0];

            }


            showAlert(
                'Please check the highlighted fields.',
                'danger'
            );

        }


        // =========================================
        // CLEAR ERRORS
        // =========================================

        function clearErrors() {

            document.getElementById(
                'emailError'
            ).textContent = '';


            document.getElementById(
                'passwordError'
            ).textContent = '';


            alertBox.className =
                'alert auth-alert d-none';

            alertBox.textContent = '';

        }

    });
</script>

@endpush