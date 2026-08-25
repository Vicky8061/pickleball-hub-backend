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


    const price =
        Number(
            court?.price_per_hour || 0
        );


    summaryTotal.textContent =
        `₹${formatPrice(price)}`;


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
     * Bootstrap modal
     */

    if (
        typeof bootstrap !== "undefined"
    ) {

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