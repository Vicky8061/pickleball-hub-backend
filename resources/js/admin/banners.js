import { apiFetch } from "../api.js";

let currentStatus = "";
let currentBannersList = [];
let targetDeleteBannerId = null;

document.addEventListener("DOMContentLoaded", () => {
    initFilters();
    initFileUploadPreviews();
    loadBanners();
    setupCreateForm();
    setupEditForm();
    setupDeleteForm();
});

function initFilters() {
    const pillsNav = document.getElementById("bannerStatusPillsNav");
    if (pillsNav) {
        pillsNav.querySelectorAll("button").forEach(btn => {
            btn.addEventListener("click", () => {
                pillsNav.querySelectorAll("button").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                currentStatus = btn.getAttribute("data-status") || "";
                loadBanners();
            });
        });
    }
}

function initFileUploadPreviews() {
    // Create Banner Preview
    const fileInput = document.getElementById("createBannerFileInput");
    const previewContainer = document.getElementById("createBannerPreviewContainer");
    const previewImg = document.getElementById("createBannerPreviewImg");

    if (fileInput && previewContainer && previewImg) {
        fileInput.addEventListener("change", (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (evt) => {
                    previewImg.src = evt.target.result;
                    previewContainer.classList.remove("d-none");
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add("d-none");
            }
        });
    }
}

async function loadBanners() {
    const container = document.getElementById("bannersGrid");
    if (!container) return;

    container.innerHTML = `<div class="col-12 text-center py-5 text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading promotional banners...</div>`;

    try {
        let endpoint = "/admin/banners";
        if (currentStatus) {
            endpoint += `?status=${currentStatus}`;
        }

        const response = await apiFetch(endpoint);

        if (response && response.success) {
            const resData = response.data || [];
            const banners = Array.isArray(resData) ? resData : (resData.data || []);

            currentBannersList = banners;
            updateStatSummaryCards(banners);
            renderBannersGrid(container, banners);
        }
    } catch (error) {
        console.error("Load Admin Banners Error:", error);
        container.innerHTML = `<div class="col-12 text-center py-5 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load banners. ${escapeHtml(error.message || '')}</div>`;
    }
}

function updateStatSummaryCards(banners) {
    const totalEl = document.getElementById("statTotalBanners");
    const activeEl = document.getElementById("statActiveBanners");
    const inactiveEl = document.getElementById("statInactiveBanners");
    const linkedEl = document.getElementById("statLinkedBanners");

    if (totalEl) totalEl.textContent = banners.length;

    let activeCount = 0;
    let inactiveCount = 0;
    let linkedCount = 0;

    banners.forEach(b => {
        const st = (b.status || "active").toLowerCase();
        if (st === "active") activeCount++;
        else inactiveCount++;

        if (b.redirect_url) linkedCount++;
    });

    if (activeEl) activeEl.textContent = activeCount;
    if (inactiveEl) inactiveEl.textContent = inactiveCount;
    if (linkedEl) linkedEl.textContent = linkedCount;
}

function renderBannersGrid(container, banners) {
    if (!Array.isArray(banners) || banners.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5 bg-white rounded-4 border shadow-sm my-3">
                <i class="bi bi-images display-4 text-muted d-block mb-3"></i>
                <h5 class="fw-bold text-dark">No Promotional Banners Uploaded</h5>
                <p class="text-muted small mb-3">Upload your first promotional banner to showcase ongoing tournaments or offers on player mobile devices.</p>
                <button type="button" class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createBannerModal">
                    <i class="bi bi-plus-circle me-1"></i> Upload Banner
                </button>
            </div>
        `;
        return;
    }

    container.innerHTML = banners.map(banner => {
        const title = banner.title || "Promotional Banner";
        const redirectUrl = banner.redirect_url || "";
        const status = (banner.status || "active").toLowerCase();
        const imgUrl = getBannerImageUrl(banner);
        const createdAt = formatDateReadable(banner.created_at);

        return `
            <div class="col-lg-4 col-md-6 col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 bg-white border">
                    <!-- HERO IMAGE PREVIEW -->
                    <div class="position-relative bg-dark" style="height: 180px;">
                        <img src="${escapeHtml(imgUrl)}" alt="${escapeHtml(title)}" class="w-100 h-100 object-fit-cover">
                        <div class="position-absolute top-0 end-0 p-3">
                            ${renderStatusBadge(status)}
                        </div>
                    </div>

                    <!-- CARD BODY -->
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold text-dark mb-2">${escapeHtml(title)}</h5>

                            ${redirectUrl ? `
                                <div class="mb-3">
                                    <span class="badge bg-light text-primary border rounded-pill px-3 py-1.5 fs-8 text-truncate d-inline-block" style="max-width: 100%;">
                                        <i class="bi bi-link-45deg me-1"></i>${escapeHtml(redirectUrl)}
                                    </span>
                                </div>
                            ` : `
                                <div class="mb-3">
                                    <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5 fs-8">
                                        <i class="bi bi-dash-circle me-1"></i>No target link attached
                                    </span>
                                </div>
                            `}
                        </div>

                        <!-- CARD FOOTER ACTIONS -->
                        <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                            <small class="text-muted fs-8"><i class="bi bi-calendar3 me-1"></i>${escapeHtml(createdAt)}</small>

                            <div class="d-flex align-items-center gap-1">
                                <button type="button" class="btn btn-sm btn-light border text-dark rounded-circle btn-edit-banner" data-id="${banner.id}" title="Edit Banner">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                <button type="button" class="btn btn-sm ${status === 'active' ? 'btn-outline-warning' : 'btn-outline-success'} rounded-circle btn-toggle-banner-status" data-id="${banner.id}" data-status="${status === 'active' ? 'inactive' : 'active'}" title="${status === 'active' ? 'Deactivate' : 'Activate'}">
                                    <i class="bi bi-${status === 'active' ? 'pause-fill' : 'play-fill'}"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-delete-banner" data-id="${banner.id}" data-title="${escapeHtml(title)}" title="Delete Banner">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join("");

    attachGridEventListeners();
}

function attachGridEventListeners() {
    // Edit Banner
    document.querySelectorAll(".btn-edit-banner").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            openEditBannerModal(id);
        });
    });

    // Toggle Banner Status
    document.querySelectorAll(".btn-toggle-banner-status").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const newStatus = btn.getAttribute("data-status");
            toggleBannerStatus(id, newStatus);
        });
    });

    // Delete Banner
    document.querySelectorAll(".btn-delete-banner").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const title = btn.getAttribute("data-title");
            openDeleteBannerModal(id, title);
        });
    });
}

function renderStatusBadge(status) {
    if (status === "active") {
        return `<span class="badge bg-success rounded-pill px-3 py-1 shadow-sm"><i class="bi bi-check-circle-fill me-1"></i> Active</span>`;
    }
    return `<span class="badge bg-secondary rounded-pill px-3 py-1 shadow-sm"><i class="bi bi-eye-slash-fill me-1"></i> Inactive</span>`;
}

function getBannerImageUrl(banner) {
    let url = banner.image_url || banner.image;
    if (url) {
        return url.startsWith("http://") || url.startsWith("https://") ? url : url.startsWith("/") ? url : `/storage/${url}`;
    }
    return "https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=800&q=80";
}

/**
 * Setup Create Banner Submission
 */
function setupCreateForm() {
    const form = document.getElementById("createBannerForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById("submitCreateBannerBtn");
        if (submitBtn) submitBtn.disabled = true;

        try {
            const formData = new FormData(form);

            // Fetch token for multipart request
            const token = localStorage.getItem("token") || sessionStorage.getItem("token");
            const response = await fetch("/api/admin/banners", {
                method: "POST",
                headers: {
                    "Authorization": `Bearer ${token}`,
                    "Accept": "application/json"
                },
                body: formData
            });

            const resJson = await response.json();

            if (response.ok && resJson.success) {
                form.reset();
                document.getElementById("createBannerPreviewContainer")?.classList.add("d-none");
                bootstrap.Modal.getInstance(document.getElementById("createBannerModal"))?.hide();
                loadBanners();
            } else {
                alert(resJson.message || "Failed to create banner.");
            }
        } catch (error) {
            console.error("Create Banner Error:", error);
            alert(error.message || "Failed to upload banner.");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

/**
 * Open Edit Banner Modal
 */
function openEditBannerModal(bannerId) {
    const banner = currentBannersList.find(b => String(b.id) === String(bannerId));
    if (!banner) return;

    document.getElementById("editBannerId").value = banner.id;
    document.getElementById("editBannerTitle").value = banner.title || "";
    document.getElementById("editBannerRedirectUrl").value = banner.redirect_url || "";
    document.getElementById("editBannerStatus").value = (banner.status || "active").toLowerCase();
    document.getElementById("editCurrentBannerImg").src = getBannerImageUrl(banner);

    const modal = new bootstrap.Modal(document.getElementById("editBannerModal"));
    modal.show();
}

/**
 * Setup Edit Banner Form
 */
function setupEditForm() {
    const form = document.getElementById("editBannerForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const bannerId = document.getElementById("editBannerId").value;
        if (!bannerId) return;

        const submitBtn = document.getElementById("submitEditBannerBtn");
        if (submitBtn) submitBtn.disabled = true;

        try {
            const formData = new FormData(form);

            const token = localStorage.getItem("token") || sessionStorage.getItem("token");
            const response = await fetch(`/api/admin/banners/${bannerId}`, {
                method: "POST",
                headers: {
                    "Authorization": `Bearer ${token}`,
                    "Accept": "application/json"
                },
                body: formData
            });

            const resJson = await response.json();

            if (response.ok && resJson.success) {
                bootstrap.Modal.getInstance(document.getElementById("editBannerModal"))?.hide();
                loadBanners();
            } else {
                alert(resJson.message || "Failed to update banner.");
            }
        } catch (error) {
            console.error("Update Banner Error:", error);
            alert(error.message || "Failed to update banner.");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

/**
 * Toggle Banner Status directly
 */
async function toggleBannerStatus(bannerId, newStatus) {
    try {
        const formData = new FormData();
        formData.append("status", newStatus);

        const token = localStorage.getItem("token") || sessionStorage.getItem("token");
        const response = await fetch(`/api/admin/banners/${bannerId}`, {
            method: "POST",
            headers: {
                "Authorization": `Bearer ${token}`,
                "Accept": "application/json"
            },
            body: formData
        });

        const resJson = await response.json();
        if (response.ok && resJson.success) {
            loadBanners();
        }
    } catch (error) {
        console.error("Toggle Banner Status Error:", error);
    }
}

/**
 * Open Delete Banner Modal
 */
function openDeleteBannerModal(bannerId, bannerTitle) {
    targetDeleteBannerId = bannerId;

    const promptEl = document.getElementById("deleteBannerPrompt");
    if (promptEl) {
        promptEl.innerHTML = `Are you sure you want to permanently delete <strong>"${escapeHtml(bannerTitle)}"</strong>?`;
    }

    const modal = new bootstrap.Modal(document.getElementById("deleteBannerModal"));
    modal.show();
}

/**
 * Setup Delete Form
 */
function setupDeleteForm() {
    const form = document.getElementById("deleteBannerForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!targetDeleteBannerId) return;

        const submitBtn = document.getElementById("submitDeleteBannerBtn");
        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await apiFetch(`/admin/banners/${targetDeleteBannerId}`, {
                method: "DELETE"
            });

            if (response && response.success) {
                bootstrap.Modal.getInstance(document.getElementById("deleteBannerModal"))?.hide();
                loadBanners();
            }
        } catch (error) {
            console.error("Delete Banner Error:", error);
            alert(error.message || "Failed to delete banner.");
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
