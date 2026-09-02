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

    const commissionEl = document.getElementById("kpiCommissionRevenue");
    const pendingEl = document.getElementById("kpiPendingApplications");
    const courtsEl = document.getElementById("kpiActiveCourts");
    const bookingsEl = document.getElementById("kpiTotalBookings");
    const navBadge = document.getElementById("navPendingBadge");

    if (commissionEl) commissionEl.textContent = `₹${formatPrice(rev.commission_revenue || 0)}`;
    if (pendingEl) pendingEl.textContent = app.pending_count || 0;
    if (courtsEl) courtsEl.textContent = court.active_courts || 0;
    if (bookingsEl) bookingsEl.textContent = book.total_bookings || 0;

    if (navBadge) {
        if (app.pending_count > 0) {
            navBadge.textContent = app.pending_count;
            navBadge.classList.remove("d-none");
        } else {
            navBadge.classList.add("d-none");
        }
    }
}

function renderRecentApplications(applications) {
    const tbody = document.getElementById("recentApplicationsTbody");
    if (!tbody) return;

    if (!Array.isArray(applications) || applications.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No owner applications recorded yet.</td></tr>`;
        return;
    }

    tbody.innerHTML = applications.map(app => {
        const userName = app.user?.name || "Applicant";
        const email = app.user?.email || "";
        const status = (app.status || "").toLowerCase();

        return `
            <tr>
                <td class="ps-4">
                    <strong class="text-dark d-block">${escapeHtml(userName)}</strong>
                    <small class="text-muted fs-8">${escapeHtml(email)}</small>
                </td>
                <td>
                    <span class="fw-semibold text-dark">${escapeHtml(app.business_name || 'Venue Partner')}</span>
                </td>
                <td>
                    <small class="text-muted">${escapeHtml(app.city || '')}, ${escapeHtml(app.state || '')}</small>
                </td>
                <td>
                    <small class="text-dark">${escapeHtml(app.phone || '-')}</small>
                </td>
                <td>
                    <small class="text-muted">${formatDateReadable(app.created_at)}</small>
                </td>
                <td class="text-end pe-4">
                    ${renderStatusBadge(status)}
                </td>
            </tr>
        `;
    }).join("");
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

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}
