import { apiFetch } from "../api.js";

let currentPage = 1;
let currentRole = "";
let currentSearch = "";
let currentSort = "latest";
let targetUserId = null;
let targetUserRole = null;
let targetIsBlocking = false;
let currentUsersList = [];

document.addEventListener("DOMContentLoaded", () => {
    initFilters();
    loadUsers();
    setupBlockForm();
});

function initFilters() {
    // Role Pills Nav
    const pillsNav = document.getElementById("userRolePillsNav");
    if (pillsNav) {
        pillsNav.querySelectorAll("button").forEach(btn => {
            btn.addEventListener("click", () => {
                pillsNav.querySelectorAll("button").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                currentRole = btn.getAttribute("data-role") || "";
                currentPage = 1;
                loadUsers();
            });
        });
    }

    // Search Input Debounce
    const searchInput = document.getElementById("userSearchInput");
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener("input", (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentSearch = e.target.value.trim();
                currentPage = 1;
                loadUsers();
            }, 300);
        });
    }

    // Sort Select
    const sortSelect = document.getElementById("userSortSelect");
    if (sortSelect) {
        sortSelect.addEventListener("change", (e) => {
            currentSort = e.target.value;
            currentPage = 1;
            loadUsers();
        });
    }
}

async function loadUsers() {
    const tbody = document.getElementById("usersTbody");
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading user directory...</td></tr>`;

    try {
        const queryParams = new URLSearchParams({
            page: currentPage,
            sort: currentSort
        });
        if (currentSearch) queryParams.append("search", currentSearch);

        let endpoint = "/admin/users";
        if (currentRole === "owner") {
            endpoint = "/admin/owners";
        }

        const response = await apiFetch(`${endpoint}?${queryParams.toString()}`);

        if (response && response.success) {
            const resData = response.data || {};
            let users = resData.data || (Array.isArray(resData) ? resData : []);
            const meta = response.meta || response.data || {};

            // If role filter is 'admin' or 'user' on all accounts endpoint
            if (currentRole && currentRole !== "owner") {
                users = users.filter(u => (u.role || "user").toLowerCase() === currentRole);
            }

            currentUsersList = users;
            updateStatSummaryCards(users, meta);
            renderUsersTable(tbody, users);
            renderPagination(meta);
        }
    } catch (error) {
        console.error("Load Admin Users Error:", error);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load user directory. ${escapeHtml(error.message || '')}</td></tr>`;
    }
}

function updateStatSummaryCards(users, meta) {
    const totalEl = document.getElementById("statTotalUsers");
    const playersEl = document.getElementById("statTotalPlayers");
    const ownersEl = document.getElementById("statTotalOwners");
    const blockedEl = document.getElementById("statBlockedAccounts");

    if (totalEl) totalEl.textContent = meta.total || users.length;

    let playersCount = 0;
    let ownersCount = 0;
    let blockedCount = 0;

    users.forEach(u => {
        const r = (u.role || "user").toLowerCase();
        const st = (u.status || "active").toLowerCase();

        if (r === "user") playersCount++;
        else if (r === "owner") ownersCount++;

        if (st === "blocked" || st === "suspended" || st === "inactive") blockedCount++;
    });

    if (playersEl) playersEl.textContent = playersCount;
    if (ownersEl) ownersEl.textContent = ownersCount;
    if (blockedEl) blockedEl.textContent = blockedCount;
}

function renderUsersTable(tbody, users) {
    if (!Array.isArray(users) || users.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">No accounts found matching criteria.</td></tr>`;
        return;
    }

    tbody.innerHTML = users.map(user => {
        const name = user.name || "Member User";
        const email = user.email || "";
        const phone = user.phone || user.phone_number || "-";
        const role = (user.role || "user").toLowerCase();
        const status = (user.status || "active").toLowerCase();
        const firstLetter = name.charAt(0).toUpperCase();

        return `
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle ${getRoleAvatarBg(role)} text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                            ${escapeHtml(firstLetter)}
                        </div>
                        <div>
                            <strong class="text-dark d-block">${escapeHtml(name)}</strong>
                            <small class="text-muted fs-8">ID #${user.id}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="text-dark small">${escapeHtml(email)}</span>
                </td>
                <td>
                    <small class="text-muted">${escapeHtml(phone)}</small>
                </td>
                <td>
                    ${renderRoleBadge(role)}
                </td>
                <td>
                    <small class="text-muted">${formatDateReadable(user.created_at)}</small>
                </td>
                <td>
                    ${renderStatusBadge(status)}
                </td>
                <td class="text-end pe-4">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-light border text-dark rounded-circle btn-inspect-user" data-id="${user.id}" title="Inspect Profile">
                            <i class="bi bi-eye"></i>
                        </button>

                        ${role !== 'admin' ? `
                            ${status === 'blocked' || status === 'suspended' ? `
                                <button type="button" class="btn btn-sm btn-success rounded-circle btn-toggle-block" data-id="${user.id}" data-role="${role}" data-status="active" data-name="${escapeHtml(name)}" title="Unblock Account">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            ` : `
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-toggle-block" data-id="${user.id}" data-role="${role}" data-status="blocked" data-name="${escapeHtml(name)}" title="Block Account">
                                    <i class="bi bi-slash-circle"></i>
                                </button>
                            `}
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join("");

    attachTableEventListeners();
}

function attachTableEventListeners() {
    // Inspect User
    document.querySelectorAll(".btn-inspect-user").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            inspectUser(id);
        });
    });

    // Toggle Block Status
    document.querySelectorAll(".btn-toggle-block").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const role = btn.getAttribute("data-role");
            const targetStatus = btn.getAttribute("data-status");
            const userName = btn.getAttribute("data-name");
            openToggleBlockModal(id, targetStatus, userName, role);
        });
    });
}

function getRoleAvatarBg(role) {
    if (role === "admin") return "bg-danger";
    if (role === "owner") return "bg-success";
    return "bg-primary";
}

function renderRoleBadge(role) {
    switch (role) {
        case "admin":
            return `<span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-shield-check me-1"></i> Admin</span>`;
        case "owner":
            return `<span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-patch-check me-1"></i> Court Owner</span>`;
        default:
            return `<span class="badge bg-info text-dark rounded-pill px-3 py-1"><i class="bi bi-person me-1"></i> Player</span>`;
    }
}

function renderStatusBadge(status) {
    if (status === "blocked" || status === "suspended" || status === "inactive") {
        return `<span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-slash-circle me-1"></i> Blocked</span>`;
    }
    return `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1"><i class="bi bi-circle-fill fs-9 me-1"></i> Active</span>`;
}

/**
 * Inspect User Modal
 */
function inspectUser(userId) {
    const user = currentUsersList.find(u => String(u.id) === String(userId));
    if (!user) return;

    const modalBody = document.getElementById("inspectUserModalBody");
    const modalFooter = document.getElementById("inspectUserModalFooter");
    const role = (user.role || "user").toLowerCase();
    const status = (user.status || "active").toLowerCase();

    if (modalBody) {
        modalBody.innerHTML = `
            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 mb-4 border">
                <div class="rounded-circle ${getRoleAvatarBg(role)} text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px; font-size: 1.5rem;">
                    ${escapeHtml((user.name || "U").charAt(0).toUpperCase())}
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0">${escapeHtml(user.name)}</h5>
                    <div class="text-muted small">${escapeHtml(user.email)}</div>
                    <div class="mt-1">${renderRoleBadge(role)} ${renderStatusBadge(status)}</div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">MEMBER ID</small>
                    <strong class="text-dark">#${user.id}</strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">CONTACT PHONE</small>
                    <span class="text-dark">${escapeHtml(user.phone || user.phone_number || 'N/A')}</span>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">REGISTERED ON</small>
                    <span class="text-dark">${formatDateReadable(user.created_at)}</span>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">LAST UPDATED</small>
                    <span class="text-dark">${formatDateReadable(user.updated_at)}</span>
                </div>
            </div>
        `;
    }

    if (modalFooter) {
        const isBlocked = status === "blocked" || status === "suspended";
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            ${role !== 'admin' ? `
                <button type="button" class="btn ${isBlocked ? 'btn-success' : 'btn-danger'} rounded-pill px-4 btn-modal-toggle-block" data-id="${user.id}" data-role="${role}" data-status="${isBlocked ? 'active' : 'blocked'}" data-name="${escapeHtml(user.name)}">
                    <i class="bi bi-${isBlocked ? 'check-lg' : 'slash-circle'} me-1"></i>
                    ${isBlocked ? 'Unblock Account' : 'Block Account'}
                </button>
            ` : ''}
        `;

        modalFooter.querySelector(".btn-modal-toggle-block")?.addEventListener("click", (e) => {
            const btn = e.currentTarget;
            bootstrap.Modal.getInstance(document.getElementById("inspectUserModal"))?.hide();
            openToggleBlockModal(btn.getAttribute("data-id"), btn.getAttribute("data-status"), btn.getAttribute("data-name"), btn.getAttribute("data-role"));
        });
    }

    const modal = new bootstrap.Modal(document.getElementById("inspectUserModal"));
    modal.show();
}

/**
 * Open Toggle Block Modal
 */
function openToggleBlockModal(userId, targetStatus, userName, role) {
    targetUserId = userId;
    targetUserRole = role;
    targetIsBlocking = targetStatus === "blocked";

    const modalHeader = document.getElementById("blockModalHeader");
    const modalTitle = document.getElementById("blockModalTitle");
    const modalPrompt = document.getElementById("blockModalPrompt");
    const modalAlert = document.getElementById("blockModalAlert");
    const submitBtn = document.getElementById("submitBlockUserBtn");

    if (modalHeader) {
        modalHeader.className = `modal-header p-4 text-white ${targetIsBlocking ? 'bg-danger' : 'bg-success'}`;
    }
    if (modalTitle) {
        modalTitle.textContent = targetIsBlocking ? "Block Account Access" : "Unblock Account Access";
    }
    if (modalPrompt) {
        modalPrompt.innerHTML = `Are you sure you want to <strong>${targetIsBlocking ? 'block' : 'unblock'}</strong> "${escapeHtml(userName)}"?`;
    }
    if (modalAlert) {
        modalAlert.className = `alert ${targetIsBlocking ? 'alert-danger' : 'alert-success'} rounded-3 small mb-0`;
        modalAlert.innerHTML = targetIsBlocking
            ? `<i class="bi bi-exclamation-triangle-fill me-1"></i> Blocking this account will immediately revoke login sessions and restrict access to the platform.`
            : `<i class="bi bi-check-circle-fill me-1"></i> Unblocking this account will restore full access and allow login.`;
    }
    if (submitBtn) {
        submitBtn.className = `btn ${targetIsBlocking ? 'btn-danger' : 'btn-success'} rounded-pill px-4 fw-bold`;
        submitBtn.textContent = targetIsBlocking ? "Confirm Block Access" : "Confirm Unblock Access";
    }

    const modal = new bootstrap.Modal(document.getElementById("toggleBlockUserModal"));
    modal.show();
}

/**
 * Setup Block Form Handler
 */
function setupBlockForm() {
    const form = document.getElementById("toggleBlockUserForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!targetUserId) return;

        const submitBtn = document.getElementById("submitBlockUserBtn");
        if (submitBtn) submitBtn.disabled = true;

        try {
            const rolePath = targetUserRole === "owner" ? "owners" : "users";
            const actionPath = targetIsBlocking ? "block" : "unblock";

            const response = await apiFetch(`/admin/${rolePath}/${targetUserId}/${actionPath}`, {
                method: "PATCH"
            });

            if (response && response.success) {
                bootstrap.Modal.getInstance(document.getElementById("toggleBlockUserModal"))?.hide();
                loadUsers();
            }
        } catch (error) {
            console.error("Toggle Block Access Error:", error);
            alert(error.message || "Failed to update user status.");
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
        container.innerHTML = `<small class="text-muted">Showing all ${meta.total || 0} accounts</small>`;
        return;
    }

    container.innerHTML = `
        <small class="text-muted">Page ${current} of ${last} (${meta.total} total accounts)</small>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary ${current <= 1 ? 'disabled' : ''}" id="prevPageBtn">Previous</button>
            <button class="btn btn-sm btn-outline-secondary ${current >= last ? 'disabled' : ''}" id="nextPageBtn">Next</button>
        </div>
    `;

    document.getElementById("prevPageBtn")?.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadUsers();
        }
    });

    document.getElementById("nextPageBtn")?.addEventListener("click", () => {
        if (currentPage < last) {
            currentPage++;
            loadUsers();
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
