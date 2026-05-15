// Global variables will be initialized in the PHP file
// currentMonth, currentYear, selectedDate, hoursData, monthHoursData, allHoursData, userId, filterFromDate, filterToDate

// Initialize calendar
document.addEventListener("DOMContentLoaded", function () {
  if (document.getElementById("filter-from-date")) {
    setDefaultDates();
  }
  loadAllHours();
  loadAbsences();
  loadHours();
  loadInterns();
  renderCalendar();
});

function getUserIdQuery() {
  return typeof userId !== "undefined" ? "&userId=" + userId : "";
}

function loadInterns() {
  const list = document.getElementById("interns-list");
  if (!list) return;

  fetch("../../../api/interns.php")
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
          div.className = "flex flex-col items-center gap-2 bg-white p-3 rounded-lg border border-gray-100 shadow-sm";
          const hoursBadge = intern.total_hours !== null 
            ? `<div class="mt-1 px-2 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-full">${parseFloat(intern.total_hours).toFixed(1)}h</div>` 
            : '';
            
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
      }
    });
}

function setDefaultDates() {
  const today = new Date();
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

  document.getElementById("filter-from-date").valueAsDate = firstDay;
  document.getElementById("filter-to-date").valueAsDate = today;
}

function loadAllHours() {
  fetch("../../../api/hours.php?all=true" + getUserIdQuery())
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        allHoursData = data.hours;
        updateTotalHours();
      }
    })
    .catch((error) => console.error("Error loading all hours:", error));
}

function renderCalendar() {
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

    cell.innerHTML = `
            <div class="day-cell-date">${day}</div>
            ${hoursData[fullDate] ? `<div class="day-cell-hours">${hoursData[fullDate]}h</div>` : ""}
            ${absencesData[fullDate] ? `<div class="absence-badge ${absencesData[fullDate].status.toLowerCase()}">${absencesData[fullDate].status}</div>` : ""}
        `;

    if (!isFuture) {
      cell.onclick = () => openLogModal(fullDate);
    } else {
      cell.onclick = () => openAbsenceModal(fullDate);
    }
    calendarGrid.appendChild(cell);
  }

  updateStats();
}

function loadAbsences() {
  fetch(
    "../../../api/absences.php?month=" +
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

  fetch("../../../api/absences.php", {
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

  fetch("../../../api/absences.php", {
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
    "../../../api/hours.php?month=" +
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

function openLogModal(dateStr) {
  selectedDate = dateStr;
  document.getElementById("modal-date").value = dateStr;
  document.getElementById("modal-hours").value = hoursData[dateStr] || "";
  document.getElementById("delete-btn").style.display = hoursData[dateStr]
    ? "block"
    : "none";
  document.getElementById("log-modal").classList.add("active");
  document.getElementById("modal-hours").focus();
}

function closeModal() {
  document.getElementById("log-modal").classList.remove("active");
  selectedDate = null;
}

function saveHours() {
  const hours = document.getElementById("modal-hours").value;

  if (hours === "" || isNaN(hours) || parseFloat(hours) < 0) {
    alert("Please enter a valid number of hours");
    return;
  }

  const formData = new FormData();
  formData.append("date", selectedDate);
  formData.append("hours", hours);

  fetch("../../../api/hours.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const parsedHours = parseFloat(hours);
        hoursData[selectedDate] = parsedHours;
        monthHoursData[selectedDate] = parsedHours;
        renderCalendar();
        closeModal();
      } else {
        alert(data.error || "Error saving hours");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error saving hours");
    });
}

function deleteHours() {
  if (!confirm("Are you sure you want to delete this entry?")) return;

  const formData = new FormData();
  formData.append("date", selectedDate);
  formData.append("delete", "true");

  fetch("../../../api/hours.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        delete hoursData[selectedDate];
        delete monthHoursData[selectedDate];
        renderCalendar();
        closeModal();
      } else {
        alert(data.error || "Error deleting entry");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error deleting entry");
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
}

function updateStats() {
  const monthTotal = Object.values(monthHoursData).reduce(
    (sum, val) => sum + parseFloat(val),
    0,
  );
  const monthTotalEl = document.getElementById("month-total");
  if (monthTotalEl) monthTotalEl.textContent = monthTotal.toFixed(1);

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
    todayHoursEl.textContent = (hoursData[todayStr] || 0).toFixed(1);

  // Average
  const daysLogged = Object.keys(monthHoursData).length;
  const average = daysLogged > 0 ? monthTotal / daysLogged : 0;
  const averageEl = document.getElementById("average-hours");
  if (averageEl) averageEl.textContent = average.toFixed(1);
}

function updateTotalHours() {
  const total = Object.values(allHoursData).reduce(
    (sum, val) => sum + parseFloat(val),
    0,
  );
  document.getElementById("total-hours").textContent = total.toFixed(1);
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

  fetch("../../../api/hours.php?" + params.toString() + getUserIdQuery())
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
