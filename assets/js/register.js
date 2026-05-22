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

function checkPasswordMatch() {
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirm_password").value;
  const message = document.getElementById("password-match-message");

  if (confirmPassword === "") {
    message.textContent = "";
    message.className = "validation-message";
    return;
  }

  if (password === confirmPassword) {
    message.textContent = "Passwords match";
    message.className = "validation-message success";
  } else {
    message.textContent = "Passwords do not match";
    message.className = "validation-message error";
  }
}

let emailCheckTimeout;
function checkEmailAvailability() {
  const email = document.getElementById("email").value;
  const message = document.getElementById("email-message");

  clearTimeout(emailCheckTimeout);

  if (email === "" || !email.includes("@")) {
    message.textContent = "";
    message.className = "validation-message";
    return;
  }

  message.textContent = "Checking...";
  message.className = "validation-message";

  emailCheckTimeout = setTimeout(() => {
    fetch("../api/check-email.php?email=" + encodeURIComponent(email))
      .then((response) => response.json())
      .then((data) => {
        if (data.available) {
          message.textContent = "Email is available";
          message.className = "validation-message success";
        } else {
          message.textContent = "Email is already registered";
          message.className = "validation-message error";
        }
      })
      .catch((error) => {
        message.textContent = "Error checking email";
        message.className = "validation-message error";
      });
  }, 500);
}
function toggleNewInput(type) {
  const select = document.getElementById(type + "_id");
  const input = document.getElementById("new_" + type + "_name");

  if (select.value === "new") {
    input.style.display = "block";
    input.required = true;
    input.focus();
  } else {
    input.style.display = "none";
    input.required = false;
  }
}
