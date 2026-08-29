import { apiFetch } from "../api.js";

let allCourts = [];
let allTimeSlots = [];

document.addEventListener("DOMContentLoaded", () => {
    initTimeSlotsPage();
});

async function initTimeSlotsPage() {
    await loadOwnerCourts();
    await loadTimeSlots();
    setupFilterListeners();
    setupSingleSlotForm();
    setupBulkSlotForm();
}

/**
 * Load Owner Courts for Dropdowns
 */
async function loadOwnerCourts() {
    try {
        const response = await apiFetch("/owner/courts");
        if (response && response.success) {
            allCourts = response.data || [];
            populateCourtDropdowns(allCourts);
        }
    } catch (error) {
        console.error("Load Courts Error:", error);
    }
}

function populateCourtDropdowns(courts) {
    const filterSelect = document.getElementById("filterCourtSelect");
    const addSelect = document.getElementById("addSlotCourtId");
    const bulkSelect = document.getElementById("bulkSlotCourtId");

    const optionsHtml = courts.map(c => `<option value="${c.id}">${escapeHtml(c.name)} (${escapeHtml(c.court_type)})</option>`).join("");

    if (filterSelect) {
        filterSelect.innerHTML = `<option value="">All Courts (${courts.length})</option>` + optionsHtml;
    }
    if (addSelect) {
        addSelect.innerHTML = `<option value="">Select Court Venue</option>` + optionsHtml;
    }
    if (bulkSelect) {
        bulkSelect.innerHTML = `<option value="">Select Court Venue</option>` + optionsHtml;
    }
}

/**
 * Load All Owner Time Slots
 */
async function loadTimeSlots() {
    const grid = document.getElementById("ownerTimeSlotsGrid");
    if (!grid) return;

    try {
        const response = await apiFetch("/owner/time-slots");
        if (response && response.success) {
            allTimeSlots = response.data || [];
            applyFiltersAndRender();
        }
    } catch (error) {
        console.error("Load Time Slots Error:", error);
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="owner-card p-4 text-danger">
                    <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                    Failed to load time slots. Please try refreshing.
                </div>
            </div>
        `;
    }
}

function setupFilterListeners() {
    const courtFilter = document.getElementById("filterCourtSelect");
    const statusFilter = document.getElementById("filterSlotStatus");

    if (courtFilter) courtFilter.addEventListener("change", applyFiltersAndRender);
    if (statusFilter) statusFilter.addEventListener("change", applyFiltersAndRender);
}

function applyFiltersAndRender() {
    const courtId = document.getElementById("filterCourtSelect")?.value;
    const statusVal = document.getElementById("filterSlotStatus")?.value;

    let filtered = [...allTimeSlots];

    if (courtId) {
        filtered = filtered.filter(s => String(s.court_id) === String(courtId));
    }

    if (statusVal) {
        filtered = filtered.filter(s => (s.status || "").toLowerCase() === statusVal.toLowerCase());
    }

    updateStatsSummary(filtered);
    renderTimeSlots(filtered);
}

function updateStatsSummary(slots) {
    const totalEl = document.getElementById("statTotalSlots");
    const activeEl = document.getElementById("statActiveSlots");
    const inactiveEl = document.getElementById("statInactiveSlots");

    const total = slots.length;
    const active = slots.filter(s => (s.status || "").toLowerCase() === "active").length;
    const inactive = total - active;

    if (totalEl) totalEl.textContent = total;
    if (activeEl) activeEl.textContent = active;
    if (inactiveEl) inactiveEl.textContent = inactive;
}

function renderTimeSlots(slots) {
    const grid = document.getElementById("ownerTimeSlotsGrid");
    if (!grid) return;

    if (!Array.isArray(slots) || slots.length === 0) {
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="owner-card p-5">
                    <i class="bi bi-clock text-muted fs-1 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">No Time Slots Found</h5>
                    <p class="text-muted small mb-4">No time slots match your current court or status filter. Click below to add or bulk generate slots.</p>
                    <button type="button" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark shadow-sm me-2" data-bs-toggle="modal" data-bs-target="#bulkTimeSlotModal">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Bulk Generate Slots
                    </button>
                </div>
            </div>
        `;
        return;
    }

    grid.innerHTML = slots.map(slot => {
        const isActive = (slot.status || "").toLowerCase() === "active";
        const startTimeStr = formatTime12H(slot.start_time);
        const endTimeStr = formatTime12H(slot.end_time);
        const courtName = slot.court?.name || "Court";

        return `
            <div class="col-lg-3 col-md-4 col-6">
                <div class="owner-card h-100 p-3 d-flex flex-column border-start border-4 ${isActive ? 'border-success' : 'border-secondary'}">
                    
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-light text-dark border small fw-normal text-truncate" style="max-width: 70%;">
                            <i class="bi bi-geo-alt me-1 text-success"></i>${escapeHtml(courtName)}
                        </span>
                        
                        <!-- ACTIVE TOGGLE -->
                        <div class="form-check form-switch mb-0" title="Toggle Active/Inactive">
                            <input class="form-check-input cursor-pointer" type="checkbox" role="switch" ${isActive ? 'checked' : ''} onchange="toggleSlotStatus(${slot.id}, this.checked, '${escapeHtml(slot.start_time)}', '${escapeHtml(slot.end_time)}')">
                        </div>
                    </div>

                    <!-- TIME DISPLAY -->
                    <div class="py-2 text-center bg-light rounded-3 mb-2">
                        <strong class="text-dark fs-6 d-block">${startTimeStr} - ${endTimeStr}</strong>
                    </div>

                    <!-- FOOTER DELETE -->
                    <div class="mt-auto d-flex align-items-center justify-content-between pt-2 border-top">
                        <span class="badge ${isActive ? 'bg-success' : 'bg-secondary'} rounded-pill px-2 py-1 fs-8">
                            ${isActive ? 'Active' : 'Inactive'}
                        </span>
                        <button type="button" class="btn btn-outline-danger btn-sm rounded-circle px-2" onclick="deleteSlot(${slot.id})" title="Delete Time Slot">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>

                </div>
            </div>
        `;
    }).join("");
}

/**
 * Toggle Slot Status
 */
window.toggleSlotStatus = async function (slotId, isChecked, startTime, endTime) {
    try {
        const response = await apiFetch(`/owner/time-slots/${slotId}`, {
            method: "PUT",
            body: JSON.stringify({
                start_time: startTime,
                end_time: endTime,
                status: isChecked ? "active" : "inactive"
            })
        });

        if (response && response.success) {
            await loadTimeSlots();
        }
    } catch (error) {
        console.error("Toggle Status Error:", error);
        alert(error.message || "Failed to update slot status.");
        await loadTimeSlots();
    }
};

/**
 * Delete Time Slot
 */
window.deleteSlot = async function (slotId) {
    if (!confirm("Are you sure you want to delete this time slot?")) return;

    try {
        const response = await apiFetch(`/owner/time-slots/${slotId}`, {
            method: "DELETE"
        });

        if (response && response.success) {
            await loadTimeSlots();
        }
    } catch (error) {
        console.error("Delete Slot Error:", error);
        alert(error.message || "Cannot delete time slot because active bookings exist.");
    }
};

/**
 * Setup Single Slot Form
 */
function setupSingleSlotForm() {
    const form = document.getElementById("addTimeSlotForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById("submitAddSlotBtn");
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);
        const payload = {
            court_id: formData.get("court_id"),
            start_time: formData.get("start_time"),
            end_time: formData.get("end_time")
        };

        try {
            const response = await apiFetch("/owner/time-slots", {
                method: "POST",
                body: JSON.stringify(payload)
            });

            if (response && response.success) {
                const modalEl = document.getElementById("addTimeSlotModal");
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                form.reset();
                await loadTimeSlots();
            }
        } catch (error) {
            console.error("Add Time Slot Error:", error);
            alert(error.message || "Failed to create time slot.");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

/**
 * Setup Bulk Slot Form
 */
function setupBulkSlotForm() {
    const form = document.getElementById("bulkTimeSlotForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById("submitBulkSlotBtn");
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(form);
        const payload = {
            court_id: formData.get("court_id"),
            start_time: formData.get("start_time"),
            end_time: formData.get("end_time"),
            slot_duration_minutes: formData.get("slot_duration_minutes")
        };

        try {
            const response = await apiFetch("/owner/time-slots/bulk", {
                method: "POST",
                body: JSON.stringify(payload)
            });

            if (response && response.success) {
                alert(response.message || "Time slots generated successfully!");
                const modalEl = document.getElementById("bulkTimeSlotModal");
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                form.reset();
                await loadTimeSlots();
            }
        } catch (error) {
            console.error("Bulk Slot Error:", error);
            alert(error.message || "Failed to generate time slots.");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

/**
 * Helpers
 */
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
