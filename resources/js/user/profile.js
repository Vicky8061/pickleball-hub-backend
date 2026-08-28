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
    loadProfileStats();


    async function loadProfile() {

        const token =
            localStorage.getItem('auth_token');


        if (!token) {

            window.location.href = '/login';

            return;

        }


        try {

            const result = await apiFetch('/profile');


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

    function escapeHTML(value) {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function escapeAttribute(value) {
        return escapeHTML(value);
    }

    // =========================================
    // LOAD PROFILE STATS, BADGES & HISTORY
    // =========================================

    async function loadProfileStats() {
        try {
            const response = await apiFetch('/user/profile-stats');
            const data = response?.data;
            if (!data) return;

            // 1. Counters
            const stats = data.stats || {};
            const statTotalMatches = document.getElementById('statTotalMatches');
            const statUpcomingMatches = document.getElementById('statUpcomingMatches');
            const statTournamentsJoined = document.getElementById('statTournamentsJoined');
            const statReviewsCount = document.getElementById('statReviewsCount');

            if (statTotalMatches) statTotalMatches.textContent = stats.total_matches || 0;
            if (statUpcomingMatches) statUpcomingMatches.textContent = stats.upcoming_matches || 0;
            if (statTournamentsJoined) statTournamentsJoined.textContent = stats.tournaments_joined || 0;
            if (statReviewsCount) statReviewsCount.textContent = stats.reviews_count || 0;

            // 2. Favorite Court Highlight Card
            const favCourt = data.favorite_court;
            const favContainer = document.getElementById('favoriteCourtContainer');
            const favContent = document.getElementById('favoriteCourtContent');

            if (favCourt && favContainer && favContent) {
                favContent.innerHTML = `
                    <div class="d-flex flex-column flex-md-row align-items-center gap-3 bg-light p-3 rounded-4">
                        <img src="${escapeAttribute(favCourt.primary_image)}" class="rounded-3 object-fit-cover" style="width: 110px; height: 85px;" alt="${escapeAttribute(favCourt.name)}">
                        <div class="flex-grow-1 text-center text-md-start">
                            <h5 class="fw-bold mb-1 text-dark">${escapeHTML(favCourt.name)}</h5>
                            <p class="text-muted small mb-1"><i class="bi bi-geo-alt-fill text-success me-1"></i>${escapeHTML(favCourt.address)}</p>
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small">Booked ${favCourt.times_booked} time${favCourt.times_booked > 1 ? 's' : ''}</span>
                        </div>
                        <a href="/user/courts/${favCourt.id}" class="btn user-primary-btn rounded-pill px-4 py-2 text-nowrap">
                            <i class="bi bi-arrow-repeat me-1"></i> Book Again
                        </a>
                    </div>
                `;
                favContainer.classList.remove('d-none');
            }

            // 3. Player Badges Grid
            const badges = data.badges || [];
            const badgesGrid = document.getElementById('playerBadgesGrid');
            if (badgesGrid && badges.length > 0) {
                badgesGrid.innerHTML = badges.map(b => `
                    <div class="col-md-6 col-lg-3">
                        <div class="border rounded-4 p-3 text-center h-100 bg-white shadow-sm position-relative overflow-hidden" style="${b.unlocked ? '' : 'opacity: 0.55; filter: grayscale(0.8);'}">
                            <div class="rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: ${b.color}20; color: ${b.color}; font-size: 22px;">
                                <i class="bi ${b.icon}"></i>
                            </div>
                            <h6 class="fw-bold mb-1 text-dark">${escapeHTML(b.name)}</h6>
                            <p class="text-muted mb-0" style="font-size: 11px;">${escapeHTML(b.description)}</p>
                            <span class="badge ${b.unlocked ? 'bg-success' : 'bg-secondary'} rounded-pill position-absolute top-0 end-0 m-2" style="font-size: 10px;">
                                ${b.unlocked ? 'Unlocked' : 'Locked'}
                            </span>
                        </div>
                    </div>
                `).join('');
            }

            // 4. Recent Activity Timeline
            const activities = data.recent_activity || [];
            const recentList = document.getElementById('recentActivityList');
            if (recentList) {
                if (activities.length === 0) {
                    recentList.innerHTML = `<div class="alert alert-light text-muted text-center rounded-3 mb-0">No recent match activity yet.</div>`;
                } else {
                    recentList.innerHTML = activities.map(act => `
                        <div class="d-flex align-items-center gap-3 p-2 border-bottom last-border-0">
                            <div class="rounded-circle ${act.badge_class} bg-opacity-10 text-dark p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 18px;">
                                <i class="bi ${act.icon}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-0 text-dark small">${escapeHTML(act.title)}</h6>
                                <small class="text-muted" style="font-size: 11px;">${escapeHTML(act.subtitle)}</small>
                            </div>
                        </div>
                    `).join('');
                }
            }

        } catch (err) {
            console.error('Error loading profile stats:', err);
        }
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