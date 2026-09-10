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

const detailCourtPrice =
    document.getElementById("detailCourtPrice");

const detailPlatformFee =
    document.getElementById("detailPlatformFee");

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
            bookingStatus,
            booking
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

                        ${canPayBooking(booking)
            ? `
                                    <button
                                        type="button"
                                        class="btn btn-success btn-sm pay-booking-btn"
                                        data-booking-id="${booking.id}"
                                    >
                                        <i class="bi bi-credit-card me-1"></i>
                                        Pay Now
                                    </button>
                                `
            : ""
        }

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


    /* Pay button */

    const payButton =
        wrapper.querySelector(
            ".pay-booking-btn"
        );

    if (payButton) {

        payButton.addEventListener(
            "click",
            () => {
                startBookingPayment(booking, payButton);
            }
        );

    }


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


    const courtPrice = Number(booking.court_price || (booking.total_amount ? booking.total_amount - (booking.platform_fee || 50) : 0));
    const platformFee = Number(booking.platform_fee ?? 50);

    if (detailCourtPrice) {
        detailCourtPrice.textContent = `₹${formatPrice(courtPrice)}`;
    }

    if (detailPlatformFee) {
        detailPlatformFee.textContent = `+ ₹${formatPrice(platformFee)}`;
    }

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
            booking.booking_status,
            booking
        );

    const detailPayNowBtn =
        document.getElementById(
            "detailPayNowBtn"
        );

    if (detailPayNowBtn) {
        if (canPayBooking(booking)) {
            detailPayNowBtn.classList.remove("d-none");
            detailPayNowBtn.onclick = () => {
                const modal = bootstrap.Modal.getInstance(bookingDetailsModal);
                if (modal) {
                    modal.hide();
                }
                startBookingPayment(booking, detailPayNowBtn);
            };
        } else {
            detailPayNowBtn.classList.add("d-none");
        }
    }

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
   START BOOKING PAYMENT (RAZORPAY / SIMULATOR)
========================================= */

async function startBookingPayment(booking, triggerBtn) {
    if (!canPayBooking(booking)) {
        alert("This reservation hold has expired. Please make a new court booking.");
        await loadBookings();
        return;
    }

    const originalHTML = triggerBtn ? triggerBtn.innerHTML : "";
    if (triggerBtn) {
        triggerBtn.disabled = true;
        triggerBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Initializing Gateway...`;
    }

    try {
        // Step 1: Create Payment Order on backend
        const orderResp = await fetch(`${API_BASE_URL}/bookings/${booking.id}/create-order`, {
            method: "POST",
            headers: getHeaders()
        });

        const orderResult = await orderResp.json();

        if (!orderResp.ok || !orderResult.success) {
            throw new Error(orderResult.message || "Failed to initialize payment gateway order.");
        }

        const orderData = orderResult.data;

        // Step 2: Handle Simulator vs Official Razorpay Checkout
        if (orderData.is_mock) {
            if (triggerBtn) {
                triggerBtn.disabled = false;
                triggerBtn.innerHTML = originalHTML;
            }
            openPaymentSimulator(booking.id, orderData, async () => {
                showBookingAlert("Payment successful! Your court booking has been confirmed.", "success");
                await loadBookings();
            });
            return;
        }

        // Step 3: Official Razorpay Checkout SDK
        if (typeof window.Razorpay === "undefined") {
            throw new Error("Razorpay Checkout SDK is still loading. Please try again in a few seconds.");
        }

        const rzpOptions = {
            key: orderData.key_id,
            amount: orderData.amount,
            currency: orderData.currency || "INR",
            name: "Pickleball Hub",
            description: `Court Booking Fee for ${orderData.court_name || 'Court'}`,
            order_id: orderData.order_id,
            prefill: {
                name: orderData.customer?.name || "",
                email: orderData.customer?.email || "",
            },
            theme: {
                color: "#198754"
            },
            handler: async function (response) {
                if (triggerBtn) {
                    triggerBtn.disabled = true;
                    triggerBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Verifying...`;
                }

                try {
                    const verifyResp = await fetch(`${API_BASE_URL}/bookings/${booking.id}/verify-payment`, {
                        method: "POST",
                        headers: getHeaders(),
                        body: JSON.stringify({
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature,
                            payment_method: "razorpay"
                        })
                    });

                    const verifyResult = await verifyResp.json();

                    if (!verifyResp.ok || !verifyResult.success) {
                        throw new Error(verifyResult.message || "Payment verification failed.");
                    }

                    showBookingAlert("Payment successful! Your court booking has been confirmed.", "success");
                    await loadBookings();
                } catch (err) {
                    alert(err.message || "Payment verification failed.");
                } finally {
                    if (triggerBtn) {
                        triggerBtn.disabled = false;
                        triggerBtn.innerHTML = originalHTML;
                    }
                }
            },
            modal: {
                ondismiss: function () {
                    if (triggerBtn) {
                        triggerBtn.disabled = false;
                        triggerBtn.innerHTML = originalHTML;
                    }
                }
            }
        };

        const rzp = new window.Razorpay(rzpOptions);
        rzp.on('payment.failed', function (resp) {
            alert("Payment Failed: " + (resp.error?.description || "Transaction failed."));
            if (triggerBtn) {
                triggerBtn.disabled = false;
                triggerBtn.innerHTML = originalHTML;
            }
        });
        rzp.open();

    } catch (err) {
        console.error("Payment error:", err);
        alert(err.message || "Payment could not be completed.");
    } finally {
        if (triggerBtn) {
            triggerBtn.disabled = false;
            triggerBtn.innerHTML = originalHTML;
        }
    }
}

/* =========================================
   PAYMENT GATEWAY SIMULATOR (LEARNING)
========================================= */

function openPaymentSimulator(bookingId, orderData, onSuccess) {
    const simModalEl = document.getElementById("paymentSimulatorModal");
    if (!simModalEl) {
        alert("Payment simulator modal not found.");
        return;
    }

    const simAmountEl = simModalEl.querySelector("#simModalAmount");
    const simOrderEl = simModalEl.querySelector("#simModalOrderId");
    const successBtn = simModalEl.querySelector("#simSuccessBtn");
    const failBtn = simModalEl.querySelector("#simFailBtn");

    const amountDisplay = orderData.amount_in_rupees || (orderData.amount / 100);
    if (simAmountEl) simAmountEl.textContent = `₹${formatPrice(amountDisplay)}`;
    if (simOrderEl) simOrderEl.textContent = `Order ID: ${orderData.order_id}`;

    const simModal = bootstrap.Modal.getOrCreateInstance(simModalEl);
    simModal.show();

    successBtn.onclick = async () => {
        successBtn.disabled = true;
        successBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Verifying Payment...`;

        const selectedMethod = simModalEl.querySelector('input[name="simPaymentMethod"]:checked')?.value || "upi";

        try {
            const resp = await fetch(`${API_BASE_URL}/bookings/${bookingId}/verify-payment`, {
                method: "POST",
                headers: getHeaders(),
                body: JSON.stringify({
                    razorpay_order_id: orderData.order_id,
                    razorpay_payment_id: "pay_sim_" + Date.now(),
                    razorpay_signature: "sim_signature_success",
                    payment_method: selectedMethod
                })
            });

            const result = await resp.json();

            if (!resp.ok || !result.success) {
                throw new Error(result.message || "Simulated payment verification failed.");
            }

            simModal.hide();
            if (typeof onSuccess === "function") {
                onSuccess();
            }
        } catch (err) {
            alert(err.message || "Simulated payment failed.");
        } finally {
            successBtn.disabled = false;
            successBtn.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Simulate Successful Payment`;
        }
    };

    failBtn.onclick = () => {
        simModal.hide();
        alert("Payment cancelled. Your temporary court reservation hold remains active until the timer reaches zero.");
    };
}

/* =========================================
   CAN PAY?
========================================= */

function canPayBooking(booking) {
    if (!booking) return false;
    if (booking.booking_status !== "pending") return false;
    if (!booking.expires_at) return true;
    return new Date(booking.expires_at).getTime() > Date.now();
}


/* =========================================
   BOOKING STATUS BADGE
========================================= */

function getBookingStatusBadge(
    status,
    booking = null
) {

    const normalized =
        String(
            status || "unknown"
        ).toLowerCase();

    if (normalized === "pending" && booking && booking.expires_at) {
        const remaining = Math.max(0, Math.floor((new Date(booking.expires_at).getTime() - Date.now()) / 1000));
        if (remaining > 0) {
            const m = Math.floor(remaining / 60);
            const s = remaining % 60;
            return `
                <span class="booking-status status-pending" title="Hold expires in ${m}m ${s}s">
                    <i class="bi bi-stopwatch me-1"></i>Hold (${m}m left)
                </span>
            `;
        } else {
            return `
                <span class="booking-status status-cancelled">
                    <i class="bi bi-x-circle me-1"></i>Hold Expired
                </span>
            `;
        }
    }


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