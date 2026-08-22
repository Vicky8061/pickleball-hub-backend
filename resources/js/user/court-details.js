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

        const response = await fetch(
            `${API_BASE_URL}/courts/${courtId}`,
            {
                method: "GET",
                headers: getHeaders()
            }
        );


        const result =
            await response.json();


        if (!response.ok) {

            throw new Error(
                result.message ||
                "Unable to fetch court details."
            );

        }


        if (!result.success) {

            throw new Error(
                result.message ||
                "Unable to fetch court details."
            );

        }


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
   INITIAL LOAD
========================================= */

loadCourtDetails();