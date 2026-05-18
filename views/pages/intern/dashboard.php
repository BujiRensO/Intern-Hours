<?php
// Ensure this is included through feed.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("Location: ../../feed.php?page=dashboard");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch burnout settings
$burnout_stmt = $pdo->prepare("SELECT hour_goal, hour_from, duty_from, duty_to FROM burnout_counter WHERE user_id = ?");
$burnout_stmt->execute([$user_id]);
$burnout = $burnout_stmt->fetch();
$hour_goal = $burnout ? (int)$burnout['hour_goal'] : 480;
$hour_from = $burnout ? date('Y-m-d', strtotime($burnout['hour_from'])) : date('Y-m-d');
$duty_from = $burnout ? $burnout['duty_from'] : 'Monday';
$duty_to = $burnout ? $burnout['duty_to'] : 'Friday';

$current_month = (int)($_GET['month'] ?? date('m'));
$current_year = (int)($_GET['year'] ?? date('Y'));

$office_id = $_SESSION['office_id'] ?? null;
$organization_id = $_SESSION['organization_id'] ?? null;

$birthdays = [];
if ($office_id && $organization_id) {
    $stmt = $pdo->prepare("
        SELECT name, nickname, birthdate 
        FROM users 
        WHERE office_id = ? 
          AND organization_id = ? 
          AND role = 'Intern' 
          AND birthdate IS NOT NULL 
          AND birthdate != ''
    ");
    $stmt->execute([$office_id, $organization_id]);
    $birthdays = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$base_url = "../";
?>
<link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/dashboard.css">
<link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/colleagues.css">

<div class="dashboard-container">
        <div class="welcome-card full-width mb-6" style="background: white; padding: 20px; border-radius: 12px; shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h1 class="text-2xl font-bold text-gray-900">Welcome, <?php echo htmlspecialchars($user_name); ?></h1>
            <p class="text-gray-600"><?php echo htmlspecialchars($_SESSION['office_name'] ?? 'N/A'); ?> | <?php echo htmlspecialchars($_SESSION['organization_name'] ?? 'N/A'); ?></p>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <style>
        .analytics-grid {
            display: grid;
            grid-template-columns: 180px 1fr 240px;
            gap: 24px;
            align-items: center;
        }

        @media (max-width: 900px) {
            .analytics-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        .chart-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            transition: all 0.3s ease;
        }

        .chart-box:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e1;
        }

        .chart-box.flex-grow {
            align-items: stretch;
        }

        .chart-title {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            text-align: center;
        }

        .burndown-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            text-align: left;
            width: 100%;
        }

        .burndown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #475569;
            font-weight: 600;
        }

        .burndown-item.highlight {
            background: #f0fdf4;
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid #bbf7d0;
            margin-top: 6px;
        }

        .burndown-item .label {
            color: #64748b;
        }

        .burndown-item .value {
            color: #1e293b;
        }

        .text-blue {
            color: #2563eb !important;
        }

        .text-green {
            color: #16a34a !important;
        }

        /* 🌓 Burnout Counter Dark Mode Adaptation Rules */
        .burnout-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }

        .dark-mode .burnout-card {
            background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%) !important;
            border: 1px solid rgba(75, 85, 99, 0.4) !important;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.2) !important;
        }

        .burnout-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
            transition: border-color 0.3s ease;
        }

        .dark-mode .burnout-header {
            border-bottom-color: #374151 !important;
        }

        .burnout-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            letter-spacing: -0.02em;
            color: #0f172a;
            transition: color 0.3s ease;
        }

        .dark-mode .burnout-title {
            color: #f8fafc !important;
        }

        .burnout-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .dark-mode .burnout-subtitle {
            color: #94a3b8 !important;
        }

        .dark-mode .burnout-subtitle span {
            color: #cbd5e1 !important;
        }

        .burnout-goal-badge {
            font-size: 12px;
            font-weight: 800;
            color: #1e3a8a;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 6px 14px;
            border-radius: 9999px;
            letter-spacing: 0.02em;
            box-shadow: 0 2px 5px rgba(59, 130, 246, 0.05);
            transition: all 0.3s ease;
        }

        .dark-mode .burnout-goal-badge {
            color: #60a5fa !important;
            background: rgba(30, 58, 138, 0.4) !important;
            border-color: #1e3a8a !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2) !important;
        }

        .dark-mode .chart-box {
            background: #1e293b !important;
            border-color: #374151 !important;
        }

        .dark-mode .chart-box:hover {
            border-color: #4b5563 !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3) !important;
        }

        .dark-mode .chart-title {
            color: #94a3b8 !important;
        }

        .dark-mode #chart-percent {
            color: #f8fafc !important;
        }

        .dark-mode #chart-ratio {
            color: #94a3b8 !important;
        }

        .dark-mode .burndown-item {
            color: #cbd5e1 !important;
        }

        .dark-mode .burndown-item.highlight {
            background: rgba(20, 83, 45, 0.3) !important;
            border-color: #166534 !important;
        }

        .dark-mode .burndown-item .label {
            color: #94a3b8 !important;
        }

        .dark-mode .burndown-item .value {
            color: #e2e8f0 !important;
        }
        </style>

        <!-- Internship Burnout Counter -->
        <div class="burnout-card full-width mb-6 p-6">
            <div class="burnout-header">
                <div>
                    <h3 class="text-xl font-extrabold burnout-title">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2.2;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                        Internship Burnout Counter
                    </h3>
                    <p class="burnout-subtitle">
                        Started: <span class="font-bold"><?php echo date('M d, Y', strtotime($hour_from)); ?></span> &bull; 
                        Schedule: <span class="font-bold"><?php echo $duty_from; ?> to <?php echo $duty_to; ?></span>
                    </p>
                </div>
                <span class="burnout-goal-badge">
                    Goal: <?php echo $hour_goal; ?> Hours
                </span>
            </div>
            
            <div class="analytics-grid">
                <!-- Doughnut Gauge Chart -->
                <div class="chart-box">
                    <h4 class="chart-title">Progress to Target</h4>
                    <div style="position: relative; width: 120px; height: 120px; margin: 8px auto 0 auto;">
                        <canvas id="progressChart"></canvas>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                            <div id="chart-percent" style="font-size: 20px; font-weight: 900; color: #0f172a; line-height: 1; letter-spacing: -0.03em;">0%</div>
                            <div id="chart-ratio" style="font-size: 10px; color: #64748b; font-weight: 700; margin-top: 2px;">0/<?php echo $hour_goal; ?>h</div>
                        </div>
                    </div>
                </div>
                
                <!-- Weekly Bar Chart -->
                <div class="chart-box flex-grow">
                    <h4 class="chart-title">Hours Logged per Day (This Month)</h4>
                    <div style="position: relative; height: 130px; width: 100%; margin-top: 8px;">
                        <canvas id="hoursBarChart"></canvas>
                    </div>
                </div>
                
                <!-- Burndown Calculator Metrics -->
                <div class="chart-box burndown-box" style="align-items: stretch;">
                    <h4 class="chart-title">Burndown Calculator</h4>
                    <div class="burndown-list" style="margin-top: 4px;">
                        <div class="burndown-item" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; margin-bottom: 4px;">
                            <span class="label">Target:</span>
                            <span class="value"><?php echo $hour_goal; ?> hrs</span>
                        </div>
                        <div class="burndown-item" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; margin-bottom: 4px;">
                            <span class="label">Remaining:</span>
                            <span class="value text-blue" id="burndown-remaining">—</span>
                        </div>
                        <div class="burndown-item" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 6px; margin-bottom: 4px;">
                            <span class="label">Daily Avg:</span>
                            <span class="value" id="burndown-avg">—</span>
                        </div>
                        <div class="burndown-item highlight" style="box-shadow: 0 2px 4px rgba(22, 163, 74, 0.05);">
                            <span class="label" style="color: #15803d;">Est. Completion:</span>
                            <span class="value text-green font-bold" id="burndown-completion">—</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="calendar-section">

            <div class="calendar-header">
                <h2 id="calendar-title">December 2024</h2>
                <div class="calendar-nav">
                    <button onclick="previousMonth()">← Prev</button>
                    <button onclick="nextMonth()">Next →</button>
                    <button class="btn-download-pdf" id="btn-download-pdf" onclick="downloadPDF()">
                        <span>📄</span> Download DTR
                    </button>
                </div>
            </div>

            <div class="calendar-grid" id="calendar-grid"></div>

            <div style="text-align: center; color: #666; font-size: 12px;">
                <p>Click a day to log or edit hours</p>
            </div>
        </div>

        <div class="stats-sidebar">
            <!-- Quick Clock-In/Out Card -->
            <div class="quick-clock-card" id="quick-clock-card">
                <div class="quick-clock-header">
                    <div class="quick-clock-title">🕒 Quick Clock-In</div>
                    <div class="quick-clock-time" id="quick-clock-current-time">00:00</div>
                </div>
                <div class="quick-clock-body">
                    <button class="quick-clock-btn" id="quick-clock-morning-in" onclick="quickClockStamp('morning_in')">
                        <span>🌅 Morning Time In</span>
                        <span class="btn-status" id="status-morning-in">--:--</span>
                    </button>
                    <button class="quick-clock-btn" id="quick-clock-morning-out" onclick="quickClockStamp('morning_out')">
                        <span>🌅 Morning Time Out</span>
                        <span class="btn-status" id="status-morning-out">--:--</span>
                    </button>
                    <button class="quick-clock-btn" id="quick-clock-afternoon-in" onclick="quickClockStamp('afternoon_in')">
                        <span>☀️ Afternoon Time In</span>
                        <span class="btn-status" id="status-afternoon-in">--:--</span>
                    </button>
                    <button class="quick-clock-btn" id="quick-clock-afternoon-out" onclick="quickClockStamp('afternoon_out')">
                        <span>☀️ Afternoon Time Out</span>
                        <span class="btn-status" id="status-afternoon-out">--:--</span>
                    </button>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Total Hours</div>
                <div class="stat-value">
                    <span id="total-hours">0</span>
                    <span class="stat-unit">hrs</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Month Total</div>
                <div class="stat-value">
                    <span id="month-total">0</span>
                    <span class="stat-unit">hrs</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Today's Hours</div>
                <div class="stat-value">
                    <span id="today-hours">0</span>
                    <span class="stat-unit">hrs</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Average/Day</div>
                <div class="stat-value">
                    <span id="average-hours">0</span>
                    <span class="stat-unit">hrs</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label" id="filtered-label">Filtered Total</div>
                <div class="stat-value">
                    <span id="filtered-total">0</span>
                    <span class="stat-unit">hrs</span>
                </div>
            </div>

            <div class="filter-section">
                <div class="stat-label">Filter by Date</div>
                <div class="filter-group">
                    <label>From Date</label>
                    <input type="date" id="filter-from-date">
                </div>
                <div class="filter-group">
                    <label>To Date</label>
                    <input type="date" id="filter-to-date">
                </div>
                <div class="filter-buttons">
                    <button class="btn-filter" onclick="applyFilter()">Apply</button>
                    <button class="btn-reset" onclick="resetFilter()">Reset</button>
                </div>
            </div>
        </div>

        <!-- Sections below calendar and sidebar -->
        <div class="colleagues-section full-width mt-6" style="background: white; padding: 20px; border-radius: 12px; shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 class="text-xl font-bold text-gray-800">Your Colleagues</h3>
                <a href="feed.php?page=colleagues" style="font-size: 13px; font-weight: 600; color: #2563eb; text-decoration: none;">View All →</a>
            </div>
            <div id="interns-list" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <p class="text-gray-500 italic text-sm">Loading colleagues...</p>
            </div>
        </div>
    </div>

    <!-- Intern Hours Detail Modal (shared with colleagues page) -->
    <div class="intern-hours-modal" id="intern-hours-modal">
        <div class="intern-hours-modal-content">
            <div class="intern-modal-header">
                <div class="intern-info">
                    <div class="modal-avatar" id="intern-modal-avatar"></div>
                    <div>
                        <h3 id="intern-modal-name">Loading...</h3>
                        <div class="modal-subtitle" id="intern-modal-subtitle"></div>
                    </div>
                </div>
                <button class="intern-modal-close" onclick="closeInternModal()">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="intern-modal-stats">
                <div class="intern-modal-stat">
                    <div class="value" id="intern-stat-total">—</div>
                    <div class="label">Total Hours</div>
                </div>
                <div class="intern-modal-stat">
                    <div class="value" id="intern-stat-days">—</div>
                    <div class="label">Days Logged</div>
                </div>
                <div class="intern-modal-stat">
                    <div class="value" id="intern-stat-avg">—</div>
                    <div class="label">Avg/Day</div>
                </div>
            </div>
            <div id="intern-modal-body"></div>
        </div>
    </div>

    <!-- Log Hours / Check-In Modal -->
    <div class="modal" id="log-modal">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">Time Log & Check-In</div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Date</label>
                <input type="text" id="modal-date" readonly style="background: #f8fafc; border: 1px solid #cbd5e1; padding: 8px 12px; font-weight: 600; color: #475569;">
            </div>
            
            <div class="time-grid">
                <!-- Morning Segment -->
                <div class="time-segment">
                    <div class="time-segment-title">🌅 Morning Segment</div>
                    <div class="time-input-group">
                        <label for="modal-morning-in">Time In</label>
                        <input type="time" id="modal-morning-in" oninput="calculateModalDuration()">
                    </div>
                    <div class="time-input-group">
                        <label for="modal-morning-out">Time Out</label>
                        <input type="time" id="modal-morning-out" oninput="calculateModalDuration()">
                    </div>
                </div>

                <!-- Afternoon Segment -->
                <div class="time-segment">
                    <div class="time-segment-title">☀️ Afternoon Segment</div>
                    <div class="time-input-group">
                        <label for="modal-afternoon-in">Time In</label>
                        <input type="time" id="modal-afternoon-in" oninput="calculateModalDuration()">
                    </div>
                    <div class="time-input-group">
                        <label for="modal-afternoon-out">Time Out</label>
                        <input type="time" id="modal-afternoon-out" oninput="calculateModalDuration()">
                    </div>
                </div>
            </div>

            <!-- Live Calculated Duration Preview -->
            <div class="live-duration-display">
                Calculated Duty: <span id="modal-duration-preview">0.00</span> hrs
            </div>

            <div class="modal-buttons">
                <button class="btn-save" onclick="saveHours()">Save</button>
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="btn-delete" id="delete-btn" style="display: none;" onclick="deleteHours()">Delete</button>
            </div>
        </div>
    </div>

    <!-- Absence Modal -->
    <div class="modal" id="absence-modal">
        <div class="modal-content">
            <div class="modal-header">Absence Request</div>
            <div id="absence-status-display" style="margin-bottom: 15px; padding: 8px; border-radius: 4px; font-weight: 600; text-align: center; display: none;"></div>
            <div class="form-group">
                <label>Date</label>
                <input type="text" id="absence-modal-date" readonly style="background: #f5f5f5;">
            </div>
            <div class="form-group">
                <label>Reason for Absence</label>
                <textarea id="absence-modal-reason" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: inherit; font-size: 14px; box-sizing: border-box;" placeholder="Explain why you will be absent..."></textarea>
            </div>
            <div class="modal-buttons">
                <button class="btn-save" id="absence-submit-btn" onclick="saveAbsence()">Submit Request</button>
                <button class="btn-delete" id="absence-delete-btn" style="display: none;" onclick="deleteAbsence()">Cancel Request</button>
                <button class="btn-cancel" onclick="closeAbsenceModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        let currentMonth = parseInt('<?php echo $current_month; ?>');
        let currentYear = parseInt('<?php echo $current_year; ?>');
        let userId = parseInt('<?php echo $user_id; ?>');
        let selectedDate = null;
        let hoursData = {};
        let absencesData = {};
        let monthHoursData = {};
        let allHoursData = {};
        let filterFromDate = null;
        let filterToDate = null;
        const currentUserId = userId;
        const apiBasePath = '../';
        const birthdaysData = <?php echo json_encode($birthdays); ?>;
        const hourGoal = parseInt('<?php echo $hour_goal; ?>');
        const hourFrom = '<?php echo $hour_from; ?>';
        const dutyFrom = '<?php echo $duty_from; ?>';
        const dutyTo = '<?php echo $duty_to; ?>';
    </script>
    <script src="../assets/js/dashboard.js"></script>
    <script src="../assets/js/colleagues.js"></script>
