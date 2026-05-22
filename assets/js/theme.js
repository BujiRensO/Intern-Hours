/**
 * System-wide Theme Management
 */

(function () {
  // Check if we are on the landing page (index.php or /)
  const isLandingPage =
    window.location.pathname.endsWith("index.php") ||
    window.location.pathname.endsWith("/") ||
    window.location.pathname === "";

  if (isLandingPage) {
    document.documentElement.classList.remove("dark");
    document.body && document.body.classList.remove("dark-mode");
    return;
  }

  // Determine if we should use dark mode
  // Rule: Must be logged in AND have the preference enabled in session
  // fallback to localStorage ONLY if we want to support dark mode on login page (user said no)

  let shouldBeDark = false;

  if (typeof isLoggedIn !== "undefined" && isLoggedIn) {
    // Use session preference if logged in
    shouldBeDark = sessionDarkMode;
    // Sync localStorage for consistency
    localStorage.setItem("theme", shouldBeDark ? "dark" : "light");
  } else {
    // If not logged in, default to light mode (per user request)
    shouldBeDark = false;
  }

  // Apply theme
  if (shouldBeDark) {
    document.documentElement.classList.add("dark");
    if (document.body) {
      document.body.classList.add("dark-mode");
    } else {
      document.addEventListener("DOMContentLoaded", () => {
        document.body.classList.add("dark-mode");
      });
    }
  } else {
    document.documentElement.classList.remove("dark");
    if (document.body) {
      document.body.classList.remove("dark-mode");
    }
  }
})();

/**
 * Toggle theme between light and dark
 */
function toggleDarkMode() {
  const isDark = document.documentElement.classList.toggle("dark");
  document.body && document.body.classList.toggle("dark-mode", isDark);

  const theme = isDark ? "dark" : "light";
  localStorage.setItem("theme", theme);

  // Sync with backend (the apiBasePath should be defined in the page)
  const base = typeof apiBasePath !== "undefined" ? apiBasePath : "../";

  fetch(base + "api/profile_update.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "is_darkmode=" + (isDark ? "1" : "0"),
  })
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        console.log("Theme preference saved");
        // We don't reload here to avoid interrupting the user,
        // but the session is now updated on the server.
      }
    })
    .catch((err) => console.error("Failed to sync theme with backend:", err));

  return isDark;
}
