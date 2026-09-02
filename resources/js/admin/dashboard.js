import { apiFetch } from "../api.js";

document.addEventListener("DOMContentLoaded", () => {
    loadAdminDashboard();
});

async function loadAdminDashboard() {
    try {
        const response = await apiFetch("/admin/dashboard");
        if (response && response.success) {
            const data = response.data || {};
            renderKPIs(data);
            renderRecentApplications(data.recent_owner_applications || []);
            renderRecentBookings(data.recent_bookings || []);
        }
    } catch (error) {
        console.error("Load Admin Dashboard Error:", error);
    }
}

function renderKPIs(data) {
    const rev = data.revenue || {};
    const app = data.owner_applications || {};
    const court = data.courts || {};
    const book = data.bookings || {};
    const users = data.users || {};
    const reviews = data.reviews || {};
    const tournaments = data.tournaments || {};

    // 4 Main KPI Cards
    const commissionEl = document.getElementById("kpiCommissionRevenue");
    const grossEl = document.getElementById("kpiGrossRevenue");
    const pendingEl = document.getElementById("kpiPendingApplications");
    const courtsEl = document.getElementById("kpiActiveCourts");
    const totalCourtsEl = document.getElementById("kpiTotalCourts");
    const bookingsEl = document.getElementById("kpiTotalBookings");
    const completedBookingsEl = document.getElementById("kpiCompletedBookings");

    if (commissionEl) commissionEl.textContent = `₹${formatPrice(rev.commission_revenue || 0)}`;
    if (grossEl) grossEl.innerHTML = `<i class="bi bi-graph-up-arrow text-success me-1"></i> Gross Vol: ₹${formatPrice(rev.gross_revenue || 0)}`;

    if (pendingEl) pendingEl.textContent = app.pending_count || 0;

    if (courtsEl) courtsEl.textContent = court.active_courts || 0;
    if (totalCourtsEl) totalCourtsEl.innerHTML = `<i class="bi bi-building text-primary me-1"></i> ${court.total_courts || 0} total court venues`;

    if (bookingsEl) bookingsEl.textContent = book.total_bookings || 0;
    if (completedBookingsEl) completedBookingsEl.innerHTML = `<i class="bi bi-check-circle text-info me-1"></i> ${book.completed_bookings || 0} completed matches`;

    // Navigation Badges
    const navBadge = document.getElementById("navPendingBadge");
    const mobileNavBadge = document.getElementById("mobileNavPendingBadge");

    if (navBadge) {
        if (app.pending_count > 0) {
            navBadge.textContent = app.pending_count;
            navBadge.classList.remove("d-none");
        } else {
            navBadge.classList.add("d-none");
        }
    }

    if (mobileNavBadge) {
        if (app.pending_count > 0) {
            mobileNavBadge.textContent = app.pending_count;
            mobileNavBadge.classList.remove("d-none");
        } else {
            mobileNavBadge.classList.add("d-none");
        }
    }

    // Account Directory Breakdown
    const playersEl = document.getElementById("statTotalPlayers");
    const ownersEl = document.getElementById("statTotalOwners");
    const adminsEl = document.getElementById("statTotalAdmins");

    if (playersEl) playersEl.textContent = (users.total_users || 0).toLocaleString("en-IN");
    if (ownersEl) ownersEl.textContent = (users.total_owners || 0).toLocaleString("en-IN");
    if (adminsEl) adminsEl.textContent = (users.total_admins || 0).toLocaleString("en-IN");

    // Reviews Breakdown
    const avgRatingEl = document.getElementById("statAverageRating");
    const totalReviewsEl = document.getElementById("statTotalReviews");

    if (avgRatingEl) avgRatingEl.innerHTML = `★ ${reviews.average_rating || '0.0'} <small class="text-muted fs-8">/ 5</small>`;
    if (totalReviewsEl) totalReviewsEl.textContent = (reviews.total_reviews || 0).toLocaleString("en-IN");

    // Tournaments Breakdown
    const activeTournamentsEl = document.getElementById("statActiveTournaments");
    const totalTournamentsEl = document.getElementById("statTotalTournaments");

    if (activeTournamentsEl) activeTournamentsEl.textContent = (tournaments.active_tournaments || 0).toLocaleString("en-IN");
    if (totalTournamentsEl) totalTournamentsEl.textContent = (tournaments.total_tournaments || 0).toLocaleString("en-IN");
}

function renderRecentApplications(applications) {
    const tbody = document.getElementById("recentApplicationsTbody");
    if (!tbody) return;

    if (!Array.isArray(applications) || applications.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted">No pending owner applications found.</td></tr>`;
        return;
    }

    tbody.innerHTML = applications.map(app => {
        const applicantName = app.user?.name || app.full_name || "Applicant";
        const businessName = app.business_name || "N/A";
        const city = app.city || app.address || "N/A";
        const status = (app.status || "pending").toLowerCase();

        return `
            <tr>
                <td class="ps-4">
                    <strong class="text-dark d-block small">${escapeHtml(applicantName)}</strong>
                    <small class="text-muted fs-8">${escapeHtml(app.user?.email || '')}</small>
                </td>
                <td>
                    <span class="text-dark small fw-semibold">${escapeHtml(businessName)}</span>
                </td>
                <td>
                    <small class="text-muted"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(city)}</small>
                </td>
                <td class="text-end pe-4">
                    ${renderStatusBadge(status)}
                </td>
            </tr>
        `;
    }).join("");
}

function renderRecentBookings(bookings) {
    const tbody = document.getElementById("recentBookingsTbody");
    if (!tbody) return;

    const bookingList = Array.isArray(bookings.data) ? bookings.data : (Array.isArray(bookings) ? bookings : []);

    if (bookingList.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted">No recent court bookings found.</td></tr>`;
        return;
    }

    tbody.innerHTML = bookingList.map(booking => {
        const playerName = booking.user?.name || "Player";
        const courtName = booking.court?.name || "Venue Court";
        const amount = Number(booking.total_amount || booking.total_price || 0);
        const status = (booking.booking_status || "confirmed").toLowerCase();

        return `
            <tr>
                <td class="ps-4">
                    <strong class="text-dark d-block small">${escapeHtml(playerName)}</strong>
                    <small class="text-muted fs-8">${formatDateReadable(booking.created_at)}</small>
                </td>
                <td>
                    <span class="text-dark small fw-semibold">${escapeHtml(courtName)}</span>
                </td>
                <td>
                    <strong class="text-success small">₹${amount.toLocaleString("en-IN")}</strong>
                </td>
                <td class="text-end pe-4">
                    ${renderBookingStatusBadge(status)}
                </td>
            </tr>
        `;
    }).join("");
}

function renderStatusBadge(status) {
    switch (status) {
        case "pending":
            return `<span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fs-8">Pending</span>`;
        case "approved":
            return `<span class="badge bg-success rounded-pill px-2.5 py-1 fs-8">Approved</span>`;
        case "rejected":
            return `<span class="badge bg-danger rounded-pill px-2.5 py-1 fs-8">Rejected</span>`;
        default:
            return `<span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fs-8">${escapeHtml(status)}</span>`;
    }
}

function renderBookingStatusBadge(status) {
    switch (status) {
        case "confirmed":
            return `<span class="badge bg-success rounded-pill px-2.5 py-1 fs-8">Confirmed</span>`;
        case "completed":
            return `<span class="badge bg-primary rounded-pill px-2.5 py-1 fs-8">Completed</span>`;
        case "pending":
            return `<span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fs-8">Pending</span>`;
        case "cancelled":
            return `<span class="badge bg-danger rounded-pill px-2.5 py-1 fs-8">Cancelled</span>`;
        default:
            return `<span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fs-8">${escapeHtml(status)}</span>`;
    }
}

function formatPrice(val) {
    const num = Number(val || 0);
    return num.toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatDateReadable(dateStr) {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    return d.toLocaleDateString("en-IN", { day: "numeric", month: "short" });
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}
