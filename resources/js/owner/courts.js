/* =========================================
   OWNER COURTS MANAGEMENT LOGIC
========================================= */

let allOwnerCourts = [];
let currentGalleryCourt = null;

document.addEventListener("DOMContentLoaded", () => {
    loadOwnerCourts();
    setupFilterListeners();
    setupAddCourtForm();
    setupEditCourtForm();
    setupUploadImageForm();
});

/* -----------------------------------------
   LOAD ALL OWNER COURTS
----------------------------------------- */
async function loadOwnerCourts() {
    const grid = document.getElementById("ownerCourtsGrid");
    if (!grid) return;

    try {
        const response = await apiFetch("/owner/courts");

        allOwnerCourts = response?.data || response || [];

        renderCourts(allOwnerCourts);
    } catch (error) {
        console.error("Error loading owner courts:", error);
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger fs-1 d-block mb-2"></i>
                <h5 class="fw-bold">Unable to load courts</h5>
                <p class="text-muted small mb-3">${error.message || "An unexpected error occurred."}</p>
                <button onclick="window.location.reload()" class="btn btn-outline-success btn-sm rounded-pill">
                    <i class="bi bi-arrow-clockwise me-1"></i> Retry
                </button>
            </div>
        `;
    }
}

/* -----------------------------------------
   RENDER COURTS GRID
----------------------------------------- */
function renderCourts(courts) {
    const grid = document.getElementById("ownerCourtsGrid");
    if (!grid) return;

    if (!Array.isArray(courts) || courts.length === 0) {
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="owner-card p-5">
                    <i class="bi bi-grid-3x3-gap text-muted fs-1 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">No Courts Found</h5>
                    <p class="text-muted small mb-4">You haven't added any court venues yet or no courts match your search filters.</p>
                    <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addCourtModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Your First Court
                    </button>
                </div>
            </div>
        `;
        return;
    }

    grid.innerHTML = courts.map(court => {
        const images = court.images || [];
        const primaryImg = images.find(img => img.is_primary) || images[0];
        const coverImg = primaryImg ? (primaryImg.image_url || primaryImg.image_path) : "/assets/images/tournament-placeholder.jpg";
        const isActive = (court.status || "").toLowerCase() === "active";
        const rating = Number(court.reviews_avg_rating || 0).toFixed(1);
        const totalReviews = court.reviews_count ?? 0;

        return `
            <div class="col-lg-4 col-md-6">
                <div class="owner-card h-100 d-flex flex-column">

                    <!-- CARD THUMBNAIL -->
                    <div class="position-relative overflow-hidden" style="height: 190px;">
                        <img src="${escapeHtml(coverImg)}" class="w-100 h-100 object-fit-cover" alt="${escapeHtml(court.name)}" onerror="this.src='/assets/images/tournament-placeholder.jpg';">
                        
                        <div class="position-absolute top-0 start-0 m-3">
                            <span class="badge ${court.court_type === 'Indoor' ? 'bg-primary' : 'bg-success'} rounded-pill px-3 py-1 shadow-sm">
                                <i class="bi ${court.court_type === 'Indoor' ? 'bi-house-door' : 'bi-sun'} me-1"></i> ${escapeHtml(court.court_type || 'Court')}
                            </span>
                        </div>

                        <div class="position-absolute top-0 end-0 m-3">
                            <button type="button" class="btn btn-light btn-sm rounded-circle shadow-sm" onclick="openPhotoGalleryModal(${court.id})" title="Manage Photos">
                                <i class="bi bi-camera-fill text-dark"></i>
                            </button>
                        </div>
                    </div>

                    <!-- CARD BODY -->
                    <div class="p-3 d-flex flex-column flex-grow-1">

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h5 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 70%;">${escapeHtml(court.name)}</h5>
                            
                            <!-- STATUS TOGGLE SWITCH -->
                            <div class="form-check form-switch mb-0" title="Toggle Active/Inactive Status">
                                <input class="form-check-input cursor-pointer" type="checkbox" role="switch" ${isActive ? 'checked' : ''} onchange="toggleCourtStatus(${court.id}, this.checked)">
                            </div>
                        </div>

                        <p class="text-muted small mb-3 text-truncate"><i class="bi bi-geo-alt me-1 text-danger"></i>${escapeHtml(court.address)}</p>

                        <div class="row g-2 text-center bg-light rounded-3 p-2 mb-3">
                            <div class="col-4 border-end">
                                <small class="text-muted d-block fs-8">PRICE/HR</small>
                                <strong class="text-success small">₹${formatPrice(court.price_per_hour)}</strong>
                            </div>
                            <div class="col-4 border-end">
                                <small class="text-muted d-block fs-8">HOURS</small>
                                <strong class="text-dark small">${escapeHtml(court.opening_time || '06:00')} - ${escapeHtml(court.closing_time || '23:00')}</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block fs-8">RATING</small>
                                <strong class="text-warning small"><i class="bi bi-star-fill me-1"></i>${rating}</strong>
                            </div>
                        </div>

                        <!-- ACTIONS FOOTER -->
                        <div class="mt-auto d-flex gap-2 pt-2 border-top">
                            <button type="button" class="btn btn-outline-success btn-sm w-100 rounded-pill fw-semibold" onclick="openEditCourtModal(${court.id})">
                                <i class="bi bi-pencil me-1"></i> Edit Details
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle px-2" onclick="deleteCourt(${court.id})" title="Delete Court">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                    </div>

                </div>
            </div>
        `;
    }).join("");
}

/* -----------------------------------------
   SEARCH & FILTERS
----------------------------------------- */
function setupFilterListeners() {
    const searchInput = document.getElementById("searchCourtInput");
    const filterType = document.getElementById("filterCourtType");
    const filterStatus = document.getElementById("filterCourtStatus");
    const resetBtn = document.getElementById("resetCourtFiltersBtn");

    const applyFilters = () => {
        const query = (searchInput?.value || "").toLowerCase().trim();
        const type = (filterType?.value || "").toLowerCase();
        const status = (filterStatus?.value || "").toLowerCase();

        const filtered = allOwnerCourts.filter(court => {
            const matchesSearch = !query || court.name.toLowerCase().includes(query) || (court.address || "").toLowerCase().includes(query);
            const matchesType = !type || (court.court_type || "").toLowerCase() === type;
            const matchesStatus = !status || (court.status || "").toLowerCase() === status;
            return matchesSearch && matchesType && matchesStatus;
        });

        renderCourts(filtered);
    };

    if (searchInput) searchInput.addEventListener("input", applyFilters);
    if (filterType) filterType.addEventListener("change", applyFilters);
    if (filterStatus) filterStatus.addEventListener("change", applyFilters);

    if (resetBtn) {
        resetBtn.addEventListener("click", () => {
            if (searchInput) searchInput.value = "";
            if (filterType) filterType.value = "";
            if (filterStatus) filterStatus.value = "";
            renderCourts(allOwnerCourts);
        });
    }
}

/* -----------------------------------------
   TOGGLE STATUS (ACTIVE / INACTIVE)
----------------------------------------- */
window.toggleCourtStatus = async function (courtId, isChecked) {
    const newStatus = isChecked ? "active" : "inactive";

    try {
        const response = await apiFetch(`/owner/courts/${courtId}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ status: newStatus })
        });

        if (response && response.success) {
            const court = allOwnerCourts.find(c => c.id === courtId);
            if (court) court.status = newStatus;
        }
    } catch (error) {
        console.error("Status update error:", error);
        alert(error.message || "Failed to update court status.");
        loadOwnerCourts();
    }
};

/* -----------------------------------------
   ADD COURT SUBMISSION
----------------------------------------- */
function setupAddCourtForm() {
    const form = document.getElementById("addCourtForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const spinner = document.getElementById("saveCourtSpinner");
        const btn = document.getElementById("saveCourtBtn");

        if (spinner) spinner.classList.remove("d-none");
        if (btn) btn.disabled = true;

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        try {
            const response = await apiFetch("/owner/courts", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            if (response && response.success) {
                const modalEl = document.getElementById("addCourtModal");
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                form.reset();

                await loadOwnerCourts();
            }
        } catch (error) {
            console.error("Add Court Error:", error);
            alert(error.message || "Unable to create court venue.");
        } finally {
            if (spinner) spinner.classList.add("d-none");
            if (btn) btn.disabled = false;
        }
    });
}

/* -----------------------------------------
   EDIT COURT MODAL & SUBMISSION
----------------------------------------- */
window.openEditCourtModal = function (courtId) {
    const court = allOwnerCourts.find(c => c.id === courtId);
    if (!court) return;

    document.getElementById("editCourtId").value = court.id;
    document.getElementById("editCourtName").value = court.name || "";
    document.getElementById("editCourtType").value = court.court_type || "Outdoor";
    document.getElementById("editCourtAddress").value = court.address || "";
    document.getElementById("editCourtPrice").value = court.price_per_hour || 500;
    document.getElementById("editCourtOpening").value = court.opening_time || "06:00";
    document.getElementById("editCourtClosing").value = court.closing_time || "23:00";
    document.getElementById("editCourtStatus").value = court.status || "active";
    document.getElementById("editCourtLat").value = court.latitude || "";
    document.getElementById("editCourtLng").value = court.longitude || "";
    document.getElementById("editCourtDesc").value = court.description || "";

    const modal = new bootstrap.Modal(document.getElementById("editCourtModal"));
    modal.show();
};

function setupEditCourtForm() {
    const form = document.getElementById("editCourtForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const courtId = document.getElementById("editCourtId").value;
        const spinner = document.getElementById("updateCourtSpinner");
        const btn = document.getElementById("updateCourtBtn");

        if (spinner) spinner.classList.remove("d-none");
        if (btn) btn.disabled = true;

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        try {
            const response = await apiFetch(`/owner/courts/${courtId}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            if (response && response.success) {
                const modalEl = document.getElementById("editCourtModal");
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                await loadOwnerCourts();
            }
        } catch (error) {
            console.error("Edit Court Error:", error);
            alert(error.message || "Unable to update court details.");
        } finally {
            if (spinner) spinner.classList.add("d-none");
            if (btn) btn.disabled = false;
        }
    });
}

/* -----------------------------------------
   PHOTO GALLERY MODAL & UPLOADER
----------------------------------------- */
window.openPhotoGalleryModal = function (courtId) {
    const court = allOwnerCourts.find(c => c.id === courtId);
    if (!court) return;

    currentGalleryCourt = court;
    document.getElementById("galleryCourtId").value = court.id;
    document.getElementById("galleryCourtTitle").textContent = court.name;

    renderPhotoGallery(court.images || []);

    const modal = new bootstrap.Modal(document.getElementById("courtImagesModal"));
    modal.show();
};

function renderPhotoGallery(images) {
    const container = document.getElementById("courtPhotosGrid");
    if (!container) return;

    if (!Array.isArray(images) || images.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-4 text-muted">
                <i class="bi bi-images fs-2 d-block mb-1 text-secondary"></i>
                No photos uploaded for this court yet.
            </div>
        `;
        return;
    }

    container.innerHTML = images.map(img => {
        const url = img.image_url || img.image_path;
        const isPrimary = Boolean(img.is_primary);

        return `
            <div class="col-md-4 col-6">
                <div class="position-relative overflow-hidden rounded-3 border ${isPrimary ? 'border-success border-2 shadow-sm' : ''}" style="height: 145px;">
                    <img src="${escapeHtml(url)}" class="w-100 h-100 object-fit-cover" alt="Court Photo">
                    
                    ${isPrimary 
                        ? `<span class="badge bg-success position-absolute top-0 start-0 m-2 shadow-sm"><i class="bi bi-star-fill me-1"></i> Cover</span>`
                        : `<button type="button" class="btn btn-dark btn-sm opacity-75 position-absolute top-0 start-0 m-2 fs-8 rounded-pill shadow-sm" onclick="setPrimaryCourtPhoto(${img.id})" title="Set as Primary Cover">Set Cover</button>`
                    }

                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle shadow-sm" onclick="deleteCourtPhoto(${img.id})" title="Delete Photo">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        `;
    }).join("");
}

window.setPrimaryCourtPhoto = async function (imageId) {
    try {
        const response = await apiFetch(`/owner/court-images/${imageId}/primary`, {
            method: "PATCH"
        });

        if (response && response.success) {
            await loadOwnerCourts();

            const updated = allOwnerCourts.find(c => c.id === Number(currentGalleryCourt?.id));
            if (updated) {
                renderPhotoGallery(updated.images || []);
            }
        }
    } catch (error) {
        console.error("Set Primary Photo Error:", error);
        alert(error.message || "Failed to set primary cover photo.");
    }
};

function setupUploadImageForm() {
    const form = document.getElementById("uploadCourtImageForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const courtId = document.getElementById("galleryCourtId").value;
        const fileInput = document.getElementById("courtPhotoInput");
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            alert("Please select at least one photo to upload.");
            return;
        }

        const formData = new FormData();
        formData.append("court_id", courtId);

        for (let i = 0; i < fileInput.files.length; i++) {
            formData.append("images[]", fileInput.files[i]);
        }

        const uploadBtn = document.getElementById("uploadPhotoBtn");
        if (uploadBtn) uploadBtn.disabled = true;

        try {
            const response = await apiFetch("/owner/court-images", {
                method: "POST",
                body: formData
            });

            if (response && response.success) {
                fileInput.value = "";
                await loadOwnerCourts();

                const updated = allOwnerCourts.find(c => c.id === Number(courtId));
                if (updated) {
                    renderPhotoGallery(updated.images || []);
                }
            }
        } catch (error) {
            console.error("Photo Upload Error:", error);
            alert(error.message || "Failed to upload photo.");
        } finally {
            if (uploadBtn) uploadBtn.disabled = false;
        }
    });
}

window.deleteCourtPhoto = async function (imageId) {
    if (!confirm("Are you sure you want to delete this photo?")) return;

    try {
        const response = await apiFetch(`/owner/court-images/${imageId}`, {
            method: "DELETE"
        });

        if (response && response.success) {
            await loadOwnerCourts();

            const updated = allOwnerCourts.find(c => c.id === currentGalleryCourt?.id);
            if (updated) {
                renderPhotoGallery(updated.images || []);
            }
        }
    } catch (error) {
        console.error("Photo Delete Error:", error);
        alert(error.message || "Unable to delete photo.");
    }
};

/* -----------------------------------------
   DELETE COURT
----------------------------------------- */
window.deleteCourt = async function (courtId) {
    if (!confirm("Are you sure you want to delete this court? This action cannot be undone if no bookings exist.")) return;

    try {
        const response = await apiFetch(`/owner/courts/${courtId}`, {
            method: "DELETE"
        });

        if (response && response.success) {
            await loadOwnerCourts();
        }
    } catch (error) {
        console.error("Delete Court Error:", error);
        alert(error.message || "Cannot delete court because active bookings exist or unauthorized.");
    }
};

/* -----------------------------------------
   HELPERS
----------------------------------------- */
function formatPrice(val) {
    const num = Number(val);
    if (isNaN(num)) return "0";
    return num.toLocaleString("en-IN");
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}
