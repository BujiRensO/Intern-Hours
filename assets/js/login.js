function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const eyeIcon = document.getElementById(inputId + "-eye");

  if (input.type === "password") {
    input.type = "text";
    eyeIcon.innerHTML = `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
        `;
  } else {
    input.type = "password";
    eyeIcon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        `;
  }
}

function loginWithGoogle() {
  window.location.href = "../api/google-login.php";
}

document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("login-form");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const loginBtn = document.getElementById("login-btn");
      const loginMessage = document.getElementById("login-message");

      if (loginBtn.disabled) {
        return;
      }

      loginBtn.disabled = true;
      loginBtn.textContent = "Logging in...";

      const formData = new FormData(this);

      fetch("../api/login.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            window.location.href = data.redirect;
          } else if (data.error) {
            if (data.blocked) {
              blockLogin(data.blocked_until);
            } else {
              const attemptsMessage =
                document.getElementById("attempts-message");
              attemptsMessage.textContent = "";
              loginMessage.textContent = data.error;
              loginMessage.className = "validation-message error";
              if (data.attempts) {
                showAttempts(data.attempts);
              }
            }

            loginBtn.disabled = false;
            loginBtn.textContent = "Login";
          }
        })
        .catch((error) => {
          console.error("Login error:", error);
          loginMessage.textContent = "An error occurred. Please try again.";
          loginMessage.className = "validation-message error";
          loginBtn.disabled = false;
          loginBtn.textContent = "Login";
        });
    });
  }
});

function showAttempts(attempts) {
  const attemptsMessage = document.getElementById("attempts-message");
  const loginMessage = document.getElementById("login-message");
  const remaining = 3 - attempts;
  loginMessage.textContent = "";
  attemptsMessage.textContent = `Login attempts remaining: ${remaining}`;
  attemptsMessage.className = "validation-message error";
}

function blockLogin(blockedUntil) {
  if (window.isBlockedLogin) {
    return;
  }
  window.isBlockedLogin = true;

  const loginBtn = document.getElementById("login-btn");
  const attemptsMessage = document.getElementById("attempts-message");
  const loginMessage = document.getElementById("login-message");

  loginMessage.textContent = "";
  attemptsMessage.textContent = "";
  loginBtn.disabled = true;
  loginBtn.textContent = "Login Blocked";

  startCountdown(blockedUntil);
}

function startCountdown(blockedUntil) {
  const attemptsMessage = document.getElementById("attempts-message");
  const loginBtn = document.getElementById("login-btn");

  if (window.countdownInterval) {
    clearInterval(window.countdownInterval);
  }

  attemptsMessage.textContent = "";

  window.countdownInterval = setInterval(() => {
    const now = Math.floor(Date.now() / 1000);
    const remaining = blockedUntil - now;

    if (remaining <= 0) {
      clearInterval(window.countdownInterval);
      window.isBlockedLogin = false;
      loginBtn.disabled = false;
      loginBtn.textContent = "Login";
      attemptsMessage.textContent = "";
      return;
    }

    loginBtn.disabled = true;
    loginBtn.textContent = `Wait ${remaining}s`;
    attemptsMessage.textContent = `Too many failed attempts. Please wait ${remaining} seconds.`;
    attemptsMessage.className = "validation-message error";
  }, 1000);
}
