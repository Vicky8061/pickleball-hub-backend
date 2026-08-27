const API_BASE_URL = "/api";

const courtId = window.COURT_ID;


/* =========================================
   DOM ELEMENTS
========================================= */

const courtDetailsLoading =
    document.getElementById("courtDetailsLoading");

const courtDetailsError =
    document.getElementById("courtDetailsError");

const courtDetailsErrorMessage =
    document.getElementById("courtDetailsErrorMessage");

const courtDetails =
    document.getElementById("courtDetails");

const retryCourtDetails =
    document.getElementById("retryCourtDetails");

const courtMainImage =
    document.getElementById("courtMainImage");

const courtImagePlaceholder =
    document.getElementById("courtImagePlaceholder");

const courtImageCounter =
    document.getElementById("courtImageCounter");

const courtImageThumbnails =
    document.getElementById("courtImageThumbnails");

const courtImagePrevious =
    document.getElementById("courtImagePrevious");

const courtImageNext =
    document.getElementById("courtImageNext");

const courtTypeBadge =
    document.getElementById("courtTypeBadge");

const courtName =
    document.getElementById("courtName");

const courtAddress =
    document.getElementById("courtAddress");

const courtPrice =
    document.getElementById("courtPrice");

const courtDescription =
    document.getElementById("courtDescription");

const courtOpeningTime =
    document.getElementById("courtOpeningTime");

const courtClosingTime =
    document.getElementById("courtClosingTime");

const courtLatitude =
    document.getElementById("courtLatitude");

const courtLongitude =
    document.getElementById("courtLongitude");

const bookCourtBtn =
    document.getElementById("bookCourtBtn");


/* =========================================
   TOKEN
========================================= */

function getToken() {

    return (
        localStorage.getItem("auth_token") ||
        localStorage.getItem("token") ||
        sessionStorage.getItem("auth_token") ||
        sessionStorage.getItem("token")
    );

}


/* =========================================
   HEADERS
========================================= */

function getHeaders() {

    const token = getToken();

    return {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`
    };

}


/* =========================================
   LOAD COURT
========================================= */

async function loadCourtDetails() {

    showLoading();
    hideError();

    try {

        if (!courtId) {

            throw new Error(
                "Court ID is missing."
            );

        }


        /*
         * IMPORTANT:
         * Change this URL only if your
         * Court Details API uses another route.
         */

        const result = await apiFetch(`/courts/${courtId}`);


        /*
         * Supports both:
         *
         * data: {}
         *
         * and
         *
         * data: {
         *     data: {}
         * }
         */

        const court =
            result.data?.data ||
            result.data;


        if (!court) {

            throw new Error(
                "Court details not found."
            );

        }


        renderCourtDetails(court);

        hideLoading();

        courtDetails.classList.remove("d-none");

    }
    catch (error) {

        console.error(
            "Court Details API Error:",
            error
        );

        hideLoading();

        showError(
            error.message ||
            "Something went wrong while loading court details."
        );

    }

}


/* =========================================
   RENDER COURT
========================================= */

function renderCourtDetails(court) {

    courtTypeBadge.textContent =
        court.court_type ||
        "Court";


    courtName.textContent =
        court.name ||
        court.court_name ||
        "Pickleball Court";


    courtAddress.textContent =
        court.address ||
        "Location unavailable";


    courtPrice.textContent =
        `₹${formatPrice(court.price_per_hour)}`;


    courtDescription.textContent =
        court.description ||
        court.discription ||
        "No description available.";


    courtOpeningTime.textContent =
        formatTime(
            court.opening_time
        );


    courtClosingTime.textContent =
        formatTime(
            court.closing_time
        );


    courtLatitude.textContent =
        court.latitude ??
        "--";


    courtLongitude.textContent =
        court.longitude ??
        "--";


    renderImages(court.images || []);

}


/* =========================================
   IMAGES
========================================= */

let courtImages = [];

let currentImageIndex = 0;


function renderImages(images) {

    courtImages =
        Array.isArray(images)
            ? images.filter(image =>
                image &&
                (
                    image.image_url ||
                    image.image
                )
            )
            : [];


    currentImageIndex = 0;


    courtImageThumbnails.innerHTML = "";


    if (!courtImages.length) {

        courtMainImage.classList.add(
            "d-none"
        );

        courtImagePlaceholder.classList.remove(
            "d-none"
        );

        courtImageCounter.textContent =
            "0 / 0";

        courtImagePrevious.classList.add(
            "d-none"
        );

        courtImageNext.classList.add(
            "d-none"
        );

        return;

    }


    courtMainImage.classList.remove(
        "d-none"
    );

    courtImagePlaceholder.classList.add(
        "d-none"
    );


    courtImagePrevious.classList.toggle(
        "d-none",
        courtImages.length <= 1
    );


    courtImageNext.classList.toggle(
        "d-none",
        courtImages.length <= 1
    );


    courtImages.forEach(
        (image, index) => {

            const imageUrl =
                image.image_url ||
                image.image;


            const thumbnail =
                document.createElement("button");


            thumbnail.type =
                "button";


            thumbnail.className =
                "court-thumbnail";


            if (index === 0) {

                thumbnail.classList.add(
                    "active"
                );

            }


            thumbnail.innerHTML = `
                <img
                    src="${escapeAttribute(imageUrl)}"
                    alt="Court image ${index + 1}"
                >
            `;


            thumbnail.addEventListener(
                "click",
                () => {

                    showImage(index);

                }
            );


            courtImageThumbnails.appendChild(
                thumbnail
            );

        }
    );


    showImage(0);

}


/* =========================================
   SHOW IMAGE
========================================= */

function showImage(index) {

    if (!courtImages.length) {
        return;
    }


    currentImageIndex =
        (
            index +
            courtImages.length
        ) %
        courtImages.length;


    const image =
        courtImages[currentImageIndex];


    const imageUrl =
        image.image_url ||
        image.image;


    courtMainImage.src =
        imageUrl;


    courtMainImage.alt =
        "Court image";


    courtImageCounter.textContent =
        `${currentImageIndex + 1} / ${courtImages.length}`;


    const thumbnails =
        courtImageThumbnails.querySelectorAll(
            ".court-thumbnail"
        );


    thumbnails.forEach(
        (thumbnail, index) => {

            thumbnail.classList.toggle(
                "active",
                index === currentImageIndex
            );

        }
    );

}


/* =========================================
   PREVIOUS
========================================= */

courtImagePrevious.addEventListener(
    "click",
    () => {

        showImage(
            currentImageIndex - 1
        );

    }
);


/* =========================================
   NEXT
========================================= */

courtImageNext.addEventListener(
    "click",
    () => {

        showImage(
            currentImageIndex + 1
        );

    }
);


/* =========================================
   BOOK COURT
========================================= */

bookCourtBtn.addEventListener(
    "click",
    () => {

        window.location.href =
            `/user/courts/${courtId}/book`;

    }
);


/* =========================================
   RETRY
========================================= */

retryCourtDetails.addEventListener(
    "click",
    () => {

        loadCourtDetails();

    }
);


/* =========================================
   LOADING
========================================= */

function showLoading() {

    courtDetailsLoading.classList.remove(
        "d-none"
    );

    courtDetails.classList.add(
        "d-none"
    );

}


function hideLoading() {

    courtDetailsLoading.classList.add(
        "d-none"
    );

}


/* =========================================
   ERROR
========================================= */

function showError(message) {

    courtDetails.classList.add(
        "d-none"
    );

    courtDetailsError.classList.remove(
        "d-none"
    );

    courtDetailsErrorMessage.textContent =
        message;

}


function hideError() {

    courtDetailsError.classList.add(
        "d-none"
    );

}


/* =========================================
   PRICE
========================================= */

function formatPrice(price) {

    const number =
        Number(price);


    if (Number.isNaN(number)) {

        return "0";

    }


    return number.toLocaleString(
        "en-IN"
    );

}



/* =========================================
   TIME
========================================= */

function formatTime(time) {

    if (!time) {

        return "--";

    }


    const parts =
        String(time).split(":");


    if (parts.length < 2) {

        return time;

    }


    let hour =
        parseInt(parts[0], 10);

    const minute =
        parts[1];


    const period =
        hour >= 12
            ? "PM"
            : "AM";


    hour =
        hour % 12 ||
        12;


    return `${hour}:${minute} ${period}`;

}


/* =========================================
   ESCAPE ATTRIBUTE
========================================= */

function escapeAttribute(value) {

    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

}


/* =========================================
   REVIEWS & STAR RATING LOGIC
========================================= */

let currentRatingValue = 0;
let userReviews = [];

function initializeReviews() {
    initializeStarRatingInput();
    initializeReviewForm();
    loadCourtReviews();
}

function initializeStarRatingInput() {
    const container = document.getElementById("starRatingInput");
    const ratingInput = document.getElementById("reviewRatingValue");
    if (!container || !ratingInput) return;

    const stars = container.querySelectorAll(".star-btn");

    stars.forEach((star) => {
        star.addEventListener("mouseenter", () => {
            const val = parseInt(star.getAttribute("data-value"), 10);
            highlightStars(stars, val, "hover");
        });

        star.addEventListener("mouseleave", () => {
            highlightStars(stars, currentRatingValue, "active");
        });

        star.addEventListener("click", () => {
            currentRatingValue = parseInt(star.getAttribute("data-value"), 10);
            ratingInput.value = currentRatingValue;
            highlightStars(stars, currentRatingValue, "active");
        });
    });
}

function highlightStars(stars, count, className) {
    stars.forEach((s) => {
        const sVal = parseInt(s.getAttribute("data-value"), 10);
        s.classList.remove("hover", "active");
        if (sVal <= count) {
            s.classList.add(className);
            s.classList.replace("bi-star", "bi-star-fill");
            s.classList.replace("text-secondary", "text-warning");
        } else {
            s.classList.replace("bi-star-fill", "bi-star");
            s.classList.replace("text-warning", "text-secondary");
        }
    });
}

async function loadCourtReviews() {
    const reviewsList = document.getElementById("courtReviewsList");
    if (!reviewsList || !courtId) return;

    try {
        const response = await apiFetch(`/courts/${courtId}/reviews`);
        userReviews = response?.data?.data || response?.data || [];
        renderReviews(userReviews);
    } catch (err) {
        console.error("Error loading reviews:", err);
        reviewsList.innerHTML = `<div class="alert alert-light text-muted text-center rounded-3">Unable to load reviews.</div>`;
    }
}

function renderReviews(reviews) {
    const reviewsList = document.getElementById("courtReviewsList");
    const avgRatingText = document.getElementById("averageRatingText");
    const avgRatingStars = document.getElementById("averageRatingStars");
    const totalCountText = document.getElementById("totalReviewsCountText");

    if (!reviewsList) return;

    const total = reviews.length;
    let sumRating = 0;
    const counts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };

    reviews.forEach((r) => {
        const rVal = Math.min(5, Math.max(1, parseInt(r.rating || 0, 10)));
        sumRating += rVal;
        counts[rVal] = (counts[rVal] || 0) + 1;
    });

    const avg = total > 0 ? (sumRating / total).toFixed(1) : "0.0";

    if (avgRatingText) avgRatingText.textContent = avg;
    if (totalCountText) totalCountText.textContent = `Based on ${total} review${total === 1 ? "" : "s"}`;

    if (avgRatingStars) {
        let starsHtml = "";
        const numericAvg = parseFloat(avg);
        for (let i = 1; i <= 5; i++) {
            if (i <= Math.floor(numericAvg)) {
                starsHtml += '<i class="bi bi-star-fill text-warning"></i>';
            } else if (i - 0.5 <= numericAvg) {
                starsHtml += '<i class="bi bi-star-half text-warning"></i>';
            } else {
                starsHtml += '<i class="bi bi-star text-warning"></i>';
            }
        }
        avgRatingStars.innerHTML = starsHtml;
    }

    for (let i = 1; i <= 5; i++) {
        const barEl = document.getElementById(`barStar${i}`);
        const countEl = document.getElementById(`countStar${i}`);
        const pct = total > 0 ? Math.round((counts[i] / total) * 100) : 0;
        if (barEl) barEl.style.width = `${pct}%`;
        if (countEl) countEl.textContent = counts[i];
    }

    if (total === 0) {
        reviewsList.innerHTML = `<div class="card border-0 shadow-sm rounded-4 p-4 text-center text-muted bg-white">No reviews yet for this court. Be the first to share your experience!</div>`;
        return;
    }

    // Current Auth User Check for Edit/Delete
    let currentUserId = null;
    try {
        const userStr = localStorage.getItem("auth_user") || sessionStorage.getItem("auth_user");
        if (userStr) {
            const u = JSON.parse(userStr);
            currentUserId = u.id || u.user_id;
        }
    } catch (e) {}

    let cardsHtml = "";
    reviews.forEach((rev) => {
        const authorName = rev.user?.name || "Verified Player";
        const authorInitial = authorName.charAt(0).toUpperCase();
        const ratingVal = parseInt(rev.rating || 5, 10);
        const reviewText = rev.review || "";
        const formattedDate = rev.created_at ? new Date(rev.created_at).toLocaleDateString("en-IN", { month: "short", day: "numeric", year: "numeric" }) : "";
        const isAuthor = currentUserId && (rev.user_id == currentUserId || rev.user?.id == currentUserId);

        let starsHtml = "";
        for (let s = 1; s <= 5; s++) {
            starsHtml += s <= ratingVal ? '<i class="bi bi-star-fill text-warning me-1"></i>' : '<i class="bi bi-star text-secondary me-1"></i>';
        }

        cardsHtml += `
            <div class="card border-0 shadow-sm review-card p-3 bg-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="user-avatar-circle">${escapeHTML(authorInitial)}</div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">${escapeHTML(authorName)}</h6>
                            <small class="text-muted">${escapeHTML(formattedDate)}</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div>${starsHtml}</div>
                        ${isAuthor ? `
                            <div class="dropdown ms-2">
                                <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button class="dropdown-menu-item dropdown-item btn-edit-review" data-id="${rev.id}" data-rating="${ratingVal}" data-review="${escapeAttribute(reviewText)}"><i class="bi bi-pencil me-2"></i>Edit</button></li>
                                    <li><button class="dropdown-menu-item dropdown-item text-danger btn-delete-review" data-id="${rev.id}"><i class="bi bi-trash me-2"></i>Delete</button></li>
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                </div>
                <p class="text-secondary mb-0 small mt-1">${escapeHTML(reviewText)}</p>
            </div>
        `;
    });

    reviewsList.innerHTML = cardsHtml;

    // Attach Edit / Delete Listeners
    reviewsList.querySelectorAll(".btn-edit-review").forEach((btn) => {
        btn.addEventListener("click", () => {
            const revId = btn.getAttribute("data-id");
            const rating = parseInt(btn.getAttribute("data-rating"), 10);
            const text = btn.getAttribute("data-review");
            setupEditReviewForm(revId, rating, text);
        });
    });

    reviewsList.querySelectorAll(".btn-delete-review").forEach((btn) => {
        btn.addEventListener("click", async () => {
            const revId = btn.getAttribute("data-id");
            if (confirm("Are you sure you want to delete your review?")) {
                await deleteReview(revId);
            }
        });
    });
}

function initializeReviewForm() {
    const reviewForm = document.getElementById("reviewForm");
    const cancelBtn = document.getElementById("cancelEditReviewBtn");

    if (!reviewForm) return;

    reviewForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        await submitReviewForm();
    });

    if (cancelBtn) {
        cancelBtn.addEventListener("click", resetReviewForm);
    }
}

function setupEditReviewForm(reviewId, rating, text) {
    const editIdInput = document.getElementById("editReviewId");
    const reviewTextInput = document.getElementById("reviewTextInput");
    const formTitle = document.getElementById("reviewFormTitle");
    const submitBtn = document.getElementById("submitReviewBtn");
    const cancelBtn = document.getElementById("cancelEditReviewBtn");
    const container = document.getElementById("starRatingInput");

    if (editIdInput) editIdInput.value = reviewId;
    if (reviewTextInput) reviewTextInput.value = text;
    if (formTitle) formTitle.textContent = "Edit Your Review";
    if (submitBtn) submitBtn.innerHTML = `<i class="bi bi-check2 me-1"></i> Update Review`;
    if (cancelBtn) cancelBtn.classList.remove("d-none");

    currentRatingValue = rating;
    const ratingInput = document.getElementById("reviewRatingValue");
    if (ratingInput) ratingInput.value = rating;

    if (container) {
        const stars = container.querySelectorAll(".star-btn");
        highlightStars(stars, rating, "active");
    }

    document.getElementById("writeReviewCard")?.scrollIntoView({ behavior: "smooth" });
}

function resetReviewForm() {
    const editIdInput = document.getElementById("editReviewId");
    const reviewTextInput = document.getElementById("reviewTextInput");
    const formTitle = document.getElementById("reviewFormTitle");
    const submitBtn = document.getElementById("submitReviewBtn");
    const cancelBtn = document.getElementById("cancelEditReviewBtn");
    const alertBox = document.getElementById("reviewAlert");
    const container = document.getElementById("starRatingInput");
    const ratingInput = document.getElementById("reviewRatingValue");

    if (editIdInput) editIdInput.value = "";
    if (reviewTextInput) reviewTextInput.value = "";
    if (formTitle) formTitle.textContent = "Write a Review";
    if (submitBtn) submitBtn.innerHTML = `<i class="bi bi-send me-1"></i> Submit Review`;
    if (cancelBtn) cancelBtn.classList.add("d-none");
    if (alertBox) alertBox.classList.add("d-none");

    currentRatingValue = 0;
    if (ratingInput) ratingInput.value = 0;
    if (container) {
        const stars = container.querySelectorAll(".star-btn");
        highlightStars(stars, 0, "active");
    }
}

async function submitReviewForm() {
    const editId = document.getElementById("editReviewId")?.value;
    const rating = parseInt(document.getElementById("reviewRatingValue")?.value || "0", 10);
    const reviewText = document.getElementById("reviewTextInput")?.value?.trim();
    const alertBox = document.getElementById("reviewAlert");
    const submitBtn = document.getElementById("submitReviewBtn");

    if (!rating || rating === 0) {
        showReviewAlert("Please select a star rating (1-5 stars).", "danger");
        return;
    }

    if (!reviewText) {
        showReviewAlert("Please write a short review.", "danger");
        return;
    }

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Submitting...`;
    }

    try {
        if (editId) {
            await apiFetch(`/reviews/${editId}`, {
                method: "PUT",
                body: JSON.stringify({ rating, review: reviewText }),
            });
            showReviewAlert("Review updated successfully!", "success");
        } else {
            await apiFetch(`/reviews`, {
                method: "POST",
                body: JSON.stringify({ court_id: courtId, rating, review: reviewText }),
            });
            showReviewAlert("Review submitted successfully!", "success");
        }

        resetReviewForm();
        await loadCourtReviews();
    } catch (err) {
        console.error("Submit review error:", err);
        showReviewAlert(err.message || "Unable to submit review.", "danger");
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = editId ? `<i class="bi bi-check2 me-1"></i> Update Review` : `<i class="bi bi-send me-1"></i> Submit Review`;
        }
    }
}

async function deleteReview(reviewId) {
    try {
        await apiFetch(`/reviews/${reviewId}`, { method: "DELETE" });
        showReviewAlert("Review deleted successfully.", "success");
        resetReviewForm();
        await loadCourtReviews();
    } catch (err) {
        console.error("Delete review error:", err);
        showReviewAlert(err.message || "Unable to delete review.", "danger");
    }
}

function showReviewAlert(msg, type = "danger") {
    const alertBox = document.getElementById("reviewAlert");
    if (!alertBox) return;
    alertBox.className = `alert alert-${type} alert-dismissible fade show mb-3`;
    alertBox.innerHTML = `${msg} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
}


/* =========================================
   INITIAL LOAD
========================================= */

document.addEventListener("DOMContentLoaded", () => {
    loadCourtDetails();
    initializeReviews();
});