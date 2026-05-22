<?php
// Ensure this is included through feed.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("Location: ../../feed.php?page=compare");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$current_month = (int)($_GET['month'] ?? date('m'));
$current_year = (int)($_GET['year'] ?? date('Y'));
$base_url = "../";

// Fetch current user's profile picture URL
$stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$currentUserProfilePicture = $stmt->fetchColumn() ?: '';
?>

<link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/dashboard.css">

<div class="dashboard-container" style="max-width: 1000px; margin: 0 auto; padding: 24px;">
    <!-- Welcome Header / Navigation Link -->
    <div class="welcome-card full-width mb-6" style="background: white; padding: 20px; border-radius: 12px; shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Colleague Comparison</h1>
            <p class="text-gray-600">Analyze daily logs, month totals, and averages side-by-side with your colleagues.</p>
        </div>
        <a href="feed.php?page=dashboard" class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 font-bold rounded-xl transition text-sm">
            ← Back to Dashboard
        </a>
    </div>

    <!-- Chart.js source -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
    /* 📊 Colleague Comparison Styles */
    .comparison-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        transition: all 0.3s ease;
    }
    .dark-mode .comparison-card {
        background: #1e293b !important;
        border-color: #374151 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3) !important;
    }
    .compare-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    @media (max-width: 768px) {
        .compare-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
    }
    .compare-column select {
        width: 100%;
        padding: 10px 14px;
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        font-size: 14px;
        font-weight: 600;
        outline: none;
        background-color: white;
        color: #1e293b;
        transition: all 0.2s ease;
    }
    .dark-mode .compare-column select {
        background-color: #111827 !important;
        border-color: #374151 !important;
        color: #f3f4f6 !important;
    }
    .compare-column select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    .dark-mode .compare-column select:focus {
        border-color: #3b82f6 !important;
    }
    .compare-profile-card {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 16px;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        transition: all 0.3s ease;
    }
    .dark-mode .compare-profile-card {
        background: #111827 !important;
        border-color: #1f2937 !important;
    }
    .compare-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 18px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .compare-stat-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        padding-bottom: 8px;
        border-bottom: 1px dashed #e2e8f0;
    }
    .dark-mode .compare-stat-row {
        border-bottom-color: #1f2937 !important;
    }
    .compare-stat-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .compare-stat-label {
        color: #64748b;
        font-weight: 500;
    }
    .dark-mode .compare-stat-label {
        color: #94a3b8 !important;
    }
    .compare-stat-value {
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .dark-mode .compare-stat-value {
        color: #f3f4f6 !important;
    }
    .diff-badge {
        font-size: 10px;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 6px;
        display: inline-block;
    }
    .diff-badge.positive {
        background-color: #ecfdf5;
        color: #059669;
    }
    .dark-mode .diff-badge.positive {
        background-color: rgba(6, 78, 59, 0.4) !important;
        color: #34d399 !important;
    }
    .diff-badge.negative {
        background-color: #fef2f2;
        color: #dc2626;
    }
    .dark-mode .diff-badge.negative {
        background-color: rgba(69, 10, 10, 0.4) !important;
        color: #f87171 !important;
    }
    .diff-badge.neutral {
        background-color: #f1f5f9;
        color: #64748b;
    }
    .dark-mode .diff-badge.neutral {
        background-color: #1f2937 !important;
        color: #94a3b8 !important;
    }

    /* Calendar Header for Page Monthly Switcher */
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding: 16px 24px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
    }
    .dark-mode .calendar-header {
        background: #1e293b !important;
        border-color: #374151 !important;
    }
    .calendar-header h2 {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .dark-mode .calendar-header h2 {
        color: #f8fafc !important;
    }
    .calendar-nav {
        display: flex;
        gap: 8px;
    }
    .calendar-nav button {
        padding: 8px 16px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .dark-mode .calendar-nav button {
        background: rgba(96, 165, 250, 0.1) !important;
        color: #60a5fa !important;
        border-color: rgba(96, 165, 250, 0.25) !important;
    }
    .calendar-nav button:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .dark-mode .calendar-nav button:hover {
        background: #2563eb !important;
        color: white !important;
        border-color: #2563eb !important;
    }
    </style>

    <!-- Month Navigation Section -->
    <div class="calendar-header">
        <h2 id="calendar-title">Loading month...</h2>
        <div class="calendar-nav">
            <button onclick="previousMonth()">← Prev</button>
            <button onclick="nextMonth()">Next →</button>
        </div>
    </div>

    <!-- Colleague Comparison Section -->
    <div class="comparison-card full-width p-6">
        <div class="compare-grid">
            <!-- Left Individual -->
            <div class="compare-column">
                <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2" for="compare-select-left">First Individual</label>
                <select id="compare-select-left" onchange="onCompareSelectChange('left')">
                    <option value="">Select individual...</option>
                </select>
                
                <div id="compare-card-left" class="compare-profile-card mt-3">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; text-align: center; font-size: 13px; padding: 24px 0;">
                        <span>Select a colleague or yourself to compare.</span>
                    </div>
                </div>
            </div>

            <!-- Right Individual -->
            <div class="compare-column">
                <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2" for="compare-select-right">Second Individual</label>
                <select id="compare-select-right" onchange="onCompareSelectChange('right')">
                    <option value="">Select individual...</option>
                </select>
                
                <div id="compare-card-right" class="compare-profile-card mt-3">
                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #94a3b8; text-align: center; font-size: 13px; padding: 24px 0;">
                        <span>Select a colleague or yourself to compare.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart visual -->
        <div id="compare-chart-container" class="mt-6 p-4 rounded-xl border border-gray-100 bg-gray-50/20 dark:border-gray-800 dark:bg-gray-900/40 hidden">
            <h4 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4 text-center" id="compare-chart-title">Daily Hours Comparison Timeline</h4>
            <div style="position: relative; height: 320px; width: 100%;">
                <canvas id="comparisonBarChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    let currentMonth = parseInt('<?php echo $current_month; ?>');
    let currentYear = parseInt('<?php echo $current_year; ?>');
    let userId = parseInt('<?php echo $user_id; ?>');
    let allHoursData = {};
    const currentUserId = userId;
    const currentUserName = '<?php echo htmlspecialchars($user_name, ENT_QUOTES); ?>';
    const currentUserEmail = '<?php echo htmlspecialchars($_SESSION['user_email'] ?? '', ENT_QUOTES); ?>';
    const currentUserProfilePicture = '<?php echo htmlspecialchars($currentUserProfilePicture, ENT_QUOTES); ?>';
    const apiBasePath = '../';
</script>
<script src="../assets/js/compare.js"></script>
