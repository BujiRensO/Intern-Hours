// Global variables will be initialized in the PHP file
// currentMonth, currentYear, selectedDate, hoursData, monthHoursData, allHoursData, userId, filterFromDate, filterToDate

const fixedHolidays = {
  "01-01": "New Year's Day",
  "02-25": "EDSA Revolution",
  "04-09": "Araw ng Kagitingan",
  "05-01": "Labor Day",
  "06-12": "Independence Day",
  "08-21": "Ninoy Aquino Day",
  "11-01": "All Saints' Day",
  "11-02": "All Souls' Day",
  "11-30": "Bonifacio Day",
  "12-08": "Immaculate Conception",
  "12-25": "Christmas Day",
  "12-30": "Rizal Day",
  "12-31": "New Year's Eve",

  "02-21": "Lawrenze Bheras Day",
};

const movableHolidays = {
  // 2024
  "2024-02-10": "Chinese New Year",
  "2024-03-28": "Maundy Thursday",
  "2024-03-29": "Good Friday",
  "2024-03-30": "Black Saturday",
  "2024-08-26": "National Heroes Day",
  // 2025
  "2025-01-29": "Chinese New Year",
  "2025-04-17": "Maundy Thursday",
  "2025-04-18": "Good Friday",
  "2025-04-19": "Black Saturday",
  "2025-08-25": "National Heroes Day",
  // 2026
  "2026-02-17": "Chinese New Year",
  "2026-04-02": "Maundy Thursday",
  "2026-04-03": "Good Friday",
  "2026-04-04": "Black Saturday",
  "2026-08-31": "National Heroes Day",
};

// Initialize calendar
document.addEventListener("DOMContentLoaded", function () {
  if (document.getElementById("filter-from-date")) {
    setDefaultDates();
  }
  loadAllHours();
  loadAbsences();
  loadHours();
  loadCheckIns();
  startQuickClockTimer();
  loadInterns();
  renderCalendar();
});

function getUserIdQuery() {
  return typeof userId !== "undefined" ? "&userId=" + userId : "";
}

function loadInterns() {
  const list = document.getElementById("interns-list");
  if (!list) return;

  fetch(apiBasePath + "api/interns.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        if (data.interns.length === 0) {
          list.innerHTML =
            '<p class="text-gray-500 text-sm">No colleagues found.</p>';
          return;
        }

        list.innerHTML = "";
        data.interns.forEach((intern) => {
          // Skip self
          if (parseInt(intern.id) === userId) return;

          const div = document.createElement("div");
          div.className =
            "flex flex-col items-center gap-2 bg-white p-3 rounded-lg border border-gray-100 shadow-sm cursor-pointer hover:border-blue-200 hover:shadow-md transition-all";
          div.title = "Click to view " + intern.name.split(" ")[0] + "'s hours";
          div.onclick = () => {
            if (typeof openInternModal === "function") {
              openInternModal(parseInt(intern.id));
            }
          };
          const hoursBadge =
            intern.total_hours !== null
              ? `<div class="mt-1 px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-full">${parseFloat(intern.total_hours).toFixed(1)}h</div>`
              : `<div class="mt-1 px-2 py-0.5 bg-gray-100 text-gray-400 text-[10px] font-bold rounded-full">Private</div>`;

          div.innerHTML = `
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 font-bold text-lg" title="${intern.email}">
                            ${intern.name.charAt(0)}
                        </div>
                        <div class="text-center">
                            <span class="text-xs font-semibold text-gray-800 truncate block w-20">${intern.name.split(" ")[0]}</span>
                            ${hoursBadge}
                        </div>
                    `;
          list.appendChild(div);
        });

        if (list.children.length === 0) {
          list.innerHTML =
            '<p class="text-gray-500 text-sm">No other colleagues.</p>';
        }
      } else {
        list.innerHTML = `<p class="text-red-500 text-sm">Error: ${data.error || "Failed to load colleagues."}</p>`;
      }
    })
    .catch((error) => {
      console.error("Error loading colleagues:", error);
      list.innerHTML = '<p class="text-red-500 text-sm">Connection error. Please try again.</p>';
    });
}

function setDefaultDates() {
  const today = new Date();
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

  document.getElementById("filter-from-date").valueAsDate = firstDay;
  document.getElementById("filter-to-date").valueAsDate = today;
}

function loadAllHours() {
  fetch(apiBasePath + "api/hours.php?all=true" + getUserIdQuery())
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        allHoursData = data.hours;
        updateTotalHours();
      }
    })
    .catch((error) => console.error("Error loading all hours:", error));
}

function getDailyDutyHours() {
    if (typeof dutyFrom === 'undefined' || typeof dutyTo === 'undefined' || !dutyFrom || !dutyTo) return 8;
    const fromParts = dutyFrom.split(':').map(Number);
    const toParts = dutyTo.split(':').map(Number);
    if (fromParts.length !== 2 || toParts.length !== 2) return 8;
    
    let hours = (toParts[0] - fromParts[0]) + (toParts[1] - fromParts[1]) / 60;
    
    // Deduct lunch break only if the toggle is enabled
    if (typeof hasLunchBreak !== 'undefined' && hasLunchBreak) {
        hours -= 1;
    }
    
    return hours > 0 ? hours : 8;
}

// Calculate OT based on actual clock-in/out times vs the duty window
function getOvertimeFromCheckIn(logs) {
    if (!logs || typeof dutyFrom === 'undefined' || typeof dutyTo === 'undefined' || !dutyFrom || !dutyTo) {
        return 0;
    }
    
    const toMins = (timeStr) => {
        if (!timeStr) return null;
        // timeStr may be HH:MM or HH:MM:SS
        const parts = timeStr.split(':').map(Number);
        return parts[0] * 60 + parts[1];
    };
    
    const dutyFromMins = toMins(dutyFrom);
    const dutyToMins = toMins(dutyTo);
    
    const morningIn  = toMins(logs.morning_in);
    const morningOut = toMins(logs.morning_out);
    const afternoonIn  = toMins(logs.afternoon_in);
    const afternoonOut = toMins(logs.afternoon_out);
    
    let otMins = 0;
    
    // OT before duty start: earliest clock-in before dutyFrom
    const firstIn = (morningIn !== null) ? morningIn : afternoonIn;
    if (firstIn !== null && firstIn < dutyFromMins) {
        otMins += dutyFromMins - firstIn;
    }
    
    // OT after duty end: latest clock-out after dutyTo
    const lastOut = (afternoonOut !== null) ? afternoonOut : morningOut;
    if (lastOut !== null && lastOut > dutyToMins) {
        otMins += lastOut - dutyToMins;
    }
    
    return otMins / 60;
}

function renderCalendar() {
  const dailyDutyHours = getDailyDutyHours();
  const firstDay = new Date(currentYear, currentMonth - 1, 1);
  const lastDay = new Date(currentYear, currentMonth, 0);
  const daysInMonth = lastDay.getDate();
  const startingDayOfWeek = firstDay.getDay();

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

  let titleText = monthNames[currentMonth - 1] + " " + currentYear;
  if (filterFromDate && filterToDate) {
    titleText =
      "Filtered (" +
      formatDate(filterFromDate) +
      " to " +
      formatDate(filterToDate) +
      ")";
  }

  document.getElementById("calendar-title").textContent = titleText;

  const calendarGrid = document.getElementById("calendar-grid");
  calendarGrid.innerHTML = "";

  // Day headers
  const dayHeaders = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  dayHeaders.forEach((day) => {
    const header = document.createElement("div");
    header.className = "day-header";
    header.textContent = day;
    calendarGrid.appendChild(header);
  });

  // Empty cells for days from previous month
  for (let i = 0; i < startingDayOfWeek; i++) {
    const emptyCell = document.createElement("div");
    emptyCell.className = "day-cell other-month";
    calendarGrid.appendChild(emptyCell);
  }

  // Days of current month
  const today = new Date();
  for (let day = 1; day <= daysInMonth; day++) {
    const cell = document.createElement("div");
    const dateStr = String(day).padStart(2, "0");
    const fullDate =
      currentYear + "-" + String(currentMonth).padStart(2, "0") + "-" + dateStr;

    cell.className = "day-cell";

    // Check if today
    if (
      day === today.getDate() &&
      currentMonth === today.getMonth() + 1 &&
      currentYear === today.getFullYear()
    ) {
      cell.classList.add("today");
    }

    // Check if date is in the future
    const cellDate = new Date(currentYear, currentMonth - 1, day);
    cellDate.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);
    const isFuture = cellDate > today;

    // Check if has logged hours
    if (hoursData[fullDate]) {
      cell.classList.add("logged");
    }

    if (isFuture) {
      cell.classList.add("disabled");
    }

    const monthDay = String(currentMonth).padStart(2, "0") + "-" + dateStr;
    const holiday = movableHolidays[fullDate] || fixedHolidays[monthDay];

    if (holiday) {
      console.log(`Holiday found: ${fullDate} (${monthDay}) - ${holiday}`);
      cell.classList.add("holiday");
    }

    let birthdayBadgesHtml = "";
    if (typeof birthdaysData !== "undefined" && Array.isArray(birthdaysData)) {
      const dayBirthdays = birthdaysData.filter((b) => {
        if (!b.birthdate) return false;
        const parts = b.birthdate.split(/[-/.]/);
        if (parts.length !== 3) return false;

        let bMonth = 0;
        let bDay = 0;

        if (parts[0].length === 4) {
          // YYYY-MM-DD or YYYY/MM/DD
          bMonth = parseInt(parts[1], 10);
          bDay = parseInt(parts[2], 10);
        } else if (parts[2].length === 4) {
          // MM-DD-YYYY or DD-MM-YYYY
          const p0 = parseInt(parts[0], 10);
          const p1 = parseInt(parts[1], 10);
          if (p0 > 12) {
            bDay = p0;
            bMonth = p1;
          } else if (p1 > 12) {
            bMonth = p0;
            bDay = p1;
          } else {
            bMonth = p0;
            bDay = p1;
          }
        } else {
          bMonth = parseInt(parts[1], 10);
          bDay = parseInt(parts[2], 10);
        }

        return bMonth === currentMonth && bDay === day;
      });

      dayBirthdays.forEach((b) => {
        const displayName = b.nickname || b.name.split(" ")[0];
        const parts = b.birthdate.split(/[-/.]/);
        let ageHtml = "";
        if (parts.length === 3) {
          const birthYear = parseInt(
            parts[0].length === 4
              ? parts[0]
              : parts[2].length === 4
                ? parts[2]
                : null,
            10,
          );
          if (birthYear && birthYear <= currentYear) {
            const age = currentYear - birthYear;
            if (age > 0) {
              ageHtml = ` (${age})`;
            }
          }
        }
        const bEscaped = JSON.stringify(b).replace(/"/g, '&quot;');
        birthdayBadgesHtml += `<div class="birthday-badge" onclick="event.stopPropagation(); showBirthdayModal(${bEscaped}, event)" title="${displayName}'s Birthday! 🎉">🎂 ${displayName}${ageHtml}</div>`;
      });
    }

    // Compute time-window OT for this day
    const dayLogs = checkInsData[fullDate];
    const otHours = getOvertimeFromCheckIn(dayLogs);
    const otHtml = otHours > 0
        ? `<span class="ot-tag" title="Overtime: ${otHours.toFixed(1)} hrs beyond duty window">+${otHours.toFixed(1)} OT</span>`
        : '';

    cell.innerHTML = `
            <div class="day-cell-inner">
              <div class="day-cell-date">${day}</div>
              ${hoursData[fullDate] ? `<div class="day-cell-hours">${hoursData[fullDate]}h ${otHtml}</div>` : ""}
              ${absencesData[fullDate] ? `<div class="absence-badge ${absencesData[fullDate].status.toLowerCase()}">${absencesData[fullDate].status}</div>` : ""}
              ${holiday ? `<div class="holiday-badge" title="${holiday}">${holiday}</div>` : ""}
              ${birthdayBadgesHtml}
            </div>
            <div class="day-cell-spotlight"></div>
        `;

    if (absencesData[fullDate]) {
      cell.onclick = () => openAbsenceModal(fullDate);
    } else if (!isFuture) {
      cell.onclick = () => openLogModal(fullDate);
    } else {
      cell.onclick = () => openAbsenceModal(fullDate);
    }

    calendarGrid.appendChild(cell);
  }

  // High-performance proximity border glow for the entire Bento Grid
  const grid = document.getElementById("calendar-grid");
  if (grid) {
    // Enable glowing border calculations when mouse is within the grid
    grid.addEventListener("mouseenter", () => {
      grid.querySelectorAll(".day-cell").forEach((cell) => {
        cell.style.setProperty("--border-opacity", "1");
      });
    });

    // Fade out glows when mouse leaves the grid completely
    grid.addEventListener("mouseleave", () => {
      grid.querySelectorAll(".day-cell").forEach((cell) => {
        cell.style.setProperty("--border-opacity", "0");
      });
    });

    // Track coordinates globally for all active day cells in the grid
    grid.addEventListener("mousemove", (e) => {
      const activeCells = grid.querySelectorAll(
        ".day-cell:not(.disabled):not(.other-month)",
      );
      activeCells.forEach((cell) => {
        const rect = cell.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        cell.style.setProperty("--mouse-x", `${x}px`);
        cell.style.setProperty("--mouse-y", `${y}px`);
      });
    });
  }

  updateStats();
}

function loadAbsences() {
  fetch(
    apiBasePath +
    "api/absences.php?month=" +
    currentMonth +
    "&year=" +
    currentYear +
    getUserIdQuery(),
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        absencesData = {};
        data.absences.forEach((abs) => {
          absencesData[abs.date] = abs;
        });
        renderCalendar();
      }
    })
    .catch((error) => console.error("Error loading absences:", error));
}

function openAbsenceFromLogModal() {
  const dateStr = selectedDate;
  closeModal();
  openAbsenceModal(dateStr);
}

function openAbsenceModal(dateStr) {
  selectedDate = dateStr;
  const absence = absencesData[dateStr];
  const statusDisplay = document.getElementById("absence-status-display");
  const deleteBtn = document.getElementById("absence-delete-btn");
  const submitBtn = document.getElementById("absence-submit-btn");

  document.getElementById("absence-modal-date").value = dateStr;
  document.getElementById("absence-modal-reason").value = absence
    ? absence.reason
    : "";

  if (absence) {
    statusDisplay.textContent = "Status: " + absence.status;
    statusDisplay.className = "absence-badge " + absence.status.toLowerCase();
    statusDisplay.style.display = "block";
    statusDisplay.style.fontSize = "14px";
    statusDisplay.style.padding = "10px";
    deleteBtn.style.display = "block";
    submitBtn.textContent = "Update Reason";
  } else {
    statusDisplay.style.display = "none";
    deleteBtn.style.display = "none";
    submitBtn.textContent = "Submit Request";
  }

  document.getElementById("absence-modal").classList.add("active");
  document.getElementById("absence-modal-reason").focus();
}

function closeAbsenceModal() {
  document.getElementById("absence-modal").classList.remove("active");
  selectedDate = null;
}

function saveAbsence() {
  const reason = document.getElementById("absence-modal-reason").value;

  if (reason.trim() === "") {
    alert("Please provide a reason for your absence");
    return;
  }

  const formData = new FormData();
  formData.append("action", "apply");
  formData.append("date", selectedDate);
  formData.append("reason", reason);

  fetch(apiBasePath + "api/absences.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        loadAbsences();
        closeAbsenceModal();
      } else {
        alert(data.error || "Error submitting request");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error submitting request");
    });
}

function deleteAbsence() {
  if (!confirm("Are you sure you want to cancel this absence request?")) return;

  const absence = absencesData[selectedDate];
  if (!absence) return;

  const formData = new FormData();
  formData.append("action", "delete");
  formData.append("id", absence.absences_id);

  fetch(apiBasePath + "api/absences.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        delete absencesData[selectedDate];
        loadAbsences();
        closeAbsenceModal();
      } else {
        alert(data.error || "Error deleting request");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error deleting request");
    });
}

function loadHours() {
  fetch(
    apiBasePath +
    "api/hours.php?month=" +
    currentMonth +
    "&year=" +
    currentYear +
    getUserIdQuery(),
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        hoursData = data.hours;
        monthHoursData = JSON.parse(JSON.stringify(data.hours));
        renderCalendar();
      }
    })
    .catch((error) => console.error("Error loading hours:", error));
}

let checkInsData = {};

function loadCheckIns() {
  fetch(
    apiBasePath +
    "api/check-in.php?month=" +
    currentMonth +
    "&year=" +
    currentYear +
    getUserIdQuery(),
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        checkInsData = data.check_ins || {};
        updateQuickClockWidget();
      }
    })
    .catch((error) => console.error("Error loading check-ins:", error));
}

function getTodayDateStr() {
  const d = new Date();
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, "0");
  const day = String(d.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

function startQuickClockTimer() {
  if (!document.getElementById("quick-clock-card")) return;
  const timeEl = document.getElementById("quick-clock-current-time");
  if (!timeEl) return;

  function updateTime() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString([], {
      hour: "2-digit",
      minute: "2-digit",
      hour12: false,
    });
    timeEl.textContent = timeStr;
  }

  updateTime();
  setInterval(updateTime, 1000);
}

function updateQuickClockWidget() {
  if (!document.getElementById("quick-clock-card")) return;
  const todayStr = getTodayDateStr();
  const todayLogs = checkInsData[todayStr] || {
    morning_in: "",
    morning_out: "",
    afternoon_in: "",
    afternoon_out: "",
  };

  const fields = ["morning_in", "morning_out", "afternoon_in", "afternoon_out"];
  fields.forEach((field) => {
    const btn = document.getElementById(
      `quick-clock-${field.replace("_", "-")}`,
    );
    const statusEl = document.getElementById(
      `status-${field.replace("_", "-")}`,
    );

    if (btn && statusEl) {
      if (todayLogs[field]) {
        statusEl.textContent = todayLogs[field];
        btn.classList.add("active");
      } else {
        statusEl.textContent = "--:--";
        btn.classList.remove("active");
      }
    }
  });
}

function quickClockStamp(field) {
  const todayStr = getTodayDateStr();
  const todayLogs = JSON.parse(
    JSON.stringify(
      checkInsData[todayStr] || {
        morning_in: "",
        morning_out: "",
        afternoon_in: "",
        afternoon_out: "",
      },
    ),
  );

  // Toggle or Set current time
  if (todayLogs[field]) {
    if (
      !confirm(
        `Do you want to clear your clocked time for ${field.replace("_", " ")}?`,
      )
    )
      return;
    todayLogs[field] = "";
  } else {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");
    todayLogs[field] = `${hours}:${minutes}`;
  }

  const formData = new FormData();
  formData.append("date", todayStr);
  formData.append("morning_in", todayLogs.morning_in);
  formData.append("morning_out", todayLogs.morning_out);
  formData.append("afternoon_in", todayLogs.afternoon_in);
  formData.append("afternoon_out", todayLogs.afternoon_out);

  fetch(apiBasePath + "api/check-in.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        checkInsData[todayStr] = todayLogs;

        // Update local hoursData so calendar displays hours instantly
        if (data.hours > 0) {
          hoursData[todayStr] = data.hours;
          monthHoursData[todayStr] = data.hours;
        } else {
          delete hoursData[todayStr];
          delete monthHoursData[todayStr];
        }

        renderCalendar();
        updateQuickClockWidget();
        loadHours(); // Reload total hours metrics
        
        // Prompt for accomplishment if stamping afternoon out
        if (field === 'afternoon_out' && todayLogs[field] !== "") {
            promptForAccomplishment(todayStr);
        }
      } else {
        alert(data.error || "Error clocking time");
      }
    })
    .catch((err) => {
      console.error(err);
      alert("Error clocking time");
    });
}

function calculateModalDuration() {
  const mi = document.getElementById("modal-morning-in").value;
  const mo = document.getElementById("modal-morning-out").value;
  const ai = document.getElementById("modal-afternoon-in").value;
  const ao = document.getElementById("modal-afternoon-out").value;

  let morningHours = 0;
  if (mi && mo) {
    const inTime = parseTimeStr(mi);
    const outTime = parseTimeStr(mo);
    if (outTime > inTime) {
      morningHours = (outTime - inTime) / (1000 * 3600);
    }
  }

  let afternoonHours = 0;
  if (ai && ao) {
    const inTime = parseTimeStr(ai);
    const outTime = parseTimeStr(ao);
    if (outTime > inTime) {
      afternoonHours = (outTime - inTime) / (1000 * 3600);
    }
  }

  const total = morningHours + afternoonHours;
  document.getElementById("modal-duration-preview").textContent =
    total.toFixed(2);
}

function parseTimeStr(timeStr) {
  const parts = timeStr.split(":");
  const d = new Date();
  d.setHours(parseInt(parts[0], 10), parseInt(parts[1], 10), 0, 0);
  return d;
}

function openLogModal(dateStr) {
  selectedDate = dateStr;
  document.getElementById("modal-date").value = dateStr;

  // Clear modal inputs
  document.getElementById("modal-morning-in").value = "";
  document.getElementById("modal-morning-out").value = "";
  document.getElementById("modal-afternoon-in").value = "";
  document.getElementById("modal-afternoon-out").value = "";
  document.getElementById("modal-duration-preview").textContent = "0.00";
  document.getElementById("modal-accomplishment").value = "";

  // Check if check-in log exists for this date
  const logs = checkInsData[dateStr];
  if (logs) {
    document.getElementById("modal-morning-in").value = logs.morning_in || "";
    document.getElementById("modal-morning-out").value = logs.morning_out || "";
    document.getElementById("modal-afternoon-in").value =
      logs.afternoon_in || "";
    document.getElementById("modal-afternoon-out").value =
      logs.afternoon_out || "";
  }

  calculateModalDuration();

  // Fetch accomplishment data for the modal
  fetch(apiBasePath + "api/accomplishments.php?date=" + dateStr)
      .then(res => res.json())
      .then(data => {
          if (data.success && data.accomplishment) {
              document.getElementById("modal-accomplishment").value = data.accomplishment;
          }
      })
      .catch(err => console.error("Error fetching accomplishment:", err));

  const hasLogs =
    logs &&
    (logs.morning_in ||
      logs.morning_out ||
      logs.afternoon_in ||
      logs.afternoon_out);
  document.getElementById("delete-btn").style.display = hasLogs
    ? "block"
    : "none";
  document.getElementById("log-modal").classList.add("active");
}

function closeModal() {
  document.getElementById("log-modal").classList.remove("active");
  selectedDate = null;
}

function saveHours() {
  const mi = document.getElementById("modal-morning-in").value;
  const mo = document.getElementById("modal-morning-out").value;
  const ai = document.getElementById("modal-afternoon-in").value;
  const ao = document.getElementById("modal-afternoon-out").value;
  const accomp = document.getElementById("modal-accomplishment").value;

  if (ao !== "" && accomp.trim() === "") {
      alert("Please enter your Daily Accomplishment before saving.");
      return;
  }

  const formData = new FormData();
  formData.append("date", selectedDate);
  formData.append("morning_in", mi);
  formData.append("morning_out", mo);
  formData.append("afternoon_in", ai);
  formData.append("afternoon_out", ao);

  fetch(apiBasePath + "api/check-in.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        checkInsData[selectedDate] = {
          morning_in: mi,
          morning_out: mo,
          afternoon_in: ai,
          afternoon_out: ao,
        };

        if (data.hours > 0) {
          hoursData[selectedDate] = data.hours;
          monthHoursData[selectedDate] = data.hours;
        } else {
          delete hoursData[selectedDate];
          delete monthHoursData[selectedDate];
        }

        renderCalendar();
        updateQuickClockWidget();
        closeModal();
        loadHours();

        if (accomp.trim() !== "") {
            const accData = new FormData();
            accData.append("date", selectedDate);
            accData.append("accomplishment", accomp);
            fetch(apiBasePath + "api/accomplishments.php", {
                method: "POST",
                body: accData
            }).catch(e => console.error(e));
        }
      } else {
        alert(data.error || "Error saving check-in");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error saving check-in");
    });
}

// ==========================================
// Daily Accomplishment Feature
// ==========================================

function promptForAccomplishment(dateStr) {
    fetch(apiBasePath + "api/accomplishments.php?date=" + dateStr)
        .then(res => res.json())
        .then(data => {
            if (data.success && !data.accomplishment) {
                openAccomplishmentModal(dateStr);
            }
        })
        .catch(err => console.error("Error fetching accomplishment:", err));
}

function openAccomplishmentModal(dateStr) {
    document.getElementById("accomplishment-modal-date").value = dateStr;
    document.getElementById("accomplishment-text").value = "";
    document.getElementById("accomplishment-modal").classList.add("active");
    // Small timeout to allow transition before focus
    setTimeout(() => {
        document.getElementById("accomplishment-text").focus();
    }, 100);
}

function closeAccomplishmentModal() {
    document.getElementById("accomplishment-modal").classList.remove("active");
}

function saveAccomplishment() {
    const dateStr = document.getElementById("accomplishment-modal-date").value;
    const text = document.getElementById("accomplishment-text").value;
    
    if (text.trim() === '') {
        alert("Please enter an accomplishment, or click Skip.");
        return;
    }
    
    const formData = new FormData();
    formData.append("date", dateStr);
    formData.append("accomplishment", text);

    fetch(apiBasePath + "api/accomplishments.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            closeAccomplishmentModal();
            // Optional: alert toast here
        } else {
            alert("Error: " + (data.error || "Failed to save accomplishment"));
        }
    })
    .catch(err => {
        console.error("Error:", err);
        alert("Error saving accomplishment");
    });
}

function deleteHours() {
  if (
    !confirm(
      "Are you sure you want to delete all clocked entries for this date?",
    )
  )
    return;

  const formData = new FormData();
  formData.append("date", selectedDate);
  formData.append("delete", "true");

  fetch(apiBasePath + "api/check-in.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        delete checkInsData[selectedDate];
        delete hoursData[selectedDate];
        delete monthHoursData[selectedDate];

        renderCalendar();
        updateQuickClockWidget();
        closeModal();
        loadHours();
      } else {
        alert(data.error || "Error deleting check-in");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error deleting check-in");
    });
}

function previousMonth() {
  currentMonth--;
  if (currentMonth < 1) {
    currentMonth = 12;
    currentYear--;
  }
  updateCalendarData();
}

function nextMonth() {
  currentMonth++;
  if (currentMonth > 12) {
    currentMonth = 1;
    currentYear++;
  }
  updateCalendarData();
}

function updateCalendarData() {
  // Update URL without refreshing
  const params = new URLSearchParams(window.location.search);
  params.set("month", currentMonth);
  params.set("year", currentYear);
  window.history.pushState({}, "", "?" + params.toString());

  // Reload data
  loadHours();
  loadAbsences();
  loadCheckIns();
}


function updateStats() {
  const dailyDutyHours = getDailyDutyHours();
  const monthTotal = Object.values(monthHoursData).reduce(
    (sum, val) => sum + parseFloat(val),
    0,
  );
  const monthTotalEl = document.getElementById("month-total");
  if (monthTotalEl) monthTotalEl.textContent = monthTotal.toFixed(1);

  // Overtime Hours (daily hours > dailyDutyHours)
  const overtimeTotal = Object.values(monthHoursData).reduce(
    (sum, val) => {
      const h = parseFloat(val);
      return sum + (h > dailyDutyHours ? h - dailyDutyHours : 0);
    },
    0,
  );
  const overtimeHoursEl = document.getElementById("overtime-hours");
  if (overtimeHoursEl) overtimeHoursEl.textContent = overtimeTotal.toFixed(1);

  // Today's hours
  const today = new Date();
  const todayStr =
    today.getFullYear() +
    "-" +
    String(today.getMonth() + 1).padStart(2, "0") +
    "-" +
    String(today.getDate()).padStart(2, "0");
  const todayHoursEl = document.getElementById("today-hours");
  if (todayHoursEl)
    todayHoursEl.textContent = parseFloat(hoursData[todayStr] || 0).toFixed(1);

  // Average
  const daysLogged = Object.keys(monthHoursData).length;
  const average = daysLogged > 0 ? monthTotal / daysLogged : 0;
  const averageEl = document.getElementById("average-hours");
  if (averageEl) averageEl.textContent = average.toFixed(1);

  // Render Charts
  if (typeof renderDashboardCharts === "function") {
    renderDashboardCharts();
  }
}

function updateTotalHours() {
  const total = Object.values(allHoursData).reduce(
    (sum, val) => sum + parseFloat(val),
    0,
  );
  document.getElementById("total-hours").textContent = total.toFixed(1);

  // Render Charts
  if (typeof renderDashboardCharts === "function") {
    renderDashboardCharts();
  }
}

function applyFilter() {
  const fromDate = document.getElementById("filter-from-date").value;
  const toDate = document.getElementById("filter-to-date").value;

  if (!fromDate || !toDate) {
    alert("Please select both dates");
    return;
  }

  if (fromDate > toDate) {
    alert("From date must be before to date");
    return;
  }

  filterFromDate = fromDate;
  filterToDate = toDate;

  loadFilteredHours();
}

function resetFilter() {
  filterFromDate = null;
  filterToDate = null;
  setDefaultDates();
  loadAllHours();
  document.getElementById("filtered-total").textContent = "0";
  document.getElementById("filtered-label").textContent = "Filtered Total";
  renderCalendar();
}

function loadFilteredHours() {
  const params = new URLSearchParams();
  params.append("from_date", filterFromDate);
  params.append("to_date", filterToDate);

  fetch(apiBasePath + "api/hours.php?" + params.toString() + getUserIdQuery())
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        hoursData = data.hours;
        updateFilteredTotal();
        renderCalendar();
      }
    })
    .catch((error) => console.error("Error loading filtered hours:", error));
}

function updateFilteredTotal() {
  const filteredTotal = Object.values(hoursData).reduce(
    (sum, val) => sum + parseFloat(val),
    0,
  );
  const filteredTotalEl = document.getElementById("filtered-total");
  if (filteredTotalEl) filteredTotalEl.textContent = filteredTotal.toFixed(1);

  const filteredLabelEl = document.getElementById("filtered-label");
  if (filteredLabelEl) {
    if (filterFromDate && filterToDate) {
      const formattedFrom = formatDate(filterFromDate);
      const formattedTo = formatDate(filterToDate);
      filteredLabelEl.textContent = `${formattedFrom} to ${formattedTo} Total`;
    } else {
      filteredLabelEl.textContent = "Filtered Total";
    }
  }
}

function formatDate(dateStr) {
  if (!dateStr) return "";
  const months = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec",
  ];
  const [y, m, d] = dateStr.split("-");
  return `${months[parseInt(m) - 1]} ${parseInt(d)}, ${y}`;
}

// Close modal on escape
document.addEventListener("keydown", function (e) {
  if (e.key === "Escape") {
    closeModal();
    closeAbsenceModal();
  }
});

// Close modal on outside click
const logModal = document.getElementById("log-modal");
if (logModal) {
  logModal.addEventListener("click", function (e) {
    if (e.target === this) {
      closeModal();
    }
  });
}

const absenceModal = document.getElementById("absence-modal");
if (absenceModal) {
  absenceModal.addEventListener("click", function (e) {
    if (e.target === this) {
      closeAbsenceModal();
    }
  });
}

function downloadPDF() {
  let fromDate = filterFromDate;
  let toDate = filterToDate;

  // If filter date is not set, default to the entire currently viewed month
  if (!fromDate || !toDate) {
    const firstDay = "01";
    const lastDayObj = new Date(currentYear, currentMonth, 0);
    const lastDay = String(lastDayObj.getDate()).padStart(2, "0");
    const monthStr = String(currentMonth).padStart(2, "0");

    fromDate = `${currentYear}-${monthStr}-${firstDay}`;
    toDate = `${currentYear}-${monthStr}-${lastDay}`;
  }

  // Build the API URL
  let url =
    apiBasePath +
    "api/download-dtr.php?from_date=" +
    fromDate +
    "&to_date=" +
    toDate;

  // If in supervisor view, append the target userId
  if (typeof isSupervisorView !== "undefined" && isSupervisorView) {
    url += "&userId=" + userId;
  }

  // Visual feedback: show loading state on the button
  const btn = document.getElementById("btn-download-pdf");
  if (!btn) return;

  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = "<span>⏳</span> Generating PDF...";

  fetch(url)
    .then((response) => {
      if (!response.ok) {
        return response.text().then((text) => {
          try {
            const err = JSON.parse(text);
            throw new Error(err.error || "Server error");
          } catch (e) {
            const plainText = text
              .replace(/<[^>]*>/g, "")
              .trim()
              .substring(0, 150);
            throw new Error(plainText || `Server error (${response.status})`);
          }
        });
      }
      return response.blob();
    })
    .then((blob) => {
      const downloadUrl = window.URL.createObjectURL(blob);
      const a = document.createElement("a");
      a.style.display = "none";
      a.href = downloadUrl;
      a.download = `DTR_${fromDate}_to_${toDate}.pdf`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(downloadUrl);
      document.body.removeChild(a);
    })
    .catch((error) => {
      console.error("Error downloading DTR:", error);
      alert("Failed to download DTR: " + error.message);
    })
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = originalText;
    });
}

// =========================================================================
// 📊 INTERACTIVE CHARTS & BURNDOWN ENGINE (Powered by Chart.js)
// =========================================================================

let myProgressChart = null;
let myBarChart = null;

function renderDashboardCharts() {
  const progressCanvas = document.getElementById("progressChart");
  const barCanvas = document.getElementById("hoursBarChart");

  if (!progressCanvas || !barCanvas) return;
  if (typeof Chart === "undefined") return;

  // 1. Calculate stats
  const totalLogged = Object.values(allHoursData).reduce(
    (sum, val) => sum + parseFloat(val),
    0,
  );
  const target = typeof hourGoal !== "undefined" ? hourGoal : 480;

  const percent = Math.min((totalLogged / target) * 100, 100);
  const displayPercent = (totalLogged / target) * 100;

  const remaining = Math.max(target - totalLogged, 0);

  // Calculate average of days with logged hours
  const loggedDays = Object.values(allHoursData).filter(
    (val) => parseFloat(val) > 0,
  );
  const totalLoggedDaysCount = loggedDays.length;
  const dailyAverage =
    totalLoggedDaysCount > 0 ? totalLogged / totalLoggedDaysCount : 0;

  // Update HTML elements
  const percentEl = document.getElementById("chart-percent");
  if (percentEl) percentEl.textContent = `${displayPercent.toFixed(0)}%`;

  const ratioEl = document.getElementById("chart-ratio");
  if (ratioEl) ratioEl.textContent = `${totalLogged.toFixed(1)}/${target}h`;

  const remainingEl = document.getElementById("burndown-remaining");
  if (remainingEl) remainingEl.textContent = `${remaining.toFixed(1)} hrs`;

  const avgEl = document.getElementById("burndown-avg");
  if (avgEl) avgEl.textContent = `${dailyAverage.toFixed(1)} hrs/day`;

  const compEl = document.getElementById("burndown-completion");
  if (compEl) {
    compEl.textContent = calculateEstimatedCompletion(remaining, dailyAverage);
  }

  const isDark =
    document.documentElement.classList.contains("dark") ||
    document.body.classList.contains("dark-mode");

  // 2. Render Doughnut Gauge Chart
  if (myProgressChart) {
    myProgressChart.destroy();
  }

  myProgressChart = new Chart(progressCanvas, {
    type: "doughnut",
    data: {
      datasets: [
        {
          data: [totalLogged, remaining],
          backgroundColor: ["#2563eb", isDark ? "#374151" : "#e2e8f0"],
          borderWidth: 0,
        },
      ],
    },
    options: {
      cutout: "80%",
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: { enabled: false },
      },
    },
  });

  // 3. Render Monthly Bar Chart
  if (myBarChart) {
    myBarChart.destroy();
  }

  // Get days in current month
  const lastDay = new Date(currentYear, currentMonth, 0).getDate();
  const labels = [];
  const barData = [];

  for (let day = 1; day <= lastDay; day++) {
    const dateStr = String(day).padStart(2, "0");
    const fullDate =
      currentYear + "-" + String(currentMonth).padStart(2, "0") + "-" + dateStr;
    labels.push(day);
    barData.push(parseFloat(monthHoursData[fullDate] || 0));
  }

  myBarChart = new Chart(barCanvas, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Hours Logged",
          data: barData,
          backgroundColor: "rgba(37, 99, 235, 0.75)",
          hoverBackgroundColor: "#2563eb",
          borderRadius: 4,
          borderWidth: 0,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: {
            color: isDark ? "#94a3b8" : "#64748b",
            font: { size: 10 },
          },
        },
        y: {
          grid: { color: isDark ? "#374151" : "#f1f5f9" },
          ticks: {
            color: isDark ? "#94a3b8" : "#64748b",
            font: { size: 10 },
            stepSize: 2,
          },
        },
      },
    },
  });
}

function calculateEstimatedCompletion(remainingHours, dailyAvg) {
  if (remainingHours <= 0) return "Goal Met";
  if (dailyAvg <= 0) return "N/A";

  const daysNeeded = Math.ceil(remainingHours / dailyAvg);
  let currentDate = new Date();
  let addedDays = 0;
  let safetyLoop = 0;

  const daysOfWeekNames = [
    "Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
  ];
  const activeDaysList = (
    typeof dutyDays !== "undefined"
      ? dutyDays
      : "Monday,Tuesday,Wednesday,Thursday,Friday"
  ).split(",");

  while (addedDays < daysNeeded && safetyLoop < 1000) {
    safetyLoop++;
    currentDate.setDate(currentDate.getDate() + 1);

    // Check if on-duty day
    const dayName = daysOfWeekNames[currentDate.getDay()];
    if (!activeDaysList.includes(dayName)) {
      continue; // Skip off-duty days
    }

    // Check if holiday
    const dateStr =
      currentDate.getFullYear() +
      "-" +
      String(currentDate.getMonth() + 1).padStart(2, "0") +
      "-" +
      String(currentDate.getDate()).padStart(2, "0");
    const monthDay =
      String(currentDate.getMonth() + 1).padStart(2, "0") +
      "-" +
      String(currentDate.getDate()).padStart(2, "0");

    if (movableHolidays[dateStr] || fixedHolidays[monthDay]) {
      continue; // Skip holidays
    }

    addedDays++;
  }

  const options = { year: "numeric", month: "short", day: "numeric" };
  return currentDate.toLocaleDateString("en-US", options);
}

// =========================================================================
// 🎯 INTERNSHIP GOAL MODAL EVENT HANDLERS
// =========================================================================
document.addEventListener("DOMContentLoaded", () => {
  const goalModal = document.getElementById("goal-modal");
  const openGoalModalBtn = document.getElementById("open-goal-modal-btn");
  const closeGoalModalBtn = document.getElementById("close-goal-modal-btn");
  const cancelGoalModalBtn = document.getElementById("cancel-goal-modal-btn");
  const goalModalForm = document.getElementById("goal-modal-form");
  const goalModalAlert = document.getElementById("goal-modal-alert");

  if (!goalModal) return;

  function openModal() {
    goalModal.classList.remove("hidden");
    goalModal.classList.add("flex");
    if (goalModalAlert) {
      goalModalAlert.classList.add("hidden");
      goalModalAlert.textContent = "";
    }
  }

  function closeModal() {
    goalModal.classList.add("hidden");
    goalModal.classList.remove("flex");
  }

  if (openGoalModalBtn) openGoalModalBtn.addEventListener("click", openModal);
  if (closeGoalModalBtn)
    closeGoalModalBtn.addEventListener("click", closeModal);
  if (cancelGoalModalBtn)
    cancelGoalModalBtn.addEventListener("click", closeModal);

  // Close modal when clicking outside content box
  goalModal.addEventListener("click", (e) => {
    if (e.target === goalModal) {
      closeModal();
    }
  });

  if (goalModalForm) {
    goalModalForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const hourGoal = document.getElementById("modal_hour_goal").value;
      const startingDate = document.getElementById("modal_starting_date").value;
      const dutyFromVal = document.getElementById("modal_duty_from").value;
      const dutyToVal = document.getElementById("modal_duty_to").value;
      const checkedBoxes = Array.from(
        document.querySelectorAll('input[name="modal_duty_days[]"]:checked'),
      );

      if (checkedBoxes.length === 0) {
        if (goalModalAlert) {
          goalModalAlert.className =
            "p-4 rounded-xl text-xs font-bold mb-5 bg-red-50 text-red-600 border border-red-100";
          goalModalAlert.textContent = "Please select at least one duty day.";
          goalModalAlert.classList.remove("hidden");
        }
        return;
      }

      const dutyDays = checkedBoxes.map((cb) => cb.value).join(",");
      const submitBtn = goalModalForm.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.textContent;

      // Show loading spinner/state
      submitBtn.disabled = true;
      submitBtn.innerHTML = `<span class="inline-block animate-spin mr-2">⏳</span> Saving...`;

      const formData = new FormData();
      formData.append("hour_goal", hourGoal);
      formData.append("starting_date", startingDate);
      formData.append("duty_days", dutyDays);
      formData.append("duty_from", dutyFromVal);
      formData.append("duty_to", dutyToVal);
      const lunchBreakCheckbox = document.getElementById("modal_has_lunch_break");
      if (lunchBreakCheckbox && lunchBreakCheckbox.checked) {
        formData.append("has_lunch_break", "1");
      }

      fetch("../api/burnout_update.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            if (goalModalAlert) {
              goalModalAlert.className =
                "p-4 rounded-xl text-xs font-bold mb-5 bg-green-50 text-green-600 border border-green-100";
              goalModalAlert.textContent =
                "Goal saved successfully! Reloading...";
              goalModalAlert.classList.remove("hidden");
            }
            setTimeout(() => {
              window.location.reload();
            }, 800);
          } else {
            throw new Error(data.error || "Failed to save goal settings.");
          }
        })
        .catch((err) => {
          console.error("Error updating goal:", err);
          if (goalModalAlert) {
            goalModalAlert.className =
              "p-4 rounded-xl text-xs font-bold mb-5 bg-red-50 text-red-600 border border-red-100";
            goalModalAlert.textContent = err.message;
            goalModalAlert.classList.remove("hidden");
          }
          submitBtn.disabled = false;
          submitBtn.textContent = originalBtnText;
        });
    });
  }
});

// =========================================================================
// BIRTHDAY MODAL & CONFETTI CELEBRATION
// =========================================================================
function showBirthdayModal(b, event) {
  let modal = document.getElementById("birthday-modal");
  if (!modal) {
    modal = document.createElement("div");
    modal.id = "birthday-modal";
    modal.className = "modal birthday-modal-container";
    modal.innerHTML = `
      <div class="modal-content birthday-modal-content">
        <button class="birthday-modal-close" onclick="closeBirthdayModal()">&times;</button>
        <div class="birthday-card-deco">
          <picture>
            <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f389/512.webp" type="image/webp">
            <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f389/512.gif" alt="🎉" width="48" height="48">
          </picture>
          <picture>
            <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f382/512.webp" type="image/webp">
            <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f382/512.gif" alt="🎂" width="48" height="48">
          </picture>
          <picture>
            <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/2728/512.webp" type="image/webp">
            <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/2728/512.gif" alt="✨" width="48" height="48">
          </picture>
        </div>
        <h3 class="birthday-title">It's a Celebration!</h3>
        <div class="birthday-name-wrap">
          <span class="birthday-emoji">
            <picture>
              <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f382/512.webp" type="image/webp">
              <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f382/512.gif" alt="🎂" width="32" height="32">
            </picture>
          </span>
          <span class="birthday-name" id="birthday-modal-name"></span>
        </div>
        <p class="birthday-nickname" id="birthday-modal-nickname"></p>
        <div class="birthday-details">
          <p id="birthday-modal-date-text"></p>
        </div>
        <button class="birthday-modal-btn" onclick="triggerBirthdayConfetti();">
          Blow More Confetti! 
          <picture class="birthday-btn-emoji">
            <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f38a/512.webp" type="image/webp">
            <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f38a/512.gif" alt="🎊" width="20" height="20">
          </picture>
        </button>
      </div>
    `;
    document.body.appendChild(modal);

    // Add event listener to close modal when clicking outside content
    modal.addEventListener("click", function (e) {
      if (e.target === this) closeBirthdayModal();
    });
  }

  // Fill data
  const displayName = b.name;
  const nickname = b.nickname ? `"${b.nickname}"` : "";

  // Format birthdate nicely (e.g. "May 22")
  let formattedDate = "";
  if (b.birthdate) {
    const parts = b.birthdate.split(/[-/.]/);
    if (parts.length === 3) {
      let month = 0, day = 0;
      if (parts[0].length === 4) {
        month = parseInt(parts[1], 10);
        day = parseInt(parts[2], 10);
      } else {
        month = parseInt(parts[0], 10);
        day = parseInt(parts[1], 10);
      }
      const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
      if (month >= 1 && month <= 12) {
        formattedDate = months[month - 1] + " " + day;
      }
    }
  }

  document.getElementById("birthday-modal-name").textContent = displayName;
  document.getElementById("birthday-modal-nickname").textContent = nickname;

  if (formattedDate) {
    document.getElementById("birthday-modal-date-text").innerHTML = `
      Birthday: ${formattedDate} 
      <picture class="birthday-text-emoji">
        <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f388/512.webp" type="image/webp">
        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f388/512.gif" alt="🎈" width="20" height="20">
      </picture>
    `;
  } else {
    document.getElementById("birthday-modal-date-text").innerHTML = `
      Happy Birthday! 
      <picture class="birthday-text-emoji">
        <source srcset="https://fonts.gstatic.com/s/e/notoemoji/latest/1f389/512.webp" type="image/webp">
        <img src="https://fonts.gstatic.com/s/e/notoemoji/latest/1f389/512.gif" alt="🎉" width="20" height="20">
      </picture>
    `;
  }

  // Show modal
  modal.classList.add("active");

  // Trigger confetti!
  const x = event ? event.clientX : window.innerWidth / 2;
  const y = event ? event.clientY : window.innerHeight / 2;
  triggerBirthdayConfetti(x, y);
}

function closeBirthdayModal() {
  const modal = document.getElementById("birthday-modal");
  if (modal) {
    modal.classList.remove("active");
  }
}

function triggerBirthdayConfetti(startX, startY) {
  const x = startX !== undefined ? startX : window.innerWidth / 2;
  const y = startY !== undefined ? startY : window.innerHeight / 2;

  const particleCount = 80;
  const colors = ["#ff007f", "#ff00ff", "#7f00ff", "#00ffff", "#00ff00", "#ffff00", "#ff7f00", "#ff0000"];
  const container = document.createElement("div");
  container.className = "confetti-container";
  document.body.appendChild(container);

  for (let i = 0; i < particleCount; i++) {
    const particle = document.createElement("div");
    particle.className = "confetti-particle";

    // Randomize colors
    particle.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];

    // Randomize shape (square, circle, triangle, rectangle)
    const shapeType = Math.floor(Math.random() * 4);
    if (shapeType === 1) {
      particle.style.borderRadius = "50%"; // circle
    } else if (shapeType === 2) {
      particle.style.width = "0";
      particle.style.height = "0";
      particle.style.borderLeft = "5px solid transparent";
      particle.style.borderRight = "5px solid transparent";
      particle.style.borderBottom = "10px solid " + colors[Math.floor(Math.random() * colors.length)];
      particle.style.backgroundColor = "transparent"; // triangle
    } else if (shapeType === 3) {
      particle.style.width = "4px";
      particle.style.height = "12px"; // long strip
    } else {
      particle.style.width = "8px";
      particle.style.height = "8px"; // square
    }

    if (shapeType !== 2) {
      const w = Math.floor(Math.random() * 6) + 6;
      const h = Math.floor(Math.random() * 6) + 6;
      particle.style.width = w + "px";
      particle.style.height = h + "px";
    }

    // Starting position at click coordinate
    particle.style.left = x + "px";
    particle.style.top = y + "px";

    // Trajectory parameters
    const angle = Math.random() * Math.PI * 2; // Direction
    const velocity = Math.random() * 180 + 80; // Speed

    const explodeX = Math.cos(angle) * velocity;
    const explodeY = Math.sin(angle) * velocity - 80; // Extra upward thrust

    const driftX = explodeX + (Math.random() * 80 - 40);
    const fallY = explodeY + (Math.random() * 250 + 250);

    const rotateMid = Math.random() * 360;
    const rotateEnd = rotateMid + Math.random() * 720 + 360;
    const duration = Math.random() * 1.2 + 1.3; // 1.3s - 2.5s

    particle.style.setProperty("--explode-x", `${explodeX}px`);
    particle.style.setProperty("--explode-y", `${explodeY}px`);
    particle.style.setProperty("--drift-x", `${driftX}px`);
    particle.style.setProperty("--fall-y", `${fallY}px`);
    particle.style.setProperty("--rotate-mid", `${rotateMid}deg`);
    particle.style.setProperty("--rotate-end", `${rotateEnd}deg`);
    particle.style.setProperty("--duration", `${duration}s`);

    container.appendChild(particle);
  }

  // Clean up container
  setTimeout(() => {
    container.remove();
  }, 3000);
}
