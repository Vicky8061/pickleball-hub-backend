import { apiFetch } from "../api.js";

document.addEventListener("DOMContentLoaded", () => {
    loadEarningsReport();
});

async function loadEarningsReport() {
    try {
        const response = await apiFetch("/owner/earnings");
        if (response && response.success) {
            const data = response.data || {};
            renderSummaryCards(data.summary || {});
            renderMonthlyChart(data.monthly_breakdown || []);
            renderCourtBreakdown(data.court_breakdown || []);
            renderLedgerTable(data.recent_transactions || []);
        }
    } catch (error) {
        console.error("Load Earnings Error:", error);
    }
}

function renderSummaryCards(summary) {
    const netPayoutEl = document.getElementById("earnNetPayout");
    const grossVolumeEl = document.getElementById("earnGrossVolume");
    const completedPayoutEl = document.getElementById("earnCompletedPayout");
    const pendingSettlementsEl = document.getElementById("earnPendingSettlements");

    if (netPayoutEl) netPayoutEl.textContent = `₹${formatPrice(summary.net_payout)}`;
    if (grossVolumeEl) grossVolumeEl.textContent = `₹${formatPrice(summary.gross_volume)}`;
    if (completedPayoutEl) completedPayoutEl.textContent = `₹${formatPrice(summary.completed_payout)}`;
    if (pendingSettlementsEl) pendingSettlementsEl.textContent = `₹${formatPrice(summary.pending_settlements)}`;
}

function renderMonthlyChart(monthlyData) {
    const container = document.getElementById("monthlyChartContainer");
    if (!container) return;

    if (!Array.isArray(monthlyData) || monthlyData.length === 0) {
        container.innerHTML = `<div class="text-center py-4 text-muted">No monthly earnings history recorded yet.</div>`;
        return;
    }

    const maxEarnings = Math.max(...monthlyData.map(m => Number(m.net_payout || 0)), 1000);

    const barsHtml = monthlyData.map(item => {
        const payout = Number(item.net_payout || 0);
        const percent = Math.max(10, Math.min(100, Math.round((payout / maxEarnings) * 100)));

        return `
            <div class="col text-center d-flex flex-column justify-content-end h-100 px-2">
                <small class="fw-bold text-success fs-8 mb-1">₹${formatPrice(payout)}</small>
                <div class="bg-success bg-opacity-75 rounded-top w-100 transition-all shadow-sm" style="height: ${percent}%; min-height: 12px;" title="${item.month}: ₹${formatPrice(payout)} (${item.booking_count} bookings)"></div>
                <small class="text-muted fw-semibold fs-8 mt-2 d-block text-truncate">${escapeHtml(item.month)}</small>
            </div>
        `;
    }).join("");

    container.innerHTML = `
        <div class="row align-items-end h-100 g-0 pt-3" style="height: 180px;">
            ${barsHtml}
        </div>
    `;
}

function renderCourtBreakdown(courtData) {
    const tbody = document.getElementById("courtRevenueTbody");
    if (!tbody) return;

    if (!Array.isArray(courtData) || courtData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" class="text-center py-4 text-muted">No court revenue recorded yet.</td></tr>`;
        return;
    }

    tbody.innerHTML = courtData.map(c => `
        <tr>
            <td>
                <strong class="text-dark d-block">${escapeHtml(c.court_name)}</strong>
                <small class="text-muted fs-8">${escapeHtml(c.court_type || 'Court')}</small>
            </td>
            <td class="text-center">
                <span class="badge bg-light text-dark border rounded-pill">${c.total_bookings}</span>
            </td>
            <td class="text-end">
                <strong class="text-success">₹${formatPrice(c.net_payout)}</strong>
            </td>
        </tr>
    `).join("");
}

function renderLedgerTable(transactions) {
    const tbody = document.getElementById("earningsLedgerTbody");
    if (!tbody) return;

    if (!Array.isArray(transactions) || transactions.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">No transactions found.</td></tr>`;
        return;
    }

    tbody.innerHTML = transactions.map(b => {
        const status = (b.booking_status || "").toLowerCase();
        const courtFee = Number(b.court_price || 0);
        const commission = Number(b.admin_commission_amount || (courtFee * 0.10));
        const netPayout = Number(b.owner_payout_amount || (courtFee * 0.90));

        return `
            <tr>
                <td class="ps-4">
                    <strong class="text-dark font-monospace">#${b.id}</strong>
                </td>
                <td>
                    <span class="fw-semibold text-dark">${escapeHtml(b.user?.name || 'Player')}</span>
                </td>
                <td>
                    <span class="text-dark">${escapeHtml(b.court?.name || 'Court')}</span>
                </td>
                <td>
                    <small class="text-muted">${formatDateReadable(b.booking_date)}</small>
                </td>
                <td>
                    <span class="text-dark">₹${formatPrice(courtFee)}</span>
                </td>
                <td>
                    <span class="text-danger small">-₹${formatPrice(commission)}</span>
                </td>
                <td>
                    <strong class="text-success">₹${formatPrice(netPayout)}</strong>
                </td>
                <td class="text-end pe-4">
                    ${renderSettlementBadge(status)}
                </td>
            </tr>
        `;
    }).join("");
}

function renderSettlementBadge(status) {
    switch (status) {
        case "completed":
            return `<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1"><i class="bi bi-check-circle me-1"></i> Settled</span>`;
        case "confirmed":
            return `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1"><i class="bi bi-clock me-1"></i> Confirmed</span>`;
        case "cancelled":
            return `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1"><i class="bi bi-x-circle me-1"></i> Cancelled</span>`;
        default:
            return `<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1"><i class="bi bi-hourglass-split me-1"></i> Pending</span>`;
    }
}

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

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}
