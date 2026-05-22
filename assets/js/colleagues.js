// ===== Colleagues Page JavaScript =====

let allInterns = [];
let currentSort = "name"; // 'name', 'hours'
let currentSearch = "";

// Avatar colors — deterministic based on name
const avatarColors = [
  "#2563eb",
  "#7c3aed",
  "#059669",
  "#dc2626",
  "#d97706",
  "#0891b2",
  "#4f46e5",
  "#be185d",
  "#065f46",
  "#92400e",
  "#1d4ed8",
  "#9333ea",
  "#16a34a",
  "#e11d48",
  "#ca8a04",
];

function getAvatarColor(name) {
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  return avatarColors[Math.abs(hash) % avatarColors.length];
}

// Month names
const monthNames = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December",
];

// ===== Load Colleagues List =====
function loadColleaguesList() {
  const container = document.getElementById("colleagues-container");
  container.innerHTML = `
        <div class="colleagues-loading">
            <div class="spinner"></div>
            <p>Loading colleagues...</p>
        </div>
    `;

  fetch(apiBasePath + "api/interns.php")
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        allInterns = data.interns.filter(
          (i) => parseInt(i.id) !== currentUserId,
        );
        updateColleaguesStats();
        renderInterns();
      } else {
        container.innerHTML = `<div class="colleagues-empty"><p>Could not load colleagues.</p></div>`;
      }
    })
    .catch((err) => {
      console.error("Error:", err);
      container.innerHTML = `<div class="colleagues-empty"><p>Connection error. Please try again.</p></div>`;
    });
}

// ===== Update Colleagues Stats =====
function updateColleaguesStats() {
  const totalEl = document.getElementById("stat-total-colleagues");
  const publicEl = document.getElementById("stat-public-count");
  const hoursEl = document.getElementById("stat-total-team-hours");

  if (totalEl) totalEl.textContent = allInterns.length;

  const publicCount = allInterns.filter((i) => i.total_hours !== null).length;
  if (publicEl) publicEl.textContent = publicCount;

  const totalHours = allInterns.reduce((sum, i) => {
    return sum + (i.total_hours !== null ? parseFloat(i.total_hours) : 0);
  }, 0);
  if (hoursEl) hoursEl.textContent = totalHours.toFixed(1);
}

// ===== Render Intern Cards =====
function renderInterns() {
  const container = document.getElementById("colleagues-container");
  let filtered = allInterns;

  // Apply search filter
  if (currentSearch) {
    const q = currentSearch.toLowerCase();
    filtered = filtered.filter(
      (i) =>
        i.name.toLowerCase().includes(q) ||
        (i.email && i.email.toLowerCase().includes(q)),
    );
  }

  // Apply sort
  filtered.sort((a, b) => {
    if (currentSort === "hours") {
      const ha = a.total_hours !== null ? parseFloat(a.total_hours) : -1;
      const hb = b.total_hours !== null ? parseFloat(b.total_hours) : -1;
      return hb - ha;
    }
    return a.name.localeCompare(b.name);
  });

  if (filtered.length === 0) {
    container.innerHTML = `
            <div class="colleagues-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <p>${currentSearch ? "No colleagues match your search." : "No colleagues found in your office."}</p>
                <span class="sub">${currentSearch ? "Try a different search term." : "They will appear here once they join."}</span>
            </div>
        `;
    return;
  }

  container.innerHTML = "";
  filtered.forEach((intern) => {
    const card = document.createElement("div");
    card.className = "colleague-card";
    card.onclick = () => openInternModal(intern.id);

    const color = getAvatarColor(intern.name);
    const initial = intern.name.charAt(0).toUpperCase();
    const hasHours = intern.total_hours !== null;
    const hours = hasHours ? parseFloat(intern.total_hours).toFixed(1) : null;
    const avatarHtml = intern.profile_picture
      ? `<img src="${escapeHtml(intern.profile_picture)}" alt="${escapeHtml(intern.name)}" class="w-full h-full object-cover">`
      : initial;
    const avatarBg = intern.profile_picture ? 'transparent' : color;

    card.innerHTML = `
            <div class="colleague-card-header">
                <div class="colleague-avatar" style="background: ${avatarBg}; overflow: hidden;">${avatarHtml}</div>
                <div>
                    <div class="colleague-name">${escapeHtml(intern.name)}</div>
                    <div class="colleague-email">${escapeHtml(intern.email || "")}</div>
                </div>
            </div>
            <div class="colleague-card-body">
                <div>
                    ${
                      hasHours
                        ? `<div class="colleague-hours">${hours}<span class="unit">hrs</span></div>
                           <div class="colleague-hours-label">Total Hours</div>`
                        : `<div class="colleague-private-badge">
                               <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                               Private
                           </div>`
                    }
                </div>
                <button class="colleague-view-btn" onclick="event.stopPropagation(); openInternModal(${intern.id});">
                    View
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        `;

    container.appendChild(card);
  });
}

// ===== Intern Hours Modal =====
let modalMonth = new Date().getMonth() + 1;
let modalYear = new Date().getFullYear();
let modalInternId = null;
let modalHoursData = {};
let modalAllHoursData = {};

function openInternModal(internId) {
  modalInternId = internId;
  modalMonth = new Date().getMonth() + 1;
  modalYear = new Date().getFullYear();
  modalHoursData = {};
  modalAllHoursData = {};

  const modal = document.getElementById("intern-hours-modal");
  modal.classList.add("active");

  // Show loading in modal body
  document.getElementById("intern-modal-body").innerHTML = `
        <div class="colleagues-loading" style="padding: 40px;">
            <div class="spinner"></div>
            <p>Loading hours data...</p>
        </div>
    `;

  // Load all hours first, then monthly
  loadInternAllHours(() => {
    loadInternMonthHours();
  });
}

function closeInternModal() {
  document.getElementById("intern-hours-modal").classList.remove("active");
  modalInternId = null;
}

function loadInternAllHours(callback) {
  fetch(
    apiBasePath +
      "api/intern-hours.php?intern_id=" +
      modalInternId +
      "&all=true",
  )
    .then((r) => r.json())
    .then((data) => {
      if (data.success) {
        if (data.is_private) {
          renderPrivateNotice(data.intern);
          return;
        }

        // Populate modal header
        const intern = data.intern;
        const color = getAvatarColor(intern.name);
        const modalAvatar = document.getElementById("intern-modal-avatar");
        if (modalAvatar) {
          if (intern.profile_picture) {
            modalAvatar.style.background = "transparent";
            modalAvatar.style.overflow = "hidden";
            modalAvatar.innerHTML = `<img src="${escapeHtml(intern.profile_picture)}" alt="${escapeHtml(intern.name)}" class="w-full h-full object-cover">`;
          } else {
            modalAvatar.style.background = color;
            modalAvatar.style.overflow = "";
            modalAvatar.textContent = intern.name.charAt(0).toUpperCase();
          }
        }
        document.getElementById("intern-modal-name").textContent = intern.name;
        document.getElementById("intern-modal-subtitle").textContent =
          (intern.office_name || "") + " • " + (intern.organization_name || "");

        modalAllHoursData = data.hours;

        // Stats
        const totalHours = parseFloat(data.total_hours || 0);
        const daysLogged = Object.keys(data.hours).length;
        const avgPerDay = daysLogged > 0 ? totalHours / daysLogged : 0;

        document.getElementById("intern-stat-total").textContent =
          totalHours.toFixed(1);
        document.getElementById("intern-stat-days").textContent = daysLogged;
        document.getElementById("intern-stat-avg").textContent =
          avgPerDay.toFixed(1);

        if (callback) callback();
      } else {
        document.getElementById("intern-modal-body").innerHTML = `
                    <div class="private-notice">
                        <p>${data.error || "Could not load data."}</p>
                    </div>
                `;
      }
    })
    .catch((err) => {
      console.error("Error:", err);
      document.getElementById("intern-modal-body").innerHTML = `
                <div class="private-notice"><p>Connection error.</p></div>
            `;
    });
}

function loadInternMonthHours() {
  fetch(
    apiBasePath +
      "api/intern-hours.php?intern_id=" +
      modalInternId +
      "&month=" +
      modalMonth +
      "&year=" +
      modalYear,
  )
    .then((r) => r.json())
    .then((data) => {
      if (data.success && !data.is_private) {
        modalHoursData = data.hours;
        renderModalCalendar();
      }
    })
    .catch((err) => console.error("Error loading month hours:", err));
}

function renderPrivateNotice(intern) {
  const color = getAvatarColor(intern.name);
  const modalAvatar = document.getElementById("intern-modal-avatar");
  if (modalAvatar) {
    if (intern.profile_picture) {
      modalAvatar.style.background = "transparent";
      modalAvatar.style.overflow = "hidden";
      modalAvatar.innerHTML = `<img src="${escapeHtml(intern.profile_picture)}" alt="${escapeHtml(intern.name)}" class="w-full h-full object-cover">`;
    } else {
      modalAvatar.style.background = color;
      modalAvatar.style.overflow = "";
      modalAvatar.textContent = intern.name.charAt(0).toUpperCase();
    }
  }
  document.getElementById("intern-modal-name").textContent = intern.name;
  document.getElementById("intern-modal-subtitle").textContent =
    (intern.office_name || "") + " • " + (intern.organization_name || "");

  document.getElementById("intern-stat-total").textContent = "—";
  document.getElementById("intern-stat-days").textContent = "—";
  document.getElementById("intern-stat-avg").textContent = "—";

  document.getElementById("intern-modal-body").innerHTML = `
        <div class="private-notice">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <p>This intern's profile is private</p>
            <span class="sub">They haven't enabled public visibility for their hours yet.</span>
        </div>
    `;
}

function renderModalCalendar() {
  const body = document.getElementById("intern-modal-body");

  const firstDay = new Date(modalYear, modalMonth - 1, 1);
  const lastDay = new Date(modalYear, modalMonth, 0);
  const daysInMonth = lastDay.getDate();
  const startingDow = firstDay.getDay();

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  // Compute month total
  const monthTotal = Object.values(modalHoursData).reduce(
    (sum, v) => sum + parseFloat(v),
    0,
  );

  let html = `
        <div class="intern-modal-calendar">
            <div class="intern-modal-calendar-nav">
                <button onclick="modalPrevMonth()">← Prev</button>
                <h4>${monthNames[modalMonth - 1]} ${modalYear} <span style="font-weight:500;color:#6b7280;font-size:13px;">(${monthTotal.toFixed(1)} hrs)</span></h4>
                <button onclick="modalNextMonth()">Next →</button>
            </div>
            <div class="intern-modal-grid">
    `;

  // Day headers
  ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"].forEach((d) => {
    html += `<div class="day-hdr">${d}</div>`;
  });

  // Empty cells
  for (let i = 0; i < startingDow; i++) {
    html += `<div class="day empty"></div>`;
  }

  // Days
  for (let day = 1; day <= daysInMonth; day++) {
    const dateStr =
      modalYear +
      "-" +
      String(modalMonth).padStart(2, "0") +
      "-" +
      String(day).padStart(2, "0");
    const cellDate = new Date(modalYear, modalMonth - 1, day);
    cellDate.setHours(0, 0, 0, 0);

    let classes = "day";
    let content = `<span>${day}</span>`;

    if (modalHoursData[dateStr]) {
      classes += " logged";
      content += `<span class="day-hrs">${parseFloat(modalHoursData[dateStr]).toFixed(1)}h</span>`;
    }

    if (cellDate.getTime() === today.getTime()) {
      classes += " today";
    }

    html += `<div class="${classes}">${content}</div>`;
  }

  html += `</div></div>`;
  body.innerHTML = html;
}

function modalPrevMonth() {
  modalMonth--;
  if (modalMonth < 1) {
    modalMonth = 12;
    modalYear--;
  }
  loadInternMonthHours();
}

function modalNextMonth() {
  modalMonth++;
  if (modalMonth > 12) {
    modalMonth = 1;
    modalYear++;
  }
  loadInternMonthHours();
}

// ===== Search =====
function onSearchInput(e) {
  currentSearch = e.target.value;
  renderInterns();
}

// ===== Sort =====
function setSort(type) {
  currentSort = type;
  document.querySelectorAll(".sort-btn").forEach((btn) => {
    btn.classList.toggle("active", btn.dataset.sort === type);
  });
  renderInterns();
}

// ===== Helpers =====
function escapeHtml(str) {
  const div = document.createElement("div");
  div.textContent = str;
  return div.innerHTML;
}

// ===== Close modal on escape / outside click =====
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") closeInternModal();
});

document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("intern-hours-modal");
  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === this) closeInternModal();
    });
  }

  // Only auto-load on the dedicated colleagues page (not dashboard)
  if (document.getElementById("colleagues-container")) {
    loadColleaguesList();
  }
});
