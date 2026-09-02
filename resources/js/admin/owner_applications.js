import { apiFetch } from "../api.js";

let currentPage = 1;
let currentStatus = "";
let currentSearch = "";
let currentSort = "latest";
let targetAppId = null;
let currentApplicationsList = [];

document.addEventListener("DOMContentLoaded", () => {
    initFilters();
    loadApplications();
    setupModalForms();
});

function initFilters() {
    // Status Pills Nav
    const pillsNav = document.getElementById("statusPillsNav");
    if (pillsNav) {
        pillsNav.querySelectorAll("button").forEach(btn => {
            btn.addEventListener("click", () => {
                pillsNav.querySelectorAll("button").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                currentStatus = btn.getAttribute("data-status") || "";
                currentPage = 1;
                loadApplications();
            });
        });
    }

    // Search Input Debounce
    const searchInput = document.getElementById("appSearchInput");
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener("input", (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentSearch = e.target.value.trim();
                currentPage = 1;
                loadApplications();
            }, 300);
        });
    }

    // Sort Select
    const sortSelect = document.getElementById("appSortSelect");
    if (sortSelect) {
        sortSelect.addEventListener("change", (e) => {
            currentSort = e.target.value;
            currentPage = 1;
            loadApplications();
        });
    }
}

async function loadApplications() {
    const tbody = document.getElementById("applicationsTbody");
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading applications...</td></tr>`;

    try {
        const queryParams = new URLSearchParams({
            page: currentPage,
            sort: currentSort
        });
        if (currentStatus) queryParams.append("status", currentStatus);
        if (currentSearch) queryParams.append("search", currentSearch);

        const response = await apiFetch(`/admin/owner-applications?${queryParams.toString()}`);

        if (response && response.success) {
            const resData = response.data || {};
            const applications = resData.data || (Array.isArray(resData) ? resData : []);
            const meta = response.meta || response.data || {};
            
            currentApplicationsList = applications;
            updateStatSummaryCards(applications, meta);
            renderApplicationsTable(tbody, applications);
            renderPagination(meta);
        }
    } catch (error) {
        console.error("Load Owner Applications Error:", error);
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load applications. ${escapeHtml(error.message || '')}</td></tr>`;
    }
}

function updateStatSummaryCards(applications, meta) {
    const totalEl = document.getElementById("statTotalApps");
    const pendingEl = document.getElementById("statPendingApps");
    const approvedEl = document.getElementById("statApprovedApps");
    const rejectedEl = document.getElementById("statRejectedApps");

    if (totalEl) totalEl.textContent = meta.total || applications.length;

    let pendingCount = 0;
    let approvedCount = 0;
    let rejectedCount = 0;

    applications.forEach(a => {
        const st = (a.status || "").toLowerCase();
        if (st === "pending") pendingCount++;
        else if (st === "approved") approvedCount++;
        else if (st === "rejected") rejectedCount++;
    });

    if (pendingEl) pendingEl.textContent = pendingCount;
    if (approvedEl) approvedEl.textContent = approvedCount;
    if (rejectedEl) rejectedEl.textContent = rejectedCount;

    // Update Navbar pending badge
    const navBadge = document.getElementById("navPendingBadge");
    if (navBadge) {
        if (pendingCount > 0) {
            navBadge.textContent = pendingCount;
            navBadge.classList.remove("d-none");
        } else {
            navBadge.classList.add("d-none");
        }
    }
}

function renderApplicationsTable(tbody, applications) {
    if (!Array.isArray(applications) || applications.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">No owner applications found matching criteria.</td></tr>`;
        return;
    }

    tbody.innerHTML = applications.map(app => {
        const userName = app.user?.name || "Applicant";
        const email = app.user?.email || "";
        const status = (app.status || "pending").toLowerCase();
        const docUrl = getFormattedDocUrl(app);

        return `
            <tr>
                <td class="ps-4">
                    <strong class="text-dark d-block">${escapeHtml(userName)}</strong>
                    <small class="text-muted fs-8">${escapeHtml(email)}</small>
                </td>
                <td>
                    <span class="fw-bold text-dark">${escapeHtml(app.business_name || '-')}</span>
                </td>
                <td>
                    <small class="text-muted">${escapeHtml(app.city || '')}, ${escapeHtml(app.state || '')}</small>
                </td>
                <td>
                    <small class="text-dark">${escapeHtml(app.phone || '-')}</small>
                </td>
                <td>
                    ${docUrl ? `
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 btn-inspect-doc" data-id="${app.id}">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Inspect Document
                        </button>
                    ` : `<span class="badge bg-secondary">No File</span>`}
                </td>
                <td>
                    <small class="text-muted">${formatDateReadable(app.created_at)}</small>
                </td>
                <td>
                    ${renderStatusBadge(status)}
                </td>
                <td class="text-end pe-4">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-light border text-dark rounded-circle btn-inspect-doc" data-id="${app.id}" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>

                        ${status === "pending" ? `
                            <button type="button" class="btn btn-sm btn-success rounded-circle btn-approve-app" data-id="${app.id}" data-name="${escapeHtml(app.business_name)}" title="Approve Application">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger rounded-circle btn-reject-app" data-id="${app.id}" data-name="${escapeHtml(app.business_name)}" title="Reject Application">
                                <i class="bi bi-x-lg"></i>
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
    // Inspect Document
    document.querySelectorAll(".btn-inspect-doc").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            inspectApplication(id);
        });
    });

    // Approve Button
    document.querySelectorAll(".btn-approve-app").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const name = btn.getAttribute("data-name");
            openApproveModal(id, name);
        });
    });

    // Reject Button
    document.querySelectorAll(".btn-reject-app").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const name = btn.getAttribute("data-name");
            openRejectModal(id, name);
        });
    });
}

function renderStatusBadge(status) {
    switch (status) {
        case "approved":
            return `<span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-check-circle me-1"></i> Approved</span>`;
        case "rejected":
            return `<span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-x-circle me-1"></i> Rejected</span>`;
        default:
            return `<span class="badge bg-warning text-dark rounded-pill px-3 py-1"><i class="bi bi-clock me-1"></i> Pending</span>`;
    }
}

/**
 * Inspect Document Modal
 */
function inspectApplication(appId) {
    const app = currentApplicationsList.find(a => String(a.id) === String(appId));
    if (!app) return;

    const modalBody = document.getElementById("inspectModalBody");
    const modalFooter = document.getElementById("inspectModalFooter");
    const docUrl = getFormattedDocUrl(app);

    if (modalBody) {
        modalBody.innerHTML = `
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">APPLICANT NAME</small>
                    <strong class="text-dark fs-6">${escapeHtml(app.user?.name || 'N/A')}</strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">EMAIL ADDRESS</small>
                    <span class="text-dark fs-6">${escapeHtml(app.user?.email || 'N/A')}</span>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">BUSINESS / CLUB NAME</small>
                    <strong class="text-primary fs-6">${escapeHtml(app.business_name)}</strong>
                </div>
                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">CONTACT PHONE</small>
                    <span class="text-dark fs-6">${escapeHtml(app.phone)}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">CITY</small>
                    <span class="text-dark">${escapeHtml(app.city)}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">STATE</small>
                    <span class="text-dark">${escapeHtml(app.state)}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">PINCODE</small>
                    <span class="text-dark">${escapeHtml(app.pincode)}</span>
                </div>
                <div class="col-12">
                    <small class="text-muted d-block fs-8">FULL ADDRESS</small>
                    <span class="text-dark">${escapeHtml(app.address)}</span>
                </div>
                ${app.experience ? `
                    <div class="col-12">
                        <small class="text-muted d-block fs-8">EXPERIENCE</small>
                        <span class="text-dark">${escapeHtml(app.experience)}</span>
                    </div>
                ` : ''}
                ${app.description ? `
                    <div class="col-12">
                        <small class="text-muted d-block fs-8">FACILITY DESCRIPTION & PLAN</small>
                        <p class="text-dark bg-light p-3 rounded-3 mb-0">${escapeHtml(app.description)}</p>
                    </div>
                ` : ''}
            </div>

            <div class="border-top pt-3">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-file-earmark-text text-danger me-2"></i>Uploaded Verification Proof Document</h6>
                ${docUrl ? `
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <a href="${escapeHtml(docUrl)}" target="_blank" class="btn btn-danger rounded-pill px-4 py-2 fw-bold shadow-sm">
                            <i class="bi bi-box-arrow-up-right me-2"></i> Open Verification Document in New Tab
                        </a>
                    </div>
                ` : `<div class="alert alert-secondary">No document proof uploaded.</div>`}
            </div>
        `;
    }

    if (modalFooter) {
        const isPending = (app.status || "").toLowerCase() === "pending";
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            ${isPending ? `
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-danger rounded-pill px-4 btn-modal-reject" data-id="${app.id}" data-name="${escapeHtml(app.business_name)}">
                        <i class="bi bi-x-lg me-1"></i> Reject
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-4 btn-modal-approve" data-id="${app.id}" data-name="${escapeHtml(app.business_name)}">
                        <i class="bi bi-check-lg me-1"></i> Approve & Promote User
                    </button>
                </div>
            ` : ''}
        `;

        modalFooter.querySelector(".btn-modal-approve")?.addEventListener("click", () => {
            bootstrap.Modal.getInstance(document.getElementById("inspectDocumentModal"))?.hide();
            openApproveModal(app.id, app.business_name);
        });

        modalFooter.querySelector(".btn-modal-reject")?.addEventListener("click", () => {
            bootstrap.Modal.getInstance(document.getElementById("inspectDocumentModal"))?.hide();
            openRejectModal(app.id, app.business_name);
        });
    }

    const modal = new bootstrap.Modal(document.getElementById("inspectDocumentModal"));
    modal.show();
}

/**
 * Open Approve Modal
 */
function openApproveModal(appId, businessName) {
    targetAppId = appId;
    const nameEl = document.getElementById("approveModalBusinessName");
    const noteEl = document.getElementById("approveAdminNote");
    if (nameEl) nameEl.textContent = businessName || "this application";
    if (noteEl) noteEl.value = "";

    const modal = new bootstrap.Modal(document.getElementById("approveApplicationModal"));
    modal.show();
}

/**
 * Open Reject Modal
 */
function openRejectModal(appId, businessName) {
    targetAppId = appId;
    const nameEl = document.getElementById("rejectModalBusinessName");
    const noteEl = document.getElementById("rejectAdminNote");
    if (nameEl) nameEl.textContent = businessName || "this application";
    if (noteEl) noteEl.value = "";

    const modal = new bootstrap.Modal(document.getElementById("rejectApplicationModal"));
    modal.show();
}

/**
 * Setup Modal Forms Handler
 */
function setupModalForms() {
    // Approve Form
    const approveForm = document.getElementById("approveApplicationForm");
    if (approveForm) {
        approveForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            if (!targetAppId) return;

            const submitBtn = document.getElementById("submitApproveBtn");
            const adminNote = document.getElementById("approveAdminNote")?.value || "";

            if (submitBtn) submitBtn.disabled = true;

            try {
                const response = await apiFetch(`/admin/owner-applications/${targetAppId}/approve`, {
                    method: "PATCH",
                    body: JSON.stringify({ admin_note: adminNote })
                });

                if (response && response.success) {
                    bootstrap.Modal.getInstance(document.getElementById("approveApplicationModal"))?.hide();
                    loadApplications();
                }
            } catch (error) {
                console.error("Approve Owner Application Error:", error);
                alert(error.message || "Failed to approve application.");
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    // Reject Form
    const rejectForm = document.getElementById("rejectApplicationForm");
    if (rejectForm) {
        rejectForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            if (!targetAppId) return;

            const submitBtn = document.getElementById("submitRejectBtn");
            const adminNote = document.getElementById("rejectAdminNote")?.value || "";

            if (!adminNote) {
                alert("Please enter a reason for rejecting this application.");
                return;
            }

            if (submitBtn) submitBtn.disabled = true;

            try {
                const response = await apiFetch(`/admin/owner-applications/${targetAppId}/reject`, {
                    method: "PATCH",
                    body: JSON.stringify({ admin_note: adminNote })
                });

                if (response && response.success) {
                    bootstrap.Modal.getInstance(document.getElementById("rejectApplicationModal"))?.hide();
                    loadApplications();
                }
            } catch (error) {
                console.error("Reject Owner Application Error:", error);
                alert(error.message || "Failed to reject application.");
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }
}

function renderPagination(meta) {
    const container = document.getElementById("paginationContainer");
    if (!container) return;

    const current = meta.current_page || 1;
    const last = meta.last_page || 1;

    if (last <= 1) {
        container.innerHTML = `<small class="text-muted">Showing all ${meta.total || 0} applications</small>`;
        return;
    }

    container.innerHTML = `
        <small class="text-muted">Page ${current} of ${last} (${meta.total} total applications)</small>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary ${current <= 1 ? 'disabled' : ''}" id="prevPageBtn">Previous</button>
            <button class="btn btn-sm btn-outline-secondary ${current >= last ? 'disabled' : ''}" id="nextPageBtn">Next</button>
        </div>
    `;

    document.getElementById("prevPageBtn")?.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadApplications();
        }
    });

    document.getElementById("nextPageBtn")?.addEventListener("click", () => {
        if (currentPage < last) {
            currentPage++;
            loadApplications();
        }
    });
}

function formatDateReadable(dateStr) {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    return d.toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" });
}

function getFormattedDocUrl(app) {
    if (!app) return null;
    let url = app.document_url || app.document;
    if (!url) return null;
    if (url.startsWith("http://") || url.startsWith("https://")) {
        return url;
    }
    if (url.startsWith("/")) {
        return url;
    }
    return `/storage/${url}`;
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}
