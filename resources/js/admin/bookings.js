import { apiFetch } from "../api.js";

let currentPage = 1;
let currentStatus = "";
let currentSearch = "";
let currentSort = "latest";
let currentBookingsList = [];

document.addEventListener("DOMContentLoaded", () => {
    initFilters();
    loadBookings();
});

function initFilters() {
    // Status Pills Nav
    const pillsNav = document.getElementById("bookingStatusPillsNav");
    if (pillsNav) {
        pillsNav.querySelectorAll("button").forEach(btn => {
            btn.addEventListener("click", () => {
                pillsNav.querySelectorAll("button").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                currentStatus = btn.getAttribute("data-status") || "";
                currentPage = 1;
                loadBookings();
            });
        });
    }

    // Search Input Debounce
    const searchInput = document.getElementById("bookingSearchInput");
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener("input", (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentSearch = e.target.value.trim();
                currentPage = 1;
                loadBookings();
            }, 300);
        });
    }

    // Sort Select
    const sortSelect = document.getElementById("bookingSortSelect");
    if (sortSelect) {
        sortSelect.addEventListener("change", (e) => {
            currentSort = e.target.value;
            currentPage = 1;
            loadBookings();
        });
    }
}

async function loadBookings() {
    const tbody = document.getElementById("bookingsTbody");
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading master bookings...</td></tr>`;

    try {
        const queryParams = new URLSearchParams({
            page: currentPage,
            sort: currentSort
        });
        if (currentStatus) queryParams.append("status", currentStatus);
        if (currentSearch) queryParams.append("search", currentSearch);

        const response = await apiFetch(`/admin/bookings?${queryParams.toString()}`);

        if (response && response.success) {
            const resData = response.data || {};
            const bookings = resData.data || (Array.isArray(resData) ? resData : []);
            const meta = response.pagination || response.meta || {};

            currentBookingsList = bookings;
            updateStatSummaryCards(bookings, meta);
            renderBookingsTable(tbody, bookings);
            renderPagination(meta);
        }
    } catch (error) {
        console.error("Load Admin Bookings Error:", error);
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load bookings. ${escapeHtml(error.message || '')}</td></tr>`;
    }
}

function updateStatSummaryCards(bookings, meta) {
    const totalEl = document.getElementById("statTotalBookings");
    const confirmedEl = document.getElementById("statConfirmedBookings");
    const pendingEl = document.getElementById("statPendingBookings");
    const cancelledEl = document.getElementById("statCancelledBookings");

    if (totalEl) totalEl.textContent = meta.total || bookings.length;

    let confirmedCount = 0;
    let pendingCount = 0;
    let cancelledCount = 0;

    bookings.forEach(b => {
        const st = (b.booking_status || b.status || "").toLowerCase();
        if (st === "confirmed" || st === "completed") confirmedCount++;
        else if (st === "pending") pendingCount++;
        else if (st === "cancelled" || st === "canceled") cancelledCount++;
    });

    if (confirmedEl) confirmedEl.textContent = confirmedCount;
    if (pendingEl) pendingEl.textContent = pendingCount;
    if (cancelledEl) cancelledEl.textContent = cancelledCount;
}

function renderBookingsTable(tbody, bookings) {
    if (!Array.isArray(bookings) || bookings.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-5 text-muted">No reservations found matching criteria.</td></tr>`;
        return;
    }

    tbody.innerHTML = bookings.map(booking => {
        const refId = `BK-${String(booking.id).padStart(5, '0')}`;
        const userName = booking.user?.name || "Player";
        const userEmail = booking.user?.email || "";
        const courtName = booking.court?.name || "Court Venue";
        const city = booking.court?.city || "";
        const bookingDate = formatDateReadable(booking.booking_date || booking.date || booking.created_at);
        const slotTime = booking.time_slot ? `${booking.time_slot.start_time || ''} - ${booking.time_slot.end_time || ''}` : (booking.start_time ? `${booking.start_time} - ${booking.end_time}` : 'Full Day');
        const amount = Number(booking.total_price || booking.amount || booking.price || 0);
        const status = (booking.booking_status || booking.status || "confirmed").toLowerCase();

        return `
            <tr>
                <td class="ps-4">
                    <strong class="text-dark font-monospace fs-8">#${refId}</strong>
                </td>
                <td>
                    <strong class="text-dark d-block small">${escapeHtml(userName)}</strong>
                    <small class="text-muted fs-8">${escapeHtml(userEmail)}</small>
                </td>
                <td>
                    <strong class="text-dark d-block small">${escapeHtml(courtName)}</strong>
                    <small class="text-muted fs-8"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(city)}</small>
                </td>
                <td>
                    <span class="text-dark small d-block">${escapeHtml(bookingDate)}</span>
                    <small class="text-muted fs-8"><i class="bi bi-clock me-1"></i>${escapeHtml(slotTime)}</small>
                </td>
                <td>
                    <strong class="text-dark">₹${amount.toLocaleString("en-IN")}</strong>
                </td>
                <td>
                    ${renderStatusBadge(status)}
                </td>
                <td class="text-end pe-4">
                    <button type="button" class="btn btn-sm btn-light border text-dark rounded-circle btn-inspect-booking" data-id="${booking.id}" title="Inspect Invoice">
                        <i class="bi bi-eye"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join("");

    attachTableEventListeners();
}

function attachTableEventListeners() {
    document.querySelectorAll(".btn-inspect-booking").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            inspectBooking(id);
        });
    });
}

function renderStatusBadge(status) {
    switch (status) {
        case "confirmed":
        case "completed":
            return `<span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-check-circle me-1"></i> Confirmed</span>`;
        case "pending":
            return `<span class="badge bg-warning text-dark rounded-pill px-3 py-1"><i class="bi bi-clock me-1"></i> Pending</span>`;
        case "cancelled":
        case "canceled":
            return `<span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-x-circle me-1"></i> Cancelled</span>`;
        default:
            return `<span class="badge bg-secondary rounded-pill px-3 py-1">${escapeHtml(status)}</span>`;
    }
}

/**
 * Inspect Booking Invoice Modal
 */
async function inspectBooking(bookingId) {
    const modalBody = document.getElementById("inspectBookingModalBody");
    if (!modalBody) return;

    modalBody.innerHTML = `<div class="text-center py-5"><span class="spinner-border text-primary"></span></div>`;
    const modal = new bootstrap.Modal(document.getElementById("inspectBookingModal"));
    modal.show();

    try {
        const response = await apiFetch(`/admin/bookings/${bookingId}`);

        if (response && response.success) {
            const booking = response.data || {};
            const refId = `BK-${String(booking.id).padStart(5, '0')}`;
            const userName = booking.user?.name || "N/A";
            const userEmail = booking.user?.email || "";
            const userPhone = booking.user?.phone || booking.user?.phone_number || "N/A";
            const courtName = booking.court?.name || "Venue Court";
            const courtAddress = booking.court?.address || "";
            const courtCity = booking.court?.city || "";
            const bookingDate = formatDateReadable(booking.booking_date || booking.date || booking.created_at);
            const slotTime = booking.time_slot ? `${booking.time_slot.start_time || ''} - ${booking.time_slot.end_time || ''}` : (booking.start_time ? `${booking.start_time} - ${booking.end_time}` : 'N/A');
            const status = (booking.booking_status || booking.status || "confirmed").toLowerCase();
            const totalPrice = Number(booking.total_price || booking.amount || booking.price || 0);

            modalBody.innerHTML = `
                <div class="row g-4">

                    <!-- HEADER INVOICE SUMMARY -->
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-4 border d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <small class="text-muted d-block fs-8 fw-bold text-uppercase">RESERVATION REFERENCE</small>
                                <h4 class="fw-bold text-dark mb-0 font-monospace">#${refId}</h4>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block fs-8">BOOKING STATUS</small>
                                ${renderStatusBadge(status)}
                            </div>
                        </div>
                    </div>

                    <!-- PLAYER INFORMATION -->
                    <div class="col-md-6">
                        <div class="card border-0 bg-light p-3 rounded-4 h-100">
                            <small class="text-muted d-block fs-8 fw-bold text-uppercase mb-2"><i class="bi bi-person me-1"></i>PLAYER / CUSTOMER</small>
                            <h6 class="fw-bold text-dark mb-1">${escapeHtml(userName)}</h6>
                            <div class="text-muted fs-8">${escapeHtml(userEmail)}</div>
                            <div class="text-muted fs-8"><i class="bi bi-telephone me-1"></i>${escapeHtml(userPhone)}</div>
                        </div>
                    </div>

                    <!-- COURT VENUE INFORMATION -->
                    <div class="col-md-6">
                        <div class="card border-0 bg-light p-3 rounded-4 h-100">
                            <small class="text-muted d-block fs-8 fw-bold text-uppercase mb-2"><i class="bi bi-building me-1"></i>COURT VENUE</small>
                            <h6 class="fw-bold text-dark mb-1">${escapeHtml(courtName)}</h6>
                            <div class="text-muted fs-8">${escapeHtml(courtAddress)}${courtAddress && courtCity ? ', ' : ''}${escapeHtml(courtCity)}</div>
                            <div class="text-muted fs-8"><i class="bi bi-door-open me-1"></i>${escapeHtml(booking.court?.court_type || 'Indoor')} Court</div>
                        </div>
                    </div>

                    <!-- SCHEDULE TIMING -->
                    <div class="col-md-6">
                        <small class="text-muted d-block fs-8">SCHEDULED DATE</small>
                        <span class="text-dark fw-bold fs-6">${escapeHtml(bookingDate)}</span>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted d-block fs-8">RESERVED TIME SLOT</small>
                        <span class="text-dark fw-bold fs-6 font-monospace">${escapeHtml(slotTime)}</span>
                    </div>

                    <!-- FINANCIAL SUMMARY BREAKDOWN -->
                    <div class="col-12">
                        <div class="card border-0 bg-dark text-white p-4 rounded-4 shadow-sm">
                            <small class="text-white-50 d-block fs-8 fw-bold text-uppercase mb-3">FINANCIAL STATEMENT</small>

                            <div class="d-flex justify-content-between mb-2 fs-7 text-white-50">
                                <span>Court Booking Fee:</span>
                                <span class="text-white fw-semibold">₹${(totalPrice > 50 ? totalPrice - 50 : totalPrice).toLocaleString("en-IN")}</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2 fs-7 text-white-50">
                                <span>Platform Booking Fee:</span>
                                <span class="text-white fw-semibold">₹50</span>
                            </div>

                            <hr class="border-secondary my-2">

                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="fs-6 text-white">TOTAL PAID BY PLAYER:</strong>
                                <h3 class="fw-extrabold text-success mb-0">₹${totalPrice.toLocaleString("en-IN")}</h3>
                            </div>
                        </div>
                    </div>

                </div>
            `;
        }
    } catch (error) {
        console.error("Inspect Booking Error:", error);
        modalBody.innerHTML = `<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to fetch booking details.</div>`;
    }
}

function renderPagination(meta) {
    const container = document.getElementById("paginationContainer");
    if (!container) return;

    const current = meta.current_page || 1;
    const last = meta.last_page || 1;

    if (last <= 1) {
        container.innerHTML = `<small class="text-muted">Showing all ${meta.total || 0} reservations</small>`;
        return;
    }

    container.innerHTML = `
        <small class="text-muted">Page ${current} of ${last} (${meta.total} total reservations)</small>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary ${current <= 1 ? 'disabled' : ''}" id="prevPageBtn">Previous</button>
            <button class="btn btn-sm btn-outline-secondary ${current >= last ? 'disabled' : ''}" id="nextPageBtn">Next</button>
        </div>
    `;

    document.getElementById("prevPageBtn")?.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadBookings();
        }
    });

    document.getElementById("nextPageBtn")?.addEventListener("click", () => {
        if (currentPage < last) {
            currentPage++;
            loadBookings();
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
