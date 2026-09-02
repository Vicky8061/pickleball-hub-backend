import { apiFetch } from "../api.js";

let currentStatus = "";
let currentSearch = "";
let currentPayoutsList = [];

document.addEventListener("DOMContentLoaded", () => {
    initFilters();
    loadPayouts();
    setupSettleForm();
});

function initFilters() {
    // Status Pills Nav
    const pillsNav = document.getElementById("payoutStatusPillsNav");
    if (pillsNav) {
        pillsNav.querySelectorAll("button").forEach(btn => {
            btn.addEventListener("click", () => {
                pillsNav.querySelectorAll("button").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                currentStatus = btn.getAttribute("data-status") || "";
                loadPayouts();
            });
        });
    }

    // Search Input Debounce
    const searchInput = document.getElementById("payoutSearchInput");
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener("input", (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentSearch = e.target.value.trim();
                loadPayouts();
            }, 300);
        });
    }
}

async function loadPayouts() {
    const tbody = document.getElementById("payoutsTbody");
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading payout ledger...</td></tr>`;

    try {
        const queryParams = new URLSearchParams();
        if (currentStatus) queryParams.append("status", currentStatus);
        if (currentSearch) queryParams.append("search", currentSearch);

        const response = await apiFetch(`/admin/payouts?${queryParams.toString()}`);

        if (response && response.success) {
            const summary = response.summary || {};
            const payouts = response.data || [];

            currentPayoutsList = payouts;
            updateStatSummaryCards(summary);
            renderPayoutsTable(tbody, payouts);
        }
    } catch (error) {
        console.error("Load Admin Payouts Error:", error);
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load payout ledger. ${escapeHtml(error.message || '')}</td></tr>`;
    }
}

function updateStatSummaryCards(summary) {
    const grossEl = document.getElementById("statGrossVolume");
    const commissionEl = document.getElementById("statRetainedCommission");
    const settledEl = document.getElementById("statSettledPayouts");
    const pendingEl = document.getElementById("statPendingPayouts");

    if (grossEl) grossEl.textContent = `₹${Number(summary.total_gross_volume || 0).toLocaleString("en-IN")}`;
    if (commissionEl) commissionEl.textContent = `₹${Number(summary.total_retained_commission || 0).toLocaleString("en-IN")}`;
    if (settledEl) settledEl.textContent = `₹${Number(summary.total_settled_payouts || 0).toLocaleString("en-IN")}`;
    if (pendingEl) pendingEl.textContent = `₹${Number(summary.total_pending_payouts || 0).toLocaleString("en-IN")}`;
}

function renderPayoutsTable(tbody, payouts) {
    if (!Array.isArray(payouts) || payouts.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">No owner payout records found matching criteria.</td></tr>`;
        return;
    }

    tbody.innerHTML = payouts.map(owner => {
        const name = owner.name || "Court Owner";
        const email = owner.email || "";
        const gross = Number(owner.gross_revenue || 0);
        const commission = Number(owner.platform_commission || 0);
        const netPayout = Number(owner.net_owner_payout || 0);
        const status = (owner.payout_status || "pending").toLowerCase();

        return `
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                            ${escapeHtml(name.charAt(0).toUpperCase())}
                        </div>
                        <div>
                            <strong class="text-dark d-block">${escapeHtml(name)}</strong>
                            <small class="text-muted fs-8">${escapeHtml(email)}</small>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1">${owner.total_venues || 0} Courts</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1">${owner.total_bookings || 0} Bookings</span>
                </td>
                <td>
                    <strong class="text-dark">₹${gross.toLocaleString("en-IN")}</strong>
                </td>
                <td>
                    <span class="text-success fw-bold">₹${commission.toLocaleString("en-IN")}</span>
                </td>
                <td>
                    <strong class="text-primary fs-6">₹${netPayout.toLocaleString("en-IN")}</strong>
                </td>
                <td>
                    ${renderStatusBadge(status, owner.tx_reference)}
                </td>
                <td class="text-end pe-4">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-light border text-dark rounded-circle btn-inspect-payout" data-id="${owner.id}" title="Inspect Financials">
                            <i class="bi bi-eye"></i>
                        </button>

                        <button type="button" class="btn btn-sm ${status === 'settled' ? 'btn-outline-success' : 'btn-success'} rounded-circle btn-settle-payout" data-id="${owner.id}" data-status="${status}" data-name="${escapeHtml(name)}" data-tx="${escapeHtml(owner.tx_reference || '')}" data-method="${escapeHtml(owner.payment_method || '')}" data-notes="${escapeHtml(owner.notes || '')}" title="Settle Payout">
                            <i class="bi bi-check-circle"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join("");

    attachTableEventListeners();
}

function attachTableEventListeners() {
    // Inspect Payout
    document.querySelectorAll(".btn-inspect-payout").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            inspectPayout(id);
        });
    });

    // Settle Payout Modal
    document.querySelectorAll(".btn-settle-payout").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const status = btn.getAttribute("data-status");
            const name = btn.getAttribute("data-name");
            const tx = btn.getAttribute("data-tx");
            const method = btn.getAttribute("data-method");
            const notes = btn.getAttribute("data-notes");
            openSettleModal(id, status, name, tx, method, notes);
        });
    });
}

function renderStatusBadge(status, txRef) {
    switch (status) {
        case "settled":
            return `
                <span class="badge bg-success rounded-pill px-3 py-1">
                    <i class="bi bi-check-circle-fill me-1"></i> Settled
                </span>
                ${txRef ? `<small class="d-block text-muted fs-9 font-monospace mt-0.5">Ref: ${escapeHtml(txRef)}</small>` : ''}
            `;
        case "processing":
            return `<span class="badge bg-info text-dark rounded-pill px-3 py-1"><i class="bi bi-arrow-repeat me-1"></i> Processing</span>`;
        case "pending":
        default:
            return `<span class="badge bg-warning text-dark rounded-pill px-3 py-1"><i class="bi bi-clock-history me-1"></i> Pending Payout</span>`;
    }
}

/**
 * Inspect Owner Financials Modal
 */
function inspectPayout(ownerId) {
    const owner = currentPayoutsList.find(o => String(o.id) === String(ownerId));
    if (!owner) return;

    const modalBody = document.getElementById("inspectPayoutModalBody");
    const modalFooter = document.getElementById("inspectPayoutModalFooter");
    const gross = Number(owner.gross_revenue || 0);
    const commission = Number(owner.platform_commission || 0);
    const netPayout = Number(owner.net_owner_payout || 0);
    const status = (owner.payout_status || "pending").toLowerCase();

    if (modalBody) {
        modalBody.innerHTML = `
            <div class="row g-4">

                <!-- OWNER HEADER SUMMARY -->
                <div class="col-12">
                    <div class="p-3 bg-light rounded-4 border d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; font-size: 1.25rem;">
                                ${escapeHtml((owner.name || 'O').charAt(0).toUpperCase())}
                            </div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">${escapeHtml(owner.name)}</h5>
                                <div class="text-muted small">${escapeHtml(owner.email)} | ${escapeHtml(owner.phone || 'N/A')}</div>
                            </div>
                        </div>
                        <div>
                            ${renderStatusBadge(status, owner.tx_reference)}
                        </div>
                    </div>
                </div>

                <!-- METRICS GRID -->
                <div class="col-md-4">
                    <div class="card border-0 bg-light p-3 rounded-4 text-center">
                        <small class="text-muted d-block fs-8 fw-bold">TOTAL REGISTERED VENUES</small>
                        <h4 class="fw-bold text-dark my-1">${owner.total_venues || 0}</h4>
                        <small class="text-muted fs-8">Active Court Properties</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 bg-light p-3 rounded-4 text-center">
                        <small class="text-muted d-block fs-8 fw-bold">PAID RESERVATIONS</small>
                        <h4 class="fw-bold text-dark my-1">${owner.total_bookings || 0}</h4>
                        <small class="text-muted fs-8">Player Reservations</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 bg-light p-3 rounded-4 text-center">
                        <small class="text-muted d-block fs-8 fw-bold">GROSS VENUE REVENUE</small>
                        <h4 class="fw-bold text-dark my-1">₹${gross.toLocaleString("en-IN")}</h4>
                        <small class="text-muted fs-8">Gross Booking Volume</small>
                    </div>
                </div>

                <!-- FINANCIAL BREAKDOWN CARD -->
                <div class="col-12">
                    <div class="card border-0 bg-dark text-white p-4 rounded-4 shadow-sm">
                        <small class="text-white-50 d-block fs-8 fw-bold text-uppercase mb-3">COMMISSION & NET PAYOUT BREAKDOWN</small>

                        <div class="d-flex justify-content-between mb-2 fs-7 text-white-50">
                            <span>Gross Player Bookings Volume:</span>
                            <span class="text-white fw-semibold">₹${gross.toLocaleString("en-IN")}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2 fs-7 text-white-50">
                            <span>Less: Platform Commission (10% Retained):</span>
                            <span class="text-danger fw-semibold">- ₹${commission.toLocaleString("en-IN")}</span>
                        </div>

                        <hr class="border-secondary my-2">

                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="fs-6 text-white">NET OWNER PAYOUT OWED (90%):</strong>
                            <h3 class="fw-extrabold text-success mb-0">₹${netPayout.toLocaleString("en-IN")}</h3>
                        </div>
                    </div>
                </div>

                ${owner.tx_reference ? `
                    <div class="col-12">
                        <div class="p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3">
                            <small class="text-success fw-bold d-block fs-8">SETTLEMENT TRANSACTION REFERENCE</small>
                            <span class="text-dark font-monospace fw-bold">${escapeHtml(owner.tx_reference)}</span>
                            <div class="text-muted fs-8 mt-1">Payment Method: ${escapeHtml(owner.payment_method || 'Bank Transfer')} ${owner.settlement_date ? '| Settled Date: ' + formatDateReadable(owner.settlement_date) : ''}</div>
                            ${owner.notes ? `<p class="text-dark small mb-0 mt-2 bg-white p-2 rounded-2">${escapeHtml(owner.notes)}</p>` : ''}
                        </div>
                    </div>
                ` : ''}

            </div>
        `;
    }

    if (modalFooter) {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success rounded-pill px-4 btn-modal-settle" data-id="${owner.id}" data-status="${status}" data-name="${escapeHtml(owner.name)}" data-tx="${escapeHtml(owner.tx_reference || '')}" data-method="${escapeHtml(owner.payment_method || '')}" data-notes="${escapeHtml(owner.notes || '')}">
                <i class="bi bi-check-circle me-1"></i> Record Payout Settlement
            </button>
        `;

        modalFooter.querySelector(".btn-modal-settle")?.addEventListener("click", (e) => {
            const btn = e.currentTarget;
            bootstrap.Modal.getInstance(document.getElementById("inspectPayoutModal"))?.hide();
            openSettleModal(btn.getAttribute("data-id"), btn.getAttribute("data-status"), btn.getAttribute("data-name"), btn.getAttribute("data-tx"), btn.getAttribute("data-method"), btn.getAttribute("data-notes"));
        });
    }

    const modal = new bootstrap.Modal(document.getElementById("inspectPayoutModal"));
    modal.show();
}

/**
 * Open Settle Payout Modal
 */
function openSettleModal(ownerId, currentStatus, ownerName, txRef, method, notes) {
    document.getElementById("settleOwnerId").value = ownerId;
    document.getElementById("settleOwnerPrompt").innerHTML = `Recording payout settlement for court owner <strong>"${escapeHtml(ownerName)}"</strong>.`;
    document.getElementById("settleStatusSelect").value = currentStatus === 'settled' ? 'settled' : 'settled';
    document.getElementById("settleTxReference").value = txRef || "";
    document.getElementById("settlePaymentMethod").value = method || "Bank Transfer (NEFT/RTGS/IMPS)";
    document.getElementById("settleNotes").value = notes || "";

    const modal = new bootstrap.Modal(document.getElementById("settlePayoutModal"));
    modal.show();
}

/**
 * Setup Settle Payout Form Handler
 */
function setupSettleForm() {
    const form = document.getElementById("settlePayoutForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const ownerId = document.getElementById("settleOwnerId").value;
        if (!ownerId) return;

        const submitBtn = document.getElementById("submitSettlePayoutBtn");
        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await apiFetch(`/admin/payouts/${ownerId}/settle`, {
                method: "PATCH",
                body: JSON.stringify({
                    status: document.getElementById("settleStatusSelect").value,
                    tx_reference: document.getElementById("settleTxReference").value.trim(),
                    payment_method: document.getElementById("settlePaymentMethod").value,
                    notes: document.getElementById("settleNotes").value.trim(),
                })
            });

            if (response && response.success) {
                bootstrap.Modal.getInstance(document.getElementById("settlePayoutModal"))?.hide();
                loadPayouts();
            }
        } catch (error) {
            console.error("Settle Payout Error:", error);
            alert(error.message || "Failed to update payout settlement status.");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
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
