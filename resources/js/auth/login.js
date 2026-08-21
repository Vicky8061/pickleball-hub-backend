document.addEventListener('DOMContentLoaded', function () {

    // =========================================
    // ELEMENTS
    // =========================================

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

    const togglePassword =
        document.getElementById('togglePassword');


    // =========================================
    // SAFETY CHECK
    // =========================================

    if (!form) {
        console.error('Login form not found.');
        return;
    }


    // =========================================
    // PASSWORD TOGGLE
    // =========================================

    if (togglePassword) {

        togglePassword.addEventListener(
            'click',
            function () {

                const password =
                    document.getElementById('password');

                const icon =
                    this.querySelector('i');


                if (!password) {
                    return;
                }


                if (password.type === 'password') {

                    password.type = 'text';

                    if (icon) {
                        icon.className =
                            'bi bi-eye-slash';
                    }

                } else {

                    password.type = 'password';

                    if (icon) {
                        icon.className =
                            'bi bi-eye';
                    }

                }

            }
        );

    }


    // =========================================
    // LOGIN
    // =========================================

    form.addEventListener(
        'submit',
        async function (e) {

            e.preventDefault();

            clearErrors();

            setLoading(true);


            // =========================================
            // GET FORM VALUES
            // =========================================

            const email =
                document
                    .getElementById('email')
                    .value
                    .trim();

            const password =
                document
                    .getElementById('password')
                    .value;


            try {

                // =========================================
                // LOGIN API
                // =========================================

                const response =
                    await fetch('/api/login', {

                        method: 'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json'

                        },

                        body: JSON.stringify({

                            email: email,

                            password: password

                        })

                    });


                const result =
                    await response.json();


                // =========================================
                // VALIDATION ERROR
                // =========================================

                if (response.status === 422) {

                    showValidationErrors(
                        result.errors
                    );

                    return;

                }


                // =========================================
                // OTHER ERROR
                // =========================================

                if (!response.ok) {

                    showAlert(
                        result.message ||
                        'Invalid email or password.',
                        'danger'
                    );

                    return;

                }


                // =========================================
                // GET TOKEN
                // =========================================

                const token =
                    result.data?.token ??
                    result.token;


                // =========================================
                // GET USER
                // =========================================

                const user =
                    result.data?.user ??
                    result.user;


                // =========================================
                // TOKEN CHECK
                // =========================================

                if (!token) {

                    showAlert(
                        'Login successful but authentication token was not received.',
                        'danger'
                    );

                    return;

                }


                // =========================================
                // USER CHECK
                // =========================================

                if (!user) {

                    showAlert(
                        'Login successful but user information was not received.',
                        'danger'
                    );

                    return;

                }


                // =========================================
                // STORE TOKEN
                // =========================================

                localStorage.setItem(
                    'auth_token',
                    token
                );


                // =========================================
                // STORE USER
                // =========================================

                localStorage.setItem(
                    'auth_user',
                    JSON.stringify(user)
                );


                // =========================================
                // CREATE LARAVEL SESSION
                // =========================================

                const csrfMeta =
                    document.querySelector(
                        'meta[name="csrf-token"]'
                    );


                if (!csrfMeta) {

                    console.error(
                        'CSRF token meta tag not found.'
                    );

                    showAlert(
                        'CSRF token not found. Please refresh the page.',
                        'danger'
                    );

                    return;

                }


                const csrfToken =
                    csrfMeta.getAttribute('content');


                try {

                    const sessionResponse =
                        await fetch(
                            '/auth/session',
                            {

                                method: 'POST',

                                headers: {

                                    'Content-Type':
                                        'application/json',

                                    'Accept':
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        csrfToken

                                },

                                credentials: 'same-origin',

                                body: JSON.stringify({

                                    user: user

                                })

                            }
                        );


                    const sessionResult =
                        await sessionResponse.json();


                    // =========================================
                    // SESSION ERROR
                    // =========================================

                    if (!sessionResponse.ok) {

                        console.error(
                            'Session creation failed:',
                            sessionResult
                        );

                        showAlert(
                            sessionResult.message ||
                            'Login successful but session could not be created.',
                            'danger'
                        );

                        return;

                    }


                    // =========================================
                    // SESSION SUCCESS
                    // =========================================

                    console.log(
                        'Laravel session created successfully.'
                    );


                } catch (sessionError) {

                    console.error(
                        'Session Error:',
                        sessionError
                    );

                    showAlert(
                        'Login successful but session could not be created.',
                        'danger'
                    );

                    return;

                }


                // =========================================
                // LOGIN SUCCESS
                // =========================================

                showAlert(
                    result.message ||
                    'Login successful!',
                    'success'
                );


                // =========================================
                // REDIRECT
                // =========================================

                setTimeout(function () {

                    if (user.role === 'admin') {

                        window.location.replace(
                            '/admin/dashboard'
                        );

                    } else if (user.role === 'owner') {

                        window.location.replace(
                            '/owner/dashboard'
                        );

                    } else {

                        window.location.replace(
                            '/user/dashboard'
                        );

                    }

                }, 700);


            } catch (error) {

                console.error(
                    'Login Error:',
                    error
                );

                showAlert(
                    'Unable to connect to the server.',
                    'danger'
                );

            } finally {

                setLoading(false);

            }

        }
    );


    // =========================================
    // LOADING
    // =========================================

    function setLoading(loading) {

        if (!loginBtn) {
            return;
        }


        loginBtn.disabled = loading;


        if (loading) {

            if (loginText) {

                loginText.textContent =
                    'Logging in...';

            }


            if (spinner) {

                spinner.classList.remove(
                    'd-none'
                );

            }

        } else {

            if (loginText) {

                loginText.textContent =
                    'Login';

            }


            if (spinner) {

                spinner.classList.add(
                    'd-none'
                );

            }

        }

    }


    // =========================================
    // ALERT
    // =========================================

    function showAlert(message, type) {

        if (!alertBox) {
            return;
        }


        alertBox.className =
            `alert alert-${type} auth-alert`;


        alertBox.textContent =
            message;

    }


    // =========================================
    // VALIDATION ERRORS
    // =========================================

    function showValidationErrors(errors) {

        if (!errors) {
            return;
        }


        // =========================================
        // EMAIL ERROR
        // =========================================

        if (errors.email) {

            const emailError =
                document.getElementById(
                    'emailError'
                );


            if (emailError) {

                emailError.textContent =
                    errors.email[0];

            }

        }


        // =========================================
        // PASSWORD ERROR
        // =========================================

        if (errors.password) {

            const passwordError =
                document.getElementById(
                    'passwordError'
                );


            if (passwordError) {

                passwordError.textContent =
                    errors.password[0];

            }

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

        const emailError =
            document.getElementById(
                'emailError'
            );


        const passwordError =
            document.getElementById(
                'passwordError'
            );


        if (emailError) {

            emailError.textContent = '';

        }


        if (passwordError) {

            passwordError.textContent = '';

        }


        if (alertBox) {

            alertBox.className =
                'alert auth-alert d-none';

            alertBox.textContent = '';

        }

    }

});