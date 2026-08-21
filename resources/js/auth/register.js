document.addEventListener('DOMContentLoaded', function () {

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
            .addEventListener('click', function () {

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
        .addEventListener('input', function () {

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
        async function (e) {

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
                        '/api/register',
                        {

                            method: 'POST',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json'

                            },

                            body: JSON.stringify({

                                name: name,

                                email: email,

                                password: password,

                                password_confirmation:
                                    confirmation

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

                setTimeout(function () {

                    window.location.href =
                        '/login';

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