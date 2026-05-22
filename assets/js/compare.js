let allColleagues = [];
let compareDataLeft = null;
let compareDataRight = null;
let myComparisonChart = null;
let diffOnlyMode = false;

// Initialize on load
document.addEventListener("DOMContentLoaded", function () {
  loadAllHours();
  loadInterns();
  renderCalendarHeader();
});

function loadAllHours() {
  fetch(apiBasePath + "api/hours.php?all=true")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        allHoursData = data.hours;
        // Re-render self data if it was selected already
        updateComparisonUI();
      }
    })
    .catch((error) => console.error("Error loading all hours:", error));
}

function loadInterns() {
  fetch(apiBasePath + "api/interns.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        allColleagues = data.interns || [];
        initializeComparisonDropdowns();
      }
    })
    .catch((error) => console.error("Error loading interns:", error));
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

  // Render calendar header
  renderCalendarHeader();

  // Recalculate metrics & redraw chart
  updateComparisonUI();
}

function renderCalendarHeader() {
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
  const titleEl = document.getElementById("calendar-title");
  if (titleEl) {
    titleEl.textContent = monthNames[currentMonth - 1] + " " + currentYear;
  }
}

function initializeComparisonDropdowns() {
  const leftSelect = document.getElementById("compare-select-left");
  const rightSelect = document.getElementById("compare-select-right");
  if (!leftSelect || !rightSelect) return;

  // Clear existing options except placeholder
  leftSelect.innerHTML = '<option value="">Select individual...</option>';
  rightSelect.innerHTML = '<option value="">Select individual...</option>';

  // Add "You" option
  const selfLabel = `You (${currentUserName})`;
  leftSelect.add(new Option(selfLabel, "self"));
  rightSelect.add(new Option(selfLabel, "self"));

  // Add other colleagues
  allColleagues.forEach((intern) => {
    if (parseInt(intern.id) === userId) return; // Skip self as it's already added as 'self'
    leftSelect.add(new Option(intern.name, intern.id));
    rightSelect.add(new Option(intern.name, intern.id));
  });
}

function onCompareSelectChange(side) {
  const select = document.getElementById(`compare-select-${side}`);
  const val = select.value;

  if (!val) {
    if (side === "left") compareDataLeft = null;
    else compareDataRight = null;
    updateComparisonUI();
    return;
  }

  // Show loading state in the profile card
  const card = document.getElementById(`compare-card-${side}`);
  card.innerHTML = `
    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; text-align: center; font-size: 13px; padding: 24px 0;">
      <span>⏳ Loading details...</span>
    </div>
  `;

  if (val === "self") {
    const total = Object.values(allHoursData).reduce(
      (sum, val) => sum + parseFloat(val),
      0,
    );
    const selfData = {
      id: userId,
      name: currentUserName,
      email: currentUserEmail,
      profile_picture: typeof currentUserProfilePicture !== "undefined" ? currentUserProfilePicture : "",
      is_private: false,
      total_hours: total,
      hours: allHoursData,
    };
    if (side === "left") compareDataLeft = selfData;
    else compareDataRight = selfData;
    updateComparisonUI();
  } else {
    fetch(apiBasePath + `api/intern-hours.php?intern_id=${val}&all=true`)
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          const colleagueData = {
            id: data.intern.id,
            name: data.intern.name,
            email: data.intern.email || "",
            profile_picture: data.intern.profile_picture || "",
            is_private: data.is_private,
            total_hours: data.total_hours,
            hours: data.hours || {},
          };
          if (side === "left") compareDataLeft = colleagueData;
          else compareDataRight = colleagueData;
          updateComparisonUI();
        } else {
          card.innerHTML = `
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #dc2626; text-align: center; font-size: 13px; padding: 24px 0;">
              <span>❌ Error: ${data.error || "Failed to load"}</span>
            </div>
          `;
        }
      })
      .catch((error) => {
        console.error("Error loading colleague comparison:", error);
        card.innerHTML = `
          <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #dc2626; text-align: center; font-size: 13px; padding: 24px 0;">
            <span>❌ Failed to fetch data.</span>
          </div>
        `;
      });
  }
}

function getMetricsForMonth(personData, year, month) {
  if (!personData || personData.is_private) return null;

  const hoursMap = personData.hours || {};
  let monthlyTotal = 0;
  let daysLogged = 0;

  const prefix = `${year}-${String(month).padStart(2, "0")}`;

  Object.keys(hoursMap).forEach((dateStr) => {
    if (dateStr.startsWith(prefix)) {
      const h = parseFloat(hoursMap[dateStr]);
      if (h > 0) {
        monthlyTotal += h;
        daysLogged++;
      }
    }
  });

  const dailyAvg = daysLogged > 0 ? monthlyTotal / daysLogged : 0;

  return {
    monthlyTotal,
    daysLogged,
    dailyAvg,
  };
}

function getAvatarGradient(name) {
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  const c1 = Math.abs(hash % 360);
  const c2 = (c1 + 40) % 360;
  return `linear-gradient(135deg, hsl(${c1}, 70%, 55%) 0%, hsl(${c2}, 80%, 45%) 100%)`;
}

function renderMetricValueWithDiff(valLeft, valRight, isLeft, unit = "hrs") {
  if (
    valLeft === null ||
    valRight === null ||
    typeof valLeft === "undefined" ||
    typeof valRight === "undefined"
  ) {
    return ``;
  }
  const diff = valLeft - valRight;
  if (diff === 0) {
    return `<span class="diff-badge neutral">0.0</span>`;
  }

  if (isLeft) {
    if (diff > 0) {
      return `<span class="diff-badge positive">+${diff.toFixed(1)}${unit}</span>`;
    } else {
      return `<span class="diff-badge negative">${diff.toFixed(1)}${unit}</span>`;
    }
  } else {
    const rightDiff = -diff;
    if (rightDiff > 0) {
      return `<span class="diff-badge positive">+${rightDiff.toFixed(1)}${unit}</span>`;
    } else {
      return `<span class="diff-badge negative">${rightDiff.toFixed(1)}${unit}</span>`;
    }
  }
}

function renderProfileCard(person, metrics, otherMetrics, isLeft) {
  if (person.is_private) {
    return `
      <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; padding: 16px; gap: 8px;">
        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center text-gray-400 dark:text-gray-500 font-bold text-lg">
          🔒
        </div>
        <div class="font-bold text-gray-800 dark:text-gray-200">${person.name}</div>
        <div class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full border border-gray-200 dark:border-gray-700">Private Profile</div>
      </div>
    `;
  }

  const showDiff = otherMetrics !== null;
  const totDiffBadge = showDiff
    ? renderMetricValueWithDiff(
        person.total_hours,
        otherMetrics.total_hours,
        isLeft,
        "h",
      )
    : "";
  const monDiffBadge = showDiff
    ? renderMetricValueWithDiff(
        metrics.monthlyTotal,
        otherMetrics.monthlyTotal,
        isLeft,
        "h",
      )
    : "";
  const daysDiffBadge = showDiff
    ? renderMetricValueWithDiff(
        metrics.daysLogged,
        otherMetrics.daysLogged,
        isLeft,
        "d",
      )
    : "";
  const avgDiffBadge = showDiff
    ? renderMetricValueWithDiff(
        metrics.dailyAvg,
        otherMetrics.dailyAvg,
        isLeft,
        "h",
      )
    : "";

  const color = getAvatarGradient(person.name);
  const initial = escapeHtml(person.name.charAt(0).toUpperCase());
  const avatarHtml = person.profile_picture
    ? `<img src="${escapeHtml(person.profile_picture)}" alt="${escapeHtml(person.name)}" class="w-full h-full object-cover" onerror="this.style.display='none'; this.parentElement.style.background='${color}'; this.parentElement.textContent='${initial}';">`
    : initial;
  const avatarBg = person.profile_picture ? 'transparent' : color;

  return `
    <div style="display: flex; align-items: center; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid rgba(226, 232, 240, 0.5);">
      <div class="compare-avatar" style="background: ${avatarBg}; min-width: 48px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
        ${avatarHtml}
      </div>
      <div style="overflow: hidden; width: 100%;">
        <div class="font-bold text-gray-800 dark:text-gray-200 truncate" style="font-size: 15px;">${escapeHtml(person.name)}</div>
        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">${escapeHtml(person.email)}</div>
      </div>
    </div>
    <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
      <div class="compare-stat-row">
        <span class="compare-stat-label">Total Hours</span>
        <span class="compare-stat-value">
          ${parseFloat(person.total_hours).toFixed(1)}h
          ${totDiffBadge}
        </span>
      </div>
      <div class="compare-stat-row">
        <span class="compare-stat-label">Month Total</span>
        <span class="compare-stat-value">
          ${metrics.monthlyTotal.toFixed(1)}h
          ${monDiffBadge}
        </span>
      </div>
      <div class="compare-stat-row">
        <span class="compare-stat-label">Days Logged</span>
        <span class="compare-stat-value">
          ${metrics.daysLogged} days
          ${daysDiffBadge}
        </span>
      </div>
      <div class="compare-stat-row">
        <span class="compare-stat-label">Average/Day</span>
        <span class="compare-stat-value">
          ${metrics.dailyAvg.toFixed(1)}h
          ${avgDiffBadge}
        </span>
      </div>
    </div>
  `;
}

function updateComparisonUI() {
  const cardLeft = document.getElementById("compare-card-left");
  const cardRight = document.getElementById("compare-card-right");
  const chartContainer = document.getElementById("compare-chart-container");
  if (!cardLeft || !cardRight || !chartContainer) return;

  if (compareDataLeft && compareDataLeft.id === userId) {
    compareDataLeft.total_hours = Object.values(allHoursData).reduce(
      (sum, val) => sum + parseFloat(val),
      0,
    );
    compareDataLeft.hours = allHoursData;
  }
  if (compareDataRight && compareDataRight.id === userId) {
    compareDataRight.total_hours = Object.values(allHoursData).reduce(
      (sum, val) => sum + parseFloat(val),
      0,
    );
    compareDataRight.hours = allHoursData;
  }

  const metricsLeft = getMetricsForMonth(
    compareDataLeft,
    currentYear,
    currentMonth,
  );
  const metricsRight = getMetricsForMonth(
    compareDataRight,
    currentYear,
    currentMonth,
  );

  const otherForLeft =
    compareDataRight && !compareDataRight.is_private
      ? { total_hours: compareDataRight.total_hours, ...metricsRight }
      : null;
  const otherForRight =
    compareDataLeft && !compareDataLeft.is_private
      ? { total_hours: compareDataLeft.total_hours, ...metricsLeft }
      : null;

  if (compareDataLeft) {
    cardLeft.innerHTML = renderProfileCard(
      compareDataLeft,
      metricsLeft,
      otherForLeft,
      true,
    );
  } else {
    cardLeft.innerHTML = `
      <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; text-align: center; font-size: 13px; padding: 24px 0;">
        <span>Select a colleague or yourself to compare.</span>
      </div>
    `;
  }

  if (compareDataRight) {
    cardRight.innerHTML = renderProfileCard(
      compareDataRight,
      metricsRight,
      otherForRight,
      false,
    );
  } else {
    cardRight.innerHTML = `
      <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; text-align: center; font-size: 13px; padding: 24px 0;">
        <span>Select a colleague or yourself to compare.</span>
      </div>
    `;
  }

  const canShowChart =
    compareDataLeft &&
    !compareDataLeft.is_private &&
    compareDataRight &&
    !compareDataRight.is_private;

  const tableContainer = document.getElementById("compare-table-container");
  if (canShowChart) {
    chartContainer.classList.remove("hidden");
    if (tableContainer) tableContainer.classList.remove("hidden");
    renderComparisonChart();
    renderComparisonTable();
  } else {
    chartContainer.classList.add("hidden");
    if (tableContainer) tableContainer.classList.add("hidden");
    if (myComparisonChart) {
      myComparisonChart.destroy();
      myComparisonChart = null;
    }
  }
}

function renderComparisonChart() {
  const canvas = document.getElementById("comparisonBarChart");
  if (!canvas) return;
  if (typeof Chart === "undefined") return;

  if (myComparisonChart) {
    myComparisonChart.destroy();
  }

  const lastDay = new Date(currentYear, currentMonth, 0).getDate();
  const labels = [];
  const leftBarData = [];
  const rightBarData = [];

  for (let day = 1; day <= lastDay; day++) {
    const dateStr = String(day).padStart(2, "0");
    const fullDate = `${currentYear}-${String(currentMonth).padStart(2, "0")}-${dateStr}`;
    labels.push(day);
    leftBarData.push(parseFloat(compareDataLeft.hours[fullDate] || 0));
    rightBarData.push(parseFloat(compareDataRight.hours[fullDate] || 0));
  }

  const isDark =
    document.documentElement.classList.contains("dark") ||
    document.body.classList.contains("dark-mode");

  myComparisonChart = new Chart(canvas, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: compareDataLeft.name,
          data: leftBarData,
          backgroundColor: "rgba(79, 70, 229, 0.75)",
          hoverBackgroundColor: "#4f46e5",
          borderRadius: 4,
          borderWidth: 0,
        },
        {
          label: compareDataRight.name,
          data: rightBarData,
          backgroundColor: "rgba(168, 85, 247, 0.75)",
          hoverBackgroundColor: "#a855f7",
          borderRadius: 4,
          borderWidth: 0,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: "top",
          labels: {
            color: isDark ? "#cbd5e1" : "#475569",
            boxWidth: 12,
            font: { size: 11, weight: "bold" },
          },
        },
        tooltip: {
          mode: "index",
          intersect: false,
        },
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

function toggleDiffOnly() {
  const checkbox = document.getElementById("diff-only-checkbox");
  diffOnlyMode = checkbox ? checkbox.checked : false;
  renderComparisonTable();
}

function renderComparisonTable() {
  const tbody = document.getElementById("compare-table-body");
  const headerLeft = document.getElementById("table-header-left");
  const headerRight = document.getElementById("table-header-right");
  if (!tbody) return;

  // Set headers to names
  if (headerLeft) headerLeft.textContent = escapeHtml(compareDataLeft.name);
  if (headerRight) headerRight.textContent = escapeHtml(compareDataRight.name);

  tbody.innerHTML = "";

  const lastDay = new Date(currentYear, currentMonth, 0).getDate();
  const monthNames = [
    "Jan", "Feb", "Mar", "Apr", "May", "Jun", 
    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
  ];
  const monthName = monthNames[currentMonth - 1];

  let hasVisibleRows = false;

  for (let day = 1; day <= lastDay; day++) {
    const dateStr = String(day).padStart(2, "0");
    const fullDate = `${currentYear}-${String(currentMonth).padStart(2, "0")}-${dateStr}`;
    
    const hLeft = parseFloat(compareDataLeft.hours[fullDate] || 0);
    const hRight = parseFloat(compareDataRight.hours[fullDate] || 0);
    const diff = hLeft - hRight;

    // If differences only mode, skip equal hours
    if (diffOnlyMode && diff === 0) {
      continue;
    }

    hasVisibleRows = true;

    const formattedDate = `${monthName} ${day}, ${currentYear}`;
    
    let leftCellClass = "py-3 px-4 font-semibold text-gray-700 dark:text-gray-300 transition-colors";
    let rightCellClass = "py-3 px-4 font-semibold text-gray-700 dark:text-gray-300 transition-colors";
    let diffBadgeClass = "px-2 py-1 rounded text-xs font-bold";
    let diffText = "";

    if (diff > 0) {
      leftCellClass += " bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400";
      rightCellClass += " bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400";
      diffBadgeClass += " bg-emerald-100 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300";
      diffText = `+${diff.toFixed(1)}h`;
    } else if (diff < 0) {
      leftCellClass += " bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400";
      rightCellClass += " bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400";
      diffBadgeClass += " bg-red-100 dark:bg-red-950/40 text-red-800 dark:text-red-300";
      diffText = `${diff.toFixed(1)}h`;
    } else {
      diffBadgeClass += " bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400";
      diffText = "0.0h";
    }

    const tr = document.createElement("tr");
    tr.className = "hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors border-b border-gray-100 dark:border-gray-800";
    tr.innerHTML = `
      <td class="py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">${formattedDate}</td>
      <td class="${leftCellClass}">${hLeft.toFixed(1)}h</td>
      <td class="${rightCellClass}">${hRight.toFixed(1)}h</td>
      <td class="py-3 px-4 text-right">
        <span class="${diffBadgeClass}">${diffText}</span>
      </td>
    `;
    tbody.appendChild(tr);
  }

  if (!hasVisibleRows) {
    tbody.innerHTML = `
      <tr>
        <td colspan="4" class="py-8 text-center text-gray-400 dark:text-gray-500 font-medium">
          No differences found for this month!
        </td>
      </tr>
    `;
  }
}

/**
 * Escapes HTML characters to prevent XSS injection.
 * @param {string} str The string to escape.
 * @returns {string} The escaped string.
 */
function escapeHtml(str) {
  if (!str) return "";
  return str
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
