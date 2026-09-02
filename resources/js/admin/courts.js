import { apiFetch } from "../api.js";

let currentPage = 1;
let currentStatus = "";
let currentSearch = "";
let currentSort = "latest";
let targetCourtId = null;
let targetNewStatus = null;
let currentCourtsList = [];

document.addEventListener("DOMContentLoaded", () => {
    initFilters();
    loadCourts();
    setupToggleForm();
});

function initFilters() {
    // Status Pills Nav
    const pillsNav = document.getElementById("courtStatusPillsNav");
    if (pillsNav) {
        pillsNav.querySelectorAll("button").forEach(btn => {
            btn.addEventListener("click", () => {
                pillsNav.querySelectorAll("button").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                currentStatus = btn.getAttribute("data-status") || "";
                currentPage = 1;
                loadCourts();
            });
        });
    }

    // Search Input Debounce
    const searchInput = document.getElementById("courtSearchInput");
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener("input", (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentSearch = e.target.value.trim();
                currentPage = 1;
                loadCourts();
            }, 300);
        });
    }

    // Sort Select
    const sortSelect = document.getElementById("courtSortSelect");
    if (sortSelect) {
        sortSelect.addEventListener("change", (e) => {
            currentSort = e.target.value;
            currentPage = 1;
            loadCourts();
        });
    }
}

async function loadCourts() {
    const tbody = document.getElementById("courtsTbody");
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading court inventory...</td></tr>`;

    try {
        const queryParams = new URLSearchParams({
            page: currentPage,
            sort: currentSort
        });
        if (currentStatus) queryParams.append("status", currentStatus);
        if (currentSearch) queryParams.append("search", currentSearch);

        const response = await apiFetch(`/admin/courts?${queryParams.toString()}`);

        if (response && response.success) {
            const resData = response.data || {};
            const courts = resData.data || (Array.isArray(resData) ? resData : []);
            const meta = response.meta || response.data || {};

            currentCourtsList = courts;
            updateStatSummaryCards(courts, meta);
            renderCourtsTable(tbody, courts);
            renderPagination(meta);
        }
    } catch (error) {
        console.error("Load Admin Courts Error:", error);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load courts. ${escapeHtml(error.message || '')}</td></tr>`;
    }
}

function updateStatSummaryCards(courts, meta) {
    const totalEl = document.getElementById("statTotalCourts");
    const activeEl = document.getElementById("statActiveCourts");
    const inactiveEl = document.getElementById("statInactiveCourts");
    const avgPriceEl = document.getElementById("statAvgPrice");

    if (totalEl) totalEl.textContent = meta.total || courts.length;

    let activeCount = 0;
    let inactiveCount = 0;
    let totalPriceSum = 0;

    courts.forEach(c => {
        const st = (c.status || "").toLowerCase();
        if (st === "active") activeCount++;
        else inactiveCount++;

        const price = Number(c.price_per_hour || c.hourly_rate || 0);
        totalPriceSum += price;
    });

    if (activeEl) activeEl.textContent = activeCount;
    if (inactiveEl) inactiveEl.textContent = inactiveCount;

    const avgPrice = courts.length > 0 ? Math.round(totalPriceSum / courts.length) : 0;
    if (avgPriceEl) avgPriceEl.textContent = `₹${avgPrice.toLocaleString("en-IN")}`;
}

function renderCourtsTable(tbody, courts) {
    if (!Array.isArray(courts) || courts.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">No courts found matching criteria.</td></tr>`;
        return;
    }

    tbody.innerHTML = courts.map(court => {
        const courtName = court.name || "Court Venue";
        const ownerName = court.owner?.name || court.owner_name || "Venue Owner";
        const ownerEmail = court.owner?.email || "";
        const city = court.city || "";
        const state = court.state || "";
        const courtType = (court.court_type || court.type || "Indoor").toLowerCase();
        const price = Number(court.price_per_hour || court.hourly_rate || 0);
        const status = (court.status || "active").toLowerCase();
        const coverImg = getCourtCoverImage(court);

        return `
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="${escapeHtml(coverImg)}" alt="${escapeHtml(courtName)}" class="rounded-3 border object-fit-cover flex-shrink-0" style="width: 50px; height: 50px;">
                        <div>
                            <strong class="text-dark d-block">${escapeHtml(courtName)}</strong>
                            <small class="text-muted fs-8"><i class="bi bi-clock me-1"></i>${escapeHtml(court.start_time || '06:00')} - ${escapeHtml(court.end_time || '22:00')}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <strong class="text-dark d-block small">${escapeHtml(ownerName)}</strong>
                    <small class="text-muted fs-8">${escapeHtml(ownerEmail)}</small>
                </td>
                <td>
                    <small class="text-muted">${escapeHtml(city)}${city && state ? ', ' : ''}${escapeHtml(state)}</small>
                </td>
                <td>
                    ${courtType === 'indoor' ? `
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 fs-8">
                            <i class="bi bi-house-door me-1"></i> Indoor
                        </span>
                    ` : `
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2.5 py-1 fs-8">
                            <i class="bi bi-sun me-1"></i> Outdoor
                        </span>
                    `}
                </td>
                <td>
                    <strong class="text-dark">₹${price.toLocaleString("en-IN")}</strong>
                    <small class="text-muted fs-8">/ hr</small>
                </td>
                <td>
                    ${renderStatusBadge(status)}
                </td>
                <td class="text-end pe-4">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-light border text-dark rounded-circle btn-inspect-court" data-id="${court.id}" title="Inspect Venue">
                            <i class="bi bi-eye"></i>
                        </button>

                        ${status === "active" ? `
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-toggle-status" data-id="${court.id}" data-status="inactive" data-name="${escapeHtml(courtName)}" title="Suspend Court">
                                <i class="bi bi-pause-fill"></i>
                            </button>
                        ` : `
                            <button type="button" class="btn btn-sm btn-success rounded-circle btn-toggle-status" data-id="${court.id}" data-status="active" data-name="${escapeHtml(courtName)}" title="Activate Court">
                                <i class="bi bi-play-fill"></i>
                            </button>
                        `}
                    </div>
                </td>
            </tr>
        `;
    }).join("");

    attachTableEventListeners();
}

function attachTableEventListeners() {
    // Inspect Court
    document.querySelectorAll(".btn-inspect-court").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            inspectCourt(id);
        });
    });

    // Toggle Status
    document.querySelectorAll(".btn-toggle-status").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const newStatus = btn.getAttribute("data-status");
            const courtName = btn.getAttribute("data-name");
            openToggleStatusModal(id, newStatus, courtName);
        });
    });
}

function renderStatusBadge(status) {
    if (status === "active") {
        return `<span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-check-circle me-1"></i> Active</span>`;
    }
    return `<span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-slash-circle me-1"></i> Suspended</span>`;
}

function getCourtCoverImage(court) {
    if (!court) return "https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=600&q=80";

    let url = court.cover_image_url || court.cover_image;
    if (url) {
        return url.startsWith("http://") || url.startsWith("https://") ? url : url.startsWith("/") ? url : `/storage/${url}`;
    }

    if (Array.isArray(court.images) && court.images.length > 0) {
        const primary = court.images.find(i => i.is_primary) || court.images[0];
        const imgPath = primary.image_url || primary.image || primary.image_path;
        if (imgPath) {
            return imgPath.startsWith("http://") || imgPath.startsWith("https://") ? imgPath : imgPath.startsWith("/") ? imgPath : `/storage/${imgPath}`;
        }
    }

    return "https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=600&q=80";
}

/**
 * Inspect Court Modal
 */
function inspectCourt(courtId) {
    const court = currentCourtsList.find(c => String(c.id) === String(courtId));
    if (!court) return;

    const modalBody = document.getElementById("inspectCourtModalBody");
    const modalFooter = document.getElementById("inspectCourtModalFooter");
    const coverImg = getCourtCoverImage(court);
    const price = Number(court.price_per_hour || court.hourly_rate || 0);

    if (modalBody) {
        modalBody.innerHTML = `
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="position-relative rounded-4 overflow-hidden shadow-sm" style="height: 220px;">
                        <img src="${escapeHtml(coverImg)}" alt="${escapeHtml(court.name)}" class="w-100 h-100 object-fit-cover">
                        <div class="position-absolute bottom-0 start-0 w-100 p-3 text-white bg-dark bg-opacity-75 backdrop-blur">
                            <h4 class="fw-bold mb-0">${escapeHtml(court.name)}</h4>
                            <small class="text-white-50"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(court.address || '')}, ${escapeHtml(court.city || '')}</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">OWNER / PARTNER</small>
                    <strong class="text-dark fs-6">${escapeHtml(court.owner?.name || 'N/A')}</strong>
                    <div class="text-muted fs-8">${escapeHtml(court.owner?.email || '')}</div>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">HOURLY BOOKING FEE</small>
                    <h4 class="fw-bold text-success mb-0">₹${price.toLocaleString("en-IN")} <small class="fs-7 text-muted">/ hr</small></h4>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">COURT TYPE</small>
                    <span class="badge bg-secondary rounded-pill px-3 py-1">${escapeHtml(court.court_type || 'Indoor')}</span>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">SURFACE TYPE</small>
                    <span class="text-dark fw-semibold">${escapeHtml(court.surface_type || 'Acrylic Hard Court')}</span>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block fs-8">LIGHTING</small>
                    <span class="text-dark fw-semibold">${court.has_lighting ? 'LED Floodlights Enabled' : 'Daylight Only'}</span>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">OPERATING HOURS</small>
                    <span class="text-dark font-monospace">${escapeHtml(court.start_time || '06:00')} - ${escapeHtml(court.end_time || '22:00')}</span>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8">STATUS</small>
                    ${renderStatusBadge((court.status || 'active').toLowerCase())}
                </div>

                ${court.description ? `
                    <div class="col-12">
                        <small class="text-muted d-block fs-8">VENUE DESCRIPTION & AMENITIES</small>
                        <p class="text-dark bg-light p-3 rounded-3 mb-0">${escapeHtml(court.description)}</p>
                    </div>
                ` : ''}
            </div>
        `;
    }

    if (modalFooter) {
        const isCurrentActive = (court.status || "").toLowerCase() === "active";
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn ${isCurrentActive ? 'btn-danger' : 'btn-success'} rounded-pill px-4 btn-modal-toggle" data-id="${court.id}" data-status="${isCurrentActive ? 'inactive' : 'active'}" data-name="${escapeHtml(court.name)}">
                <i class="bi bi-${isCurrentActive ? 'pause-fill' : 'play-fill'} me-1"></i>
                ${isCurrentActive ? 'Suspend Court' : 'Activate Court'}
            </button>
        `;

        modalFooter.querySelector(".btn-modal-toggle")?.addEventListener("click", (e) => {
            const btn = e.currentTarget;
            bootstrap.Modal.getInstance(document.getElementById("inspectCourtModal"))?.hide();
            openToggleStatusModal(btn.getAttribute("data-id"), btn.getAttribute("data-status"), btn.getAttribute("data-name"));
        });
    }

    const modal = new bootstrap.Modal(document.getElementById("inspectCourtModal"));
    modal.show();
}

/**
 * Open Toggle Status Modal
 */
function openToggleStatusModal(courtId, newStatus, courtName) {
    targetCourtId = courtId;
    targetNewStatus = newStatus;

    const modalHeader = document.getElementById("toggleModalHeader");
    const modalTitle = document.getElementById("toggleModalTitle");
    const modalPrompt = document.getElementById("toggleModalPrompt");
    const modalAlert = document.getElementById("toggleModalAlert");
    const submitBtn = document.getElementById("submitToggleStatusBtn");

    const isSuspending = newStatus === "inactive";

    if (modalHeader) {
        modalHeader.className = `modal-header p-4 text-white ${isSuspending ? 'bg-danger' : 'bg-success'}`;
    }
    if (modalTitle) {
        modalTitle.textContent = isSuspending ? "Suspend Court Venue" : "Activate Court Venue";
    }
    if (modalPrompt) {
        modalPrompt.innerHTML = `Are you sure you want to <strong>${isSuspending ? 'suspend' : 'activate'}</strong> "${escapeHtml(courtName)}"?`;
    }
    if (modalAlert) {
        modalAlert.className = `alert ${isSuspending ? 'alert-danger' : 'alert-success'} rounded-3 small mb-0`;
        modalAlert.innerHTML = isSuspending
            ? `<i class="bi bi-exclamation-triangle-fill me-1"></i> Suspending this court will instantly hide it from public search results and block players from reserving new slots.`
            : `<i class="bi bi-check-circle-fill me-1"></i> Activating this court will make it visible in public search results and allow slot bookings.`;
    }
    if (submitBtn) {
        submitBtn.className = `btn ${isSuspending ? 'btn-danger' : 'btn-success'} rounded-pill px-4 fw-bold`;
        submitBtn.textContent = isSuspending ? "Confirm Suspension" : "Confirm Activation";
    }

    const modal = new bootstrap.Modal(document.getElementById("toggleCourtStatusModal"));
    modal.show();
}

/**
 * Setup Toggle Form Submission Handler
 */
function setupToggleForm() {
    const form = document.getElementById("toggleCourtStatusForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!targetCourtId || !targetNewStatus) return;

        const submitBtn = document.getElementById("submitToggleStatusBtn");
        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await apiFetch(`/admin/courts/${targetCourtId}/status`, {
                method: "PATCH",
                body: JSON.stringify({ status: targetNewStatus })
            });

            if (response && response.success) {
                bootstrap.Modal.getInstance(document.getElementById("toggleCourtStatusModal"))?.hide();
                loadCourts();
            }
        } catch (error) {
            console.error("Toggle Court Status Error:", error);
            alert(error.message || "Failed to update court status.");
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
        container.innerHTML = `<small class="text-muted">Showing all ${meta.total || 0} courts</small>`;
        return;
    }

    container.innerHTML = `
        <small class="text-muted">Page ${current} of ${last} (${meta.total} total courts)</small>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary ${current <= 1 ? 'disabled' : ''}" id="prevPageBtn">Previous</button>
            <button class="btn btn-sm btn-outline-secondary ${current >= last ? 'disabled' : ''}" id="nextPageBtn">Next</button>
        </div>
    `;

    document.getElementById("prevPageBtn")?.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadCourts();
        }
    });

    document.getElementById("nextPageBtn")?.addEventListener("click", () => {
        if (currentPage < last) {
            currentPage++;
            loadCourts();
        }
    });
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}
