import { apiFetch } from "../api.js";

document.addEventListener("DOMContentLoaded", () => {
    loadApplicationStatus();
});

async function loadApplicationStatus() {
    const container = document.getElementById("ownerAppStatusCard");
    if (!container) return;

    try {
        const response = await apiFetch("/owner/application");
        if (response && response.success) {
            const app = response.data;
            
            if (!app) {
                renderApplicationForm(container);
            } else if (app.status === "pending") {
                renderPendingCard(container, app);
            } else if (app.status === "approved") {
                renderApprovedCard(container, app);
            } else if (app.status === "rejected") {
                renderRejectedCard(container, app);
            } else {
                renderApplicationForm(container);
            }
        }
    } catch (error) {
        console.error("Load Owner Application Error:", error);
        renderApplicationForm(container);
    }
}

/**
 * Render Application Form
 */
function renderApplicationForm(container, existingData = null) {
    container.innerHTML = `
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            <div class="border-bottom pb-3 mb-4">
                <h3 class="fw-bold text-dark mb-1"><i class="bi bi-file-earmark-text text-success me-2"></i>Court Owner Verification Application</h3>
                <p class="text-muted small mb-0">Fill out your business details and upload verification proof to get approved by our venue team.</p>
            </div>

            <div id="becomeOwnerAlertContainer"></div>

            <form id="becomeOwnerForm" enctype="multipart/form-data">
                <div class="row g-3 mb-4">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Business / Organization Name *</label>
                        <input type="text" name="business_name" class="form-control" placeholder="e.g. Surat Smash Pickleball Club" value="${escapeHtml(existingData?.business_name || '')}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Contact Phone Number *</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" value="${escapeHtml(existingData?.phone || '')}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">City *</label>
                        <input type="text" name="city" class="form-control" placeholder="Surat" value="${escapeHtml(existingData?.city || '')}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">State *</label>
                        <input type="text" name="state" class="form-control" placeholder="Gujarat" value="${escapeHtml(existingData?.state || '')}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">Pincode *</label>
                        <input type="text" name="pincode" class="form-control" placeholder="395007" value="${escapeHtml(existingData?.pincode || '')}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small text-muted">Full Address *</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Full street address..." required>${escapeHtml(existingData?.address || '')}</textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Years of Experience in Sports Management</label>
                        <input type="text" name="experience" class="form-control" placeholder="e.g. 3 years managing badminton/pickleball courts" value="${escapeHtml(existingData?.experience || '')}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-muted">Verification Document Proof *</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" ${existingData ? '' : 'required'}>
                        <small class="text-muted fs-8">Upload Government ID, Business Registration, or Electricity Bill (PDF/JPG, max 5MB)</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small text-muted">Facility Description & Plan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Tell us about your court facilities, number of courts, lighting, and amenities...">${escapeHtml(existingData?.description || '')}</textarea>
                    </div>

                </div>

                <div class="d-flex justify-content-end pt-3 border-top">
                    <button type="submit" id="submitBecomeOwnerBtn" class="btn btn-success rounded-pill px-5 py-2 fw-bold shadow-sm">
                        <i class="bi bi-send me-1"></i> Submit Owner Application
                    </button>
                </div>
            </form>
        </div>
    `;

    setupFormSubmission();
}

/**
 * Setup Form Submission Handler
 */
function setupFormSubmission() {
    const form = document.getElementById("becomeOwnerForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById("submitBecomeOwnerBtn");
        const alertContainer = document.getElementById("becomeOwnerAlertContainer");

        if (submitBtn) submitBtn.disabled = true;
        if (alertContainer) alertContainer.innerHTML = "";

        const formData = new FormData(form);

        try {
            const response = await apiFetch("/owner/apply", {
                method: "POST",
                body: formData
            });

            if (response && response.success) {
                showAlert("Application submitted successfully! Our admin team is reviewing your verification proof.", "success");
                setTimeout(() => {
                    loadApplicationStatus();
                }, 1500);
            }
        } catch (error) {
            console.error("Submit Owner Application Error:", error);
            showAlert(error.message || "Failed to submit application. Please check form inputs.", "danger");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

/**
 * Render Pending State Card
 */
function renderPendingCard(container, app) {
    container.innerHTML = `
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-light">
            <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-4 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2.2rem;">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <h3 class="fw-bold text-dark mb-2">Application Under Admin Review</h3>
            <p class="text-muted max-w-md mx-auto mb-4">
                Thank you for applying! Your verification application for <strong>"${escapeHtml(app.business_name)}"</strong> has been submitted and is currently being reviewed by our Admin team.
            </p>

            <div class="row g-3 justify-content-center text-start max-w-lg mx-auto bg-white p-4 rounded-4 border mb-4">
                <div class="col-6">
                    <small class="text-muted d-block fs-8">BUSINESS NAME</small>
                    <strong class="text-dark small">${escapeHtml(app.business_name)}</strong>
                </div>
                <div class="col-6">
                    <small class="text-muted d-block fs-8">CITY & STATE</small>
                    <strong class="text-dark small">${escapeHtml(app.city)}, ${escapeHtml(app.state)}</strong>
                </div>
                <div class="col-6">
                    <small class="text-muted d-block fs-8">CONTACT PHONE</small>
                    <span class="text-dark small">${escapeHtml(app.phone)}</span>
                </div>
                <div class="col-6">
                    <small class="text-muted d-block fs-8">SUBMITTED ON</small>
                    <span class="text-dark small">${formatDateReadable(app.created_at)}</span>
                </div>
            </div>

            <div>
                <span class="badge bg-warning text-dark rounded-pill px-4 py-2 fw-bold fs-6">
                    <i class="bi bi-clock me-1"></i> STATUS: PENDING VERIFICATION
                </span>
            </div>
        </div>
    `;
}

/**
 * Render Approved State Card
 */
function renderApprovedCard(container, app) {
    container.innerHTML = `
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white border-success">
            <div class="rounded-circle bg-success bg-opacity-10 text-success p-4 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2.2rem;">
                <i class="bi bi-patch-check-fill"></i>
            </div>
            <h2 class="fw-bold text-success mb-2">Congratulations! You Are An Approved Court Owner</h2>
            <p class="text-muted max-w-md mx-auto mb-4">
                Your owner application for <strong>"${escapeHtml(app.business_name)}"</strong> is fully approved. You can now access the Owner Management Portal to add courts, configure time slots, and host tournaments!
            </p>

            <div>
                <a href="/owner/dashboard" class="btn btn-success rounded-pill px-5 py-3 fw-bold shadow-lg fs-5">
                    <i class="bi bi-speedometer2 me-2"></i> Go to Owner Dashboard
                </a>
            </div>
        </div>
    `;
}

/**
 * Render Rejected State Card
 */
function renderRejectedCard(container, app) {
    container.innerHTML = `
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-light border-danger mb-4">
            <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-4 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2.2rem;">
                <i class="bi bi-x-circle-fill"></i>
            </div>
            <h3 class="fw-bold text-danger mb-2">Application Requires Revision</h3>
            <p class="text-muted max-w-md mx-auto mb-3">
                Your previous owner application was not approved by our review team. Please check the feedback below, update your details, and resubmit.
            </p>

            ${app.admin_note ? `
                <div class="alert alert-danger max-w-lg mx-auto text-start rounded-3 mb-4">
                    <strong class="d-block mb-1"><i class="bi bi-exclamation-octagon me-1"></i> Admin Feedback:</strong>
                    ${escapeHtml(app.admin_note)}
                </div>
            ` : ''}

            <button type="button" id="reapplyBtn" class="btn btn-outline-danger rounded-pill px-4 py-2 fw-bold">
                <i class="bi bi-pencil-square me-1"></i> Edit & Resubmit Application
            </button>
        </div>
        <div id="reapplyFormContainer"></div>
    `;

    document.getElementById("reapplyBtn")?.addEventListener("click", () => {
        const reapplyContainer = document.getElementById("reapplyFormContainer");
        renderApplicationForm(reapplyContainer, app);
        reapplyContainer.scrollIntoView({ behavior: "smooth" });
    });
}

function showAlert(message, type = "success") {
    const container = document.getElementById("becomeOwnerAlertContainer");
    if (!container) return;

    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
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
