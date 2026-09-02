/* =====================================================
   PICKLEBALL HUB - CENTRAL API SERVICE LAYER
===================================================== */

const API_BASE_URL = "/api";

/**
 * Get stored auth token from localStorage or sessionStorage
 */
function getToken() {
    return (
        localStorage.getItem("auth_token") ||
        localStorage.getItem("token") ||
        sessionStorage.getItem("auth_token") ||
        sessionStorage.getItem("token")
    );
}

/**
 * Get CSRF Token from meta tag
 */
function getCsrfToken() {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    return csrfMeta ? csrfMeta.getAttribute("content") : null;
}

/**
 * Get default request headers with Bearer token & CSRF
 */
function getHeaders(customHeaders = {}) {
    const token = getToken();
    const csrfToken = getCsrfToken();

    const headers = {
        Accept: "application/json",
        ...customHeaders,
    };

    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }

    if (csrfToken) {
        headers["X-CSRF-TOKEN"] = csrfToken;
    }

    return headers;
}

/**
 * Global 401 Unauthorized Handler - Clears tokens & redirects to login
 */
function handleUnauthorized() {
    console.warn("Session expired or unauthenticated. Redirecting to login...");
    localStorage.clear();
    sessionStorage.clear();

    fetch("/auth/logout", {
        method: "POST",
        headers: {
            Accept: "application/json",
            "X-CSRF-TOKEN": getCsrfToken(),
        },
    }).finally(() => {
        window.location.href = "/login?expired=1";
    });
}

/**
 * Centralized API Fetch Wrapper
 * @param {string} endpoint - API endpoint (e.g. '/courts' or '/api/courts')
 * @param {Object} options - Fetch configuration options
 */
async function apiFetch(endpoint, options = {}) {
    const url =
        endpoint.startsWith("http://") || endpoint.startsWith("https://")
            ? endpoint
            : endpoint.startsWith("/api")
                ? endpoint
                : endpoint.startsWith("/")
                    ? `${API_BASE_URL}${endpoint}`
                    : `${API_BASE_URL}/${endpoint}`;

    const isFormData = options.body instanceof FormData;
    const defaultHeaders = isFormData
        ? getHeaders(options.headers || {})
        : getHeaders({
            "Content-Type": "application/json",
            ...(options.headers || {}),
        });

    if (isFormData && defaultHeaders["Content-Type"]) {
        delete defaultHeaders["Content-Type"];
    }

    const config = {
        ...options,
        headers: defaultHeaders,
    };

    try {
        const response = await fetch(url, config);

        // Check for 401 Unauthorized globally
        if (response.status === 401) {
            handleUnauthorized();
            throw new Error("Your session has expired. Please login again.");
        }

        let result;
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.includes("application/json")) {
            result = await response.json();
        } else {
            result = { success: response.ok, message: response.statusText };
        }

        if (!response.ok) {
            const errorMessage =
                result?.message ||
                (result?.errors
                    ? Object.values(result.errors).flat().join(" ")
                    : null) ||
                `HTTP error ${response.status}`;
            const error = new Error(errorMessage);
            error.status = response.status;
            error.data = result;
            throw error;
        }

        return result;
    } catch (err) {
        if (err.message && err.message.includes("session has expired")) {
            throw err;
        }
        console.error(`API Fetch Error [${url}]:`, err);
    }
}

/**
 * Global Logout Handler - Clears tokens, API logout & web session invalidate
 */
async function handleGlobalLogout() {
    try {
        const token = getToken();
        if (token) {
            await fetch("/api/auth/logout", {
                method: "POST",
                headers: getHeaders(),
            }).catch(() => { });
        }
    } catch (e) {
        console.warn("API Logout warning:", e);
    } finally {
        localStorage.clear();
        sessionStorage.clear();

        try {
            await fetch("/auth/logout", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                },
            });
        } catch (e) {
            console.warn("Web Session Logout warning:", e);
        }

        window.location.href = "/login";
    }
}

// Automatic Logout Event Delegation
if (typeof document !== "undefined") {
    document.addEventListener("click", (e) => {
        const logoutBtn = e.target.closest("#adminLogoutBtn, #logoutBtn, #mobileLogoutBtn, #profileLogoutBtn, .btn-logout");
        if (logoutBtn) {
            e.preventDefault();
            handleGlobalLogout();
        }
    });
}

// Make globally available on window object
window.API_BASE_URL = API_BASE_URL;
window.getToken = getToken;
window.getHeaders = getHeaders;
window.apiFetch = apiFetch;
window.handleUnauthorized = handleUnauthorized;
window.handleGlobalLogout = handleGlobalLogout;

export { API_BASE_URL, getToken, getHeaders, apiFetch, handleUnauthorized, handleGlobalLogout };
