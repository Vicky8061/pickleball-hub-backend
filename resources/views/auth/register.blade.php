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

<script>
    document.addEventListener('DOMContentLoaded', function() {


        /* =========================================
           ELEMENTS
        ========================================== */

        const form =
            document.getElementById('registerForm');

        const alertBox =
            document.getElementById('registerAlert');

        const registerBtn =
            document.getElementById('registerBtn');

        const registerText =
            document.getElementById('registerText');

        const spinner =
            document.getElementById('registerSpinner');



        /* =========================================
           PASSWORD TOGGLE
        ========================================== */

        function togglePassword(buttonId, inputId) {

            document
                .getElementById(buttonId)
                .addEventListener('click', function() {

                    const input =
                        document.getElementById(inputId);

                    const icon =
                        this.querySelector('i');


                    if (input.type === 'password') {

                        input.type = 'text';

                        icon.className =
                            'bi bi-eye-slash';

                    } else {

                        input.type = 'password';

                        icon.className =
                            'bi bi-eye';

                    }

                });

        }


        togglePassword(
            'togglePassword',
            'password'
        );


        togglePassword(
            'toggleConfirmPassword',
            'password_confirmation'
        );



        /* =========================================
           PASSWORD STRENGTH
        ========================================== */

        document
            .getElementById('password')
            .addEventListener('input', function() {

                const password = this.value;

                const bar =
                    document.getElementById('strengthBar');

                const text =
                    document.getElementById('strengthText');


                let score = 0;


                if (password.length >= 8)
                    score++;


                if (/[A-Z]/.test(password))
                    score++;


                if (/[0-9]/.test(password))
                    score++;


                if (/[^A-Za-z0-9]/.test(password))
                    score++;


                if (password.length === 0) {

                    bar.style.width = '0%';

                    text.textContent = '';

                } else if (score <= 1) {

                    bar.style.width = '25%';

                    text.textContent =
                        'Weak password';

                } else if (score === 2) {

                    bar.style.width = '50%';

                    text.textContent =
                        'Medium password';

                } else if (score === 3) {

                    bar.style.width = '75%';

                    text.textContent =
                        'Good password';

                } else {

                    bar.style.width = '100%';

                    text.textContent =
                        'Strong password';

                }

            });



        /* =========================================
           REGISTER
        ========================================== */

        form.addEventListener(
            'submit',
            async function(e) {

                e.preventDefault();


                clearErrors();

                setLoading(true);


                const name =
                    document
                    .getElementById('name')
                    .value
                    .trim();


                const email =
                    document
                    .getElementById('email')
                    .value
                    .trim();


                const password =
                    document
                    .getElementById('password')
                    .value;


                const confirmation =
                    document
                    .getElementById(
                        'password_confirmation'
                    )
                    .value;



                try {


                    const response =
                        await fetch(
                            '/api/register', {

                                method: 'POST',

                                headers: {

                                    'Content-Type': 'application/json',

                                    'Accept': 'application/json'

                                },

                                body: JSON.stringify({

                                    name: name,

                                    email: email,

                                    password: password,

                                    password_confirmation: confirmation

                                })

                            }
                        );



                    const result =
                        await response.json();



                    /* =================================
                       VALIDATION ERROR
                    ================================= */

                    if (response.status === 422) {

                        showValidationErrors(
                            result.errors
                        );

                        return;

                    }



                    /* =================================
                       OTHER ERROR
                    ================================= */

                    if (!response.ok) {

                        showAlert(

                            result.message ||
                            'Registration failed.',

                            'danger'

                        );

                        return;

                    }



                    /* =================================
                       SUCCESS
                    ================================= */

                    showAlert(

                        result.message ||
                        'Account created successfully!',

                        'success'

                    );



                    /* =================================
                       TOKEN
                    ================================= */

                    const token =
                        result.data?.token ??
                        result.token;


                    if (token) {

                        localStorage.setItem(
                            'auth_token',
                            token
                        );

                    }



                    /* =================================
                       USER
                    ================================= */

                    const user =
                        result.data?.user ??
                        result.user;


                    if (user) {

                        localStorage.setItem(
                            'auth_user',
                            JSON.stringify(user)
                        );

                    }



                    /* =================================
                       REDIRECT
                    ================================= */

                    setTimeout(function() {

                        window.location.href =
                            "{{ route('login') }}";

                    }, 1000);


                } catch (error) {


                    console.error(error);


                    showAlert(

                        'Unable to connect to the server.',

                        'danger'

                    );


                } finally {

                    setLoading(false);

                }

            }
        );



        /* =========================================
           LOADING
        ========================================== */

        function setLoading(loading) {

            registerBtn.disabled =
                loading;


            if (loading) {

                registerText.textContent =
                    'Creating account...';

                spinner.classList.remove(
                    'd-none'
                );

            } else {

                registerText.textContent =
                    'Create account';

                spinner.classList.add(
                    'd-none'
                );

            }

        }



        /* =========================================
           ALERT
        ========================================== */

        function showAlert(message, type) {

            alertBox.className =
                `alert alert-${type} auth-alert`;

            alertBox.textContent =
                message;

        }



        /* =========================================
           VALIDATION ERRORS
        ========================================== */

        function showValidationErrors(errors) {

            if (!errors)
                return;


            if (errors.name) {

                document.getElementById(
                        'nameError'
                    ).textContent =
                    errors.name[0];

            }


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


            if (errors.password_confirmation) {

                document.getElementById(
                        'passwordConfirmationError'
                    ).textContent =
                    errors.password_confirmation[0];

            }


            showAlert(

                'Please check the highlighted fields.',

                'danger'

            );

        }



        /* =========================================
           CLEAR ERRORS
        ========================================== */

        function clearErrors() {


            document.getElementById(
                'nameError'
            ).textContent = '';


            document.getElementById(
                'emailError'
            ).textContent = '';


            document.getElementById(
                'passwordError'
            ).textContent = '';


            document.getElementById(
                'passwordConfirmationError'
            ).textContent = '';


            alertBox.className =
                'alert auth-alert d-none';


            alertBox.textContent = '';


        }

    });
</script>

@endpush