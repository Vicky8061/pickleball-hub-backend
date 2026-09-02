import { apiFetch } from "../api.js";

let currentPage = 1;
let currentRating = "";
let currentSearch = "";
let currentSort = "latest";
let targetDeleteReviewId = null;
let currentReviewsList = [];

document.addEventListener("DOMContentLoaded", () => {
    initFilters();
    loadReviews();
    setupDeleteForm();
});

function initFilters() {
    // Rating Pills Nav
    const pillsNav = document.getElementById("reviewRatingPillsNav");
    if (pillsNav) {
        pillsNav.querySelectorAll("button").forEach(btn => {
            btn.addEventListener("click", () => {
                pillsNav.querySelectorAll("button").forEach(b => b.classList.remove("active"));
                btn.classList.add("active");
                currentRating = btn.getAttribute("data-rating") || "";
                currentPage = 1;
                loadReviews();
            });
        });
    }

    // Search Input Debounce
    const searchInput = document.getElementById("reviewSearchInput");
    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener("input", (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentSearch = e.target.value.trim();
                currentPage = 1;
                loadReviews();
            }, 300);
        });
    }

    // Sort Select
    const sortSelect = document.getElementById("reviewSortSelect");
    if (sortSelect) {
        sortSelect.addEventListener("change", (e) => {
            currentSort = e.target.value;
            currentPage = 1;
            loadReviews();
        });
    }
}

async function loadReviews() {
    const tbody = document.getElementById("reviewsTbody");
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Loading master reviews...</td></tr>`;

    try {
        const queryParams = new URLSearchParams({
            page: currentPage,
            sort: currentSort
        });
        if (currentRating) queryParams.append("rating", currentRating);
        if (currentSearch) queryParams.append("search", currentSearch);

        const response = await apiFetch(`/admin/reviews?${queryParams.toString()}`);

        if (response && response.success) {
            const resData = response.data || {};
            const reviews = resData.data || (Array.isArray(resData) ? resData : []);
            const meta = response.meta || response.data || {};

            currentReviewsList = reviews;
            updateStatSummaryCards(reviews, meta);
            renderReviewsTable(tbody, reviews);
            renderPagination(meta);
        }
    } catch (error) {
        console.error("Load Admin Reviews Error:", error);
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Failed to load reviews. ${escapeHtml(error.message || '')}</td></tr>`;
    }
}

function updateStatSummaryCards(reviews, meta) {
    const totalEl = document.getElementById("statTotalReviews");
    const avgEl = document.getElementById("statAvgRating");
    const star5El = document.getElementById("stat5StarReviews");
    const lowEl = document.getElementById("statLowRatingReviews");

    if (totalEl) totalEl.textContent = meta.total || reviews.length;

    let totalRatingSum = 0;
    let star5Count = 0;
    let lowCount = 0;

    reviews.forEach(r => {
        const rating = Number(r.rating || 0);
        totalRatingSum += rating;

        if (rating === 5) star5Count++;
        else if (rating <= 2) lowCount++;
    });

    const avgRating = reviews.length > 0 ? (totalRatingSum / reviews.length).toFixed(1) : "0.0";

    if (avgEl) avgEl.innerHTML = `${avgRating} <small class="fs-6 text-muted">/ 5</small>`;
    if (star5El) star5El.textContent = star5Count;
    if (lowEl) lowEl.textContent = lowCount;
}

function renderReviewsTable(tbody, reviews) {
    if (!Array.isArray(reviews) || reviews.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center py-5 text-muted">No player reviews found matching criteria.</td></tr>`;
        return;
    }

    tbody.innerHTML = reviews.map(review => {
        const userName = review.user?.name || "Player";
        const userEmail = review.user?.email || "";
        const courtName = review.court?.name || "Court Venue";
        const city = review.court?.city || "";
        const rating = Number(review.rating || 5);
        const comment = review.review || review.comment || "No feedback text provided.";
        const dateStr = formatDateReadable(review.created_at);

        return `
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                            ${escapeHtml(userName.charAt(0).toUpperCase())}
                        </div>
                        <div>
                            <strong class="text-dark d-block small">${escapeHtml(userName)}</strong>
                            <small class="text-muted fs-8">${escapeHtml(userEmail)}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <strong class="text-dark d-block small">${escapeHtml(courtName)}</strong>
                    <small class="text-muted fs-8"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(city)}</small>
                </td>
                <td>
                    ${renderStarRating(rating)}
                </td>
                <td>
                    <p class="text-dark small mb-0 text-truncate" style="max-width: 340px;" title="${escapeHtml(comment)}">
                        "${escapeHtml(comment)}"
                    </p>
                </td>
                <td>
                    <small class="text-muted">${escapeHtml(dateStr)}</small>
                </td>
                <td class="text-end pe-4">
                    <div class="d-inline-flex gap-1">
                        <button type="button" class="btn btn-sm btn-light border text-dark rounded-circle btn-inspect-review" data-id="${review.id}" title="Inspect Feedback">
                            <i class="bi bi-eye"></i>
                        </button>

                        <button type="button" class="btn btn-sm btn-outline-danger rounded-circle btn-delete-review" data-id="${review.id}" data-user="${escapeHtml(userName)}" data-court="${escapeHtml(courtName)}" title="Delete Review">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join("");

    attachTableEventListeners();
}

function attachTableEventListeners() {
    // Inspect Review
    document.querySelectorAll(".btn-inspect-review").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            inspectReview(id);
        });
    });

    // Delete Review
    document.querySelectorAll(".btn-delete-review").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.getAttribute("data-id");
            const userName = btn.getAttribute("data-user");
            const courtName = btn.getAttribute("data-court");
            openDeleteReviewModal(id, userName, courtName);
        });
    });
}

function renderStarRating(rating) {
    let starsHtml = "";
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            starsHtml += `<i class="bi bi-star-fill text-warning me-0.5"></i>`;
        } else {
            starsHtml += `<i class="bi bi-star text-muted opacity-50 me-0.5"></i>`;
        }
    }
    return `<div>${starsHtml} <strong class="text-dark fs-8 ms-1">${rating}.0</strong></div>`;
}

/**
 * Inspect Review Details Modal
 */
function inspectReview(reviewId) {
    const review = currentReviewsList.find(r => String(r.id) === String(reviewId));
    if (!review) return;

    const modalBody = document.getElementById("inspectReviewModalBody");
    const modalFooter = document.getElementById("inspectReviewModalFooter");
    const userName = review.user?.name || "N/A";
    const userEmail = review.user?.email || "";
    const courtName = review.court?.name || "Court Venue";
    const courtCity = review.court?.city || "";
    const rating = Number(review.rating || 5);

    if (modalBody) {
        modalBody.innerHTML = `
            <div class="row g-4">
                <div class="col-md-6">
                    <small class="text-muted d-block fs-8 fw-bold text-uppercase mb-1">REVIEWER PLAYER</small>
                    <strong class="text-dark fs-6 d-block">${escapeHtml(userName)}</strong>
                    <span class="text-muted fs-8">${escapeHtml(userEmail)}</span>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8 fw-bold text-uppercase mb-1">TARGET COURT VENUE</small>
                    <strong class="text-dark fs-6 d-block">${escapeHtml(courtName)}</strong>
                    <span class="text-muted fs-8"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(courtCity)}</span>
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8 fw-bold text-uppercase mb-1">STAR RATING SCORE</small>
                    ${renderStarRating(rating)}
                </div>

                <div class="col-md-6">
                    <small class="text-muted d-block fs-8 fw-bold text-uppercase mb-1">SUBMISSION DATE</small>
                    <span class="text-dark fw-semibold">${formatDateReadable(review.created_at)}</span>
                </div>

                <div class="col-12">
                    <small class="text-muted d-block fs-8 fw-bold text-uppercase mb-1">PLAYER FEEDBACK COMMENT</small>
                    <div class="p-3 bg-light rounded-3 border text-dark fs-6">
                        "${escapeHtml(review.review || review.comment || 'No feedback comment provided.')}"
                    </div>
                </div>
            </div>
        `;
    }

    if (modalFooter) {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-danger rounded-pill px-4 btn-modal-delete-review" data-id="${review.id}" data-user="${escapeHtml(userName)}" data-court="${escapeHtml(courtName)}">
                <i class="bi bi-trash me-1"></i> Delete Review
            </button>
        `;

        modalFooter.querySelector(".btn-modal-delete-review")?.addEventListener("click", (e) => {
            const btn = e.currentTarget;
            bootstrap.Modal.getInstance(document.getElementById("inspectReviewModal"))?.hide();
            openDeleteReviewModal(btn.getAttribute("data-id"), btn.getAttribute("data-user"), btn.getAttribute("data-court"));
        });
    }

    const modal = new bootstrap.Modal(document.getElementById("inspectReviewModal"));
    modal.show();
}

/**
 * Open Delete Review Modal
 */
function openDeleteReviewModal(reviewId, userName, courtName) {
    targetDeleteReviewId = reviewId;

    const promptEl = document.getElementById("deleteReviewPrompt");
    if (promptEl) {
        promptEl.innerHTML = `Are you sure you want to delete the review by <strong>"${escapeHtml(userName)}"</strong> for <strong>"${escapeHtml(courtName)}"</strong>?`;
    }

    const modal = new bootstrap.Modal(document.getElementById("deleteReviewModal"));
    modal.show();
}

/**
 * Setup Delete Form Handler
 */
function setupDeleteForm() {
    const form = document.getElementById("deleteReviewForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        if (!targetDeleteReviewId) return;

        const submitBtn = document.getElementById("submitDeleteReviewBtn");
        if (submitBtn) submitBtn.disabled = true;

        try {
            const response = await apiFetch(`/admin/reviews/${targetDeleteReviewId}`, {
                method: "DELETE"
            });

            if (response && response.success) {
                bootstrap.Modal.getInstance(document.getElementById("deleteReviewModal"))?.hide();
                loadReviews();
            }
        } catch (error) {
            console.error("Delete Review Error:", error);
            alert(error.message || "Failed to delete review.");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

function renderPagination(meta) {
    const container = document.getElementById("paginationContainer");
    if (!container) return;

    const current = meta.current_page || 1;
    const last = meta.last_page || 1;

    if (last <= 1) {
        container.innerHTML = `<small class="text-muted">Showing all ${meta.total || 0} reviews</small>`;
        return;
    }

    container.innerHTML = `
        <small class="text-muted">Page ${current} of ${last} (${meta.total} total reviews)</small>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary ${current <= 1 ? 'disabled' : ''}" id="prevPageBtn">Previous</button>
            <button class="btn btn-sm btn-outline-secondary ${current >= last ? 'disabled' : ''}" id="nextPageBtn">Next</button>
        </div>
    `;

    document.getElementById("prevPageBtn")?.addEventListener("click", () => {
        if (currentPage > 1) {
            currentPage--;
            loadReviews();
        }
    });

    document.getElementById("nextPageBtn")?.addEventListener("click", () => {
        if (currentPage < last) {
            currentPage++;
            loadReviews();
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
