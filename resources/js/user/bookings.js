const API_BASE_URL = "/api";


/* =========================================
   DOM
========================================= */

const bookingsLoading =
    document.getElementById("bookingsLoading");

const bookingsError =
    document.getElementById("bookingsError");

const bookingsErrorMessage =
    document.getElementById("bookingsErrorMessage");

const retryBookings =
    document.getElementById("retryBookings");

const noBookings =
    document.getElementById("noBookings");

const bookingsContent =
    document.getElementById("bookingsContent");

const bookingsList =
    document.getElementById("bookingsList");


/* Detail Modal */

const bookingDetailsModal =
    document.getElementById("bookingDetailsModal");

const detailCourtName =
    document.getElementById("detailCourtName");

const detailCourtAddress =
    document.getElementById("detailCourtAddress");

const detailBookingId =
    document.getElementById("detailBookingId");

const detailBookingDate =
    document.getElementById("detailBookingDate");

const detailBookingTime =
    document.getElementById("detailBookingTime");

const detailBookingAmount =
    document.getElementById("detailBookingAmount");

const detailPaymentStatus =
    document.getElementById("detailPaymentStatus");

const detailBookingStatus =
    document.getElementById("detailBookingStatus");


/* Cancel Modal */

const cancelBookingModal =
    document.getElementById("cancelBookingModal");

const confirmCancelBookingBtn =
    document.getElementById(
        "confirmCancelBookingBtn"
    );


/* =========================================
   STATE
========================================= */

let bookings = [];

let selectedBookingId = null;


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
   LOAD BOOKINGS
========================================= */

async function loadBookings() {

    showLoading();

    hideError();

    try {

        const result = await apiFetch("/bookings");

        console.log(
            "Bookings Response:",
            result
        );

        bookings =
            result.data?.data ||
            result.data ||
            [];


        hideLoading();


        /* Empty */

        if (bookings.length === 0) {

            showEmpty();

            return;

        }


        renderBookings();

    }
    catch (error) {

        console.error(
            "Load Bookings Error:",
            error
        );


        hideLoading();

        showError(
            error.message ||
            "Unable to load your bookings."
        );

    }

}


/* =========================================
   RENDER BOOKINGS
========================================= */

function renderBookings() {

    bookingsList.innerHTML = "";


    bookings.forEach(
        booking => {

            const card =
                createBookingCard(
                    booking
                );


            bookingsList.appendChild(card);

        }
    );


    bookingsContent.classList.remove(
        "d-none"
    );

}


/* =========================================
   CREATE BOOKING CARD
========================================= */

function createBookingCard(
    booking
) {

    const wrapper =
        document.createElement("div");

    wrapper.className =
        "col-12 col-md-6 col-xl-4";


    const court =
        booking.court || {};


    const timeSlot =
        booking.time_slot || {};


    const courtName =
        court.court_name ||
        court.name ||
        "Pickleball Court";


    const courtAddress =
        court.address ||
        "Location unavailable";


    const bookingDate =
        booking.booking_date ||
        "--";


    const bookingTime =
        getBookingTime(
            timeSlot
        );


    const amount =
        Number(
            booking.total_amount || 0
        );


    const bookingStatus =
        booking.booking_status ||
        "unknown";


    const paymentStatus =
        booking.payment_status ||
        "unknown";


    const image =
        getCourtImage(court);


    wrapper.innerHTML = `

        <div class="booking-card">

            ${image
            ? `
                        <img
                            src="${escapeHtml(image)}"
                            class="booking-card-image"
                            alt="${escapeHtml(courtName)}"
                        >
                    `
            : `
                        <div class="booking-image-placeholder">

                            <i class="bi bi-dribbble"></i>

                        </div>
                    `
        }


            <div class="booking-card-body">

                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">

                    <div>

                        <h5 class="booking-court-name">
                            ${escapeHtml(courtName)}
                        </h5>

                        <div class="booking-court-address">

                            <i class="bi bi-geo-alt me-1"></i>

                            ${escapeHtml(courtAddress)}

                        </div>

                    </div>


                    ${getBookingStatusBadge(
            bookingStatus
        )}

                </div>


                <div class="booking-info">

                    <div class="booking-info-row">

                        <i class="bi bi-calendar3"></i>

                        <span>Date</span>

                        <strong class="ms-auto">
                            ${formatDate(bookingDate)}
                        </strong>

                    </div>


                    <div class="booking-info-row">

                        <i class="bi bi-clock"></i>

                        <span>Time</span>

                        <strong class="ms-auto text-end">
                            ${escapeHtml(bookingTime)}
                        </strong>

                    </div>


                    <div class="booking-info-row">

                        <i class="bi bi-credit-card"></i>

                        <span>Payment</span>

                        <span class="ms-auto">

                            ${getPaymentBadge(
            paymentStatus
        )}

                        </span>

                    </div>

                </div>


                <div class="booking-card-footer">

                    <div>

                        <small class="text-muted d-block">
                            Total Amount
                        </small>

                        <span class="booking-amount">
                            ₹${formatPrice(amount)}
                        </span>

                    </div>


                    <div class="d-flex gap-2">

                        <button
                            type="button"
                            class="btn btn-outline-success btn-sm view-booking-btn"
                            data-booking-id="${booking.id}"
                        >
                            <i class="bi bi-eye me-1"></i>
                            View
                        </button>


                        ${canCancelBooking(
            bookingStatus
        )
            ? `
                                    <button
                                        type="button"
                                        class="btn btn-outline-danger btn-sm cancel-booking-btn"
                                        data-booking-id="${booking.id}"
                                    >
                                        <i class="bi bi-x-circle me-1"></i>
                                        Cancel
                                    </button>
                                `
            : ""
        }

                    </div>

                </div>

            </div>

        </div>

    `;


    /* View button */

    const viewButton =
        wrapper.querySelector(
            ".view-booking-btn"
        );


    if (viewButton) {

        viewButton.addEventListener(
            "click",
            () => {

                const id =
                    viewButton.dataset.bookingId;

                viewBooking(id);

            }
        );

    }


    /* Cancel button */

    const cancelButton =
        wrapper.querySelector(
            ".cancel-booking-btn"
        );


    if (cancelButton) {

        cancelButton.addEventListener(
            "click",
            () => {

                const id =
                    cancelButton.dataset.bookingId;

                openCancelModal(id);

            }
        );

    }


    return wrapper;

}


/* =========================================
   VIEW BOOKING
========================================= */

async function viewBooking(
    bookingId
) {

    try {

        const response =
            await fetch(
                `${API_BASE_URL}/bookings/${bookingId}`,
                {
                    method: "GET",
                    headers: getHeaders()
                }
            );


        const result =
            await response.json();


        if (response.status === 401) {

            showBookingAlert(
                "Your session has expired. Please login again."
            );

            return;

        }


        if (!response.ok) {

            throw new Error(
                result.message ||
                "Unable to load booking details."
            );

        }


        if (!result.success) {

            throw new Error(
                result.message ||
                "Unable to load booking details."
            );

        }


        const booking =
            result.data?.data ||
            result.data;


        if (!booking) {

            throw new Error(
                "Booking details not found."
            );

        }


        fillBookingDetails(
            booking
        );


        const modal =
            bootstrap.Modal.getOrCreateInstance(
                bookingDetailsModal
            );


        modal.show();

    }
    catch (error) {

        console.error(
            "View Booking Error:",
            error
        );


        showBookingAlert(
            error.message ||
            "Unable to load booking details."
        );

    }

}


/* =========================================
   FILL BOOKING DETAILS
========================================= */

function fillBookingDetails(
    booking
) {

    const court =
        booking.court || {};


    const timeSlot =
        booking.time_slot || {};


    detailCourtName.textContent =
        court.court_name ||
        court.name ||
        "Pickleball Court";


    detailCourtAddress.textContent =
        court.address ||
        "Location unavailable";


    detailBookingId.textContent =
        `#${booking.id || "--"}`;


    detailBookingDate.textContent =
        formatDate(
            booking.booking_date
        );


    detailBookingTime.textContent =
        getBookingTime(
            timeSlot
        );


    detailBookingAmount.textContent =
        `₹${formatPrice(
            booking.total_amount
        )}`;


    detailPaymentStatus.innerHTML =
        getPaymentBadge(
            booking.payment_status
        );


    detailBookingStatus.innerHTML =
        getBookingStatusBadge(
            booking.booking_status
        );

}


/* =========================================
   CANCEL MODAL
========================================= */

function openCancelModal(
    bookingId
) {

    selectedBookingId =
        bookingId;


    const modal =
        bootstrap.Modal.getOrCreateInstance(
            cancelBookingModal
        );


    modal.show();

}


/* =========================================
   CONFIRM CANCEL
========================================= */

confirmCancelBookingBtn.addEventListener(
    "click",
    async () => {

        if (!selectedBookingId) {

            return;

        }


        const originalHTML =
            confirmCancelBookingBtn.innerHTML;


        confirmCancelBookingBtn.disabled =
            true;


        confirmCancelBookingBtn.innerHTML = `

            <span
                class="spinner-border spinner-border-sm me-2"
            ></span>

            Cancelling...

        `;


        try {

            const response =
                await fetch(
                    `${API_BASE_URL}/bookings/${selectedBookingId}`,
                    {
                        method: "DELETE",
                        headers: getHeaders()
                    }
                );


            const result =
                await response.json();


            console.log(
                "Cancel Booking Response:",
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
                    "Unable to cancel booking."
                );

            }


            if (!result.success) {

                throw new Error(
                    result.message ||
                    "Unable to cancel booking."
                );

            }


            /* Close modal */

            const modal =
                bootstrap.Modal.getInstance(
                    cancelBookingModal
                );


            if (modal) {

                modal.hide();

            }


            selectedBookingId = null;


            showBookingAlert(
                "Booking cancelled successfully.",
                "success"
            );


            /* Reload bookings */

            await loadBookings();

        }
        catch (error) {

            console.error(
                "Cancel Booking Error:",
                error
            );


            showBookingAlert(
                error.message ||
                "Unable to cancel booking."
            );

        }
        finally {

            confirmCancelBookingBtn.disabled =
                false;


            confirmCancelBookingBtn.innerHTML =
                originalHTML;

        }

    }
);


/* =========================================
   CAN CANCEL?
========================================= */

function canCancelBooking(
    status
) {

    return (
        status === "pending" ||
        status === "confirmed"
    );

}


/* =========================================
   BOOKING STATUS BADGE
========================================= */

function getBookingStatusBadge(
    status
) {

    const normalized =
        String(
            status || "unknown"
        ).toLowerCase();


    const labels = {

        pending: "Pending",

        confirmed: "Confirmed",

        completed: "Completed",

        cancelled: "Cancelled"

    };


    const label =
        labels[normalized] ||
        capitalize(normalized);


    return `

        <span class="booking-status status-${normalized}">

            ${escapeHtml(label)}

        </span>

    `;

}


/* =========================================
   PAYMENT BADGE
========================================= */

function getPaymentBadge(
    status
) {

    const normalized =
        String(
            status || "unknown"
        ).toLowerCase();


    const labels = {

        paid: "Paid",

        pending: "Pending",

        failed: "Failed"

    };


    const label =
        labels[normalized] ||
        capitalize(normalized);


    let className =
        "payment-pending";


    if (normalized === "paid") {

        className =
            "payment-paid";

    }
    else if (normalized === "failed") {

        className =
            "payment-failed";

    }


    return `

        <span class="booking-status ${className}">

            ${escapeHtml(label)}

        </span>

    `;

}


/* =========================================
   COURT IMAGE
========================================= */

function getCourtImage(
    court
) {

    if (!court) {

        return null;

    }


    /* images array */

    if (
        Array.isArray(court.images) &&
        court.images.length > 0
    ) {

        const image =
            court.images.find(
                item =>
                    item &&
                    (
                        item.image_url ||
                        item.image
                    )
            );


        if (image) {

            return (
                image.image_url ||
                image.image
            );

        }

    }


    /* Single image */

    if (court.image_url) {

        return court.image_url;

    }


    if (court.image) {

        return court.image;

    }


    return null;

}


/* =========================================
   BOOKING TIME
========================================= */

function getBookingTime(
    timeSlot
) {

    if (!timeSlot) {

        return "Time unavailable";

    }


    if (timeSlot.time) {

        return timeSlot.time;

    }


    if (
        timeSlot.start_time &&
        timeSlot.end_time
    ) {

        return `${formatTime(
            timeSlot.start_time
        )} - ${formatTime(
            timeSlot.end_time
        )}`;

    }


    return "Time unavailable";

}


/* =========================================
   FORMAT DATE
========================================= */

function formatDate(
    date
) {

    if (!date) {

        return "--";

    }


    const parts =
        String(date).split("-");


    if (parts.length !== 3) {

        return date;

    }


    return `${parts[2]}/${parts[1]}/${parts[0]}`;

}


/* =========================================
   FORMAT TIME
========================================= */

function formatTime(
    time
) {

    if (!time) {

        return "--";

    }


    const parts =
        String(time).split(":");


    if (parts.length < 2) {

        return time;

    }


    let hour =
        parseInt(
            parts[0],
            10
        );


    const minute =
        parts[1];


    const period =
        hour >= 12
            ? "PM"
            : "AM";


    hour =
        hour % 12 || 12;


    return `${hour}:${minute} ${period}`;

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
   CAPITALIZE
========================================= */

function capitalize(
    value
) {

    if (!value) {

        return "";

    }


    return (
        value.charAt(0).toUpperCase() +
        value.slice(1)
    );

}


/* =========================================
   ESCAPE HTML
========================================= */

function escapeHtml(
    value
) {

    if (value === null || value === undefined) {

        return "";

    }


    return String(value)
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
   LOADING
========================================= */

function showLoading() {

    bookingsLoading.classList.remove(
        "d-none"
    );


    bookingsContent.classList.add(
        "d-none"
    );


    noBookings.classList.add(
        "d-none"
    );

}


function hideLoading() {

    bookingsLoading.classList.add(
        "d-none"
    );

}


/* =========================================
   EMPTY
========================================= */

function showEmpty() {

    bookingsContent.classList.add(
        "d-none"
    );


    noBookings.classList.remove(
        "d-none"
    );

}



/* =========================================
   ERROR
========================================= */

function showError(
    message
) {

    bookingsError.classList.remove(
        "d-none"
    );


    bookingsErrorMessage.textContent =
        message;


    bookingsContent.classList.add(
        "d-none"
    );


    noBookings.classList.add(
        "d-none"
    );

}


function hideError() {

    bookingsError.classList.add(
        "d-none"
    );

}


/* =========================================
   ALERT
========================================= */

function showBookingAlert(
    message,
    type = "danger"
) {

    let alertBox =
        document.getElementById(
            "bookingAlert"
        );


    if (alertBox) {

        alertBox.remove();

    }


    alertBox =
        document.createElement(
            "div"
        );


    alertBox.id =
        "bookingAlert";


    alertBox.className =
        `alert alert-${type} alert-dismissible fade show position-fixed`;


    alertBox.style.top =
        "90px";


    alertBox.style.right =
        "20px";


    alertBox.style.zIndex =
        "9999";


    alertBox.style.maxWidth =
        "400px";


    alertBox.innerHTML = `

        <i class="bi bi-${type === "success"
            ? "check-circle"
            : "exclamation-circle"
        } me-2"></i>

        <span></span>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    `;


    alertBox
        .querySelector("span")
        .textContent =
        message;


    document.body.appendChild(
        alertBox
    );


    setTimeout(
        () => {

            if (
                alertBox &&
                alertBox.parentNode
            ) {

                alertBox.remove();

            }

        },
        5000
    );

}


/* =========================================
   RETRY
========================================= */

retryBookings.addEventListener(
    "click",
    () => {

        loadBookings();

    }
);


/* =========================================
   INITIAL LOAD
========================================= */

document.addEventListener(
    "DOMContentLoaded",
    () => {

        loadBookings();

    }
);