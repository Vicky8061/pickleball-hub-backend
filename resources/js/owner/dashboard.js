/* =========================================
   OWNER DASHBOARD LOGIC
========================================= */

const API_BASE_URL = "/api";

document.addEventListener("DOMContentLoaded", () => {
    loadUserInformation();
    loadDashboardData();
    loadRecentBookings();
});

/* -----------------------------------------
   LOAD USER
----------------------------------------- */
function loadUserInformation() {
    const userStr = localStorage.getItem("auth_user");
    if (!userStr) return;

    try {
        const user = JSON.parse(userStr);
        if (user && user.role === "user") {
            window.location.replace("/user/dashboard");
            return;
        }
        const nameEl = document.getElementById("welcomeOwnerName");
        if (nameEl && user.name) {
            nameEl.textContent = `Welcome back, ${user.name} 👋`;
        }
    } catch (e) {
        console.error("User parse error", e);
    }
}

/* -----------------------------------------
   LOAD DASHBOARD STATS
----------------------------------------- */
async function loadDashboardData() {
    try {
        const response = await apiFetch("/owner/dashboard");

        if (response && response.success && response.data) {
            const data = response.data;

            // KPI 1: Net Revenue
            const revenueEl = document.getElementById("kpiRevenue");
            if (revenueEl) {
                const revenue = Number(data.total_revenue || 0);
                revenueEl.textContent = `₹${formatPrice(revenue)}`;
            }

            // KPI 2: Today Bookings
            const todayBookingsEl = document.getElementById("kpiTodayBookings");
            if (todayBookingsEl) {
                todayBookingsEl.textContent = data.today_bookings ?? 0;
            }

            // KPI 3: Active Courts
            const activeCourtsEl = document.getElementById("kpiActiveCourts");
            const courtsSubtextEl = document.getElementById("kpiCourtsSubtext");
            if (activeCourtsEl) {
                activeCourtsEl.textContent = data.active_courts ?? 0;
            }
            if (courtsSubtextEl) {
                courtsSubtextEl.textContent = `${data.total_courts ?? 0} total courts registered`;
            }

            // KPI 4: Average Rating
            const ratingEl = document.getElementById("kpiAverageRating");
            const reviewsSubtextEl = document.getElementById("kpiReviewsSubtext");
            if (ratingEl) {
                const rating = Number(data.average_rating || 0).toFixed(1);
                ratingEl.innerHTML = `${rating} <i class="bi bi-star-fill text-warning fs-6"></i>`;
            }
            if (reviewsSubtextEl) {
                reviewsSubtextEl.textContent = `${data.total_reviews ?? 0} total reviews`;
            }
        }
    } catch (error) {
        console.error("Owner Dashboard Error:", error);
    }
}

/* -----------------------------------------
   LOAD RECENT BOOKINGS
----------------------------------------- */
async function loadRecentBookings() {
    const tbody = document.getElementById("ownerRecentBookingsBody");
    if (!tbody) return;

    try {
        const response = await apiFetch("/owner/bookings");

        const bookings = response?.data?.data || response?.data || response || [];

        if (!Array.isArray(bookings) || bookings.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x fs-2 d-block mb-2 text-secondary"></i>
                        No court bookings recorded yet.
                    </td>
                </tr>
            `;
            return;
        }

        const recent = bookings.slice(0, 5);

        tbody.innerHTML = recent.map(booking => {
            const court = booking.court || {};
            const user = booking.user || {};
            const timeSlot = booking.time_slot || {};

            const courtPrice = Number(booking.court_price || (booking.total_amount ? booking.total_amount - (booking.platform_fee || 50) : 0));
            const ownerPayout = Number(booking.owner_payout_amount || (courtPrice * 0.90));

            return `
                <tr>
                    <td><strong class="text-dark">#${booking.id}</strong></td>
                    <td>
                        <div class="fw-bold text-dark">${escapeHtml(user.name || "Player")}</div>
                        <small class="text-muted fs-8">${escapeHtml(user.email || "")}</small>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">${escapeHtml(court.name || court.court_name || "Court")}</div>
                        <small class="text-muted fs-8">${escapeHtml(court.court_type || "Court")}</small>
                    </td>
                    <td>
                        <div>${formatDate(booking.booking_date)}</div>
                        <small class="text-muted fs-8">${escapeHtml(timeSlot.start_time || "--")} - ${escapeHtml(timeSlot.end_time || "--")}</small>
                    </td>
                    <td>₹${formatPrice(courtPrice)}</td>
                    <td class="fw-bold text-success">₹${formatPrice(ownerPayout)}</td>
                    <td>${getStatusBadge(booking.booking_status)}</td>
                </tr>
            `;
        }).join("");

    } catch (error) {
        console.error("Owner Bookings Error:", error);
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    Unable to load recent bookings.
                </td>
            </tr>
        `;
    }
}

/* -----------------------------------------
   HELPERS
----------------------------------------- */
function formatPrice(value) {
    const num = Number(value);
    if (isNaN(num)) return "0";
    return num.toLocaleString("en-IN");
}

function formatDate(dateString) {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-IN", {
        day: "numeric",
        month: "short",
        year: "numeric"
    });
}

function getStatusBadge(status) {
    const s = (status || "").toLowerCase();
    if (s === "confirmed") {
        return '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">Confirmed</span>';
    }
    if (s === "pending") {
        return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fw-bold">Pending</span>';
    }
    if (s === "completed") {
        return '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 fw-bold">Completed</span>';
    }
    if (s === "cancelled") {
        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold">Cancelled</span>';
    }
    return `<span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">${escapeHtml(status || "Unknown")}</span>`;
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}
