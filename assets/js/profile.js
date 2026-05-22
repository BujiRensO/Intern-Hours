/**
 * Profile Page Management
 */

document.addEventListener("DOMContentLoaded", function () {
  initTabs();
  initPrivacyToggle();
  initDarkModeToggle();
  initProfilePictureForm();
  initPasswordForm();
  initNetwork();
});

/**
 * Handle Tab Switching
 */
function initTabs() {
  const tabs = document.querySelectorAll(".settings-tab");
  const sections = document.querySelectorAll(".settings-section");

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      const target = tab.dataset.target;

      // Update active tab
      tabs.forEach((t) =>
        t.classList.remove("active", "bg-gray-900", "text-white"),
      );
      tabs.forEach((t) => t.classList.add("text-gray-600", "hover:bg-gray-50"));

      tab.classList.add("active", "bg-gray-900", "text-white");
      tab.classList.remove("text-gray-600", "hover:bg-gray-50");

      // Show target section
      sections.forEach((s) => s.classList.add("hidden"));
      document.getElementById("section-" + target).classList.remove("hidden");
    });
  });
}

/**
 * Initialize Privacy Toggle
 */
function initPrivacyToggle() {
  const toggle = document.getElementById("privacy-toggle");
  if (!toggle) return;

  toggle.addEventListener("change", function () {
    const isPublic = this.checked ? 1 : 0;
    const formData = new FormData();
    formData.append("is_public", isPublic);

    fetch(apiBasePath + "api/profile_update.php", {
      method: "POST",
      body: formData,
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          showToast("Privacy settings updated");
        }
      });
  });
}

/**
 * Initialize Dark Mode Toggle
 */
function initDarkModeToggle() {
  const toggle = document.getElementById("darkmode-toggle");
  if (!toggle) return;

  // Sync toggle state with current theme
  const isDark = document.documentElement.classList.contains("dark");
  toggle.checked = isDark;

  toggle.addEventListener("change", function () {
    toggleDarkMode();
    showToast("Theme updated");
  });
}

/**
 * Initialize Password Form
 */
function initPasswordForm() {
  const form = document.getElementById("password-form");
  if (!form) return;

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const password = document.getElementById("new_password").value;
    const confirm = document.getElementById("confirm_password").value;
    const alertBox = document.getElementById("password-alert");

    if (password.length < 6) {
      showAlert(alertBox, "Password must be at least 6 characters", "error");
      return;
    }

    if (password !== confirm) {
      showAlert(alertBox, "Passwords do not match", "error");
      return;
    }

    const formData = new FormData();
    formData.append("password", password);
    // Add other required fields from current user data (fetched from session or hidden inputs)
    formData.append("name", userName);
    formData.append("email", userEmail);
    formData.append("office_id", officeId);
    formData.append("organization_id", organizationId);

    fetch(apiBasePath + "api/profile.php", {
      method: "POST",
      body: formData,
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          showAlert(alertBox, "Password updated successfully", "success");
          form.reset();
        } else {
          showAlert(alertBox, data.error || "Update failed", "error");
        }
      })
      .catch((err) => {
        console.error("Error:", err);
        showAlert(alertBox, "Connection error", "error");
      });
  });
}

/**
 * Initialize Profile Picture Form and Live Preview
 */
function initProfilePictureForm() {
  const form = document.getElementById("profile-picture-form");
  const input = document.getElementById("profile_picture_url");
  const previewPic = document.getElementById("preview-profile-pic");
  const previewInitials = document.getElementById("preview-initials");
  const alertBox = document.getElementById("profile-picture-alert");

  if (!form || !input) return;

  // Real-time live preview update
  input.addEventListener("input", function () {
    const url = this.value.trim();
    if (url) {
      previewPic.src = url;
      previewPic.classList.remove("hidden");
      previewInitials.classList.add("hidden");
    } else {
      previewPic.classList.add("hidden");
      previewInitials.classList.remove("hidden");
    }
  });

  // Preview fallback on image error (broken URL)
  previewPic.addEventListener("error", function () {
    previewPic.classList.add("hidden");
    previewInitials.classList.remove("hidden");
  });

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const url = input.value.trim();

    const formData = new FormData();
    formData.append("profile_picture", url);

    fetch(apiBasePath + "api/profile_update.php", {
      method: "POST",
      body: formData
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          showAlert(alertBox, "Profile picture updated successfully", "success");
          
          // Update sidebar avatar in real-time
          const sidebarContainer = document.getElementById("sidebar-avatar-container");
          if (sidebarContainer) {
            if (url) {
              sidebarContainer.innerHTML = `<img id="sidebar-profile-pic" src="${escapeHtml(url)}" alt="Profile Picture" class="w-full h-full object-cover">`;
            } else {
              const initial = userName.charAt(0).toUpperCase();
              sidebarContainer.innerHTML = `<span id="sidebar-initials">${initial}</span>`;
            }
          }
          
          showToast("Profile Picture Updated");
        } else {
          showAlert(alertBox, data.error || "Save failed", "error");
        }
      })
      .catch((err) => {
        console.error("Error:", err);
        showAlert(alertBox, "Connection error", "error");
      });
  });
}

/**
 * Helper: Show Alert
 */
function showAlert(box, message, type) {
  box.textContent = message;
  box.className =
    "p-4 rounded-xl text-sm font-medium mb-6 " +
    (type === "success"
      ? "bg-green-50 text-green-600"
      : "bg-red-50 text-red-600");
  box.classList.remove("hidden");
  setTimeout(() => {
    box.classList.add("hidden");
  }, 5000);
}

/**
 * Helper: Show Toast
 */
function showToast(message) {
  const status = document.getElementById("save-status");
  if (status) {
    status.textContent = message.toUpperCase();
    status.style.opacity = "1";
    setTimeout(() => {
      status.style.opacity = "0";
    }, 2000);
  }
}

/**
 * Initialize Network Section
 */
function initNetwork() {
  const list = document.getElementById("interns-list");
  if (!list) return;

  fetch(apiBasePath + "api/interns.php")
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        renderNetwork(
          data.interns.filter((i) => parseInt(i.id) !== currentUserId),
        );
      } else {
        list.innerHTML =
          '<p class="col-span-full text-center text-sm text-gray-400 py-8">Could not load network</p>';
      }
    })
    .catch((err) => {
      console.error("Error:", err);
      list.innerHTML =
        '<p class="col-span-full text-center text-sm text-gray-400 py-8">Connection error</p>';
    });
}

/**
 * Render Network Cards
 */
function renderNetwork(interns) {
  const list = document.getElementById("interns-list");
  if (!list) return;

  if (interns.length === 0) {
    list.innerHTML =
      '<p class="col-span-full text-center text-sm text-gray-400 py-8">No colleagues found</p>';
    return;
  }

  list.innerHTML = "";
  interns.forEach((intern) => {
    const isPublic = parseInt(intern.is_public) === 1;
    const initial = intern.name.charAt(0).toUpperCase();
    const avatarHtml = intern.profile_picture
      ? `<img src="${escapeHtml(intern.profile_picture)}" alt="${escapeHtml(intern.name)}" class="w-full h-full object-cover">`
      : initial;
    const avatarBg = intern.profile_picture ? 'bg-transparent' : 'bg-gray-900 text-white';

    const card = document.createElement("div");
    card.className =
      "group p-4 rounded-2xl border border-gray-100 bg-gray-50/50 hover:bg-white hover:shadow-xl hover:shadow-gray-100 transition duration-300";

    card.innerHTML = `
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl ${avatarBg} flex items-center justify-center font-bold text-lg mb-3 shadow-lg shadow-gray-200 overflow-hidden">
                    ${avatarHtml}
                </div>
                <h5 class="font-bold text-gray-900 text-sm truncate w-full">${intern.name}</h5>
                <div class="mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full ${isPublic ? "bg-green-500" : "bg-gray-300"}"></span>
                    <span class="text-[10px] font-bold uppercase tracking-wider ${isPublic ? "text-green-600" : "text-gray-400"}">
                        ${isPublic ? "Public" : "Private"}
                    </span>
                </div>
                ${
                  isPublic
                    ? `
                    <button onclick="openInternModal(${intern.id})" class="mt-3 w-full py-2 bg-white border border-gray-200 text-[10px] font-bold uppercase tracking-widest text-gray-600 rounded-lg hover:bg-gray-900 hover:text-white hover:border-gray-900 transition">
                        View Logs
                    </button>
                `
                    : `
                    <div class="mt-3 w-full py-2 bg-gray-100 text-[10px] font-bold uppercase tracking-widest text-gray-400 rounded-lg cursor-not-allowed">
                        Locked
                    </div>
                `
                }
            </div>
        `;
    list.appendChild(card);
  });
}

/**
 * Helper to escape HTML characters (XSS Prevention)
 */
function escapeHtml(str) {
  if (!str) return '';
  const div = document.createElement("div");
  div.textContent = str;
  return div.innerHTML;
}
