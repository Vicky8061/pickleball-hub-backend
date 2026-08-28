/*
|--------------------------------------------------------------------------
| Elements & State
|--------------------------------------------------------------------------
*/

const tournamentsContainer =
    document.getElementById('tournamentsContainer');

const noTournaments =
    document.getElementById('noTournaments');

const searchInput =
    document.getElementById('tournamentSearch');

const statusFilter =
    document.getElementById('tournamentStatus');

let tournaments = [];


/*
|--------------------------------------------------------------------------
| Load Tournaments
|--------------------------------------------------------------------------
*/

async function loadTournaments() {

    try {

        showLoading();

        const response = await apiFetch('/tournaments');

        if (
            response &&
            response.success
        ) {

            tournaments =
                response.data?.data ||
                response.data ||
                [];

            renderTournaments();

        } else {

            showEmpty();

        }

    } catch (error) {

        console.error(
            'Tournament API Error:',
            error
        );

        showError();

    }

}


/*
|--------------------------------------------------------------------------
| Render Tournaments
|--------------------------------------------------------------------------
*/

function renderTournaments() {

    const search =
        searchInput.value
            .trim()
            .toLowerCase();

    const status =
        statusFilter.value;


    const filtered =
        tournaments.filter(tournament => {

            const matchesSearch =
                tournament.title
                    ?.toLowerCase()
                    .includes(search);


            const matchesStatus =
                status === 'all' ||
                tournament.status === status;


            return (
                matchesSearch &&
                matchesStatus
            );

        });


    if (filtered.length === 0) {

        tournamentsContainer.innerHTML = '';

        noTournaments.classList.remove('d-none');

        return;

    }


    noTournaments.classList.add('d-none');


    tournamentsContainer.innerHTML =
        filtered
            .map(createTournamentCard)
            .join('');

}


/*
|--------------------------------------------------------------------------
| Tournament Card
|--------------------------------------------------------------------------
*/

function createTournamentCard(tournament) {

    const banner =
        tournament.banner ||
        '/assets/images/tournament-placeholder.jpg';


    const statusClass =
        getStatusClass(tournament.status);


    return `

        <div class="col-md-6 col-xl-4">

            <div class="tournament-card">

                <!-- IMAGE -->

                <div class="tournament-card-image">

                    <img
                        src="${banner}"
                        alt="${escapeHtml(tournament.title)}"
                        class="tournament-banner"
                        onerror="
                            this.onerror=null;
                            this.src='/assets/images/tournament-placeholder.jpg';
                        "
                    >


                    <!-- STATUS -->

                    <span
                        class="tournament-status ${statusClass}">

                        ${capitalize(
        tournament.status
    )}

                    </span>

                </div>


                <!-- BODY -->

                <div class="tournament-card-body">

                    <h5 class="tournament-title">

                        ${escapeHtml(
        tournament.title
    )}

                    </h5>


                    <p class="tournament-description">

                        ${escapeHtml(
        tournament.description || ''
    )}

                    </p>


                    <!-- DATE -->

                    <div class="tournament-info">

                        <div>

                            <i class="bi bi-calendar-event"></i>

                            <span>
                                ${formatDate(
        tournament.tournament_date
    )}
                            </span>

                        </div>


                        <div>

                            <i class="bi bi-clock"></i>

                            <span>
                                ${formatTime(
        tournament.start_time
    )}
                                -
                                ${formatTime(
        tournament.end_time
    )}
                            </span>

                        </div>

                    </div>


                    <!-- DETAILS -->

                    <div class="tournament-footer">

                        <div>

                            <small>
                                Entry Fee
                            </small>

                            <strong>
                                ₹${tournament.entry_fee}
                            </strong>

                        </div>


                        <div>

                            <small>
                                Prize
                            </small>

                            <strong>
                                ${escapeHtml(
        tournament.prize || '-'
    )}
                            </strong>

                        </div>


                        <div>

                            <small>
                                Players
                            </small>

                            <strong>

                                ${tournament.participants
            ?.length || 0
        }
                                /
                                ${tournament.max_participants}

                            </strong>

                        </div>

                    </div>


                    <!-- BUTTON -->

                    <button
                        type="button"
                        class="btn tournament-btn"
                        data-id="${tournament.id}">

                        View Tournament

                        <i class="bi bi-arrow-right"></i>

                    </button>

                </div>

            </div>

        </div>

    `;

}


/*
|--------------------------------------------------------------------------
| Status Class
|--------------------------------------------------------------------------
*/

function getStatusClass(status) {

    switch (status) {

        case 'upcoming':
            return 'status-upcoming';

        case 'ongoing':
            return 'status-ongoing';

        case 'completed':
            return 'status-completed';

        case 'cancelled':
            return 'status-cancelled';

        default:
            return '';

    }

}


/*
|--------------------------------------------------------------------------
| Loading
|--------------------------------------------------------------------------
*/

function showLoading() {
    const skeletonCard = `
        <div class="col-md-6 col-xl-4">
            <div class="skeleton-card">
                <div class="skeleton skeleton-img"></div>
                <div class="skeleton-card-body">
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text w-100"></div>
                    <div class="skeleton skeleton-text w-75 mb-4"></div>
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                        <div class="skeleton skeleton-text w-50 mb-0"></div>
                        <div class="skeleton skeleton-badge"></div>
                    </div>
                </div>
            </div>
        </div>
    `;

    tournamentsContainer.innerHTML = Array(6).fill(skeletonCard).join('');
}


/*
|--------------------------------------------------------------------------
| Empty
|--------------------------------------------------------------------------
*/

function showEmpty() {

    tournamentsContainer.innerHTML = '';

    noTournaments.classList.remove('d-none');

}


/*
|--------------------------------------------------------------------------
| Error
|--------------------------------------------------------------------------
*/

function showError() {

    tournamentsContainer.innerHTML = `

        <div class="col-12">

            <div class="empty-state">

                <div class="empty-state-icon bg-danger-subtle text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <h5>
                    Unable to load tournaments
                </h5>

                <p>
                    There was a problem fetching the tournaments list. Please check your connection and try again.
                </p>

                <div class="empty-state-action">
                    <button class="btn btn-primary-custom" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i> Retry
                    </button>
                </div>

            </div>

        </div>

    `;

}



/*
|--------------------------------------------------------------------------
| Search & Filters
|--------------------------------------------------------------------------
*/

searchInput.addEventListener(
    'input',
    renderTournaments
);

statusFilter.addEventListener(
    'change',
    renderTournaments
);

document.addEventListener('click', (e) => {
    if (e.target && e.target.closest('#resetFiltersBtn')) {
        searchInput.value = '';
        statusFilter.value = 'all';
        renderTournaments();
    }
});


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function capitalize(value) {

    if (!value) return '';

    return value.charAt(0).toUpperCase()
        + value.slice(1);

}


function formatDate(date) {

    if (!date) return '-';

    return new Date(date).toLocaleDateString(
        'en-IN',
        {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        }
    );

}


function formatTime(time) {

    if (!time) return '-';

    const [hours, minutes] =
        time.split(':');

    const date = new Date();

    date.setHours(
        hours,
        minutes
    );

    return date.toLocaleTimeString(
        'en-IN',
        {
            hour: 'numeric',
            minute: '2-digit'
        }
    );

}


function escapeHtml(value) {

    const div =
        document.createElement('div');

    div.textContent =
        value ?? '';

    return div.innerHTML;

}


/*
|--------------------------------------------------------------------------
| Start
|--------------------------------------------------------------------------
*/

loadTournaments();