document.addEventListener('DOMContentLoaded', function () {

    // =========================================
    // ELEMENTS
    // =========================================

    const profileName =
        document.getElementById('profileName');

    const profileEmail =
        document.getElementById('profileEmail');

    const profileStatus =
        document.getElementById('profileStatus');

    const infoName =
        document.getElementById('infoName');

    const infoEmail =
        document.getElementById('infoEmail');

    const infoRole =
        document.getElementById('infoRole');

    const infoStatus =
        document.getElementById('infoStatus');

    const editProfileBtn =
        document.getElementById('editProfileBtn');

    const editProfileCard =
        document.getElementById('editProfileCard');

    const cancelEditBtn =
        document.getElementById('cancelEditBtn');

    const profileForm =
        document.getElementById('profileForm');

    const editName =
        document.getElementById('editName');

    const editEmail =
        document.getElementById('editEmail');

    const profileAlert =
        document.getElementById('profileAlert');

    const saveProfileBtn =
        document.getElementById('saveProfileBtn');

    const saveProfileText =
        document.getElementById('saveProfileText');

    const saveProfileSpinner =
        document.getElementById('saveProfileSpinner');

    const logoutBtn =
        document.getElementById('profileLogoutBtn');


    // =========================================
    // LOAD PROFILE
    // =========================================

    loadProfile();


    async function loadProfile() {

        const token =
            localStorage.getItem('auth_token');


        if (!token) {

            window.location.href = '/login';

            return;

        }


        try {

            const response =
                await fetch('/api/profile', {

                    method: 'GET',

                    headers: {

                        'Accept':
                            'application/json',

                        'Authorization':
                            `Bearer ${token}`

                    }

                });


            const result =
                await response.json();


            // =========================================
            // UNAUTHENTICATED
            // =========================================

            if (response.status === 401) {

                logoutLocal();

                return;

            }


            if (!response.ok) {

                console.error(
                    'Profile API Error:',
                    result
                );

                showAlert(
                    result.message ||
                    'Unable to load profile.',
                    'danger'
                );

                return;

            }


            // =========================================
            // PROFILE DATA
            // =========================================

            const user =
                result.data;


            if (!user) {

                showAlert(
                    'Profile data was not received.',
                    'danger'
                );

                return;

            }


            displayProfile(user);


        } catch (error) {

            console.error(
                'Profile Error:',
                error
            );

            showAlert(
                'Unable to connect to the server.',
                'danger'
            );

        }

    }


    // =========================================
    // DISPLAY PROFILE
    // =========================================

    function displayProfile(user) {

        const name =
            user.name || 'User';

        const email =
            user.email || '-';

        const role =
            user.role || 'user';

        const status =
            user.status || 'active';


        // Header

        if (profileName) {

            profileName.textContent =
                name;

        }


        if (profileEmail) {

            profileEmail.textContent =
                email;

        }


        // Information

        if (infoName) {

            infoName.textContent =
                name;

        }


        if (infoEmail) {

            infoEmail.textContent =
                email;

        }


        if (infoRole) {

            infoRole.textContent =
                formatText(role);

        }


        if (infoStatus) {

            infoStatus.textContent =
                formatText(status);

        }


        // Status badge

        if (profileStatus) {

            profileStatus.textContent =
                formatText(status);

            updateStatusClass(
                status
            );

        }


        // Edit form

        if (editName) {

            editName.value =
                name;

        }


        if (editEmail) {

            editEmail.value =
                email;

        }


        // Navbar name

        const navbarUserName =
            document.getElementById(
                'navbarUserName'
            );


        if (navbarUserName) {

            navbarUserName.textContent =
                name;

        }


        // Store latest user

        localStorage.setItem(
            'auth_user',
            JSON.stringify(user)
        );

    }


    // =========================================
    // STATUS CLASS
    // =========================================

    function updateStatusClass(status) {

        if (!profileStatus) {
            return;
        }


        profileStatus.className =
            'profile-status';


        if (status === 'active') {

            profileStatus.classList.add(
                'profile-status-active'
            );

        } else if (status === 'blocked') {

            profileStatus.classList.add(
                'profile-status-blocked'
            );

        } else {

            profileStatus.classList.add(
                'profile-status-other'
            );

        }

    }


    // =========================================
    // EDIT PROFILE
    // =========================================

    if (editProfileBtn) {

        editProfileBtn.addEventListener(
            'click',
            function () {

                editProfileCard.classList.remove(
                    'd-none'
                );

                editProfileBtn.classList.add(
                    'd-none'
                );

                editName.focus();

            }
        );

    }


    // =========================================
    // CANCEL EDIT
    // =========================================

    if (cancelEditBtn) {

        cancelEditBtn.addEventListener(
            'click',
            function () {

                editProfileCard.classList.add(
                    'd-none'
                );

                editProfileBtn.classList.remove(
                    'd-none'
                );

                clearEditErrors();

            }
        );

    }


    // =========================================
    // PROFILE FORM
    // =========================================

    if (profileForm) {

        profileForm.addEventListener(
            'submit',
            async function (e) {

                e.preventDefault();

                clearEditErrors();

                const name =
                    editName.value.trim();

                const email =
                    editEmail.value.trim();


                if (!name) {

                    document.getElementById(
                        'nameError'
                    ).textContent =
                        'Name is required.';

                    return;

                }


                if (!email) {

                    document.getElementById(
                        'emailError'
                    ).textContent =
                        'Email is required.';

                    return;

                }


                showSaveLoading(true);


                /*
                |--------------------------------------------------------------------------
                | IMPORTANT
                |--------------------------------------------------------------------------
                | Your current AuthController only has GET /api/profile.
                |
                | We are NOT sending an update request yet.
                |
                | Once we create the update-profile API, this section
                | will be connected to it.
                |--------------------------------------------------------------------------
                */


                showAlert(
                    'Profile editing API is not available yet.',
                    'warning'
                );


                showSaveLoading(false);

            }
        );

    }


    // =========================================
    // SAVE LOADING
    // =========================================

    function showSaveLoading(loading) {

        if (!saveProfileBtn) {
            return;
        }


        saveProfileBtn.disabled =
            loading;


        if (loading) {

            if (saveProfileText) {

                saveProfileText.textContent =
                    'Saving...';

            }


            if (saveProfileSpinner) {

                saveProfileSpinner.classList.remove(
                    'd-none'
                );

            }

        } else {

            if (saveProfileText) {

                saveProfileText.textContent =
                    'Save Changes';

            }


            if (saveProfileSpinner) {

                saveProfileSpinner.classList.add(
                    'd-none'
                );

            }

        }

    }


    // =========================================
    // ALERT
    // =========================================

    function showAlert(message, type) {

        if (!profileAlert) {
            return;
        }


        profileAlert.className =
            `alert alert-${type}`;


        profileAlert.textContent =
            message;

    }


    // =========================================
    // CLEAR EDIT ERRORS
    // =========================================

    function clearEditErrors() {

        const nameError =
            document.getElementById(
                'nameError'
            );

        const emailError =
            document.getElementById(
                'emailError'
            );


        if (nameError) {

            nameError.textContent = '';

        }


        if (emailError) {

            emailError.textContent = '';

        }


        if (profileAlert) {

            profileAlert.className =
                'alert d-none';

            profileAlert.textContent =
                '';

        }

    }


    // =========================================
    // FORMAT TEXT
    // =========================================

    function formatText(value) {

        if (!value) {
            return '-';
        }


        return value
            .charAt(0)
            .toUpperCase() +
            value.slice(1);

    }


    // =========================================
    // LOGOUT
    // =========================================

    // =========================================
    // LOGOUT
    // =========================================

    if (logoutBtn) {

        logoutBtn.addEventListener(
            'click',
            async function () {

                const token =
                    localStorage.getItem(
                        'auth_token'
                    );

                const csrfMeta =
                    document.querySelector(
                        'meta[name="csrf-token"]'
                    );

                const csrfToken =
                    csrfMeta
                        ? csrfMeta.getAttribute('content')
                        : null;


                try {

                    // =========================================
                    // 1. LOGOUT SANCTUM TOKEN
                    // =========================================

                    if (token) {

                        try {

                            await fetch(
                                '/api/logout',
                                {

                                    method: 'POST',

                                    headers: {

                                        'Accept':
                                            'application/json',

                                        'Authorization':
                                            `Bearer ${token}`

                                    }

                                }
                            );

                        } catch (apiLogoutError) {

                            console.error(
                                'API Logout Error:',
                                apiLogoutError
                            );

                        }

                    }


                    // =========================================
                    // 2. LOGOUT LARAVEL SESSION
                    // =========================================

                    try {

                        const sessionLogoutResponse =
                            await fetch(
                                '/auth/logout',
                                {

                                    method: 'POST',

                                    headers: {

                                        'Accept':
                                            'application/json',

                                        'X-CSRF-TOKEN':
                                            csrfToken

                                    },

                                    credentials:
                                        'same-origin'

                                }
                            );


                        const sessionLogoutResult =
                            await sessionLogoutResponse.json();


                        if (!sessionLogoutResponse.ok) {

                            console.error(
                                'Session Logout Failed:',
                                sessionLogoutResult
                            );

                        }

                    } catch (sessionLogoutError) {

                        console.error(
                            'Session Logout Error:',
                            sessionLogoutError
                        );

                    }


                } finally {

                    // =========================================
                    // 3. CLEAR LOCAL STORAGE
                    // =========================================

                    localStorage.removeItem(
                        'auth_token'
                    );

                    localStorage.removeItem(
                        'auth_user'
                    );


                    // =========================================
                    // 4. GO TO LOGIN
                    // =========================================

                    window.location.replace(
                        '/login'
                    );

                }

            }
        );

    }

});