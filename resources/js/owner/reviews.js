import { apiFetch } from "../api.js";

let allCourts = [];
let allReviews = [];
let summaryData = {};
let activeRatingFilter = "";

document.addEventListener("DOMContentLoaded", () => {
    initReviewsPage();
});

async function initReviewsPage() {
    await loadOwnerCourts();
    await loadOwnerReviews();
    setupFilterListeners();
}

/**
 * Load Owner Courts for Dropdown
 */
async function loadOwnerCourts() {
    try {
        const response = await apiFetch("/owner/courts");
        if (response && response.success) {
            allCourts = response.data || [];
            const filterSelect = document.getElementById("filterCourtReviewSelect");
            if (filterSelect) {
                const options = allCourts.map(c => `<option value="${c.id}">${escapeHtml(c.name)} (${escapeHtml(c.court_type)})</option>`).join("");
                filterSelect.innerHTML = `<option value="">All Court Venues (${allCourts.length})</option>` + options;
            }
        }
    } catch (error) {
        console.error("Load Courts Error:", error);
    }
}

/**
 * Load Owner Reviews
 */
async function loadOwnerReviews() {
    const grid = document.getElementById("ownerReviewsListGrid");
    if (!grid) return;

    try {
        const response = await apiFetch("/owner/reviews?per_page=100");
        if (response && response.success) {
            allReviews = response.data || [];
            summaryData = response.summary || {};
            renderCourtWiseCards(summaryData.court_summaries || []);
            renderSummarySection();
            applyFiltersAndRender();
        }
    } catch (error) {
        console.error("Load Reviews Error:", error);
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="owner-card p-4 text-danger">
                    <i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>
                    Failed to load reviews. Please try refreshing.
                </div>
            </div>
        `;
    }
}

function renderCourtWiseCards(courtSummaries) {
    const container = document.getElementById("courtWiseRatingCards");
    if (!container) return;

    if (!Array.isArray(courtSummaries) || courtSummaries.length === 0) {
        container.innerHTML = "";
        return;
    }

    container.innerHTML = courtSummaries.map(c => `
        <div class="col-md-4 col-sm-6 col-12">
            <div class="owner-card p-3 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <strong class="text-dark d-block text-truncate pe-2">${escapeHtml(c.court_name)}</strong>
                        <span class="badge bg-light text-dark border fs-8">${escapeHtml(c.court_type || 'Court')}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="fs-4 fw-bold text-warning">${Number(c.average_rating || 0).toFixed(1)} ★</span>
                        <small class="text-muted">(${c.total_reviews} reviews)</small>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold" onclick="filterByCourt(${c.court_id})">
                    <i class="bi bi-filter me-1"></i> Filter Venue Reviews
                </button>
            </div>
        </div>
    `).join("");
}

function renderSummarySection(selectedCourtId = "") {
    const avgScoreEl = document.getElementById("reviewAvgScore");
    const avgStarsEl = document.getElementById("reviewAvgStars");
    const totalCountEl = document.getElementById("reviewTotalCount");

    let reviewsToCalculate = allReviews;
    if (selectedCourtId) {
        reviewsToCalculate = allReviews.filter(r => String(r.court?.id || r.court_id) === String(selectedCourtId));
    }

    const totalCount = reviewsToCalculate.length;
    const sumRating = reviewsToCalculate.reduce((acc, r) => acc + Number(r.rating || 0), 0);
    const avgScore = totalCount > 0 ? (sumRating / totalCount).toFixed(1) : "0.0";

    const starCounts = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
    reviewsToCalculate.forEach(r => {
        const rating = Number(r.rating || 0);
        if (starCounts[rating] !== undefined) starCounts[rating]++;
    });

    if (avgScoreEl) avgScoreEl.textContent = avgScore;
    if (totalCountEl) totalCountEl.textContent = totalCount;
    if (avgStarsEl) avgStarsEl.innerHTML = renderStarIcons(Math.round(Number(avgScore)));

    // Render 5-Star Breakdown Bars
    for (let star = 5; star >= 1; star--) {
        const count = Number(starCounts[star] || 0);
        const percent = totalCount > 0 ? Math.round((count / totalCount) * 100) : 0;

        const bar = document.getElementById(`barStar${star}`);
        const countEl = document.getElementById(`countStar${star}`);

        if (bar) bar.style.width = `${percent}%`;
        if (countEl) countEl.textContent = `${count} (${percent}%)`;
    }
}

window.filterByCourt = function (courtId) {
    const courtSelect = document.getElementById("filterCourtReviewSelect");
    if (courtSelect) {
        courtSelect.value = courtId;
        applyFiltersAndRender();
    }
};

function setupFilterListeners() {
    const searchInput = document.getElementById("searchReviewInput");
    const courtSelect = document.getElementById("filterCourtReviewSelect");
    const resetBtn = document.getElementById("resetReviewFiltersBtn");
    const starTabs = document.querySelectorAll("#ratingStarTabs button");

    if (searchInput) searchInput.addEventListener("input", applyFiltersAndRender);
    if (courtSelect) courtSelect.addEventListener("change", applyFiltersAndRender);

    if (starTabs) {
        starTabs.forEach(tab => {
            tab.addEventListener("click", () => {
                starTabs.forEach(t => t.classList.remove("active"));
                tab.classList.add("active");
                activeRatingFilter = tab.getAttribute("data-rating") || "";
                applyFiltersAndRender();
            });
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener("click", () => {
            if (searchInput) searchInput.value = "";
            if (courtSelect) courtSelect.value = "";
            activeRatingFilter = "";
            starTabs.forEach(t => t.classList.remove("active"));
            starTabs[0]?.classList.add("active");
            applyFiltersAndRender();
        });
    }
}

function applyFiltersAndRender() {
    const searchVal = (document.getElementById("searchReviewInput")?.value || "").toLowerCase().trim();
    const courtId = document.getElementById("filterCourtReviewSelect")?.value;

    renderSummarySection(courtId);

    let filtered = [...allReviews];

    if (searchVal) {
        filtered = filtered.filter(r => {
            const name = (r.user?.name || "").toLowerCase();
            const comment = (r.review || "").toLowerCase();
            const courtName = (r.court?.name || "").toLowerCase();
            return name.includes(searchVal) || comment.includes(searchVal) || courtName.includes(searchVal);
        });
    }

    if (courtId) {
        filtered = filtered.filter(r => String(r.court?.id || r.court_id) === String(courtId));
    }

    if (activeRatingFilter) {
        filtered = filtered.filter(r => String(r.rating) === String(activeRatingFilter));
    }

    renderReviewsGrid(filtered);
}

function renderReviewsGrid(reviews) {
    const grid = document.getElementById("ownerReviewsListGrid");
    if (!grid) return;

    if (!Array.isArray(reviews) || reviews.length === 0) {
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="owner-card p-5">
                    <i class="bi bi-star text-muted fs-1 d-block mb-3"></i>
                    <h5 class="fw-bold text-dark">No Reviews Found</h5>
                    <p class="text-muted small mb-0">No player reviews match your active search or filters.</p>
                </div>
            </div>
        `;
        return;
    }

    grid.innerHTML = reviews.map(r => {
        const userName = r.user?.name || "Player";
        const initial = userName.charAt(0).toUpperCase();
        const courtName = r.court?.name || "Court Venue";
        const rating = Number(r.rating || 5);
        const reviewText = r.review || "No written review provided.";
        const dateStr = formatDateReadable(r.created_at);

        return `
            <div class="col-12">
                <div class="owner-card p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        
                        <!-- PLAYER INFO -->
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle fw-bold d-flex align-items-center justify-content-center border border-success border-opacity-25" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                ${escapeHtml(initial)}
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">${escapeHtml(userName)}</h6>
                                <small class="text-muted fs-8">${escapeHtml(r.user?.email || '')}</small>
                            </div>
                        </div>

                        <!-- COURT & RATING METRICS -->
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                                <i class="bi bi-geo-alt me-1 text-danger"></i> ${escapeHtml(courtName)}
                            </span>
                            <div class="text-warning fs-6">
                                ${renderStarIcons(rating)}
                            </div>
                            <small class="text-muted fs-8">${escapeHtml(dateStr)}</small>
                        </div>

                    </div>

                    <!-- REVIEW COMMENT TEXT -->
                    <div class="bg-light p-3 rounded-3 text-dark">
                        <i class="bi bi-quote fs-4 text-muted d-inline-block me-1"></i>
                        <span>${escapeHtml(reviewText)}</span>
                    </div>

                </div>
            </div>
        `;
    }).join("");
}

function renderStarIcons(rating) {
    let starsHtml = "";
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            starsHtml += `<i class="bi bi-star-fill text-warning me-1"></i>`;
        } else {
            starsHtml += `<i class="bi bi-star text-muted me-1"></i>`;
        }
    }
    return starsHtml;
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
