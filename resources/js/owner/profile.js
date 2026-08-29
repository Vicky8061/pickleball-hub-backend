import { apiFetch } from "../api.js";

document.addEventListener("DOMContentLoaded", () => {
    loadOwnerProfile();
    setupProfileForm();
});

async function loadOwnerProfile() {
    try {
        const response = await apiFetch("/profile");
        if (response && response.success) {
            const user = response.data || {};
            const app = user.owner_application || {};

            // Left Sidebar Card
            const avatarEl = document.getElementById("profileAvatarBadge");
            const nameEl = document.getElementById("profileCardName");
            const emailEl = document.getElementById("profileCardEmail");
            const memberEl = document.getElementById("profileMemberSince");
            const statusEl = document.getElementById("profileStatusBadge");

            if (avatarEl) avatarEl.textContent = (user.name || "O").charAt(0).toUpperCase();
            if (nameEl) nameEl.textContent = user.name || "Owner";
            if (emailEl) emailEl.textContent = user.email || "";
            if (memberEl) memberEl.textContent = formatDateReadable(user.created_at);
            if (statusEl) statusEl.textContent = user.status || "Active";

            // Form Inputs
            setInputValue("inputProfileName", user.name);
            setInputValue("inputProfileEmail", user.email);
            setInputValue("inputProfilePhone", app.phone);
            setInputValue("inputProfileBusinessName", app.business_name);
            setInputValue("inputProfileCity", app.city);
            setInputValue("inputProfileState", app.state);
            setInputValue("inputProfilePincode", app.pincode);
            setInputValue("inputProfileAddress", app.address);
            setInputValue("inputProfileDescription", app.description);
        }
    } catch (error) {
        console.error("Load Profile Error:", error);
    }
}

function setupProfileForm() {
    const form = document.getElementById("ownerProfileForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        const submitBtn = document.getElementById("saveProfileBtn");
        const alertContainer = document.getElementById("profileAlertContainer");

        if (submitBtn) submitBtn.disabled = true;
        if (alertContainer) alertContainer.innerHTML = "";

        const formData = new FormData(form);
        const payload = {
            name: formData.get("name"),
            phone: formData.get("phone"),
            business_name: formData.get("business_name"),
            city: formData.get("city"),
            state: formData.get("state"),
            pincode: formData.get("pincode"),
            address: formData.get("address"),
            description: formData.get("description"),
        };

        const curPass = formData.get("current_password");
        const newPass = formData.get("new_password");
        const newPassConf = formData.get("new_password_confirmation");

        if (newPass) {
            payload.current_password = curPass;
            payload.new_password = newPass;
            payload.new_password_confirmation = newPassConf;
        }

        try {
            const response = await apiFetch("/owner/profile", {
                method: "PUT",
                body: JSON.stringify(payload)
            });

            if (response && response.success) {
                showAlert("Profile and venue settings updated successfully!", "success");
                
                // Clear password fields
                setInputValue("inputProfileCurrentPassword", "");
                setInputValue("inputProfileNewPassword", "");
                setInputValue("inputProfileConfirmPassword", "");

                await loadOwnerProfile();
            }
        } catch (error) {
            console.error("Update Profile Error:", error);
            showAlert(error.message || "Failed to update profile details.", "danger");
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

function showAlert(message, type = "success") {
    const container = document.getElementById("profileAlertContainer");
    if (!container) return;

    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
            ${escapeHtml(message)}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
}

function setInputValue(id, value) {
    const el = document.getElementById(id);
    if (el) el.value = value ?? "";
}

function formatDateReadable(dateStr) {
    if (!dateStr) return "-";
    const d = new Date(dateStr);
    return d.toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" });
}

function escapeHtml(str) {
    const div = document.createElement("div");
    div.textContent = str ?? "";
    return div.innerHTML;
}
