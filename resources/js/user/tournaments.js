import { apiFetch } from "../api.js";

let exploreTournamentsList = [];
let joinedTournamentsList = [];
let currentSearch = "";
let currentStatus = "";

document.addEventListener("DOMContentLoaded", () => {
    initTabs();
    initFilters();
    loadExploreTournaments();
    loadMyJoinedTournaments();
});

function initTabs() {
    const joinedTabBtn = document.getElementById("joined-tab");
    if (joinedTabBtn) {
        joinedTabBtn.addEventListener("shown.bs.tab", () => {
            loadMyJoinedTournaments();
        });
    }
}

function initFilters() {
    // Search input
    const searchInput = document.getElementById("tournamentSearchInput");
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener("input", (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentSearch = e.target.value.trim().toLowerCase();
                renderExploreGrid();
            }, 300);
        });
    }

    // Status filter pills
    const statusNav = document.getElementById("statusFilterNav");
    if (statusNav) {
        statusNav.querySelectorAll("button").forEach(btn => {
            btn.addEventListener("click", () => {
                statusNav.querySelectorAll("button").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                currentStatus = btn.getAttribute("data-status") || "";
                renderExploreGrid();
            });
        });
    }
}

async function loadExploreTournaments() {
    const grid = document.getElementById("tournamentsGrid");
    if (!grid) return;

    try {
        const response = await apiFetch("/tournaments");
        if (response && response.success) {
            const data = response.data || [];
            exploreTournamentsList = Array.isArray(data) ? data : (data.data || []);
            renderExploreGrid();
        }
    } catch (error) {
        console.error("Load Explore Tournaments Error:", error);
        grid.innerHTML = `<div class="col-12 text-center py-5 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load tournaments. ${escapeHtml(error.message || '')}</div>`;
    }
}

function renderExploreGrid() {
    const grid = document.getElementById("tournamentsGrid");
    if (!grid) return;

    let filtered = exploreTournamentsList.filter(t => {
        const title = (t.title || "").toLowerCase();
        const courtName = (t.court?.name || "").toLowerCase();
        const matchesSearch = !currentSearch || title.includes(currentSearch) || courtName.includes(currentSearch);
        const status = (t.status || "").toLowerCase();
        const matchesStatus = !currentStatus || status === currentStatus;
        return matchesSearch && matchesStatus;
    });

    if (filtered.length === 0) {
        grid.innerHTML = `<div class="col-12 text-center py-5 text-muted">No tournaments found matching your criteria.</div>`;
        return;
    }

    grid.innerHTML = filtered.map(t => {
        const title = t.title || "Pickleball Championship";
        const courtName = t.court?.name || "Court Venue";
        const city = t.court?.city || "";
        const prizePool = Number(t.prize_pool || 0);
        const entryFee = Number(t.entry_fee || 0);
        const coverImg = getTournamentCoverImage(t);
        const status = (t.status || "upcoming").toLowerCase();
        const startDate = formatDateReadable(t.start_date);
        const endDate = formatDateReadable(t.end_date);
        const regDeadline = formatDateReadable(t.registration_last_date || t.start_date);
        const joinedCount = t.participants ? t.participants.length : 0;
        const maxParticipants = t.max_participants || 32;

        const isUserJoined = joinedTournamentsList.some(j => String(j.tournament_id || j.tournament?.id) === String(t.id));

        return `
            <div class="col-xl-4 col-md-6 col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 card-hover-lift bg-white">
                    <div class="position-relative" style="height: 180px;">
                        <img src="${escapeHtml(coverImg)}" alt="${escapeHtml(title)}" class="w-100 h-100 object-fit-cover">
                        <div class="position-absolute top-0 start-0 m-3">
                            ${renderStatusBadge(status)}
                        </div>
                        <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white bg-dark bg-opacity-75 backdrop-blur d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-warning fw-bold d-block fs-8 text-uppercase">PRIZE POOL</small>
                                <strong class="fs-5 text-white">₹${prizePool.toLocaleString("en-IN")}</strong>
                            </div>
                            <div class="text-end">
                                <small class="text-white-50 d-block fs-8 text-uppercase">ENTRY FEE</small>
                                <strong class="fs-6 text-warning">₹${entryFee.toLocaleString("en-IN")}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">${escapeHtml(title)}</h5>
                            <p class="text-muted fs-8 mb-3"><i class="bi bi-geo-alt me-1 text-primary"></i>${escapeHtml(courtName)} ${city ? '• ' + escapeHtml(city) : ''}</p>

                            <div class="d-flex align-items-center justify-content-between bg-light p-2.5 rounded-3 mb-3 fs-8">
                                <div>
                                    <span class="text-muted d-block">Dates</span>
                                    <strong class="text-dark">${escapeHtml(startDate)}</strong>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted d-block">Deadline</span>
                                    <strong class="text-danger">${escapeHtml(regDeadline)}</strong>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between fs-8 text-muted mb-3">
                                <span><i class="bi bi-people me-1"></i> Capacity:</span>
                                <strong class="text-dark">${joinedCount} / ${maxParticipants} Joined</strong>
                            </div>
                        </div>

                        <div>
                            ${isUserJoined ? `
                                <button type="button" class="btn btn-success rounded-pill w-100 fw-bold disabled">
                                    <i class="bi bi-check-circle-fill me-1"></i> Already Joined
                                </button>
                            ` : `
                                <button type="button" class="btn btn-warning rounded-pill w-100 fw-bold text-dark btn-inspect-tournament" data-id="${t.id}">
                                    <i class="bi bi-ticket-perforated me-1"></i> View & Join Event
                                </button>
                            `}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join("");

    attachGridEventListeners();
}

function attachGridEventListeners() {
    document.querySelectorAll(".btn-inspect-tournament").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            inspectTournament(id);
        });
    });
}

async function loadMyJoinedTournaments() {
    const container = document.getElementById("myJoinedContainer");
    if (!container) return;

    try {
        const response = await apiFetch("/user/my-tournaments");
        if (response && response.success) {
            const data = response.data || [];
            joinedTournamentsList = Array.isArray(data) ? data : (data.data || []);

            const badge = document.getElementById("joinedCountBadge");
            if (badge) {
                if (joinedTournamentsList.length > 0) {
                    badge.textContent = joinedTournamentsList.length;
                    badge.classList.remove("d-none");
                } else {
                    badge.classList.add("d-none");
                }
            }

            renderJoinedPasses(container);
            renderExploreGrid(); // Re-render explore grid to update "Already Joined" status
        }
    } catch (error) {
        console.error("Load My Joined Tournaments Error:", error);
        container.innerHTML = `<div class="col-12 text-center py-5 text-muted">Failed to load joined tournaments. ${escapeHtml(error.message || '')}</div>`;
    }
}

function renderJoinedPasses(container) {
    if (joinedTournamentsList.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="rounded-circle bg-light p-4 d-inline-flex mb-3 text-muted">
                    <i class="bi bi-ticket-perforated fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark">No Joined Tournaments Yet</h5>
                <p class="text-muted small">Explore available competitions and register to claim your tournament player pass.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = joinedTournamentsList.map(pass => {
        const t = pass.tournament || {};
        const title = t.title || "Pickleball Championship";
        const courtName = t.court?.name || "Court Venue";
        const city = t.court?.city || "";
        const entryFee = Number(t.entry_fee || 0);
        const prizePool = Number(t.prize_pool || 0);
        const coverImg = getTournamentCoverImage(t);
        const status = (t.status || "upcoming").toLowerCase();
        const startDate = formatDateReadable(t.start_date);
        const joinedDate = formatDateReadable(pass.created_at);

        return `
            <div class="col-xl-6 col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3 p-md-4 card-hover-lift border-start border-4 border-warning">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4 col-12">
                            <img src="${escapeHtml(coverImg)}" alt="${escapeHtml(title)}" class="w-100 rounded-3 object-fit-cover" style="height: 120px;">
                        </div>
                        <div class="col-md-8 col-12">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 fw-bold fs-8">
                                    <i class="bi bi-check-circle-fill me-1"></i> CONFIRMED PASS
                                </span>
                                ${renderStatusBadge(status)}
                            </div>
                            <h5 class="fw-bold text-dark mb-1">${escapeHtml(title)}</h5>
                            <small class="text-muted d-block mb-2"><i class="bi bi-geo-alt me-1 text-primary"></i>${escapeHtml(courtName)} ${city ? '• ' + escapeHtml(city) : ''}</small>

                            <div class="d-flex align-items-center gap-3 fs-8 text-muted mb-3">
                                <span><i class="bi bi-calendar-event me-1"></i> Start: <strong>${escapeHtml(startDate)}</strong></span>
                                <span><i class="bi bi-cash me-1"></i> Fee: <strong>₹${entryFee.toLocaleString("en-IN")}</strong></span>
                            </div>

                            <div class="d-flex align-items-center justify-content-between border-top pt-2">
                                <small class="text-muted fs-8">Joined on ${escapeHtml(joinedDate)}</small>
                                ${status === 'upcoming' ? `
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-leave-tournament" data-id="${t.id}" data-title="${escapeHtml(title)}">
                                        <i class="bi bi-x-circle me-1"></i> Leave Tournament
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join("");

    attachJoinedEventListeners();
}

function attachJoinedEventListeners() {
    document.querySelectorAll(".btn-leave-tournament").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const title = btn.getAttribute("data-title");
            leaveTournament(id, title);
        });
    });
}

function inspectTournament(tournamentId) {
    const t = exploreTournamentsList.find(item => String(item.id) === String(tournamentId));
    if (!t) return;

    const modalBody = document.getElementById("modalTournamentBody");
    const modalFooter = document.getElementById("modalTournamentFooter");
    const coverImg = getTournamentCoverImage(t);
    const prizePool = Number(t.prize_pool || 0);
    const entryFee = Number(t.entry_fee || 0);
    const status = (t.status || "upcoming").toLowerCase();
    const joinedCount = t.participants ? t.participants.length : 0;
    const maxParticipants = t.max_participants || 32;

    const isUserJoined = joinedTournamentsList.some(j => String(j.tournament_id || j.tournament?.id) === String(t.id));

    if (modalBody) {
        modalBody.innerHTML = `
            <div class="row g-4">
                <div class="col-12">
                    <div class="position-relative rounded-4 overflow-hidden shadow-sm" style="height: 220px;">
                        <img src="${escapeHtml(coverImg)}" alt="${escapeHtml(t.title)}" class="w-100 h-100 object-fit-cover">
                        <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white bg-dark bg-opacity-75 backdrop-blur">
                            <h4 class="fw-bold mb-0">${escapeHtml(t.title)}</h4>
                            <small class="text-warning"><i class="bi bi-building me-1"></i>${escapeHtml(t.court?.name || 'Venue')}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8 text-uppercase">TOTAL PRIZE POOL</small>
                    <h3 class="fw-bold text-success mb-0">₹${prizePool.toLocaleString("en-IN")}</h3>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8 text-uppercase">PLAYER ENTRY FEE</small>
                    <h3 class="fw-bold text-dark mb-0">₹${entryFee.toLocaleString("en-IN")}</h3>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">START DATE</small>
                    <span class="text-dark fw-bold">${formatDateReadable(t.start_date)}</span>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">END DATE</small>
                    <span class="text-dark fw-bold">${formatDateReadable(t.end_date)}</span>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">REGISTRATION DEADLINE</small>
                    <span class="text-danger fw-bold">${formatDateReadable(t.registration_last_date || t.start_date)}</span>
                </div>

                <div class="col-12">
                    <small class="text-muted d-block fs-8 mb-1">PARTICIPANT CAPACITY</small>
                    <div class="progress rounded-pill mb-1" style="height: 10px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: ${(joinedCount / maxParticipants) * 100}%"></div>
                    </div>
                    <small class="text-muted">${joinedCount} of ${maxParticipants} player slots registered</small>
                </div>

                ${t.description ? `
                    <div class="col-12">
                        <small class="text-muted d-block fs-8 mb-1">EVENT RULES & INFORMATION</small>
                        <div class="p-3 bg-light rounded-3 text-dark fs-6">${escapeHtml(t.description)}</div>
                    </div>
                ` : ''}
            </div>
        `;
    }

    if (modalFooter) {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            ${isUserJoined ? `
                <button type="button" class="btn btn-success rounded-pill px-4 fw-bold disabled">
                    <i class="bi bi-check-circle-fill me-1"></i> Already Joined
                </button>
            ` : status === 'upcoming' ? `
                <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold text-dark btn-modal-join-tournament" data-id="${t.id}">
                    <i class="bi bi-ticket-perforated me-1"></i> Join Tournament Now
                </button>
            ` : `
                <button type="button" class="btn btn-secondary rounded-pill px-4 disabled">
                    Registration Closed
                </button>
            `}
        `;

        modalFooter.querySelector(".btn-modal-join-tournament")?.addEventListener("click", (e) => {
            const btn = e.currentTarget;
            joinTournament(btn.getAttribute("data-id"));
        });
    }

    const modal = new bootstrap.Modal(document.getElementById("tournamentDetailsModal"));
    modal.show();
}

async function joinTournament(tournamentId) {
    try {
        const response = await apiFetch(`/tournaments/${tournamentId}/join`, {
            method: "POST"
        });

        if (response && response.success) {
            bootstrap.Modal.getInstance(document.getElementById("tournamentDetailsModal"))?.hide();
            await loadMyJoinedTournaments();
            await loadExploreTournaments();

            // Switch to Joined tab
            const joinedTabBtn = document.getElementById("joined-tab");
            if (joinedTabBtn) {
                const tab = new bootstrap.Tab(joinedTabBtn);
                tab.show();
            }
        }
    } catch (error) {
        console.error("Join Tournament Error:", error);
        alert(error.message || "Failed to join tournament.");
    }
}

async function leaveTournament(tournamentId, tournamentTitle) {
    if (!confirm(`Are you sure you want to leave "${tournamentTitle}"?`)) return;

    try {
        const response = await apiFetch(`/tournaments/${tournamentId}/leave`, {
            method: "DELETE"
        });

        if (response && response.success) {
            await loadMyJoinedTournaments();
            await loadExploreTournaments();
        }
    } catch (error) {
        console.error("Leave Tournament Error:", error);
        alert(error.message || "Failed to leave tournament.");
    }
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