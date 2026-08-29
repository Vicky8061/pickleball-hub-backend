import { apiFetch } from "../api.js";

let allCourts = [];
let allTournaments = [];
let activeStatusFilter = "";

document.addEventListener("DOMContentLoaded", () => {
    initTournamentsPage();
});

async function initTournamentsPage() {
    await loadOwnerCourts();
    await loadTournaments();
    setupFilterListeners();
    setupCreateForm();
    setupEditForm();
}

/**
 * Load Owner Courts for Dropdowns
 */
async function loadOwnerCourts() {
    try {
        const response = await apiFetch("/owner/courts");
        if (response && response.success) {
            allCourts = response.data || [];
            const filterSelect = document.getElementById("filterCourtSelect");
            const createSelect = document.getElementById("createTournamentCourtId");

            const options = allCourts.map(c => `<option value="${c.id}">${escapeHtml(c.name)} (${escapeHtml(c.court_type)})</option>`).join("");

            if (filterSelect) {
                filterSelect.innerHTML = `<option value="">All Court Venues (${allCourts.length})</option>` + options;
            }
            if (createSelect) {
                createSelect.innerHTML = `<option value="">Select Court Venue</option>` + options;
            }
        }
    } catch (error) {
        console.error("Load Courts Error:", error);
    }
}

/**
 * Load Owner Tournaments
 */
async function loadTournaments() {
    const grid = document.getElementById("ownerTournamentsGrid");
    if (!grid) return;

    try {
        const response = await apiFetch("/owner/tournaments/my?per_page=100");
        if (response && response.success) {
            allTournaments = response.data || [];
            applyFiltersAndRender();
        }
    } catch (error) {
        console.error("Load Tournaments Error:", error);
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="owner-card p-4 text-danger">
                    <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                    Failed to load tournaments. Please try refreshing.
                </div>
            </div>
        `;
    }
}

function setupFilterListeners() {
    const searchInput = document.getElementById("searchTournamentInput");
    const courtSelect = document.getElementById("filterCourtSelect");
    const resetBtn = document.getElementById("resetTournamentFiltersBtn");
    const statusTabs = document.querySelectorAll("#tournamentStatusTabs button");

    if (searchInput) searchInput.addEventListener("input", applyFiltersAndRender);
    if (courtSelect) courtSelect.addEventListener("change", applyFiltersAndRender);

    if (statusTabs) {
        statusTabs.forEach(tab => {
            tab.addEventListener("click", () => {
                statusTabs.forEach(t => t.classList.remove("active"));
                tab.classList.add("active");
                activeStatusFilter = tab.getAttribute("data-status") || "";
                applyFiltersAndRender();
            });
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener("click", () => {
            if (searchInput) searchInput.value = "";
            if (courtSelect) courtSelect.value = "";
            activeStatusFilter = "";
            statusTabs.forEach(t => t.classList.remove("active"));
            statusTabs[0]?.classList.add("active");
            applyFiltersAndRender();
        });
    }
}

function applyFiltersAndRender() {
    const searchVal = (document.getElementById("searchTournamentInput")?.value || "").toLowerCase().trim();
    const courtId = document.getElementById("filterCourtSelect")?.value;

    let filtered = [...allTournaments];

    if (searchVal) {
        filtered = filtered.filter(t => {
            const title = (t.title || "").toLowerCase();
            const courtName = (t.court?.name || "").toLowerCase();
            return title.includes(searchVal) || courtName.includes(searchVal);
        });
    }

    if (courtId) {
        filtered = filtered.filter(t => String(t.court_id) === String(courtId));
    }

    if (activeStatusFilter) {
        filtered = filtered.filter(t => (t.status || "").toLowerCase() === activeStatusFilter.toLowerCase());
    }

    updateTabCounts(allTournaments);
    renderTournamentsGrid(filtered);
}

function updateTabCounts(all) {
    const countAll = document.getElementById("countAll");
    const countUpcoming = document.getElementById("countUpcoming");
    const countOngoing = document.getElementById("countOngoing");
    const countCompleted = document.getElementById("countCompleted");
    const countCancelled = document.getElementById("countCancelled");

    if (countAll) countAll.textContent = all.length;
    if (countUpcoming) countUpcoming.textContent = all.filter(t => (t.status || "").toLowerCase() === "upcoming").length;
    if (countOngoing) countOngoing.textContent = all.filter(t => (t.status || "").toLowerCase() === "ongoing").length;
    if (countCompleted) countCompleted.textContent = all.filter(t => (t.status || "").toLowerCase() === "completed").length;
    if (countCancelled) countCancelled.textContent = all.filter(t => (t.status || "").toLowerCase() === "cancelled").length;
}

function renderTournamentsGrid(tournaments) {
    const grid = document.getElementById("ownerTournamentsGrid");
    if (!grid) return;

    if (!Array.isArray(tournaments) || tournaments.length === 0) {
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="owner-card p-5">
                    <i class="bi bi-trophy text-muted fs-1 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">No Tournaments Found</h5>
                    <p class="text-muted small mb-4">You haven't hosted any tournaments yet or no events match your filters.</p>
                    <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#createTournamentModal">
                        <i class="bi bi-plus-lg me-1"></i> Host Your First Tournament
                    </button>
                </div>
            </div>
        `;
        return;
    }

    grid.innerHTML = tournaments.map(t => {
        const status = (t.status || "").toLowerCase();
        const bannerUrl = t.banner_url || t.banner || "/assets/images/tournament-placeholder.jpg";
        const courtName = t.court?.name || "Court Venue";
        const participants = t.participants || [];
        const participantCount = participants.length;
        const maxParticipants = t.max_participants || 32;
        const fillPercent = Math.min(100, Math.round((participantCount / maxParticipants) * 100));

        return `
            <div class="col-lg-4 col-md-6">
                <div class="owner-card h-100 d-flex flex-column">

                    <!-- BANNER IMAGE -->
                    <div class="position-relative overflow-hidden" style="height: 190px;">
                        <img src="${escapeHtml(bannerUrl)}" class="w-100 h-100 object-fit-cover" alt="${escapeHtml(t.title)}" onerror="this.src='/assets/images/tournament-placeholder.jpg';">
                        
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge bg-light text-dark shadow-sm rounded-pill px-3 py-1">
                                <i class="bi bi-geo-alt me-1 text-danger"></i> ${escapeHtml(courtName)}
                            </span>
                        </div>

                        <div class="position-absolute top-0 end-0 m-3">
                            ${renderStatusBadge(status)}
                        </div>
                    </div>

                    <!-- CARD BODY -->
                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <h5 class="fw-bold text-dark mb-2 text-truncate">${escapeHtml(t.title)}</h5>
                        
                        <div class="d-flex align-items-center text-muted small mb-3">
                            <i class="bi bi-calendar-event me-2 text-primary"></i> ${formatDateReadable(t.tournament_date)}
                            <span class="mx-2">•</span>
                            <i class="bi bi-clock me-1 text-muted"></i> ${formatTime12H(t.start_time)}
                        </div>

                        <!-- PRICE & PRIZE -->
                        <div class="row g-2 text-center bg-light rounded-3 p-2 mb-3">
                            <div class="col-6 border-end">
                                <small class="text-muted d-block fs-8">ENTRY FEE</small>
                                <strong class="text-dark small">₹${formatPrice(t.entry_fee)}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block fs-8">PRIZE POOL</small>
                                <strong class="text-warning small">${escapeHtml(t.prize || 'Trophy')}</strong>
                            </div>
                        </div>

                        <!-- PARTICIPANTS PROGRESS -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Registered Players</span>
                                <strong class="text-dark">${participantCount} / ${maxParticipants}</strong>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: ${fillPercent}%;" aria-valuenow="${fillPercent}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <!-- ACTIONS FOOTER -->
                        <div class="mt-auto d-flex gap-2 pt-2 border-top">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 rounded-pill fw-semibold" onclick="openParticipantsModal(${t.id})">
                                <i class="bi bi-people me-1"></i> Players (${participantCount})
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm rounded-circle px-2" onclick="openEditTournamentModal(${t.id})" title="Edit Event">
                                <i class="bi bi-pencil"></i>
                            </button>
                            ${status !== 'cancelled' ? `
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-circle px-2" onclick="cancelTournament(${t.id})" title="Cancel Event">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            ` : ''}
                        </div>

                    </div>

                </div>
            </div>
        `;
    }).join("");
}

function renderStatusBadge(status) {
    switch (status) {
        case "ongoing":
            return `<span class="badge bg-warning text-dark rounded-pill px-3 py-1 shadow-sm"><i class="bi bi-play-circle-fill me-1"></i> Ongoing</span>`;
        case "completed":
            return `<span class="badge bg-success rounded-pill px-3 py-1 shadow-sm"><i class="bi bi-check-circle-fill me-1"></i> Completed</span>`;
        case "cancelled":
            return `<span class="badge bg-danger rounded-pill px-3 py-1 shadow-sm"><i class="bi bi-x-circle-fill me-1"></i> Cancelled</span>`;
        default:
            return `<span class="badge bg-primary rounded-pill px-3 py-1 shadow-sm"><i class="bi bi-calendar-check me-1"></i> Upcoming</span>`;
    }
}

/**
 * Setup Create Form
 */
function setupCreateForm() {
    const form = document.getElementById("createTournamentForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById("submitCreateTournamentBtn");
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);

        try {
            const response = await apiFetch("/owner/tournaments", {
                method: "POST",
                body: formData
            });

            if (response && response.success) {
                const modalEl = document.getElementById("createTournamentModal");
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                form.reset();
                await loadTournaments();
            }
        } catch (error) {
            console.error("Create Tournament Error:", error);
            alert(error.message || "Failed to create tournament.");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

/**
 * Setup Edit Form
 */
function setupEditForm() {
    const form = document.getElementById("editTournamentForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const tournamentId = document.getElementById("editTournamentId").value;
        const submitBtn = document.getElementById("submitEditTournamentBtn");
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);
        const payload = {
            title: formData.get("title"),
            description: formData.get("description"),
            status: formData.get("status"),
            tournament_date: formData.get("tournament_date"),
            registration_last_date: formData.get("registration_last_date"),
            start_time: formData.get("start_time"),
            end_time: formData.get("end_time"),
            entry_fee: Number(formData.get("entry_fee")),
            max_participants: Number(formData.get("max_participants")),
            prize: formData.get("prize")
        };

        try {
            const response = await apiFetch(`/owner/tournaments/${tournamentId}`, {
                method: "PUT",
                body: JSON.stringify(payload)
            });

            if (response && response.success) {
                const modalEl = document.getElementById("editTournamentModal");
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                await loadTournaments();
            }
        } catch (error) {
            console.error("Edit Tournament Error:", error);
            alert(error.message || "Failed to update tournament.");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

window.openEditTournamentModal = function (tournamentId) {
    const t = allTournaments.find(item => item.id === tournamentId);
    if (!t) return;

    document.getElementById("editTournamentId").value = t.id;
    document.getElementById("editTournamentTitle").value = t.title || "";
    document.getElementById("editTournamentStatus").value = (t.status || "upcoming").toLowerCase();
    document.getElementById("editTournamentDescription").value = t.description || "";
    document.getElementById("editTournamentDate").value = t.tournament_date || "";
    document.getElementById("editTournamentRegDate").value = t.registration_last_date || "";
    document.getElementById("editTournamentStartTime").value = t.start_time || "09:00";
    document.getElementById("editTournamentEndTime").value = t.end_time || "18:00";
    document.getElementById("editTournamentEntryFee").value = t.entry_fee || 0;
    document.getElementById("editTournamentMaxParticipants").value = t.max_participants || 32;
    document.getElementById("editTournamentPrize").value = t.prize || "";

    const modal = new bootstrap.Modal(document.getElementById("editTournamentModal"));
    modal.show();
};

window.cancelTournament = async function (tournamentId) {
    if (!confirm("Are you sure you want to cancel this tournament?")) return;

    try {
        const response = await apiFetch(`/owner/tournaments/${tournamentId}`, {
            method: "DELETE"
        });

        if (response && response.success) {
            await loadTournaments();
        }
    } catch (error) {
        console.error("Cancel Tournament Error:", error);
        alert(error.message || "Failed to cancel tournament.");
    }
};

window.openParticipantsModal = function (tournamentId) {
    const t = allTournaments.find(item => item.id === tournamentId);
    if (!t) return;

    document.getElementById("participantsModalTitle").textContent = t.title;

    const tbody = document.getElementById("participantsTableBody");
    const participants = t.participants || [];

    if (participants.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    No player has registered for this tournament yet.
                </td>
            </tr>
        `;
    } else {
        tbody.innerHTML = participants.map((p, idx) => `
            <tr>
                <td>${idx + 1}</td>
                <td class="fw-bold text-dark">${escapeHtml(p.user?.name || 'Player')}</td>
                <td class="text-muted small">${escapeHtml(p.user?.email || '')}</td>
                <td>
                    <span class="badge ${p.payment_status === 'paid' ? 'bg-success' : 'bg-warning'} rounded-pill px-3 py-1">
                        ${escapeHtml(p.payment_status || 'pending')}
                    </span>
                </td>
                <td class="text-end text-muted small">${formatDateReadable(p.created_at)}</td>
            </tr>
        `).join("");
    }

    const modal = new bootstrap.Modal(document.getElementById("tournamentParticipantsModal"));
    modal.show();
};

/**
 * Helpers
 */
function formatPrice(val) {
    const num = Number(val);
    if (isNaN(num)) return "0";
    return num.toLocaleString("en-IN");
}

function formatDateReadable(dateStr) {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    return d.toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" });
}

function formatTime12H(timeStr) {
    if (!timeStr) return "";
    const parts = timeStr.split(":");
    let hours = parseInt(parts[0], 10);
    const minutes = parts[1] || "00";
    const ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12;
    hours = hours ? hours : 12;
    return `${hours}:${minutes} ${ampm}`;
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}
