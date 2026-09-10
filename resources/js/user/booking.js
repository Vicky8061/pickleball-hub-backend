const API_BASE_URL = "/api";

const courtId = window.BOOKING_COURT_ID;


/* =========================================
   DOM
========================================= */

const bookingLoading =
    document.getElementById("bookingLoading");

const bookingError =
    document.getElementById("bookingError");

const bookingErrorMessage =
    document.getElementById("bookingErrorMessage");

const bookingContent =
    document.getElementById("bookingContent");

const retryBooking =
    document.getElementById("retryBooking");

const bookingCourtImage =
    document.getElementById("bookingCourtImage");

const bookingCourtImagePlaceholder =
    document.getElementById(
        "bookingCourtImagePlaceholder"
    );

const bookingCourtType =
    document.getElementById("bookingCourtType");

const bookingCourtName =
    document.getElementById("bookingCourtName");

const bookingCourtAddress =
    document.getElementById("bookingCourtAddress");

const bookingCourtPrice =
    document.getElementById("bookingCourtPrice");

const bookingDate =
    document.getElementById("bookingDate");

const timeSlots =
    document.getElementById("timeSlots");

const slotLoading =
    document.getElementById("slotLoading");

const slotError =
    document.getElementById("slotError");

const slotErrorMessage =
    document.getElementById("slotErrorMessage");

const noSlots =
    document.getElementById("noSlots");

const summaryCourtName =
    document.getElementById("summaryCourtName");

const summaryDate =
    document.getElementById("summaryDate");

const summaryTime =
    document.getElementById("summaryTime");

const summaryTotal =
    document.getElementById("summaryTotal");

const summaryCourtPrice =
    document.getElementById("summaryCourtPrice");

const summaryPlatformFee =
    document.getElementById("summaryPlatformFee");

const confirmBookingBtn =
    document.getElementById("confirmBookingBtn");


/* =========================================
   STATE
========================================= */

let court = null;

let availableSlots = [];

let selectedSlot = null;


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
   LOAD COURT
========================================= */

async function loadCourt() {

    showLoading();
    hideError();

    try {

        if (!courtId) {

            throw new Error(
                "Court ID is missing."
            );

        }


        const result = await apiFetch(`/courts/${courtId}`);

        court =
            result.data?.data ||
            result.data;


        if (!court) {

            throw new Error(
                "Court details not found."
            );

        }


        renderCourt();

        setMinimumDate();

        hideLoading();

        bookingContent.classList.remove(
            "d-none"
        );


        /*
         * Load availability for today's date
         */
        loadTimeSlots();

    }
    catch (error) {

        console.error(
            "Booking Court Error:",
            error
        );

        hideLoading();

        showError(
            error.message ||
            "Unable to load booking information."
        );

    }

}


/* =========================================
   RENDER COURT
========================================= */

function renderCourt() {

    bookingCourtType.textContent =
        court.court_type ||
        "Court";


    bookingCourtName.textContent =
        court.name ||
        court.court_name ||
        "Pickleball Court";


    bookingCourtAddress.textContent =
        court.address ||
        "Location unavailable";


    const price =
        Number(court.price_per_hour);


    bookingCourtPrice.textContent =
        `₹${formatPrice(price)}`;


    summaryCourtName.textContent =
        court.name ||
        court.court_name ||
        "Pickleball Court";


    /*
     * Court Images
     */

    const images =
        Array.isArray(court.images)
            ? court.images
            : [];


    const firstImage =
        images.find(
            image =>
                image &&
                (
                    image.image_url ||
                    image.image
                )
        );


    if (firstImage) {

        bookingCourtImage.src =
            firstImage.image_url ||
            firstImage.image;


        bookingCourtImage.classList.remove(
            "d-none"
        );


        bookingCourtImagePlaceholder.classList.add(
            "d-none"
        );

    }
    else {

        bookingCourtImage.classList.add(
            "d-none"
        );


        bookingCourtImagePlaceholder.classList.remove(
            "d-none"
        );

    }

}


/* =========================================
   MINIMUM DATE
========================================= */

function setMinimumDate() {

    const today =
        new Date();


    const year =
        today.getFullYear();


    const month =
        String(
            today.getMonth() + 1
        ).padStart(2, "0");


    const day =
        String(
            today.getDate()
        ).padStart(2, "0");


    const todayString =
        `${year}-${month}-${day}`;


    bookingDate.min =
        todayString;


    bookingDate.value =
        todayString;


    updateSummary();

}


/* =========================================
   DATE CHANGE
========================================= */

bookingDate.addEventListener(
    "change",
    () => {

        selectedSlot = null;

        updateSummary();

        loadTimeSlots();

    }
);


/* =========================================
   LOAD TIME SLOT AVAILABILITY
========================================= */

async function loadTimeSlots() {

    const date =
        bookingDate.value;


    if (!date) {
        return;
    }


    showSlotLoading();

    clearSlotMessages();

    timeSlots.innerHTML = "";


    selectedSlot = null;

    updateSummary();


    try {

        /*
         * NEW AVAILABILITY API
         *
         * GET:
         * /api/courts/{court}/availability?date=YYYY-MM-DD
         *
         * This API already returns:
         *
         * id
         * court_id
         * start_time
         * end_time
         * status
         * is_booked
         * is_available
         */

        const result =
            await apiFetch(`/courts/${courtId}/availability?date=${encodeURIComponent(date)}`);

        console.log(
            "Availability Response:",
            result
        );


        /*
         * Your availability controller returns:
         *
         * {
         *   success: true,
         *   message: "...",
         *   data: [...]
         * }
         */

        availableSlots =
            result.data || [];


        /*
         * Only active slots
         */

        availableSlots =
            availableSlots.filter(
                slot =>
                    slot.status === "active"
            );


        hideSlotLoading();


        /*
         * No slots at all
         */

        if (
            availableSlots.length === 0
        ) {

            noSlots.classList.remove(
                "d-none"
            );

            return;

        }


        renderTimeSlots();

    }
    catch (error) {

        console.error(
            "Time Slot Availability Error:",
            error
        );


        hideSlotLoading();


        showSlotError(
            error.message ||
            "Unable to load time slot availability."
        );

    }

}


/* =========================================
   RENDER TIME SLOTS
========================================= */

function renderTimeSlots() {

    timeSlots.innerHTML = "";


    availableSlots.forEach(
        slot => {

            const button =
                document.createElement(
                    "button"
                );


            button.type =
                "button";


            button.className =
                "time-slot";


            button.dataset.slotId =
                slot.id;


            /*
             * Check availability
             */

            const isBooked =
                slot.is_booked === true;


            const isAvailable =
                slot.is_available === true &&
                !isBooked;


            /*
             * BOOKED SLOT
             */

            if (!isAvailable) {

                button.classList.add(
                    "disabled"
                );


                button.disabled =
                    true;


                button.innerHTML = `
                    <i class="bi bi-lock-fill me-1"></i>
                    ${getSlotTime(slot)}
                    <small class="d-block text-danger">
                        Booked
                    </small>
                `;

            }

            /*
             * AVAILABLE SLOT
             */

            else {

                button.innerHTML = `
                    <i class="bi bi-clock me-1"></i>
                    ${getSlotTime(slot)}
                `;


                button.addEventListener(
                    "click",
                    () => {

                        selectSlot(slot);

                    }
                );

            }


            timeSlots.appendChild(
                button
            );

        }
    );

}


/* =========================================
   SELECT SLOT
========================================= */

function selectSlot(slot) {

    /*
     * Do not allow already booked slot
     */

    if (
        slot.is_booked === true ||
        slot.is_available !== true
    ) {

        return;

    }


    selectedSlot =
        slot;


    document
        .querySelectorAll(".time-slot")
        .forEach(
            button => {

                button.classList.toggle(
                    "selected",
                    Number(
                        button.dataset.slotId
                    ) ===
                    Number(slot.id)
                );

            }
        );


    updateSummary();

}


/* =========================================
   SUMMARY
========================================= */

function updateSummary() {

    summaryDate.textContent =
        bookingDate.value
            ? formatDate(
                bookingDate.value
            )
            : "Not selected";


    const courtPrice =
        Number(
            court?.price_per_hour || 0
        );

    const platformFee = 50;


    if (summaryCourtPrice) {
        summaryCourtPrice.textContent = `₹${formatPrice(courtPrice)}`;
    }

    if (summaryPlatformFee) {
        summaryPlatformFee.textContent = `+ ₹${formatPrice(platformFee)}`;
    }


    if (!selectedSlot) {

        summaryTime.textContent =
            "Not selected";


        summaryTotal.textContent =
            "₹0";


        confirmBookingBtn.disabled =
            true;


        return;

    }


    summaryTime.textContent =
        getSlotTime(
            selectedSlot
        );


    const totalPrice = courtPrice + platformFee;


    summaryTotal.textContent =
        `₹${formatPrice(totalPrice)}`;


    confirmBookingBtn.disabled =
        false;

}


/* =========================================
   SLOT TIME
========================================= */

function getSlotTime(slot) {

    if (!slot) {

        return "Selected slot";

    }


    if (slot.time) {

        return slot.time;

    }


    if (
        slot.start_time &&
        slot.end_time
    ) {

        return `${formatTime(slot.start_time)} - ${formatTime(slot.end_time)}`;

    }


    return "Selected slot";

}


/* =========================================
   CONFIRM BOOKING
========================================= */

confirmBookingBtn.addEventListener(
    "click",
    async () => {

        await createBooking();

    }
);


/* =========================================
   CREATE BOOKING
========================================= */

async function createBooking() {

    /*
     * Court validation
     */

    if (!court) {

        showBookingAlert(
            "Court information is not available."
        );

        return;

    }


    /*
     * Date validation
     */

    if (!bookingDate.value) {

        showBookingAlert(
            "Please select a booking date."
        );

        return;

    }


    /*
     * Slot validation
     */

    if (!selectedSlot) {

        showBookingAlert(
            "Please select a time slot."
        );

        return;

    }


    /*
     * Make sure slot is still available
     */

    if (
        selectedSlot.is_booked === true ||
        selectedSlot.is_available !== true
    ) {

        showBookingAlert(
            "This time slot is no longer available. Please select another slot."
        );

        await loadTimeSlots();

        return;

    }


    /*
     * Token validation
     */

    const token =
        getToken();


    if (!token) {

        showBookingAlert(
            "Please login before booking a court."
        );

        return;

    }


    /*
     * Disable button
     */

    const originalButtonHTML =
        confirmBookingBtn.innerHTML;


    confirmBookingBtn.disabled =
        true;


    confirmBookingBtn.innerHTML = `
        <span
            class="spinner-border spinner-border-sm me-2"
            role="status"
        ></span>
        Confirming Booking...
    `;


    try {

        /*
         * Request body
         *
         * Matches StoreBookingRequest
         */

        const requestData = {

            court_id:
                Number(courtId),

            time_slot_id:
                Number(selectedSlot.id),

            booking_date:
                bookingDate.value

        };


        console.log(
            "Booking Request:",
            requestData
        );


        const result =
            await apiFetch(
                "/bookings",
                {
                    method: "POST",
                    body: JSON.stringify(
                        requestData
                    )
                }
            );


        console.log(
            "Booking Response:",
            result
        );


        /*
         * Booking created successfully
         */

        const booking =
            result.data?.data ||
            result.data;


        if (!booking) {

            throw new Error(
                "Booking created, but booking details were not returned."
            );

        }


        /*
         * Show success modal
         */

        showBookingSuccessModal(
            booking
        );


        /*
         * Clear selected slot
         */

        selectedSlot =
            null;


        /*
         * Refresh availability
         *
         * The newly booked slot should
         * now appear as booked.
         */

        await loadTimeSlots();

    }
    catch (error) {

        console.error(
            "Create Booking Error:",
            error
        );


        showBookingAlert(
            error.message ||
            "Something went wrong while creating your booking."
        );

    }
    finally {

        /*
         * Restore button
         */

        confirmBookingBtn.innerHTML =
            originalButtonHTML;


        /*
         * Button stays disabled until
         * another slot is selected.
         */

        confirmBookingBtn.disabled =
            !selectedSlot;

    }

}


/* =========================================
   BOOKING SUCCESS MODAL
========================================= */

function showBookingSuccessModal(
    booking
) {

    /*
     * Booking ID
     */

    const bookingId =
        booking.id ||
        "--";


    /*
     * Booking date
     */

    const bookingDateValue =
        booking.booking_date ||
        bookingDate.value;


    /*
     * Booking amount
     */

    const bookingAmount =
        Number(
            booking.total_amount ??
            court?.price_per_hour ??
            0
        );


    /*
     * Time slot
     *
     * BookingResource returns:
     *
     * time_slot
     */

    const bookingTimeSlot =
        booking.time_slot ||
        selectedSlot;


    const bookingTime =
        bookingTimeSlot
            ? getSlotTime(
                bookingTimeSlot
            )
            : "Selected slot";


    /*
     * Create modal if it does
     * not already exist
     */

    let modal =
        document.getElementById(
            "bookingSuccessModal"
        );


    if (!modal) {

        modal =
            createBookingSuccessModal();

    }


    /*
     * Fill modal values
     */

    const successBookingId =
        modal.querySelector(
            "#successBookingId"
        );


    const successBookingDate =
        modal.querySelector(
            "#successBookingDate"
        );


    const successBookingTime =
        modal.querySelector(
            "#successBookingTime"
        );


    const successBookingAmount =
        modal.querySelector(
            "#successBookingAmount"
        );


    successBookingId.textContent =
        `#${bookingId}`;


    successBookingDate.textContent =
        formatDate(
            bookingDateValue
        );


    successBookingTime.textContent =
        bookingTime;


    successBookingAmount.textContent =
        `₹${formatPrice(
            bookingAmount
        )}`;


    /*
     * Start Hold Countdown & Payment handler
     */
    startHoldCountdown(booking, modal);

    /*
     * Bootstrap modal
     */

    if (
        typeof bootstrap !== "undefined"
    ) {
        modal.addEventListener("hide.bs.modal", () => {
            if (document.activeElement && modal.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        });

        const bootstrapModal =
            bootstrap.Modal.getOrCreateInstance(
                modal
            );


        bootstrapModal.show();

    }
    else {

        /*
         * Fallback if Bootstrap JS
         * is not loaded.
         */

        modal.classList.add(
            "show"
        );

        modal.style.display =
            "block";

        modal.removeAttribute(
            "aria-hidden"
        );

    }

}

let holdCountdownTimerInterval = null;

function startHoldCountdown(booking, modal) {
    if (holdCountdownTimerInterval) {
        clearInterval(holdCountdownTimerInterval);
        holdCountdownTimerInterval = null;
    }

    const banner = modal.querySelector("#bookingHoldBanner");
    const timerEl = modal.querySelector("#bookingHoldTimer");
    const noteEl = modal.querySelector("#bookingHoldNote");
    const payBtn = modal.querySelector("#payNowModalBtn");
    const viewBtn = modal.querySelector("#viewMyBookingsBtn");
    const downloadInvoiceBtn = modal.querySelector("#downloadInvoiceModalBtn");
    const modalTitle = modal.querySelector("#bookingSuccessModalLabel");
    const modalMsg = modal.querySelector("#bookingSuccessMessage");
    const modalIcon = modal.querySelector("#bookingModalIcon");

    if (downloadInvoiceBtn) {
        downloadInvoiceBtn.classList.add("d-none");
        downloadInvoiceBtn.onclick = () => {
            downloadBookingInvoice(booking.id);
        };
    }

    if (!timerEl) return;

    if (banner) {
        banner.classList.remove("expired", "d-none");
    }

    if (modalTitle) modalTitle.textContent = "Court Slot Held!";
    if (modalMsg) modalMsg.textContent = "Your court slot is held. Complete payment before the timer expires to confirm your reservation.";
    if (modalIcon) {
        modalIcon.innerHTML = `<i class="bi bi-clock-history"></i>`;
        modalIcon.style.background = "#fff3cd";
        modalIcon.style.color = "#856404";
    }

    if (payBtn) {
        payBtn.classList.remove("d-none", "btn-secondary");
        payBtn.classList.add("btn-success");
        payBtn.disabled = false;
        payBtn.innerHTML = `<i class="bi bi-credit-card me-1"></i> Pay & Confirm Booking`;

        payBtn.onclick = async () => {
            payBtn.disabled = true;
            payBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Initializing Gateway...`;

            try {
                // Step 1: Create Payment Order on backend
                const orderResp = await fetch(`${API_BASE_URL}/bookings/${booking.id}/create-order`, {
                    method: "POST",
                    headers: getHeaders()
                });

                const orderResult = await orderResp.json();

                if (!orderResp.ok || !orderResult.success) {
                    throw new Error(orderResult.message || "Failed to initialize payment order.");
                }

                const orderData = orderResult.data;

                // Step 2: Handle Simulator vs Official Razorpay Checkout
                if (orderData.is_mock) {
                    // Launch Interactive Learning Simulator Modal
                    openPaymentSimulator(booking.id, orderData, () => {
                        onPaymentSucceeded();
                    });
                    payBtn.disabled = false;
                    payBtn.innerHTML = `<i class="bi bi-credit-card me-1"></i> Pay & Confirm Booking`;
                    return;
                }

                // Official Razorpay Checkout SDK
                if (typeof window.Razorpay === "undefined") {
                    throw new Error("Razorpay Checkout SDK is still loading. Please try again in a few seconds.");
                }

                const rzpOptions = {
                    key: orderData.key_id,
                    amount: orderData.amount,
                    currency: orderData.currency || "INR",
                    name: "Pickleball Hub",
                    description: `Court Booking Fee for ${orderData.court_name}`,
                    order_id: orderData.order_id,
                    prefill: {
                        name: orderData.customer?.name || "",
                        email: orderData.customer?.email || "",
                    },
                    theme: {
                        color: "#198754"
                    },
                    handler: async function (response) {
                        payBtn.disabled = true;
                        payBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Verifying Signature...`;

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

                            onPaymentSucceeded();
                        } catch (err) {
                            alert(err.message || "Payment verification failed.");
                            payBtn.disabled = false;
                            payBtn.innerHTML = `<i class="bi bi-credit-card me-1"></i> Try Payment Again`;
                        }
                    },
                    modal: {
                        ondismiss: function () {
                            payBtn.disabled = false;
                            payBtn.innerHTML = `<i class="bi bi-credit-card me-1"></i> Pay & Confirm Booking`;
                        }
                    }
                };

                const rzp = new window.Razorpay(rzpOptions);
                rzp.on('payment.failed', function (resp) {
                    alert("Payment Failed: " + (resp.error?.description || "Transaction failed."));
                    payBtn.disabled = false;
                    payBtn.innerHTML = `<i class="bi bi-credit-card me-1"></i> Try Payment Again`;
                });
                rzp.open();

            } catch (err) {
                console.error("Payment error:", err);
                alert(err.message || "Payment could not be completed.");
                payBtn.disabled = false;
                payBtn.innerHTML = `<i class="bi bi-credit-card me-1"></i> Try Payment Again`;
            }
        };

        function onPaymentSucceeded() {
            if (holdCountdownTimerInterval) {
                clearInterval(holdCountdownTimerInterval);
                holdCountdownTimerInterval = null;
            }

            if (banner) banner.classList.add("d-none");
            if (modalTitle) modalTitle.textContent = "Booking Confirmed!";
            if (modalMsg) modalMsg.textContent = "Payment successful! Your court booking has been confirmed.";
            if (modalIcon) {
                modalIcon.innerHTML = `<i class="bi bi-check-lg"></i>`;
                modalIcon.style.background = "#e8f5ee";
                modalIcon.style.color = "#198754";
            }

            payBtn.classList.add("d-none");
            if (downloadInvoiceBtn) downloadInvoiceBtn.classList.remove("d-none");
            if (viewBtn) viewBtn.classList.remove("d-none");

            loadTimeSlots();
        }
    }

    if (viewBtn) {
        viewBtn.classList.add("d-none");
        viewBtn.onclick = () => {
            window.location.href = window.MY_BOOKINGS_URL || "/user/bookings";
        };
    }

    // Determine target expiration
    let targetTime = null;
    if (booking.expires_at) {
        targetTime = new Date(booking.expires_at).getTime();
    } else if (booking.expires_in_seconds) {
        targetTime = Date.now() + (booking.expires_in_seconds * 1000);
    } else {
        targetTime = Date.now() + (600 * 1000);
    }

    function tick() {
        const remaining = Math.max(0, Math.floor((targetTime - Date.now()) / 1000));
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        timerEl.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

        if (remaining <= 0) {
            clearInterval(holdCountdownTimerInterval);
            holdCountdownTimerInterval = null;

            if (banner) banner.classList.add("expired");
            if (noteEl) {
                noteEl.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> Hold expired. The court slot has been released.`;
            }
            if (modalTitle) modalTitle.textContent = "Reservation Expired";
            if (modalMsg) modalMsg.textContent = "Your reservation hold has expired. Please select another slot.";
            if (modalIcon) {
                modalIcon.innerHTML = `<i class="bi bi-x-circle"></i>`;
                modalIcon.style.background = "#f8d7da";
                modalIcon.style.color = "#842029";
            }
            if (payBtn) {
                payBtn.disabled = true;
                payBtn.classList.remove("btn-success");
                payBtn.classList.add("btn-secondary");
                payBtn.innerHTML = `<i class="bi bi-x-circle me-1"></i> Hold Expired`;
            }
            if (viewBtn) viewBtn.classList.remove("d-none");

            loadTimeSlots();
        }
    }

    tick();
    holdCountdownTimerInterval = setInterval(tick, 1000);
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
            onSuccess();
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
   DOWNLOAD BOOKING INVOICE (PDF)
========================================= */

async function downloadBookingInvoice(bookingId) {
    const downloadBtn = document.getElementById("downloadInvoiceModalBtn");
    const originalHTML = downloadBtn ? downloadBtn.innerHTML : "";

    if (downloadBtn) {
        downloadBtn.disabled = true;
        downloadBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Preparing PDF...`;
    }

    try {
        const resp = await fetch(`${API_BASE_URL}/bookings/${bookingId}/invoice?download=1`, {
            method: "GET",
            headers: getHeaders()
        });

        if (!resp.ok) {
            const errData = await resp.json().catch(() => ({}));
            throw new Error(errData.message || "Failed to download invoice.");
        }

        const blob = await resp.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `pickleball-invoice-${String(bookingId).padStart(5, '0')}.pdf`;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
    } catch (err) {
        alert(err.message || "Could not download invoice.");
    } finally {
        if (downloadBtn) {
            downloadBtn.disabled = false;
            downloadBtn.innerHTML = originalHTML || `<i class="bi bi-file-earmark-pdf me-1"></i> Download Invoice (PDF)`;
        }
    }
}




/* =========================================
   CREATE SUCCESS MODAL
========================================= */

function createBookingSuccessModal() {

    const modal =
        document.createElement(
            "div"
        );


    modal.id =
        "bookingSuccessModal";


    modal.className =
        "modal fade";


    modal.tabIndex =
        -1;


    modal.setAttribute(
        "aria-hidden",
        "true"
    );


    modal.innerHTML = `

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content border-0 shadow-lg">

                <div class="modal-body text-center p-4 p-md-5">

                    <!-- SUCCESS ICON -->

                    <div
                        class="mx-auto mb-4 d-flex align-items-center justify-content-center"
                        style="
                            width: 75px;
                            height: 75px;
                            border-radius: 50%;
                            background: #e8f5ee;
                            color: #198754;
                            font-size: 38px;
                        "
                    >

                        <i class="bi bi-check-lg"></i>

                    </div>


                    <!-- TITLE -->

                    <h3 class="fw-bold mb-2">

                        Booking Confirmed!

                    </h3>


                    <p class="text-muted mb-4">

                        Your court has been booked successfully.

                    </p>


                    <!-- BOOKING DETAILS -->

                    <div
                        class="text-start rounded-3 p-3 mb-4"
                        style="background: #f8f9fa;"
                    >

                        <!-- BOOKING ID -->

                        <div
                            class="d-flex justify-content-between align-items-center mb-3"
                        >

                            <span class="text-muted">

                                Booking ID

                            </span>

                            <strong
                                id="successBookingId"
                            >
                                --
                            </strong>

                        </div>


                        <!-- DATE -->

                        <div
                            class="d-flex justify-content-between align-items-center mb-3"
                        >

                            <span class="text-muted">

                                Date

                            </span>

                            <strong
                                id="successBookingDate"
                            >
                                --
                            </strong>

                        </div>


                        <!-- TIME -->

                        <div
                            class="d-flex justify-content-between align-items-center mb-3"
                        >

                            <span class="text-muted">

                                Time

                            </span>

                            <strong
                                id="successBookingTime"
                            >
                                --
                            </strong>

                        </div>


                        <!-- AMOUNT -->

                        <div
                            class="d-flex justify-content-between align-items-center"
                        >

                            <span class="text-muted">

                                Amount

                            </span>

                            <strong
                                id="successBookingAmount"
                                style="color: #198754;"
                            >
                                ₹0
                            </strong>

                        </div>

                    </div>


                    <!-- VIEW BOOKINGS -->

                    <button
                        type="button"
                        id="viewMyBookingsBtn"
                        class="btn btn-success w-100 py-2 mb-2"
                    >

                        <i class="bi bi-calendar-check me-1"></i>

                        View My Bookings

                    </button>


                    <!-- DONE -->

                    <button
                        type="button"
                        class="btn btn-light w-100 py-2"
                        data-bs-dismiss="modal"
                    >

                        Done

                    </button>

                </div>

            </div>

        </div>

    `;


    document.body.appendChild(
        modal
    );


    /*
     * View My Bookings
     */

    const viewMyBookingsBtn =
        modal.querySelector(
            "#viewMyBookingsBtn"
        );


    viewMyBookingsBtn.addEventListener(
        "click",
        () => {

            /*
             * Change this URL if your
             * actual route is different.
             */

            window.location.href =
                "/user/bookings";

        }
    );


    return modal;

}


/* =========================================
   BOOKING ALERT
========================================= */

function showBookingAlert(
    message
) {

    let alertBox =
        document.getElementById(
            "bookingAlert"
        );


    /*
     * Remove old alert
     */

    if (alertBox) {

        alertBox.remove();

    }


    /*
     * Create alert
     */

    alertBox =
        document.createElement(
            "div"
        );


    alertBox.id =
        "bookingAlert";


    alertBox.className =
        "alert alert-danger alert-dismissible fade show position-fixed";


    alertBox.style.top =
        "90px";


    alertBox.style.right =
        "20px";


    alertBox.style.zIndex =
        "9999";


    alertBox.style.maxWidth =
        "400px";


    alertBox.innerHTML = `

        <i class="bi bi-exclamation-circle me-2"></i>

        <span id="bookingAlertMessage"></span>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert"
        ></button>

    `;


    document.body.appendChild(
        alertBox
    );


    const alertMessage =
        alertBox.querySelector(
            "#bookingAlertMessage"
        );


    alertMessage.textContent =
        message;


    /*
     * Automatically remove
     * after 5 seconds
     */

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
   FORMAT DATE
========================================= */

function formatDate(date) {

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
   PRICE
========================================= */

function formatPrice(price) {

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
   LOADING
========================================= */

function showLoading() {

    bookingLoading.classList.remove(
        "d-none"
    );


    bookingContent.classList.add(
        "d-none"
    );

}


function hideLoading() {

    bookingLoading.classList.add(
        "d-none"
    );

}


/* =========================================
   ERROR
========================================= */

function showError(message) {

    bookingContent.classList.add(
        "d-none"
    );


    bookingError.classList.remove(
        "d-none"
    );


    bookingErrorMessage.textContent =
        message;

}


function hideError() {

    bookingError.classList.add(
        "d-none"
    );

}


/* =========================================
   SLOT LOADING
========================================= */

function showSlotLoading() {

    slotLoading.classList.remove(
        "d-none"
    );

}


function hideSlotLoading() {

    slotLoading.classList.add(
        "d-none"
    );

}


/* =========================================
   SLOT ERROR
========================================= */

function showSlotError(message) {

    slotError.classList.remove(
        "d-none"
    );


    slotErrorMessage.textContent =
        message;

}


function clearSlotMessages() {

    slotError.classList.add(
        "d-none"
    );


    noSlots.classList.add(
        "d-none"
    );

}


/* =========================================
   RETRY
========================================= */

retryBooking.addEventListener(
    "click",
    () => {

        loadCourt();

    }
);


/* =========================================
   INITIAL LOAD
========================================= */

loadCourt();