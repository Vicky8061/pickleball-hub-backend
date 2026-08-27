/*
|--------------------------------------------------------------------------
| Pickleball Hub - User Dashboard
|--------------------------------------------------------------------------
*/

const API_BASE_URL = "/api";


/*
|--------------------------------------------------------------------------
| Wishlist State
|--------------------------------------------------------------------------
*/

let wishlistCourtIds = new Set();


/*
|--------------------------------------------------------------------------
| Authentication Token
|--------------------------------------------------------------------------
*/

function getToken() {

    return localStorage.getItem("auth_token");

}


/*
|--------------------------------------------------------------------------
| API Request Helper
|--------------------------------------------------------------------------
*/

async function apiRequest(url, options = {}) {
    return apiFetch(url, options);
}


/*
|--------------------------------------------------------------------------
| Load User Wishlist
|--------------------------------------------------------------------------
*/

async function loadUserWishlist() {

    try {

        const response =
            await apiRequest(
                `${API_BASE_URL}/wishlists`
            );


        const wishlists =
            response?.data?.data ||
            response?.data ||
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


/*
|--------------------------------------------------------------------------
| Load Logged In User
|--------------------------------------------------------------------------
*/

async function loadUser() {

    try {

        const response =
            await apiRequest(
                `${API_BASE_URL}/user`
            );


        const user =
            response?.data ||
            response;


        const userName =
            user?.name ||
            "User";


        /*
        |--------------------------------------------------------------------------
        | Navbar Name
        |--------------------------------------------------------------------------
        */

        const navbarUserName =
            document.getElementById(
                "navbarUserName"
            );


        if (navbarUserName) {

            navbarUserName.textContent =
                userName;

        }


        /*
        |--------------------------------------------------------------------------
        | Dashboard Name
        |--------------------------------------------------------------------------
        */

        const dashboardUserName =
            document.getElementById(
                "dashboardUserName"
            );


        if (dashboardUserName) {

            dashboardUserName.textContent =
                `Ready to play, ${userName}?`;

        }

    } catch (error) {

        console.error(
            "User loading error:",
            error
        );

    }

}


/*
|--------------------------------------------------------------------------
| Load Featured Courts
|--------------------------------------------------------------------------
*/

async function loadFeaturedCourts() {

    const container =
        document.getElementById(
            "featuredCourts"
        );


    if (!container) {

        return;

    }


    try {

        /*
        |--------------------------------------------------------------------------
        | Get Courts
        |--------------------------------------------------------------------------
        */

        const response =
            await apiRequest(
                `${API_BASE_URL}/courts`
            );


        /*
        |--------------------------------------------------------------------------
        | Extract Courts
        |--------------------------------------------------------------------------
        */

        const courts =
            response?.data?.data ||
            response?.data ||
            [];


        if (
            !Array.isArray(courts) ||
            courts.length === 0
        ) {

            showNoCourts(container);

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | First 6 Courts
        |--------------------------------------------------------------------------
        */

        const featuredCourts =
            courts.slice(0, 6);


        /*
        |--------------------------------------------------------------------------
        | Create Cards
        |--------------------------------------------------------------------------
        */

        const courtCards =
            await Promise.all(
                featuredCourts.map(
                    court =>
                        createCourtCard(court)
                )
            );


        container.innerHTML =
            courtCards.join("");


        /*
        |--------------------------------------------------------------------------
        | Initialize Sliders
        |--------------------------------------------------------------------------
        */

        initializeCourtSliders();


    } catch (error) {

        console.error(
            "Court loading error:",
            error
        );


        showCourtError(container);

    }

}


/*
|--------------------------------------------------------------------------
| Get Court Images
|--------------------------------------------------------------------------
|
| GET /api/courts/{court}/images
|
*/

async function getCourtImages(courtId) {

    try {

        const response =
            await apiRequest(
                `${API_BASE_URL}/courts/${courtId}/images`
            );


        const images =
            response?.data?.data ||
            response?.data ||
            [];


        if (!Array.isArray(images)) {

            return [];

        }


        /*
        |--------------------------------------------------------------------------
        | Convert API Images
        |--------------------------------------------------------------------------
        */

        return images
            .map(image => {

                let url = null;


                /*
                |--------------------------------------------------------------------------
                | image_url
                |--------------------------------------------------------------------------
                */

                if (image.image_url) {

                    url =
                        image.image_url;

                }


                /*
                |--------------------------------------------------------------------------
                | url
                |--------------------------------------------------------------------------
                */

                else if (image.url) {

                    url =
                        image.url;

                }


                /*
                |--------------------------------------------------------------------------
                | image path
                |--------------------------------------------------------------------------
                */

                else if (image.image) {

                    const imagePath =
                        image.image
                            .replace(/^\/+/, "");


                    url =
                        `/storage/${imagePath}`;

                }


                return {

                    url:
                        url ||
                        "/assets/images/court-placeholder.jpg",

                    is_primary:
                        image.is_primary === true ||
                        image.is_primary === 1

                };

            })
            .filter(image => image.url);

    } catch (error) {

        console.error(
            `Image loading error for court ${courtId}:`,
            error
        );


        return [];

    }

}


/*
|--------------------------------------------------------------------------
| Create Court Card
|--------------------------------------------------------------------------
*/

async function createCourtCard(court) {

    /*
    |--------------------------------------------------------------------------
    | Get Multiple Images
    |--------------------------------------------------------------------------
    */

    const images =
        await getCourtImages(court.id);


    /*
    |--------------------------------------------------------------------------
    | Court Information
    |--------------------------------------------------------------------------
    */

    const courtName =
        court.court_name ||
        court.name ||
        "Pickleball Court";


    const address =
        court.address ||
        court.location ||
        "Location unavailable";


    const price =
        court.price_per_hour ??
        court.price ??
        0;


    const courtType =
        court.court_type ||
        "Pickleball";


    /*
    |--------------------------------------------------------------------------
    | No Image Fallback
    |--------------------------------------------------------------------------
    */

    if (images.length === 0) {

        images.push({

            url:
                "/assets/images/court-placeholder.jpg",

            is_primary: false

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Put Primary Image First
    |--------------------------------------------------------------------------
    */

    images.sort(
        (a, b) =>
            Number(b.is_primary) -
            Number(a.is_primary)
    );


    /*
    |--------------------------------------------------------------------------
    | Image Slides
    |--------------------------------------------------------------------------
    */

    const imageSlides =
        images.map(
            (image, index) => {

                return `

                    <div
                        class="
                            court-slide
                            ${index === 0 ? "active" : ""}
                        "
                        data-slide="${index}"
                    >

                        <img
                            src="${escapeAttribute(image.url)}"
                            alt="${escapeAttribute(courtName)}"
                            class="court-image"

                            onerror="
                                this.onerror=null;
                                this.src='/assets/images/court-placeholder.jpg';
                            "
                        >

                    </div>

                `;

            }
        ).join("");


    /*
    |--------------------------------------------------------------------------
    | Slider Navigation
    |--------------------------------------------------------------------------
    */

    let navigation = "";


    if (images.length > 1) {

        navigation = `

            <button
                type="button"
                class="
                    court-slider-btn
                    court-prev
                "
                aria-label="Previous image"
            >

                <i class="bi bi-chevron-left"></i>

            </button>


            <button
                type="button"
                class="
                    court-slider-btn
                    court-next
                "
                aria-label="Next image"
            >

                <i class="bi bi-chevron-right"></i>

            </button>

        `;

    }


    /*
    |--------------------------------------------------------------------------
    | Slider Dots
    |--------------------------------------------------------------------------
    */

    let dots = "";


    if (images.length > 1) {

        dots = `

            <div class="court-slider-dots">

                ${images.map(
            (image, index) => `

                        <span
                            class="
                                court-dot
                                ${index === 0 ? "active" : ""}
                            "
                            data-slide="${index}"
                        ></span>

                    `
        ).join("")}

            </div>

        `;

    }


    /*
    |--------------------------------------------------------------------------
    | Wishlist Check
    |--------------------------------------------------------------------------
    */

    const isWishlisted =
        wishlistCourtIds.has(
            Number(court.id)
        );


    /*
    |--------------------------------------------------------------------------
    | Final Card
    |--------------------------------------------------------------------------
    */

    return `

        <div
            class="
                col-xl-4
                col-lg-4
                col-md-6
            "
        >

            <div
                class="court-card"
                data-court-id="${court.id}"
            >


                <!-- =================================
                     IMAGE SLIDER
                ================================== -->

                <div class="court-image-wrapper">

                    <div class="court-slider">

                        ${imageSlides}

                    </div>


                    <!-- COURT TYPE -->

                    <span class="court-type-badge">

                        ${escapeHtml(courtType)}

                    </span>


                    <!-- NAVIGATION -->

                    ${navigation}


                    <!-- DOTS -->

                    ${dots}


                    <!-- WISHLIST HEART -->

                    <button
                        type="button"
                        class="wishlist-toggle-btn"
                        data-court-id="${court.id}"
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


                <!-- =================================
                     CARD BODY
                ================================== -->

                <div class="court-card-body">


                    <!-- COURT NAME -->

                    <h5 class="court-name">

                        ${escapeHtml(courtName)}

                    </h5>


                    <!-- LOCATION -->

                    <div class="court-location">

                        <i class="bi bi-geo-alt"></i>

                        <span>

                            ${escapeHtml(address)}

                        </span>

                    </div>


                    <!-- FOOTER -->

                    <div class="court-card-footer">


                        <!-- PRICE -->

                        <div class="court-price">

                            <strong>

                                ₹${escapeHtml(price)}

                            </strong>

                            <span>

                                / hour

                            </span>

                        </div>


                        <!-- VIEW BUTTON -->

                        <a
                            href="/user/courts/${court.id}"
                            class="court-view-btn"
                        >

                            View Court

                            <i class="bi bi-arrow-right"></i>

                        </a>

                    </div>


                </div>

            </div>

        </div>

    `;

}


/*
|--------------------------------------------------------------------------
| Initialize Court Sliders
|--------------------------------------------------------------------------
*/

function initializeCourtSliders() {

    const cards =
        document.querySelectorAll(
            ".court-card[data-court-id]"
        );


    cards.forEach(card => {

        const slides =
            card.querySelectorAll(
                ".court-slide"
            );


        const dots =
            card.querySelectorAll(
                ".court-dot"
            );


        const previousButton =
            card.querySelector(
                ".court-prev"
            );


        const nextButton =
            card.querySelector(
                ".court-next"
            );


        /*
        |--------------------------------------------------------------------------
        | No Slider Needed
        |--------------------------------------------------------------------------
        */

        if (slides.length <= 1) {

            return;

        }


        let currentSlide = 0;


        /*
        |--------------------------------------------------------------------------
        | Show Slide
        |--------------------------------------------------------------------------
        */

        function showSlide(index) {

            if (index < 0) {

                index =
                    slides.length - 1;

            }


            if (
                index >=
                slides.length
            ) {

                index = 0;

            }


            slides.forEach(
                slide => {

                    slide.classList.remove(
                        "active"
                    );

                }
            );


            dots.forEach(
                dot => {

                    dot.classList.remove(
                        "active"
                    );

                }
            );


            slides[index]
                .classList
                .add("active");


            if (dots[index]) {

                dots[index]
                    .classList
                    .add("active");

            }


            currentSlide =
                index;

        }


        /*
        |--------------------------------------------------------------------------
        | Previous Button
        |--------------------------------------------------------------------------
        */

        if (previousButton) {

            previousButton.addEventListener(
                "click",
                function (event) {

                    event.preventDefault();

                    event.stopPropagation();

                    showSlide(
                        currentSlide - 1
                    );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Next Button
        |--------------------------------------------------------------------------
        */

        if (nextButton) {

            nextButton.addEventListener(
                "click",
                function (event) {

                    event.preventDefault();

                    event.stopPropagation();

                    showSlide(
                        currentSlide + 1
                    );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Dots
        |--------------------------------------------------------------------------
        */

        dots.forEach(
            (dot, index) => {

                dot.addEventListener(
                    "click",
                    function (event) {

                        event.preventDefault();

                        event.stopPropagation();

                        showSlide(index);

                    }
                );

            }
        );

    });

}


/*
|--------------------------------------------------------------------------
| Wishlist Toggle
|--------------------------------------------------------------------------
*/

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

            await apiRequest(
                `${API_BASE_URL}/wishlists/${id}`,
                { method: "DELETE" }
            );

            wishlistCourtIds.delete(id);

            icon.className = "bi bi-heart";

            button.style.color = "#6c757d";

            button.title =
                "Add to wishlist";

        } else {

            /* ADD */

            await apiRequest(
                `${API_BASE_URL}/wishlists`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type":
                            "application/json"
                    },
                    body: JSON.stringify({
                        court_id: id
                    })
                }
            );

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


/*
|--------------------------------------------------------------------------
| Wishlist Click Handler
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| No Courts
|--------------------------------------------------------------------------
*/

function showNoCourts(container) {

    container.innerHTML = `

        <div class="col-12">

            <div class="empty-state">

                <i
                    class="bi bi-grid"
                    style="font-size:35px;"
                ></i>

                <p>
                    No courts available right now.
                </p>

            </div>

        </div>

    `;

}


/*
|--------------------------------------------------------------------------
| Court Error
|--------------------------------------------------------------------------
*/

function showCourtError(container) {

    container.innerHTML = `

        <div class="col-12">

            <div class="empty-state">

                <i
                    class="bi bi-exclamation-circle"
                    style="font-size:35px;"
                ></i>

                <p>
                    Unable to load courts.
                </p>

                <button
                    type="button"
                    class="btn btn-sm btn-success mt-2"
                    id="retryCourtsBtn"
                >

                    Try Again

                </button>

            </div>

        </div>

    `;


    const retryButton =
        document.getElementById(
            "retryCourtsBtn"
        );


    if (retryButton) {

        retryButton.addEventListener(
            "click",
            loadFeaturedCourts
        );

    }

}


/*
|--------------------------------------------------------------------------
| Load Upcoming Tournaments
|--------------------------------------------------------------------------
*/

async function loadUpcomingTournaments() {

    const container =
        document.getElementById(
            "upcomingTournaments"
        );


    if (!container) {

        return;

    }


    try {

        const response =
            await apiRequest(
                `${API_BASE_URL}/tournaments`
            );


        const tournaments =
            response?.data?.data ||
            response?.data ||
            [];


        if (
            !Array.isArray(tournaments) ||
            tournaments.length === 0
        ) {

            showNoTournaments(container);

            return;

        }


        const upcoming =
            tournaments
                .filter(
                    tournament =>
                        tournament.status ===
                        "upcoming"
                )
                .slice(0, 3);


        if (upcoming.length === 0) {

            showNoTournaments(container);

            return;

        }


        container.innerHTML =
            upcoming
                .map(
                    tournament =>
                        createTournamentCard(
                            tournament
                        )
                )
                .join("");


    } catch (error) {

        console.error(
            "Tournament loading error:",
            error
        );


        container.innerHTML = `

            <div class="col-12">

                <div class="empty-state">

                    <i
                        class="bi bi-trophy"
                        style="font-size:35px;"
                    ></i>

                    <p>
                        Unable to load tournaments.
                    </p>

                </div>

            </div>

        `;

    }

}


/*
|--------------------------------------------------------------------------
| Tournament Card
|--------------------------------------------------------------------------
*/

function createTournamentCard(tournament) {

    return `
        <div class="col-md-6 col-xl-4">

            <div class="tournament-card">

                <!-- BANNER -->
                <div class="tournament-card-image">

                    <img
                        src="${tournament.banner || '/assets/images/tournament-placeholder.jpg'}"
                        alt="${tournament.title}"
                        class="tournament-banner"
                        onerror="this.src='/assets/images/tournament-placeholder.jpg'"
                    >

                    <span class="tournament-status">
                        ${tournament.status}
                    </span>

                </div>


                <!-- CONTENT -->
                <div class="tournament-card-body">

                    <h5 class="tournament-title">
                        ${tournament.title}
                    </h5>

                    <p class="tournament-description">
                        ${tournament.description ?? ''}
                    </p>


                    <!-- DATE -->
                    <div class="tournament-info">

                        <div>
                            <i class="bi bi-calendar-event"></i>

                            <span>
                                ${tournament.tournament_date}
                            </span>
                        </div>

                        <div>
                            <i class="bi bi-clock"></i>

                            <span>
                                ${tournament.start_time}
                                -
                                ${tournament.end_time}
                            </span>
                        </div>

                    </div>


                    <!-- FOOTER -->
                    <div class="tournament-footer">

                        <div>
                            <small>Entry Fee</small>

                            <strong>
                                ₹${tournament.entry_fee}
                            </strong>
                        </div>

                        <div>
                            <small>Prize</small>

                            <strong>
                                ${tournament.prize}
                            </strong>
                        </div>

                    </div>


                    <a
                        href="/user/tournaments/${tournament.id}"
                        class="btn tournament-btn">

                        View Tournament

                        <i class="bi bi-arrow-right"></i>

                    </a>

                </div>

            </div>

        </div>
    `;
}

/*
|--------------------------------------------------------------------------
| No Tournaments
|--------------------------------------------------------------------------
*/

function showNoTournaments(
    container
) {

    container.innerHTML = `

        <div class="col-12">

            <div class="empty-state">

                <i
                    class="bi bi-trophy"
                    style="font-size:35px;"
                ></i>

                <p>
                    No upcoming tournaments.
                </p>

            </div>

        </div>

    `;

}


/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value) {

    if (
        value === null ||
        value === undefined
    ) {

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


/*
|--------------------------------------------------------------------------
| Escape Attribute
|--------------------------------------------------------------------------
*/

function escapeAttribute(value) {

    return escapeHtml(value);

}


/*
|--------------------------------------------------------------------------
| Load Banners Carousel
|--------------------------------------------------------------------------
*/

async function loadBanners() {
    const bannerContainer = document.getElementById("dashboardBannerContainer");
    const carouselIndicators = document.getElementById("bannerCarouselIndicators");
    const carouselInner = document.getElementById("bannerCarouselInner");

    if (!bannerContainer || !carouselIndicators || !carouselInner) {
        return;
    }

    try {
        const response = await apiFetch("/banners");
        const banners = response?.data?.data || response?.data || [];

        if (!Array.isArray(banners) || banners.length === 0) {
            bannerContainer.classList.add("d-none");
            return;
        }

        let indicatorsHtml = "";
        let slidesHtml = "";

        banners.forEach((banner, index) => {
            const activeClass = index === 0 ? "active" : "";
            const ariaCurrent = index === 0 ? 'aria-current="true"' : "";

            indicatorsHtml += `
                <button type="button" data-bs-target="#dashboardBannerCarousel" data-bs-slide-to="${index}" class="${activeClass}" ${ariaCurrent} aria-label="Slide ${index + 1}"></button>
            `;

            const imgUrl = banner.image_url || "/images/banner-placeholder.jpg";
            const redirectAttr = banner.redirect_url ? `href="${escapeAttribute(banner.redirect_url)}" target="_blank"` : "";

            slidesHtml += `
                <div class="carousel-item ${activeClass}">
                    <a ${redirectAttr} class="d-block text-decoration-none">
                        <img src="${escapeAttribute(imgUrl)}" class="d-block w-100 object-fit-cover" style="max-height: 380px; min-height: 200px; border-radius: 1rem;" alt="${escapeHTML(banner.title || 'Banner')}">
                        ${banner.title ? `<div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded-3 p-2 mb-2"><h5>${escapeHTML(banner.title)}</h5></div>` : ''}
                    </a>
                </div>
            `;
        });

        carouselIndicators.innerHTML = indicatorsHtml;
        carouselInner.innerHTML = slidesHtml;
        bannerContainer.classList.remove("d-none");

        const prevControl = bannerContainer.querySelector(".carousel-control-prev");
        const nextControl = bannerContainer.querySelector(".carousel-control-next");
        if (prevControl && nextControl) {
            if (banners.length <= 1) {
                prevControl.classList.add("d-none");
                nextControl.classList.add("d-none");
            } else {
                prevControl.classList.remove("d-none");
                nextControl.classList.remove("d-none");
            }
        }
    } catch (err) {
        console.error("Banner loading error:", err);
        bannerContainer.classList.add("d-none");
    }
}


/*
|--------------------------------------------------------------------------
| Dashboard Initialization
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "DOMContentLoaded",
    async () => {

        console.log(
            "Pickleball Hub Dashboard Loaded"
        );


        await Promise.all([
            loadUser(),
            loadBanners(),
            loadUserWishlist(),
            loadFeaturedCourts(),
            loadUpcomingTournaments()
        ]);

    }
);