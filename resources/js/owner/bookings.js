import { apiFetch } from "../api.js";

let allCourts = [];
let allBookings = [];
let activeStatusFilter = "";

document.addEventListener("DOMContentLoaded", () => {
    initBookingsPage();
});

async function initBookingsPage() {
    await loadOwnerCourts();
    await loadBookings();
    setupFilterListeners();
}

/**
 * Load Owner Courts for Dropdown Filter
 */
async function loadOwnerCourts() {
    try {
        const response = await apiFetch("/owner/courts");
        if (response && response.success) {
            allCourts = response.data || [];
            const select = document.getElementById("filterCourtSelect");
            if (select) {
                const options = allCourts.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join("");
                select.innerHTML = `<option value="">All Court Venues (${allCourts.length})</option>` + options;
            }
        }
    } catch (error) {
        console.error("Load Courts Error:", error);
    }
}

/**
 * Load Owner Bookings
 */
async function loadBookings() {
    const tbody = document.getElementById("ownerBookingsTbody");
    if (!tbody) return;

    try {
        const response = await apiFetch("/owner/bookings");
        if (response && response.success) {
            allBookings = response.data || [];
            applyFiltersAndRender();
        }
    } catch (error) {
        console.error("Load Bookings Error:", error);
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5 text-danger">
                    <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                    Failed to load bookings. Please try refreshing.
                </td>
            </tr>
        `;
    }
}

function setupFilterListeners() {
    const searchInput = document.getElementById("searchBookingInput");
    const courtSelect = document.getElementById("filterCourtSelect");
    const dateInput = document.getElementById("filterBookingDate");
    const resetBtn = document.getElementById("resetBookingFiltersBtn");
    const statusTabs = document.querySelectorAll("#bookingStatusTabs button");

    if (searchInput) searchInput.addEventListener("input", applyFiltersAndRender);
    if (courtSelect) courtSelect.addEventListener("change", applyFiltersAndRender);
    if (dateInput) dateInput.addEventListener("change", applyFiltersAndRender);

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
            if (dateInput) dateInput.value = "";
            activeStatusFilter = "";
            statusTabs.forEach(t => t.classList.remove("active"));
            statusTabs[0]?.classList.add("active");
            applyFiltersAndRender();
        });
    }
}

function applyFiltersAndRender() {
    const searchVal = (document.getElementById("searchBookingInput")?.value || "").toLowerCase().trim();
    const courtId = document.getElementById("filterCourtSelect")?.value;
    const dateVal = document.getElementById("filterBookingDate")?.value;

    let filtered = [...allBookings];

    if (searchVal) {
        filtered = filtered.filter(b => {
            const userName = (b.user?.name || "").toLowerCase();
            const userEmail = (b.user?.email || "").toLowerCase();
            const bookingIdStr = String(b.id);
            const courtName = (b.court?.name || "").toLowerCase();
            return userName.includes(searchVal) || userEmail.includes(searchVal) || bookingIdStr.includes(searchVal) || courtName.includes(searchVal);
        });
    }

    if (courtId) {
        filtered = filtered.filter(b => String(b.court_id) === String(courtId));
    }

    if (dateVal) {
        filtered = filtered.filter(b => b.booking_date === dateVal);
    }

    if (activeStatusFilter) {
        filtered = filtered.filter(b => (b.booking_status || "").toLowerCase() === activeStatusFilter.toLowerCase());
    }

    updateTabCountsAndKPIs(allBookings, filtered);
    renderBookingsTable(filtered);
}

function updateTabCountsAndKPIs(all, filtered) {
    const countAll = document.getElementById("countAll");
    const countPending = document.getElementById("countPending");
    const countConfirmed = document.getElementById("countConfirmed");
    const countCompleted = document.getElementById("countCompleted");
    const countCancelled = document.getElementById("countCancelled");

    const kpiNetEarnings = document.getElementById("kpiNetEarnings");
    const kpiConfirmedBookings = document.getElementById("kpiConfirmedBookings");
    const kpiPendingBookings = document.getElementById("kpiPendingBookings");

    if (countAll) countAll.textContent = all.length;
    if (countPending) countPending.textContent = all.filter(b => (b.booking_status || "").toLowerCase() === "pending").length;
    if (countConfirmed) countConfirmed.textContent = all.filter(b => (b.booking_status || "").toLowerCase() === "confirmed").length;
    if (countCompleted) countCompleted.textContent = all.filter(b => (b.booking_status || "").toLowerCase() === "completed").length;
    if (countCancelled) countCancelled.textContent = all.filter(b => (b.booking_status || "").toLowerCase() === "cancelled").length;

    const totalPayout = all
        .filter(b => ["confirmed", "completed"].includes((b.booking_status || "").toLowerCase()))
        .reduce((sum, b) => sum + Number(b.owner_payout_amount || 0), 0);

    const confirmedCount = all.filter(b => (b.booking_status || "").toLowerCase() === "confirmed").length;
    const pendingCount = all.filter(b => (b.booking_status || "").toLowerCase() === "pending").length;

    if (kpiNetEarnings) kpiNetEarnings.textContent = `₹${formatPrice(totalPayout)}`;
    if (kpiConfirmedBookings) kpiConfirmedBookings.textContent = confirmedCount;
    if (kpiPendingBookings) kpiPendingBookings.textContent = pendingCount;
}

function renderBookingsTable(bookings) {
    const tbody = document.getElementById("ownerBookingsTbody");
    if (!tbody) return;

    if (!Array.isArray(bookings) || bookings.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                    No bookings found matching your search or filter criteria.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = bookings.map(b => {
        const status = (b.booking_status || "").toLowerCase();
        const user = b.user || {};
        const court = b.court || {};
        const timeSlot = b.timeSlot || {};
        const startTimeStr = formatTime12H(timeSlot.start_time);
        const endTimeStr = formatTime12H(timeSlot.end_time);

        return `
            <tr>
                <td class="ps-4">
                    <strong class="text-dark font-monospace">#${b.id}</strong>
                    <small class="text-muted d-block fs-8">${formatDateShort(b.created_at)}</small>
                </td>
                <td>
                    <div class="fw-bold text-dark">${escapeHtml(user.name || "Customer")}</div>
                    <small class="text-muted d-block fs-8">${escapeHtml(user.email || "")}</small>
                </td>
                <td>
                    <span class="fw-semibold text-dark">${escapeHtml(court.name || "Court")}</span>
                    <small class="text-muted d-block fs-8"><i class="bi bi-house-door me-1"></i>${escapeHtml(court.court_type || "Court")}</small>
                </td>
                <td>
                    <div class="fw-semibold text-dark">${formatDateReadable(b.booking_date)}</div>
                    <small class="text-success fw-bold fs-8"><i class="bi bi-clock me-1"></i>${startTimeStr} - ${endTimeStr}</small>
                </td>
                <td>
                    <strong class="text-success fs-6">₹${formatPrice(b.owner_payout_amount)}</strong>
                    <small class="text-muted d-block fs-8">Paid ₹${formatPrice(b.total_amount)}</small>
                </td>
                <td>
                    ${renderStatusBadge(status)}
                </td>
                <td class="text-end pe-4">
                    <div class="d-flex align-items-center justify-content-end gap-1">
                        <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm" onclick="openBookingDetailsModal(${b.id})" title="View Full Details">
                            <i class="bi bi-eye"></i>
                        </button>
                        
                        ${status === 'pending' ? `
                            <button type="button" class="btn btn-success btn-sm rounded-pill px-2 fs-8 fw-bold" onclick="updateBookingStatus(${b.id}, 'confirmed')">
                                Confirm
                            </button>
                        ` : ''}

                        ${status === 'confirmed' ? `
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-2 fs-8 fw-bold" onclick="updateBookingStatus(${b.id}, 'completed')">
                                Complete
                            </button>
                        ` : ''}

                        ${['pending', 'confirmed'].includes(status) ? `
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle px-2" onclick="cancelBookingByOwner(${b.id})" title="Cancel Booking">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }).join("");
}

function renderStatusBadge(status) {
    switch (status) {
        case "confirmed":
            return `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1"><i class="bi bi-check-circle me-1"></i> Confirmed</span>`;
        case "completed":
            return `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1"><i class="bi bi-check2-all me-1"></i> Completed</span>`;
        case "cancelled":
            return `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1"><i class="bi bi-x-circle me-1"></i> Cancelled</span>`;
        default:
            return `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1"><i class="bi bi-clock-history me-1"></i> Pending</span>`;
    }
}

/**
 * Update Booking Status (Confirmed / Completed)
 */
window.updateBookingStatus = async function (bookingId, newStatus) {
    if (!confirm(`Are you sure you want to mark this booking as ${newStatus}?`)) return;

    try {
        const response = await apiFetch(`/owner/bookings/${bookingId}`, {
            method: "PUT",
            body: JSON.stringify({ booking_status: newStatus })
        });

        if (response && response.success) {
            await loadBookings();
        }
    } catch (error) {
        console.error("Update Booking Status Error:", error);
        alert(error.message || "Failed to update booking status.");
    }
};

/**
 * Cancel Booking By Owner
 */
window.cancelBookingByOwner = async function (bookingId) {
    if (!confirm("Are you sure you want to cancel this reservation?")) return;

    try {
        const response = await apiFetch(`/owner/bookings/${bookingId}/cancel`, {
            method: "POST"
        });

        if (response && response.success) {
            await loadBookings();
        }
    } catch (error) {
        console.error("Cancel Booking Error:", error);
        alert(error.message || "Failed to cancel booking.");
    }
};

/**
 * Open Booking Details Modal
 */
window.openBookingDetailsModal = function (bookingId) {
    const booking = allBookings.find(b => b.id === bookingId);
    if (!booking) return;

    document.getElementById("modalBookingId").textContent = `#${booking.id}`;
    document.getElementById("modalPlayerName").textContent = booking.user?.name || "-";
    document.getElementById("modalPlayerEmail").textContent = booking.user?.email || "-";
    document.getElementById("modalCourtName").textContent = booking.court?.name || "-";
    document.getElementById("modalCourtAddress").textContent = booking.court?.address || "-";
    document.getElementById("modalBookingDate").textContent = formatDateReadable(booking.booking_date);
    document.getElementById("modalTimeSlot").textContent = `${formatTime12H(booking.timeSlot?.start_time)} - ${formatTime12H(booking.timeSlot?.end_time)}`;

    document.getElementById("modalCourtPrice").textContent = `₹${formatPrice(booking.court_price)}`;
    document.getElementById("modalPlatformFee").textContent = `₹${formatPrice(booking.platform_fee)}`;
    document.getElementById("modalAdminCommission").textContent = `-₹${formatPrice(booking.admin_commission_amount)}`;
    document.getElementById("modalOwnerPayout").textContent = `₹${formatPrice(booking.owner_payout_amount)}`;

    const statusBadge = document.getElementById("modalStatusBadge");
    if (statusBadge) {
        statusBadge.outerHTML = renderStatusBadge((booking.booking_status || "").toLowerCase());
    }

    const modal = new bootstrap.Modal(document.getElementById("bookingDetailsModal"));
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

function formatDateShort(dateTimeStr) {
    if (!dateTimeStr) return "";
    const d = new Date(dateTimeStr);
    return d.toLocaleDateString("en-IN", { day: "numeric", month: "short" });
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
