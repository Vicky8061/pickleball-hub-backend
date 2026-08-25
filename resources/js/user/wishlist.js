const API_BASE_URL = "/api";

/* =========================================
   DOM
========================================= */

const wishlistLoading =
    document.getElementById("wishlistLoading");

const wishlistError =
    document.getElementById("wishlistError");

const wishlistErrorMessage =
    document.getElementById("wishlistErrorMessage");

const wishlistContent =
    document.getElementById("wishlistContent");

const wishlistGrid =
    document.getElementById("wishlistGrid");

const wishlistCount =
    document.getElementById("wishlistCount");

const emptyWishlist =
    document.getElementById("emptyWishlist");

const retryWishlist =
    document.getElementById("retryWishlist");

const removeWishlistModal =
    document.getElementById("removeWishlistModal");

const removeWishlistMessage =
    document.getElementById("removeWishlistMessage");

const confirmRemoveWishlist =
    document.getElementById("confirmRemoveWishlist");


/* =========================================
   STATE
========================================= */

let wishlists = [];

let selectedWishlist = null;


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

    const headers = {
        Accept: "application/json",
        "Content-Type": "application/json"
    };

    if (token) {

        headers.Authorization =
            `Bearer ${token}`;

    }

    return headers;

}


/* =========================================
   LOAD WISHLIST
========================================= */

async function loadWishlist() {

    showLoading();

    hideError();

    try {

        const token = getToken();

        if (!token) {

            throw new Error(
                "Please login to view your wishlist."
            );

        }


        const response =
            await fetch(
                `${API_BASE_URL}/wishlists`,
                {
                    method: "GET",
                    headers: getHeaders()
                }
            );


        const result =
            await response.json();


        console.log(
            "Wishlist Response:",
            result
        );


        /* =================================
           AUTHENTICATION ERROR
        ================================= */

        if (response.status === 401) {

            throw new Error(
                "Your session has expired. Please login again."
            );

        }


        /* =================================
           API ERROR
        ================================= */

        if (!response.ok) {

            throw new Error(
                result.message ||
                "Unable to load wishlist."
            );

        }


        if (!result.success) {

            throw new Error(
                result.message ||
                "Unable to load wishlist."
            );

        }


        /* =================================
           GET WISHLIST DATA
        ================================= */

        wishlists =
            result.data?.data ||
            result.data ||
            [];


        if (!Array.isArray(wishlists)) {

            wishlists = [];

        }


        hideLoading();

        wishlistContent.classList.remove(
            "d-none"
        );


        renderWishlist();


    }
    catch (error) {

        console.error(
            "Wishlist Error:",
            error
        );


        hideLoading();

        showError(
            error.message ||
            "Unable to load your wishlist."
        );

    }

}


/* =========================================
   RENDER WISHLIST
========================================= */

function renderWishlist() {

    wishlistGrid.innerHTML = "";


    /* =================================
       EMPTY WISHLIST
    ================================= */

    if (wishlists.length === 0) {

        wishlistCount.textContent =
            "0 courts";

        emptyWishlist.classList.remove(
            "d-none"
        );

        return;

    }


    emptyWishlist.classList.add(
        "d-none"
    );


    /* =================================
       COUNT
    ================================= */

    wishlistCount.textContent =
        `${wishlists.length} ${wishlists.length === 1
            ? "court"
            : "courts"
        }`;


    /* =================================
       CREATE CARDS
    ================================= */

    wishlists.forEach(
        wishlist => {

            const card =
                createWishlistCard(
                    wishlist
                );

            wishlistGrid.appendChild(
                card
            );

        }
    );

}


/* =========================================
   CREATE WISHLIST CARD
========================================= */

function createWishlistCard(
    wishlist
) {

    /*
     * Your WishlistResource returns:
     *
     * {
     *     id: 1,
     *     courts: {...}
     * }
     */

    const court =
        wishlist.courts;


    const col =
        document.createElement(
            "div"
        );


    col.className =
        "col-12 col-md-6 col-lg-4";


    if (!court) {

        return col;

    }


    const courtName =
        court.name ||
        court.court_name ||
        "Pickleball Court";


    const courtAddress =
        court.address ||
        "Location unavailable";


    const courtType =
        court.court_type ||
        "Court";


    const price =
        Number(
            court.price_per_hour || 0
        );


    const imageUrl =
        getCourtImage(
            court
        );


    col.innerHTML = `

        <div class="wishlist-card">

            <!-- COURT IMAGE AREA -->

            <div class="wishlist-image-wrapper">

                ${imageUrl
            ?
            `
                    <img
                        src="${escapeAttribute(imageUrl)}"
                        alt="${escapeAttribute(courtName)}"
                        class="wishlist-image"
                        onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');"
                    >

                    <div
                        class="d-none w-100 h-100 align-items-center justify-content-center text-muted"
                    >
                        <i
                            class="bi bi-image"
                            style="font-size: 40px;"
                        ></i>
                    </div>
                    `
            :
            `
                    <div
                        class="w-100 h-100 d-flex align-items-center justify-content-center text-muted"
                    >
                        <i
                            class="bi bi-image"
                            style="font-size: 40px;"
                        ></i>
                    </div>
                    `
        }

                <!-- BADGE -->

                <span class="wishlist-type-badge">
                    ${escapeHtml(courtType)}
                </span>


                <!-- REMOVE HEART -->

                <button
                    type="button"
                    class="remove-wishlist-btn"
                    data-wishlist-id="${wishlist.id}"
                    data-court-id="${court.id}"
                    title="Remove from wishlist"
                >

                    <i class="bi bi-heart-fill"></i>

                </button>

            </div>


            <!-- CARD BODY -->

            <div class="court-card-body">

                <!-- COURT NAME -->

                <h5 class="court-name" title="${escapeAttribute(courtName)}">

                    ${escapeHtml(courtName)}

                </h5>


                <!-- ADDRESS -->

                <div class="court-location">

                    <i class="bi bi-geo-alt"></i>

                    <span>
                        ${escapeHtml(courtAddress)}
                    </span>

                </div>


                <!-- CARD FOOTER -->

                <div class="court-card-footer">

                    <div class="court-price">

                        <strong>₹${formatPrice(price)}</strong>

                        <span>/ hour</span>

                    </div>


                    <!-- VIEW COURT -->

                    <button
                        type="button"
                        class="view-court-btn user-primary-btn"
                        data-court-id="${court.id}"
                    >

                        <i class="bi bi-eye me-1"></i>

                        View Court

                    </button>

                </div>

            </div>

        </div>

    `;


    /* =================================
       REMOVE BUTTON
    ================================= */

    const removeButton =
        col.querySelector(
            ".remove-wishlist-btn"
        );


    removeButton.addEventListener(
        "click",
        () => {

            openRemoveWishlistModal(
                wishlist
            );

        }
    );


    /* =================================
       VIEW COURT
    ================================= */

    const viewButton =
        col.querySelector(
            ".view-court-btn"
        );


    viewButton.addEventListener(
        "click",
        () => {

            const courtId =
                viewButton.dataset.courtId;


            if (!courtId) {

                return;

            }


            /*
             * Change this route if your
             * actual court details route
             * is different.
             */

            window.location.href =
                `/user/courts/${courtId}`;

        }
    );


    return col;

}


/* =========================================
   GET COURT IMAGE
========================================= */

function getCourtImage(court) {

    if (!court) {

        return null;

    }


    const images =
        Array.isArray(court.images)
            ? court.images
            : [];


    if (images.length === 0) {

        return null;

    }


    const firstImage =
        images.find(
            image =>
                image &&
                (
                    image.image_url ||
                    image.image ||
                    image.url ||
                    image.path
                )
        );


    if (!firstImage) {

        return null;

    }


    return (
        firstImage.image_url ||
        firstImage.image ||
        firstImage.url ||
        firstImage.path ||
        null
    );

}


/* =========================================
   OPEN REMOVE MODAL
========================================= */

function openRemoveWishlistModal(
    wishlist
) {

    selectedWishlist =
        wishlist;


    const court =
        wishlist.courts;


    const courtName =
        court?.name ||
        court?.court_name ||
        "this court";


    removeWishlistMessage.textContent =
        `Are you sure you want to remove "${courtName}" from your wishlist?`;


    const modal =
        bootstrap.Modal.getOrCreateInstance(
            removeWishlistModal
        );


    modal.show();

}


/* =========================================
   CONFIRM REMOVE
========================================= */

confirmRemoveWishlist.addEventListener(
    "click",
    async () => {

        if (!selectedWishlist) {

            return;

        }


        const court =
            selectedWishlist.courts;


        if (!court?.id) {

            showWishlistAlert(
                "Court information is missing."
            );

            return;

        }


        const courtId =
            court.id;


        const originalHTML =
            confirmRemoveWishlist.innerHTML;


        confirmRemoveWishlist.disabled =
            true;


        confirmRemoveWishlist.innerHTML = `

            <span
                class="spinner-border spinner-border-sm me-1"
            ></span>

            Removing...

        `;


        try {

            const response =
                await fetch(
                    `${API_BASE_URL}/wishlists/${courtId}`,
                    {
                        method: "DELETE",
                        headers: getHeaders()
                    }
                );


            const result =
                await response.json();


            console.log(
                "Remove Wishlist Response:",
                result
            );


            if (response.status === 401) {

                throw new Error(
                    "Your session has expired. Please login again."
                );

            }


            if (!response.ok) {

                throw new Error(
                    result.message ||
                    "Unable to remove court from wishlist."
                );

            }


            if (!result.success) {

                throw new Error(
                    result.message ||
                    "Unable to remove court from wishlist."
                );

            }


            /* =================================
               REMOVE FROM LOCAL STATE
            ================================= */

            wishlists =
                wishlists.filter(
                    item =>
                        Number(item.id) !==
                        Number(selectedWishlist.id)
                );


            /* =================================
               CLOSE MODAL
            ================================= */

            const modal =
                bootstrap.Modal.getInstance(
                    removeWishlistModal
                );


            if (modal) {

                modal.hide();

            }


            selectedWishlist =
                null;


            /* =================================
               UPDATE UI
            ================================= */

            renderWishlist();


            showWishlistSuccess(
                "Court removed from your wishlist."
            );


        }
        catch (error) {

            console.error(
                "Remove Wishlist Error:",
                error
            );


            showWishlistAlert(
                error.message ||
                "Unable to remove court from wishlist."
            );

        }
        finally {

            confirmRemoveWishlist.disabled =
                false;


            confirmRemoveWishlist.innerHTML =
                originalHTML;

        }

    }
);


/* =========================================
   SUCCESS ALERT
========================================= */

function showWishlistSuccess(
    message
) {

    createToast(
        message,
        "success"
    );

}


/* =========================================
   ERROR ALERT
========================================= */

function showWishlistAlert(
    message
) {

    createToast(
        message,
        "danger"
    );

}


/* =========================================
   TOAST
========================================= */

function createToast(
    message,
    type = "success"
) {

    const toast =
        document.createElement(
            "div"
        );


    toast.className =
        `alert alert-${type} shadow position-fixed`;


    toast.style.top =
        "90px";


    toast.style.right =
        "20px";


    toast.style.zIndex =
        "9999";


    toast.style.maxWidth =
        "400px";


    toast.innerHTML = `

        <div class="d-flex align-items-center">

            <i
                class="bi ${type === "success"
            ? "bi-check-circle"
            : "bi-exclamation-circle"
        } me-2"
            ></i>

            <span>
                ${escapeHtml(message)}
            </span>

            <button
                type="button"
                class="btn-close ms-auto"
            ></button>

        </div>

    `;


    document.body.appendChild(
        toast
    );


    const closeButton =
        toast.querySelector(
            ".btn-close"
        );


    closeButton.addEventListener(
        "click",
        () => {

            toast.remove();

        }
    );


    setTimeout(
        () => {

            if (
                toast &&
                toast.parentNode
            ) {

                toast.remove();

            }

        },
        4000
    );

}


/* =========================================
   LOADING
========================================= */

function showLoading() {

    wishlistLoading.classList.remove(
        "d-none"
    );


    wishlistContent.classList.add(
        "d-none"
    );

}


function hideLoading() {

    wishlistLoading.classList.add(
        "d-none"
    );

}


/* =========================================
   ERROR
========================================= */

function showError(
    message
) {

    wishlistContent.classList.add(
        "d-none"
    );


    wishlistError.classList.remove(
        "d-none"
    );


    wishlistErrorMessage.textContent =
        message;

}


function hideError() {

    wishlistError.classList.add(
        "d-none"
    );

}


/* =========================================
   RETRY
========================================= */

if (retryWishlist) {

    retryWishlist.addEventListener(
        "click",
        () => {

            loadWishlist();

        }
    );

}


/* =========================================
   FORMAT PRICE
========================================= */

function formatPrice(
    price
) {

    const number =
        Number(price);


    if (
        Number.isNaN(number)
    ) {

        return "0";

    }


    return number.toLocaleString(
        "en-IN"
    );

}


/* =========================================
   ESCAPE HTML
========================================= */

function escapeHtml(
    value
) {

    return String(
        value ?? ""
    )
        .replace(
            /&/g,
            "&amp;"
        )
        .replace(
            /</g,
            "&lt;"
        )
        .replace(
            />/g,
            "&gt;"
        )
        .replace(
            /"/g,
            "&quot;"
        )
        .replace(
            /'/g,
            "&#039;"
        );

}


/* =========================================
   ESCAPE ATTRIBUTE
========================================= */

function escapeAttribute(
    value
) {

    return escapeHtml(
        value
    );

}


/* =========================================
   INITIAL LOAD
========================================= */

loadWishlist();