/* =====================================================
   USER LAYOUT COMMON JS
   Handles mobile menu, notifications, mobile logout, & navbar user info
===================================================== */

document.addEventListener("DOMContentLoaded", () => {
    initializeMobileMenu();
    initializeNotifications();
    initializeMobileLogout();
    syncNavbarUserInfo();
});

/* =====================================================
   MOBILE MENU TOGGLE
===================================================== */
function initializeMobileMenu() {
    const menuButton = document.getElementById("userMenuBtn");
    const mobileMenu = document.getElementById("userMobileMenu");

    if (!menuButton || !mobileMenu) {
        return;
    }

    menuButton.addEventListener("click", (event) => {
        event.stopPropagation();
        mobileMenu.classList.toggle("active");
    });

    // Close menu when clicking outside
    document.addEventListener("click", (event) => {
        if (
            mobileMenu.classList.contains("active") &&
            !mobileMenu.contains(event.target) &&
            !menuButton.contains(event.target)
        ) {
            mobileMenu.classList.remove("active");
        }
    });

    // Close menu when clicking any navigation link inside mobile menu
    const mobileLinks = mobileMenu.querySelectorAll("a.user-mobile-link");
    mobileLinks.forEach((link) => {
        link.addEventListener("click", () => {
            mobileMenu.classList.remove("active");
        });
    });
}

/* =====================================================
   NOTIFICATION BUTTON
===================================================== */
function initializeNotifications() {
    const notificationBtn = document.getElementById("notificationBtn");

    if (!notificationBtn) {
        return;
    }

    notificationBtn.addEventListener("click", () => {
        alert("No new notifications.");
    });
}

/* =====================================================
   MOBILE LOGOUT BUTTON
===================================================== */
function initializeMobileLogout() {
    const mobileLogoutBtn = document.getElementById("mobileLogoutBtn");

    if (!mobileLogoutBtn) {
        return;
    }

    mobileLogoutBtn.addEventListener("click", async () => {
        try {
            await apiFetch("/logout", { method: "POST" });
        } catch (err) {
            console.error("API Logout error:", err);
        }

        try {
            await fetch("/auth/logout", {
                method: "POST",
                headers: getHeaders(),
            });
        } catch (err) {
            console.error("Session Logout error:", err);
        }

        localStorage.clear();
        sessionStorage.clear();
        window.location.href = "/login";
    });
}

/* =====================================================
   NAVBAR USER INFO SYNC
===================================================== */
function syncNavbarUserInfo() {
    const navbarUserName = document.getElementById("navbarUserName");
    if (!navbarUserName) {
        return;
    }

    try {
        const userStr =
            localStorage.getItem("auth_user") ||
            localStorage.getItem("user") ||
            sessionStorage.getItem("auth_user") ||
            sessionStorage.getItem("user");

        if (userStr) {
            const user = JSON.parse(userStr);
            if (user && user.role === "owner") {
                window.location.replace("/owner/dashboard");
                return;
            }
            if (user && user.role === "admin") {
                window.location.replace("/admin/dashboard");
                return;
            }
            if (user && user.name) {
                navbarUserName.textContent = user.name;
            }
        }
    } catch (err) {
        console.error("Error parsing stored user info:", err);
    }
}
