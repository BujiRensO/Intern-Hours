/**
 * Biometrics Attendance Module - WebAuthn & GPS Geolocation Integration
 */

// Helper to convert base64url string to ArrayBuffer
function base64urlToBuffer(base64url) {
  const padding = "=".repeat((4 - (base64url.length % 4)) % 4);
  const base64 = (base64url + padding).replace(/\-/g, "+").replace(/_/g, "/");
  const raw = window.atob(base64);
  const buffer = new Uint8Array(raw.length);
  for (let i = 0; i < raw.length; i++) {
    buffer[i] = raw.charCodeAt(i);
  }
  return buffer.buffer;
}

// Helper to convert ArrayBuffer to base64url string
function bufferToBase64url(buffer) {
  const bytes = new Uint8Array(buffer);
  let binary = "";
  for (let i = 0; i < bytes.byteLength; i++) {
    binary += String.fromCharCode(bytes[i]);
  }
  const base64 = window.btoa(binary);
  return base64.replace(/\+/g, "-").replace(/\//g, "_").replace(/=/g, "");
}

/**
 * Enroll Native Fingerprint / Facial Scanner
 */
function enrollBiometrics() {
  if (!window.PublicKeyCredential) {
    alert(
      "Your device or browser does not support biometric WebAuthn credentials.",
    );
    return;
  }

  const enrollBtn = document.querySelector(".biometric-actions-container a");
  const originalText = enrollBtn.innerHTML;
  enrollBtn.innerHTML = "🔄 Setting options...";

  fetch(apiBasePath + "api/biometric_register_options.php")
    .then((r) => r.json())
    .then((data) => {
      if (!data.success) {
        throw new Exception(data.error || "Failed to load options.");
      }

      const options = data.options;
      // Convert server base64url strings to buffers
      options.challenge = base64urlToBuffer(options.challenge);
      options.user.id = base64urlToBuffer(options.user.id);

      enrollBtn.innerHTML = "📳 Scan fingerprint...";

      return navigator.credentials.create({ publicKey: options });
    })
    .then((credential) => {
      if (!credential) {
        throw new Exception("Credential creation cancelled.");
      }

      const rawResponse = credential.response;
      const payload = {
        id: credential.id,
        rawId: bufferToBase64url(credential.rawId),
        type: credential.type,
        clientDataJSON: bufferToBase64url(rawResponse.clientDataJSON),
        attestationObject: bufferToBase64url(rawResponse.attestationObject),
      };

      enrollBtn.innerHTML = "🔄 Verifying key...";

      return fetch(apiBasePath + "api/biometric_register_verify.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
    })
    .then((r) => r.json())
    .then((res) => {
      if (res.success) {
        alert(
          "🎉 Fingerprint sensor successfully registered! You can now clock in and out using your fingerprint.",
        );
      } else {
        alert("❌ Registration failed: " + res.error);
      }
    })
    .catch((err) => {
      console.error(err);
      alert(
        "❌ Biometric enrollment error: " +
          (err.message ||
            "Credential creation aborted or timed out. Make sure you are using a secure connection (HTTPS) or localhost."),
      );
    })
    .finally(() => {
      enrollBtn.innerHTML = originalText;
    });
}

/**
 * Perform biometric check-in / check-out with GPS geolocation verification
 */
function biometricClock() {
  if (!window.PublicKeyCredential) {
    alert("Your browser or device does not support biometric check-in.");
    return;
  }

  const clockBtn = document.getElementById("biometric-clock-btn");
  const originalContent = clockBtn.innerHTML;
  clockBtn.innerHTML = "🕒 Fetching location...";
  clockBtn.style.pointerEvents = "none";

  // 1. Fetch native GPS coordinates first
  new Promise((resolve) => {
    if ("geolocation" in navigator) {
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          resolve({
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
          });
        },
        (err) => {
          console.warn("Geolocation warning: " + err.message);
          // Proceed with null coords, server/business logic handles mock/missing GPS gracefully
          resolve({ lat: null, lng: null });
        },
        { enableHighAccuracy: true, timeout: 5000 },
      );
    } else {
      resolve({ lat: null, lng: null });
    }
  })
    .then((gps) => {
      clockBtn.innerHTML = "📳 Scan fingerprint...";

      // 2. Fetch biometric challenges
      return fetch(apiBasePath + "api/biometric_auth_options.php")
        .then((r) => r.json())
        .then((data) => {
          if (!data.success) {
            throw new Error(
              data.error || "No registered biometric keys found.",
            );
          }

          const options = data.options;
          options.challenge = base64urlToBuffer(options.challenge);
          options.allowCredentials = options.allowCredentials.map((c) => {
            c.id = base64urlToBuffer(c.id);
            return c;
          });

          return navigator.credentials.get({ publicKey: options });
        })
        .then((assertion) => {
          if (!assertion) {
            throw new Error("Biometric scan cancelled.");
          }

          const rawResponse = assertion.response;
          const payload = {
            id: assertion.id,
            type: assertion.type,
            clientDataJSON: bufferToBase64url(rawResponse.clientDataJSON),
            authenticatorData: bufferToBase64url(rawResponse.authenticatorData),
            signature: bufferToBase64url(rawResponse.signature),
            gps_latitude: gps.lat,
            gps_longitude: gps.lng,
          };

          clockBtn.innerHTML = "🔄 Syncing DTR...";

          return fetch(apiBasePath + "api/attendance_action.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload),
          });
        });
    })
    .then((r) => r.json())
    .then((res) => {
      if (res.success) {
        // Trigger beautiful native alert or popup toast
        alert(res.message);

        // Reload all dashboard stats, hours progress charts, and DTR widgets immediately
        if (typeof loadHours === "function") loadHours();
        if (typeof loadCheckIns === "function") loadCheckIns();
        if (typeof renderCalendar === "function") renderCalendar();
        if (typeof updateQuickClockWidget === "function")
          updateQuickClockWidget();
      } else {
        alert("❌ Verification failed: " + res.error);
      }
    })
    .catch((err) => {
      console.error(err);
      alert(
        "❌ Attendance stamp failed: " +
          (err.message || "Authentication cancelled."),
      );
    })
    .finally(() => {
      clockBtn.innerHTML = originalContent;
      clockBtn.style.pointerEvents = "auto";
    });
}
