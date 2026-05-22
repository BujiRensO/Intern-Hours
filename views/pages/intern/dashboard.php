<?php
// Ensure this is included through feed.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("Location: ../../feed.php?page=dashboard");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch burnout settings
$burnout_stmt = $pdo->prepare("SELECT hour_goal, starting_date, duty_days FROM burnout_counter WHERE user_id = ?");
$burnout_stmt->execute([$user_id]);
$burnout = $burnout_stmt->fetch();
$hour_goal = $burnout ? (int)$burnout['hour_goal'] : 480;
$starting_date = $burnout ? date('Y-m-d', strtotime($burnout['starting_date'])) : date('Y-m-d');
$duty_days = $burnout ? $burnout['duty_days'] : 'Monday,Tuesday,Wednesday,Thursday,Friday';

// Format display schedule nicely (e.g. "Mon, Tue, Wed, Thu, Fri")
$days_list = explode(',', $duty_days);
$short_days = array_map(function($d) { return substr(trim($d), 0, 3); }, $days_list);
$display_schedule = implode(', ', $short_days);

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
                        Started: <span class="font-bold"><?php echo date('M d, Y', strtotime($starting_date)); ?></span> &bull; 
                        Schedule: <span class="font-bold"><?php echo htmlspecialchars($display_schedule); ?></span>
                    </p>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="burnout-goal-badge">
                        Goal: <?php echo $hour_goal; ?> Hours
                    </span>
                    <button id="open-goal-modal-btn" class="p-2 bg-blue-50 hover:bg-blue-100 text-blue-600 dark:bg-blue-950/40 dark:hover:bg-blue-900/60 dark:text-blue-400 rounded-xl transition shadow-sm border border-blue-100 dark:border-blue-900" title="Adjust Goal Settings">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </button>
                </div>
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

                    <!-- Sleek Biometric stamp integration -->
                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed rgba(255,255,255,0.15);" class="biometric-actions-container">
                        <button class="quick-clock-btn" id="biometric-clock-btn" onclick="biometricClock()" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); border: none; color: white; display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: bold; width: 100%; border-radius: 12px; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2); cursor: pointer; padding: 12px 16px;">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 18px; height: 18px; shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 009 11a5 5 0 00-10 0c0 1.017.07 2.019.203 3m-6.973-.001A13.9 13.9 0 003 11c0-1.898.377-3.707 1.057-5.364m-.633 13.731A13.89 13.89 0 0012 21c3.517 0 6.799-1.009 9.571-2.753m-2.04-3.44l-.09.054A13.916 13.916 0 0011 15c0 1.017-.07 2.019-.203 3m-6.973-.001A13.9 13.9 0 0011 3c1.898 0 3.707.377 5.364 1.057m-13.731-.633A13.89 13.89 0 003 12c0 3.517 1.009 6.799 2.753 9.571m3.44-2.04l.09-.054A13.916 13.916 0 0015 11c0-1.017.07-2.019.203-3m6.973.001A13.9 13.9 0 0013 3c-1.898 0-3.707.377-5.364 1.057m13.731.633A13.89 13.89 0 0021 12c0-3.517-1.009-6.799-2.753-9.571m-3.44 2.04l-.054.09z"></path></svg>
                            Fingerprint Stamp
                        </button>
                        <div style="text-align: center; margin-top: 8px;">
                            <a href="javascript:void(0)" onclick="enrollBiometrics()" class="text-indigo-400 hover:text-indigo-300 font-bold text-xs" style="text-decoration: underline; text-underline-offset: 4px; display: inline-flex; align-items: center; gap: 4px; transition: color 0.2s;">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 12px; height: 12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                Enroll Fingerprint Sensor
                            </a>
                        </div>
                    </div>
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

    <!-- Adjust Goal Settings Modal Overlay -->
    <div id="goal-modal" class="fixed inset-0 z-[1000] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-all duration-300">
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl transform scale-95 transition-all duration-300 relative">
            <button id="close-goal-modal-btn" class="absolute top-6 right-6 text-gray-400 hover:text-gray-900 dark:hover:text-white transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <h3 class="text-xl font-extrabold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke-width: 2.2;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                Goal Settings
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Configure target hours and schedule for your burnout calculation.</p>
            
            <div id="goal-modal-alert" class="hidden p-4 rounded-xl text-xs font-bold mb-5"></div>
            
            <form id="goal-modal-form" class="space-y-5">
                <div>
                    <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2" for="modal_hour_goal">Target Internship Hours</label>
                    <input class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 outline-none transition bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" type="number" id="modal_hour_goal" required min="1" value="<?php echo $hour_goal; ?>">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2" for="modal_starting_date">Start Date</label>
                    <input class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 outline-none transition bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" type="date" id="modal_starting_date" required value="<?php echo $starting_date; ?>">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2">Duty Days Schedule</label>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2.5 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                        <?php
                        $all_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        $active_days = explode(',', $duty_days);
                        foreach ($all_days as $day):
                            $checked = in_array($day, $active_days) ? 'checked' : '';
                        ?>
                            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="checkbox" name="modal_duty_days[]" value="<?php echo $day; ?>" <?php echo $checked; ?> class="w-4 h-4 rounded text-blue-600 border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-blue-500 cursor-pointer">
                                <?php echo $day; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="flex gap-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button type="button" id="cancel-goal-modal-btn" class="flex-1 py-3 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700/80 transition text-sm">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition text-sm shadow-lg shadow-blue-500/10">
                        Save Changes
                    </button>
                </div>
            </form>
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
        const currentUserName = '<?php echo htmlspecialchars($user_name, ENT_QUOTES); ?>';
        const currentUserEmail = '<?php echo htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES); ?>';
        const apiBasePath = '../';
        const birthdaysData = <?php echo json_encode($birthdays); ?>;
        const hourGoal = parseInt('<?php echo $hour_goal; ?>');
        const startingDate = '<?php echo $starting_date; ?>';
        const dutyDays = '<?php echo $duty_days; ?>';
    </script>
    <script src="../assets/js/dashboard.js"></script>
    <script src="../assets/js/colleagues.js"></script>
    <script src="../assets/js/biometrics.js"></script>
