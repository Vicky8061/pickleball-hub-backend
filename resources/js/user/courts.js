const API_BASE_URL = "/api";

let currentPage = 1;


/* =====================================================
   WISHLIST STATE
===================================================== */

let wishlistCourtIds = new Set();


/* =====================================================
   DOM ELEMENTS
===================================================== */

const courtsContainer = document.getElementById("courtsContainer");
const courtsEmpty = document.getElementById("courtsEmpty");
const courtsError = document.getElementById("courtsError");
const courtsErrorMessage = document.getElementById("courtsErrorMessage");
const courtsPagination = document.getElementById("courtsPagination");
const courtResultText = document.getElementById("courtResultText");

const courtSearch = document.getElementById("courtSearch");
const courtType = document.getElementById("courtType");
const courtCity = document.getElementById("courtCity");
const priceMin = document.getElementById("priceMin");
const priceMax = document.getElementById("priceMax");
const courtSort = document.getElementById("courtSort");

const applyFilters = document.getElementById("applyFilters");
const resetFilters = document.getElementById("resetFilters");
const retryCourts = document.getElementById("retryCourts");


/* =====================================================
   GET TOKEN
===================================================== */

function getToken() {

    return (
        localStorage.getItem("auth_token") ||
        localStorage.getItem("token") ||
        sessionStorage.getItem("auth_token") ||
        sessionStorage.getItem("token")
    );

}


/* =====================================================
   API HEADERS
===================================================== */

function getHeaders() {

    const token = getToken();

    const headers = {
        "Accept": "application/json",
        "Content-Type": "application/json"
    };

    if (token) {
        headers["Authorization"] = `Bearer ${token}`;
    }

    return headers;

}


/* =====================================================
   LOAD USER WISHLIST
===================================================== */

async function loadUserWishlist() {

    try {

        const token = getToken();

        if (!token) return;


        const response = await fetch(
            `${API_BASE_URL}/wishlists`,
            {
                method: "GET",
                headers: getHeaders()
            }
        );


        if (!response.ok) return;


        const result =
            await response.json();


        const wishlists =
            result?.data?.data ||
            result?.data ||
            [];


        if (Array.isArray(wishlists)) {

            wishlistCourtIds = new Set(
                wishlists
                    .map(w => {

                        const courtId =
                            w.courts?.id ||
                            w.court?.id ||
                            w.court_id;

                        return courtId
                            ? Number(courtId)
                            : null;

                    })
                    .filter(id => id !== null)
            );

        }

    } catch (error) {

        console.error(
            "Wishlist loading error:",
            error
        );

    }

}


/* =====================================================
   LOAD COURTS
===================================================== */

async function loadCourts(page = 1) {

    currentPage = page;

    showLoading();
    hideEmpty();
    hideError();

    const params = new URLSearchParams();


    /* -------------------------------------------------
       SEARCH
    ------------------------------------------------- */

    const search = courtSearch?.value.trim();

    if (search) {
        params.append("search", search);
    }


    /* -------------------------------------------------
       COURT TYPE
    ------------------------------------------------- */

    const type = courtType?.value;

    if (type) {
        params.append("court_type", type);
    }


    /* -------------------------------------------------
       CITY
    ------------------------------------------------- */

    const city = courtCity?.value.trim();

    if (city) {
        params.append("city", city);
    }


    /* -------------------------------------------------
       MIN PRICE
    ------------------------------------------------- */

    if (priceMin?.value !== "") {
        params.append("price_min", priceMin.value);
    }


    /* -------------------------------------------------
       MAX PRICE
    ------------------------------------------------- */

    if (priceMax?.value !== "") {
        params.append("price_max", priceMax.value);
    }


    /* -------------------------------------------------
       SORT
    ------------------------------------------------- */

    params.append(
        "sort",
        courtSort?.value || "latest"
    );


    /* -------------------------------------------------
       PAGE
    ------------------------------------------------- */

    params.append("page", page);


    try {

        const response = await fetch(
            `${API_BASE_URL}/courts?${params.toString()}`,
            {
                method: "GET",
                headers: getHeaders()
            }
        );


        let result;

        try {
            result = await response.json();
        } catch (jsonError) {

            throw new Error(
                `Invalid response from server. HTTP ${response.status}`
            );

        }


        /* -------------------------------------------------
           HTTP ERROR
        ------------------------------------------------- */

        if (!response.ok) {

            throw new Error(
                result.message ||
                `Unable to fetch courts. HTTP ${response.status}`
            );

        }


        /* -------------------------------------------------
           API SUCCESS CHECK
        ------------------------------------------------- */

        if (
            result.success !== undefined &&
            result.success === false
        ) {

            throw new Error(
                result.message ||
                "Unable to fetch courts."
            );

        }


        /* -------------------------------------------------
           GET COURTS
        ------------------------------------------------- */

        let courts = [];

        if (Array.isArray(result.data)) {

            courts = result.data;

        } else if (
            result.data &&
            Array.isArray(result.data.data)
        ) {

            courts = result.data.data;

        }


        /* -------------------------------------------------
           GET PAGINATION
        ------------------------------------------------- */

        let pagination = null;

        if (result.pagination) {

            pagination = result.pagination;

        } else if (
            result.data &&
            result.data.current_page
        ) {

            pagination = {
                current_page: result.data.current_page,
                last_page: result.data.last_page,
                per_page: result.data.per_page,
                total: result.data.total
            };

        }


        hideLoading();


        /* -------------------------------------------------
           NO COURTS
        ------------------------------------------------- */

        if (!courts.length) {

            showEmpty();

            updateResultText(0);

            renderPagination(null);

            return;

        }


        /* -------------------------------------------------
           RENDER COURTS
        ------------------------------------------------- */

        renderCourts(courts);


        /* -------------------------------------------------
           RESULT COUNT
        ------------------------------------------------- */

        updateResultText(
            pagination?.total ?? courts.length
        );


        /* -------------------------------------------------
           PAGINATION
        ------------------------------------------------- */

        renderPagination(pagination);

    } catch (error) {

        console.error(
            "Courts API Error:",
            error
        );

        hideLoading();

        showError(
            error.message ||
            "Something went wrong while loading courts."
        );

    }

}


/* =====================================================
   RENDER COURTS
===================================================== */

function renderCourts(courts) {

    courtsContainer.innerHTML = "";

    courts.forEach((court) => {

        const column = document.createElement("div");

        column.className = "col-xl-4 col-md-6";

        column.innerHTML = createCourtCard(court);

        courtsContainer.appendChild(column);

    });

}


/* =====================================================
   CREATE COURT CARD
===================================================== */

function createCourtCard(court) {

    const images = Array.isArray(court.images)
        ? court.images
        : [];


    /* -------------------------------------------------
       VALID IMAGES
    ------------------------------------------------- */

    const validImages = images.filter((image) => {

        return (
            image &&
            (
                image.image_url ||
                image.image
            )
        );

    });


    /* -------------------------------------------------
       IMAGE HTML
    ------------------------------------------------- */

    let imageHTML = "";


    if (validImages.length > 0) {

        imageHTML = `
            <div class="court-slider">

                ${validImages.map((image, index) => {

                    const imageUrl =
                        image.image_url ||
                        image.image;

                    return `
                        <div
                            class="court-slide ${index === 0 ? "active" : ""}"
                        >

                            <img
                                src="${escapeAttribute(imageUrl)}"
                                alt="${escapeAttribute(
                                    court.court_name ||
                                    court.name ||
                                    "Pickleball Court"
                                )}"
                                class="court-image"
                                loading="lazy"
                                onerror="this.style.display='none';"
                            >

                        </div>
                    `;

                }).join("")}

            </div>
        `;

    } else {

        imageHTML = `
            <div class="court-image-placeholder">

                <i class="bi bi-grid-3x3-gap"></i>

            </div>
        `;

    }


    /* -------------------------------------------------
       SLIDER CONTROLS
    ------------------------------------------------- */

    const sliderControls =
        validImages.length > 1
            ? `

                <button
                    type="button"
                    class="court-slider-btn court-prev"
                    data-action="previous"
                    aria-label="Previous image"
                >

                    <i class="bi bi-chevron-left"></i>

                </button>


                <button
                    type="button"
                    class="court-slider-btn court-next"
                    data-action="next"
                    aria-label="Next image"
                >

                    <i class="bi bi-chevron-right"></i>

                </button>


                <div class="court-slider-dots">

                    ${validImages.map(
                        (_, index) => `
                            <span
                                class="court-dot ${
                                    index === 0 ? "active" : ""
                                }"
                                data-slide="${index}"
                            ></span>
                        `
                    ).join("")}

                </div>

            `
            : "";


    /* -------------------------------------------------
       COURT DATA
    ------------------------------------------------- */

    const courtName =
        court.court_name ||
        court.name ||
        "Pickleball Court";


    const courtTypeValue =
        court.court_type ||
        "Court";


    const address =
        court.address ||
        "Location unavailable";


    const price =
        court.price_per_hour ??
        0;


    const isWishlisted =
        wishlistCourtIds.has(
            Number(court.id)
        );


    /* -------------------------------------------------
       FINAL CARD
    ------------------------------------------------- */

    return `

        <div
            class="court-card"
            data-court-id="${escapeAttribute(court.id)}"
        >


            <!-- IMAGE -->

            <div class="court-image-wrapper">

                ${imageHTML}


                <span class="court-type-badge">

                    ${escapeHTML(courtTypeValue)}

                </span>


                ${sliderControls}


                <!-- WISHLIST HEART -->

                <button
                    type="button"
                    class="wishlist-toggle-btn"
                    data-court-id="${escapeAttribute(court.id)}"
                    title="${isWishlisted ? 'Remove from wishlist' : 'Add to wishlist'}"
                    style="
                        position: absolute;
                        top: 0;
                        right: 0;
                        margin: 12px;
                        width: 38px;
                        height: 38px;
                        border: none;
                        border-radius: 50%;
                        background: rgba(255,255,255,0.9);
                        color: ${isWishlisted ? '#dc3545' : '#6c757d'};
                        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        z-index: 2;
                        transition: transform 0.2s, color 0.2s;
                        font-size: 18px;
                    "
                >

                    <i class="bi ${isWishlisted ? 'bi-heart-fill' : 'bi-heart'}"></i>

                </button>

            </div>


            <!-- BODY -->

            <div class="court-card-body">


                <h4 class="court-name">

                    ${escapeHTML(courtName)}

                </h4>


                <div class="court-location">

                    <i class="bi bi-geo-alt-fill"></i>

                    <span>

                        ${escapeHTML(address)}

                    </span>

                </div>


                <div class="court-card-footer">


                    <div class="court-price">

                        <strong>

                            ₹${formatPrice(price)}

                        </strong>

                        <span>

                            / hour

                        </span>

                    </div>


                    <a
                        href="/user/courts/${encodeURIComponent(court.id)}"
                        class="court-view-btn"
                    >

                        View Court

                        <i class="bi bi-arrow-right"></i>

                    </a>


                </div>

            </div>

        </div>

    `;

}


/* =====================================================
   PAGINATION
===================================================== */

function renderPagination(pagination) {

    if (
        !pagination ||
        !pagination.last_page ||
        pagination.last_page <= 1
    ) {

        courtsPagination.classList.add("d-none");

        courtsPagination.innerHTML = "";

        return;

    }


    courtsPagination.classList.remove("d-none");

    courtsPagination.innerHTML = "";


    const current =
        Number(pagination.current_page) || 1;

    const last =
        Number(pagination.last_page) || 1;


    /* -------------------------------------------------
       PREVIOUS
    ------------------------------------------------- */

    const previous =
        document.createElement("button");

    previous.type = "button";

    previous.className =
        "court-page-btn";

    previous.innerHTML =
        `<i class="bi bi-chevron-left"></i>`;

    previous.disabled =
        current <= 1;


    previous.addEventListener(
        "click",
        () => {

            if (current > 1) {
                loadCourts(current - 1);
            }

        }
    );


    courtsPagination.appendChild(previous);


    /* -------------------------------------------------
       PAGE NUMBERS
    ------------------------------------------------- */

    for (
        let page = 1;
        page <= last;
        page++
    ) {

        const button =
            document.createElement("button");

        button.type = "button";

        button.className =
            "court-page-btn";


        if (page === current) {

            button.classList.add("active");

        }


        button.textContent =
            page;


        button.addEventListener(
            "click",
            () => loadCourts(page)
        );


        courtsPagination.appendChild(button);

    }


    /* -------------------------------------------------
       NEXT
    ------------------------------------------------- */

    const next =
        document.createElement("button");

    next.type = "button";

    next.className =
        "court-page-btn";

    next.innerHTML =
        `<i class="bi bi-chevron-right"></i>`;

    next.disabled =
        current >= last;


    next.addEventListener(
        "click",
        () => {

            if (current < last) {
                loadCourts(current + 1);
            }

        }
    );


    courtsPagination.appendChild(next);

}


/* =====================================================
   APPLY FILTERS
===================================================== */

if (applyFilters) {

    applyFilters.addEventListener(
        "click",
        () => {

            currentPage = 1;

            loadCourts(1);

        }
    );

}


/* =====================================================
   RESET FILTERS
===================================================== */

if (resetFilters) {

    resetFilters.addEventListener(
        "click",
        () => {

            courtSearch.value = "";
            courtType.value = "";
            courtCity.value = "";
            priceMin.value = "";
            priceMax.value = "";
            courtSort.value = "latest";

            currentPage = 1;

            loadCourts(1);

        }
    );

}


/* =====================================================
   RETRY
===================================================== */

if (retryCourts) {

    retryCourts.addEventListener(
        "click",
        () => {

            loadCourts(currentPage);

        }
    );

}


/* =====================================================
   SEARCH WITH ENTER
===================================================== */

if (courtSearch) {

    courtSearch.addEventListener(
        "keydown",
        (event) => {

            if (event.key === "Enter") {

                event.preventDefault();

                loadCourts(1);

            }

        }
    );

}


/* =====================================================
   SLIDER
===================================================== */

document.addEventListener(
    "click",
    (event) => {

        const button =
            event.target.closest(
                ".court-slider-btn"
            );


        const dot =
            event.target.closest(
                ".court-dot"
            );


        if (!button && !dot) {
            return;
        }


        const card =
            event.target.closest(
                ".court-card"
            );


        if (!card) {
            return;
        }


        const slides =
            card.querySelectorAll(
                ".court-slide"
            );


        const dots =
            card.querySelectorAll(
                ".court-dot"
            );


        if (slides.length <= 1) {
            return;
        }


        let currentIndex = 0;


        slides.forEach(
            (slide, index) => {

                if (
                    slide.classList.contains(
                        "active"
                    )
                ) {

                    currentIndex = index;

                }

            }
        );


        let nextIndex =
            currentIndex;


        /* NEXT / PREVIOUS */

        if (button) {

            const action =
                button.dataset.action;


            if (action === "next") {

                nextIndex =
                    (currentIndex + 1) %
                    slides.length;

            }


            if (action === "previous") {

                nextIndex =
                    (
                        currentIndex -
                        1 +
                        slides.length
                    ) %
                    slides.length;

            }

        }


        /* DOT */

        if (dot) {

            nextIndex =
                Number(
                    dot.dataset.slide
                );

        }


        /* UPDATE SLIDES */

        slides.forEach(
            (slide, index) => {

                slide.classList.toggle(
                    "active",
                    index === nextIndex
                );

            }
        );


        /* UPDATE DOTS */

        dots.forEach(
            (item, index) => {

                item.classList.toggle(
                    "active",
                    index === nextIndex
                );

            }
        );

    }
);


/* =====================================================
   WISHLIST TOGGLE
===================================================== */

async function toggleWishlist(courtId, button) {

    const token = getToken();

    if (!token) return;


    const id = Number(courtId);

    const isWishlisted =
        wishlistCourtIds.has(id);

    const icon =
        button.querySelector("i");


    button.disabled = true;


    try {

        if (isWishlisted) {

            /* REMOVE */

            const response = await fetch(
                `${API_BASE_URL}/wishlists/${id}`,
                {
                    method: "DELETE",
                    headers: getHeaders()
                }
            );

            const result =
                await response.json();

            if (
                !response.ok ||
                !result.success
            ) {
                throw new Error(
                    result.message ||
                    "Unable to remove from wishlist."
                );
            }

            wishlistCourtIds.delete(id);

            icon.className = "bi bi-heart";

            button.style.color = "#6c757d";

            button.title =
                "Add to wishlist";

        } else {

            /* ADD */

            const response = await fetch(
                `${API_BASE_URL}/wishlists`,
                {
                    method: "POST",
                    headers: getHeaders(),
                    body: JSON.stringify({
                        court_id: id
                    })
                }
            );

            const result =
                await response.json();

            if (
                !response.ok ||
                !result.success
            ) {
                throw new Error(
                    result.message ||
                    "Unable to add to wishlist."
                );
            }

            wishlistCourtIds.add(id);

            icon.className =
                "bi bi-heart-fill";

            button.style.color = "#dc3545";

            button.title =
                "Remove from wishlist";

        }


        /* SCALE ANIMATION */

        button.style.transform =
            "scale(1.3)";

        setTimeout(() => {

            button.style.transform =
                "scale(1)";

        }, 200);


    } catch (error) {

        console.error(
            "Wishlist toggle error:",
            error
        );

    } finally {

        button.disabled = false;

    }

}


/* =====================================================
   WISHLIST CLICK HANDLER
===================================================== */

document.addEventListener(
    "click",
    (event) => {

        const wishlistBtn =
            event.target.closest(
                ".wishlist-toggle-btn"
            );

        if (!wishlistBtn) return;

        event.preventDefault();

        event.stopPropagation();

        const courtId =
            wishlistBtn.dataset.courtId;

        if (courtId) {

            toggleWishlist(
                courtId,
                wishlistBtn
            );

        }

    }
);


/* =====================================================
   UI - LOADING
===================================================== */

function showLoading() {

    courtsContainer.innerHTML = `

        <div class="col-12">

            <div class="empty-state">

                <div
                    class="spinner-border text-success"
                    role="status"
                ></div>

                <p>
                    Loading courts...
                </p>

            </div>

        </div>

    `;

}


/* =====================================================
   UI - HIDE LOADING
===================================================== */

function hideLoading() {

    const loading =
        document.getElementById(
            "courtsLoading"
        );

    if (loading) {

        loading.classList.add("d-none");

    }

}


/* =====================================================
   UI - EMPTY
===================================================== */

function showEmpty() {

    courtsContainer.innerHTML = "";

    courtsEmpty.classList.remove("d-none");

}


function hideEmpty() {

    courtsEmpty.classList.add("d-none");

}


/* =====================================================
   UI - ERROR
===================================================== */

function showError(message) {

    courtsContainer.innerHTML = "";

    courtsError.classList.remove("d-none");

    courtsErrorMessage.textContent =
        message;

}


function hideError() {

    courtsError.classList.add("d-none");

}


/* =====================================================
   RESULT TEXT
===================================================== */

function updateResultText(total) {

    if (total === 0) {

        courtResultText.textContent =
            "No courts available.";

        return;

    }


    courtResultText.textContent =
        `${total} court${total === 1 ? "" : "s"} available`;

}


/* =====================================================
   PRICE FORMAT
===================================================== */

function formatPrice(price) {

    const number =
        Number(price);


    if (Number.isNaN(number)) {

        return "0";

    }


    return number.toLocaleString("en-IN");

}


/* =====================================================
   ESCAPE HTML
===================================================== */

function escapeHTML(value) {

    const div =
        document.createElement("div");

    div.textContent =
        value ?? "";

    return div.innerHTML;

}


/* =====================================================
   ESCAPE ATTRIBUTE
===================================================== */

function escapeAttribute(value) {

    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

}


/* =====================================================
   INITIAL LOAD
===================================================== */

document.addEventListener(
    "DOMContentLoaded",
    async () => {

        await loadUserWishlist();

        loadCourts(1);

    }
);