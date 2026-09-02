import { apiFetch } from "../api.js";

let currentPage = 1;
let currentStatus = "";
let currentSearch = "";
let currentSort = "latest";
let targetCancelTournamentId = null;
let currentTournamentsList = [];

document.addEventListener("DOMContentLoaded", () => {
    initFilters();
    loadTournaments();
    setupCancelForm();
});

function initFilters() {
    // Status Pills Nav
    const pillsNav = document.getElementById("tournamentStatusPillsNav");
    if (pillsNav) {
        pillsNav.querySelectorAll("button").forEach(btn => {
            btn.addEventListener("click", () => {
                pillsNav.querySelectorAll("button").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                currentStatus = btn.getAttribute("data-status") || "";
                currentPage = 1;
                loadTournaments();
            });
        });
    }

    // Search Input Debounce
    const searchInput = document.getElementById("tournamentSearchInput");
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener("input", (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentSearch = e.target.value.trim();
                currentPage = 1;
                loadTournaments();
            }, 300);
        });
    }

    // Sort Select
    const sortSelect = document.getElementById("tournamentSortSelect");
    if (sortSelect) {
        sortSelect.addEventListener("change", (e) => {
            currentSort = e.target.value;
            currentPage = 1;
            loadTournaments();
        });
    }
}

async function loadTournaments() {
    const tbody = document.getElementById("tournamentsTbody");
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading master tournaments...</td></tr>`;

    try {
        const queryParams = new URLSearchParams({
            page: currentPage,
            sort: currentSort
        });
        if (currentStatus) queryParams.append("status", currentStatus);
        if (currentSearch) queryParams.append("search", currentSearch);

        const response = await apiFetch(`/admin/tournaments?${queryParams.toString()}`);

        if (response && response.success) {
            const resData = response.data || {};
            const tournaments = resData.data || (Array.isArray(resData) ? resData : []);
            const meta = response.meta || response.data || {};

            currentTournamentsList = tournaments;
            updateStatSummaryCards(tournaments, meta);
            renderTournamentsTable(tbody, tournaments);
            renderPagination(meta);
        }
    } catch (error) {
        console.error("Load Admin Tournaments Error:", error);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load tournaments. ${escapeHtml(error.message || '')}</td></tr>`;
    }
}

function updateStatSummaryCards(tournaments, meta) {
    const totalEl = document.getElementById("statTotalTournaments");
    const upcomingEl = document.getElementById("statUpcomingTournaments");
    const ongoingEl = document.getElementById("statOngoingTournaments");
    const completedEl = document.getElementById("statCompletedTournaments");

    if (totalEl) totalEl.textContent = meta.total || tournaments.length;

    let upcomingCount = 0;
    let ongoingCount = 0;
    let completedCount = 0;

    tournaments.forEach(t => {
        const st = (t.status || "").toLowerCase();
        if (st === "upcoming") upcomingCount++;
        else if (st === "ongoing") ongoingCount++;
        else completedCount++;
    });

    if (upcomingEl) upcomingEl.textContent = upcomingCount;
    if (ongoingEl) ongoingEl.textContent = ongoingCount;
    if (completedEl) completedEl.textContent = completedCount;
}

function renderTournamentsTable(tbody, tournaments) {
    if (!Array.isArray(tournaments) || tournaments.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">No tournaments found matching criteria.</td></tr>`;
        return;
    }

    tbody.innerHTML = tournaments.map(tournament => {
        const title = tournament.title || "Pickleball Championship";
        const courtName = tournament.court?.name || "Venue Court";
        const city = tournament.court?.city || "";
        const prizePool = Number(tournament.prize_pool || 0);
        const entryFee = Number(tournament.entry_fee || 0);
        const startDate = formatDateReadable(tournament.start_date);
        const endDate = formatDateReadable(tournament.end_date);
        const status = (tournament.status || "upcoming").toLowerCase();
        const coverImg = getTournamentCoverImage(tournament);

        return `
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="${escapeHtml(coverImg)}" alt="${escapeHtml(title)}" class="rounded-3 border object-fit-cover flex-shrink-0" style="width: 50px; height: 50px;">
                        <div>
                            <strong class="text-dark d-block">${escapeHtml(title)}</strong>
                            <small class="text-muted fs-8"><i class="bi bi-person me-1"></i>${escapeHtml(tournament.owner?.name || 'Owner')}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <strong class="text-dark d-block small">${escapeHtml(courtName)}</strong>
                    <small class="text-muted fs-8"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(city)}</small>
                </td>
                <td>
                    <strong class="text-success">₹${prizePool.toLocaleString("en-IN")}</strong>
                </td>
                <td>
                    <span class="text-dark small">₹${entryFee.toLocaleString("en-IN")}</span>
                </td>
                <td>
                    <span class="text-dark small d-block">${escapeHtml(startDate)}</span>
                    <small class="text-muted fs-8">to ${escapeHtml(endDate)}</small>
                </td>
                <td>
                    ${renderStatusBadge(status)}
                </td>
                <td class="text-end pe-4">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-light border text-dark rounded-circle btn-inspect-tournament" data-id="${tournament.id}" title="Inspect Tournament">
                            <i class="bi bi-eye"></i>
                        </button>

                        ${status !== 'cancelled' ? `
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-cancel-tournament" data-id="${tournament.id}" data-title="${escapeHtml(title)}" title="Cancel Tournament">
                                <i class="bi bi-slash-circle"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join("");

    attachTableEventListeners();
}

function attachTableEventListeners() {
    // Inspect Tournament
    document.querySelectorAll(".btn-inspect-tournament").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            inspectTournament(id);
        });
    });

    // Cancel Tournament
    document.querySelectorAll(".btn-cancel-tournament").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const title = btn.getAttribute("data-title");
            openCancelTournamentModal(id, title);
        });
    });
}

function renderStatusBadge(status) {
    switch (status) {
        case "upcoming":
            return `<span class="badge bg-info text-dark rounded-pill px-3 py-1"><i class="bi bi-calendar-event me-1"></i> Upcoming</span>`;
        case "ongoing":
            return `<span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-play-circle me-1"></i> Ongoing</span>`;
        case "completed":
            return `<span class="badge bg-secondary rounded-pill px-3 py-1"><i class="bi bi-check-all me-1"></i> Completed</span>`;
        case "cancelled":
            return `<span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-x-circle me-1"></i> Cancelled</span>`;
        default:
            return `<span class="badge bg-light text-dark border rounded-pill px-3 py-1">${escapeHtml(status)}</span>`;
    }
}

function getTournamentCoverImage(t) {
    if (t.banner_image) {
        return t.banner_image.startsWith("http") ? t.banner_image : `/storage/${t.banner_image}`;
    }
    if (t.court?.cover_image_url) return t.court.cover_image_url;
    return "https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=600&q=80";
}

/**
 * Inspect Tournament Modal
 */
function inspectTournament(tournamentId) {
    const tournament = currentTournamentsList.find(t => String(t.id) === String(tournamentId));
    if (!tournament) return;

    const modalBody = document.getElementById("inspectTournamentModalBody");
    const modalFooter = document.getElementById("inspectTournamentModalFooter");
    const coverImg = getTournamentCoverImage(tournament);
    const prizePool = Number(tournament.prize_pool || 0);
    const entryFee = Number(tournament.entry_fee || 0);
    const status = (tournament.status || "upcoming").toLowerCase();

    if (modalBody) {
        modalBody.innerHTML = `
            <div class="row g-4">
                <div class="col-12">
                    <div class="position-relative rounded-4 overflow-hidden shadow-sm" style="height: 200px;">
                        <img src="${escapeHtml(coverImg)}" alt="${escapeHtml(tournament.title)}" class="w-100 h-100 object-fit-cover">
                        <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white bg-dark bg-opacity-75 backdrop-blur">
                            <h4 class="fw-bold mb-0">${escapeHtml(tournament.title)}</h4>
                            <small class="text-white-50"><i class="bi bi-building me-1"></i>${escapeHtml(tournament.court?.name || 'N/A')}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">ORGANIZING OWNER</small>
                    <strong class="text-dark fs-6">${escapeHtml(tournament.owner?.name || 'N/A')}</strong>
                    <div class="text-muted fs-8">${escapeHtml(tournament.owner?.email || '')}</div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">TOURNAMENT STATUS</small>
                    <div class="mt-1">${renderStatusBadge(status)}</div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">TOTAL PRIZE POOL</small>
                    <h4 class="fw-bold text-success mb-0">₹${prizePool.toLocaleString("en-IN")}</h4>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">PLAYER ENTRY FEE</small>
                    <h4 class="fw-bold text-dark mb-0">₹${entryFee.toLocaleString("en-IN")}</h4>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">MAX PARTICIPANTS</small>
                    <strong class="text-dark fs-5">${tournament.max_participants || 32} Players</strong>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">START DATE</small>
                    <span class="text-dark fw-bold">${formatDateReadable(tournament.start_date)}</span>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">END DATE</small>
                    <span class="text-dark fw-bold">${formatDateReadable(tournament.end_date)}</span>
                </div>

                ${tournament.description ? `
                    <div class="col-12">
                        <small class="text-muted d-block fs-8">EVENT DESCRIPTION & RULES</small>
                        <p class="text-dark bg-light p-3 rounded-3 mb-0">${escapeHtml(tournament.description)}</p>
                    </div>
                ` : ''}
            </div>
        `;
    }

    if (modalFooter) {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            ${status !== 'cancelled' ? `
                <button type="button" class="btn btn-danger rounded-pill px-4 btn-modal-cancel-tournament" data-id="${tournament.id}" data-title="${escapeHtml(tournament.title)}">
                    <i class="bi bi-slash-circle me-1"></i> Cancel Tournament
                </button>
            ` : ''}
        `;

        modalFooter.querySelector(".btn-modal-cancel-tournament")?.addEventListener("click", (e) => {
            const btn = e.currentTarget;
            bootstrap.Modal.getInstance(document.getElementById("inspectTournamentModal"))?.hide();
            openCancelTournamentModal(btn.getAttribute("data-id"), btn.getAttribute("data-title"));
        });
    }

    const modal = new bootstrap.Modal(document.getElementById("inspectTournamentModal"));
    modal.show();
}

/**
 * Open Cancel Tournament Modal
 */
function openCancelTournamentModal(tournamentId, tournamentTitle) {
    targetCancelTournamentId = tournamentId;

    const promptEl = document.getElementById("cancelTournamentPrompt");
    if (promptEl) {
        promptEl.innerHTML = `Are you sure you want to cancel the tournament event <strong>"${escapeHtml(tournamentTitle)}"</strong>?`;
    }

    const modal = new bootstrap.Modal(document.getElementById("cancelTournamentModal"));
    modal.show();
}

/**
 * Setup Cancel Form Handler
 */
function setupCancelForm() {
    const form = document.getElementById("cancelTournamentForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!targetCancelTournamentId) return;

        const submitBtn = document.getElementById("submitCancelTournamentBtn");
        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await apiFetch(`/admin/tournaments/${targetCancelTournamentId}`, {
                method: "DELETE"
            });

            if (response && response.success) {
                bootstrap.Modal.getInstance(document.getElementById("cancelTournamentModal"))?.hide();
                loadTournaments();
            }
        } catch (error) {
            console.error("Cancel Tournament Error:", error);
            alert(error.message || "Failed to cancel tournament.");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

function renderPagination(meta) {
    const container = document.getElementById("paginationContainer");
    if (!container) return;

    const current = meta.current_page || 1;
    const last = meta.last_page || 1;

    if (last <= 1) {
        container.innerHTML = `<small class="text-muted">Showing all ${meta.total || 0} tournaments</small>`;
        return;
    }

    container.innerHTML = `
        <small class="text-muted">Page ${current} of ${last} (${meta.total} total tournaments)</small>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary ${current <= 1 ? 'disabled' : ''}" id="prevPageBtn">Previous</button>
            <button class="btn btn-sm btn-outline-secondary ${current >= last ? 'disabled' : ''}" id="nextPageBtn">Next</button>
        </div>
    `;

    document.getElementById("prevPageBtn")?.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadTournaments();
        }
    });

    document.getElementById("nextPageBtn")?.addEventListener("click", () => {
        if (currentPage < last) {
            currentPage++;
            loadTournaments();
        }
    });
}

function formatDateReadable(dateStr) {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    return d.toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" });
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}
