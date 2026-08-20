<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta name="csrf-token"
        content="{{ csrf_token() }}">

    <title>@yield('title', 'Pickleball Hub')</title>


    <!-- =========================================
         BOOTSTRAP 5
    ========================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- =========================================
         BOOTSTRAP ICONS
    ========================================== -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">


    <!-- =========================================
         GOOGLE FONT
    ========================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">


    <style>
        /* =========================================
           GLOBAL
        ========================================== */

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7f4;
        }


        /* =========================================
           AUTH PAGE
        ========================================== */

        .auth-page {
            min-height: 100vh;

            display: flex;
        }


        /* =========================================
           LEFT VISUAL SIDE
        ========================================== */

        .auth-visual {
            width: 50%;
            min-height: 100vh;

            background:
                linear-gradient(135deg,
                    rgba(12, 71, 47, 0.96),
                    rgba(25, 122, 80, 0.88)),
                url("https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1200&q=80");

            background-size: cover;
            background-position: center;

            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 60px;
        }


        .visual-content {
            width: 100%;
            max-width: 550px;
        }


        /* =========================================
           LOGO
        ========================================== */

        .brand-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;

            margin-bottom: 45px;
        }


        .brand-logo-img {
            width: 170px;
            height: auto;

            display: block;

            object-fit: contain;
        }


        /* =========================================
           LEFT HEADING
        ========================================== */

        .visual-content h1 {
            font-size: 52px;
            line-height: 1.1;

            font-weight: 800;

            margin: 0 0 25px;
        }


        .visual-content p {
            font-size: 17px;
            line-height: 1.7;

            color: rgba(255, 255, 255, 0.85);

            margin: 0;
        }


        /* =========================================
           FEATURES
        ========================================== */

        .feature-list {
            margin-top: 40px;
        }


        .feature-item {
            display: flex;
            align-items: center;

            gap: 12px;

            margin-bottom: 18px;

            font-size: 15px;
        }


        .feature-icon {
            width: 30px;
            height: 30px;

            flex-shrink: 0;

            border-radius: 50%;

            background: rgba(255, 255, 255, 0.15);

            display: flex;
            align-items: center;
            justify-content: center;
        }


        .feature-icon i {
            font-size: 16px;
        }


        /* =========================================
           RIGHT FORM SIDE
        ========================================== */

        .auth-form-section {
            width: 50%;

            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 40px;
        }


        .auth-card {
            width: 100%;
            max-width: 470px;
        }


        /* =========================================
           MOBILE BRAND
        ========================================== */

        .mobile-brand {
            display: none;
        }


        /* =========================================
           AUTH HEADING
        ========================================== */

        .auth-heading {
            margin-bottom: 35px;
        }


        .auth-heading h2 {
            font-size: 32px;
            font-weight: 800;

            color: #17251f;

            margin: 0 0 8px;
        }


        .auth-heading p {
            color: #737b77;

            margin: 0;

            font-size: 14px;
        }


        /* =========================================
           FORM
        ========================================== */

        .form-label {
            font-size: 14px;
            font-weight: 600;

            color: #27332e;

            margin-bottom: 8px;
        }


        .form-control {
            height: 52px;

            border: 1px solid #dfe5e1;

            border-radius: 10px;

            padding: 0 15px;

            font-size: 14px;

            box-shadow: none !important;

            transition: 0.2s;
        }


        .form-control::placeholder {
            color: #a1aaa5;
        }


        .form-control:focus {
            border-color: #198754;

            box-shadow:
                0 0 0 3px rgba(25, 135, 84, 0.10) !important;
        }


        /* =========================================
           PASSWORD INPUT
        ========================================== */

        .input-group .form-control {
            border-right: 0;

            border-radius: 10px 0 0 10px;
        }


        .password-toggle {
            height: 52px;

            background: white;

            border: 1px solid #dfe5e1;

            border-left: 0;

            border-radius: 0 10px 10px 0;

            color: #777;

            padding: 0 15px;

            display: flex;
            align-items: center;
            justify-content: center;

            cursor: pointer;

            transition: 0.2s;
        }


        .password-toggle:hover {
            color: #198754;
        }


        .password-toggle:focus {
            outline: none;
        }


        /* =========================================
           LOGIN BUTTON
        ========================================== */

        .auth-btn {
            height: 52px;

            border: none;

            border-radius: 10px;

            background: #198754;

            color: white;

            font-size: 15px;
            font-weight: 700;

            transition: 0.2s;

            display: flex;
            align-items: center;
            justify-content: center;

            gap: 8px;
        }


        .auth-btn:hover {
            background: #157347;

            color: white;

            transform: translateY(-1px);
        }


        .auth-btn:active {
            transform: translateY(0);
        }


        .auth-btn:disabled {
            opacity: 0.7;

            transform: none;

            cursor: not-allowed;
        }


        /* =========================================
           ALERT
        ========================================== */

        .auth-alert {
            border-radius: 10px;

            font-size: 14px;

            border: none;
        }


        /* =========================================
           PASSWORD STRENGTH
        ========================================== */

        .password-strength {
            height: 4px;

            background: #e9ecef;

            border-radius: 10px;

            overflow: hidden;

            margin-top: 8px;
        }


        .password-strength-bar {
            height: 100%;

            width: 0;

            transition: 0.3s;
        }


        .password-strength-text {
            font-size: 12px;

            margin-top: 5px;

            color: #777;
        }


        /* =========================================
           REGISTER SWITCH
        ========================================== */

        .auth-switch {
            text-align: center;

            margin-top: 25px;

            font-size: 14px;

            color: #737b77;
        }


        .auth-switch a {
            color: #198754;

            font-weight: 700;

            text-decoration: none;
        }


        .auth-switch a:hover {
            text-decoration: underline;
        }


        /* =========================================
           LARGE SCREEN
        ========================================== */

        @media (min-width: 1400px) {

            .auth-visual {
                padding: 80px;
            }

            .visual-content {
                max-width: 600px;
            }

            .visual-content h1 {
                font-size: 56px;
            }

            .brand-logo-img {
                width: 150px;
            }

        }


        /* =========================================
           TABLET
        ========================================== */

        @media (max-width: 991px) {

            .auth-visual {
                display: none;
            }

            .auth-form-section {
                width: 100%;
                min-height: 100vh;
                padding: 40px 25px;
            }

            .auth-card {
                max-width: 470px;
            }

            .mobile-brand {
                display: flex;

                justify-content: center;
                align-items: center;

                width: 100%;

                margin-bottom: 40px;
            }

            .mobile-brand .brand-logo-img {
                width: 170px;
                height: auto;
            }
        }

        /* =========================================
           MOBILE
        ========================================== */

        @media (max-width: 576px) {

            .auth-form-section {
                padding: 25px 20px;
            }


            .auth-card {
                max-width: 100%;
            }


            .mobile-brand {
                margin-bottom: 30px;
            }


            .mobile-brand .brand-logo-img {
                width: 110px;
            }


            .auth-heading {
                margin-bottom: 30px;
            }


            .auth-heading h2 {
                font-size: 27px;
            }


            .auth-heading p {
                font-size: 13px;
            }


            .form-control,
            .password-toggle,
            .auth-btn {
                height: 50px;
            }


            .auth-switch {
                font-size: 13px;
            }

        }


        /* =========================================
           VERY SMALL DEVICES
        ========================================== */

        @media (max-width: 380px) {

            .auth-form-section {
                padding: 20px 15px;
            }


            .mobile-brand .brand-logo-img {
                width: 100px;
            }


            .auth-heading h2 {
                font-size: 24px;
            }

        }
    </style>


    @stack('styles')

</head>


<body>


    @yield('content')


    <!-- =========================================
         BOOTSTRAP JS
    ========================================== -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


    @stack('scripts')


</body>

</html>