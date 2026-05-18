<?php
session_start();

// Check if user is logged in and is Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: ../../feed.php?page=login");
    exit;
}

require_once __DIR__ . '/../../../config.php';

$intern_id = $_GET['id'] ?? null;
if (!$intern_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch intern details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'Intern'");
$stmt->execute([$intern_id]);
$intern = $stmt->fetch();

if (!$intern) {
    header("Location: dashboard.php");
    exit;
}

$birthdays = [];
if (!empty($intern['office_id']) && !empty($intern['organization_id'])) {
    $stmt = $pdo->prepare("
        SELECT name, nickname, birthdate 
        FROM users 
        WHERE office_id = ? 
          AND organization_id = ? 
          AND role = 'Intern' 
          AND birthdate IS NOT NULL 
          AND birthdate != ''
    ");
    $stmt->execute([$intern['office_id'], $intern['organization_id']]);
    $birthdays = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$current_month = (int)($_GET['month'] ?? date('m'));
$current_year = (int)($_GET['year'] ?? date('Y'));

$base_url = "../../../";
require_once '../../components/header.php';
?>
<link rel="stylesheet" href="../../../assets/css/dashboard.css">
<style>
    .supervisor-view-badge {
        background: #ebf8ff;
        color: #2b6cb0;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 10px;
    }
    /* Hide log hours interaction for supervisors on this page */
    .day-cell:not(.other-month) {
        cursor: default !important;
    }
</style>
</head>
<body class="bg-gray-50">
    <?php require_once '../../components/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="welcome-card full-width mb-6" style="background: white; padding: 20px; border-radius: 12px; shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div class="supervisor-view-badge">Supervisor View</div>
            <h1 class="text-2xl font-bold text-gray-900">Intern: <?php echo htmlspecialchars($intern['name']); ?></h1>
            <p class="text-gray-600"><?php echo htmlspecialchars($intern['email']); ?></p>
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 text-sm mt-4 inline-block">← Back to Dashboard</a>
        </div>

        <div class="calendar-section">
            <div class="calendar-header">
                <h2 id="calendar-title"></h2>
                <div class="calendar-nav">
                    <button onclick="previousMonth()">← Prev</button>
                    <button onclick="nextMonth()">Next →</button>
                </div>
            </div>
            <div class="calendar-grid" id="calendar-grid"></div>
        </div>

        <div class="stats-sidebar">
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

        <!-- Absence Requests List Below -->
        <div class="colleagues-section full-width mt-8" style="background: white; padding: 20px; border-radius: 12px; shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Absence History</h2>
            <div id="intern-absences-list" class="grid gap-4">
                <p class="text-gray-500 italic">Loading absences...</p>
            </div>
        </div>
    </div>

    <script>
        let currentMonth = parseInt('<?php echo $current_month; ?>');
        let currentYear = parseInt('<?php echo $current_year; ?>');
        let userId = parseInt('<?php echo $intern_id; ?>');
        let isSupervisorView = true; // Flag to disable modal interactions in dashboard.js
        let selectedDate = null;
        let hoursData = {};
        let absencesData = {};
        let monthHoursData = {};
        let allHoursData = {};
        let filterFromDate = null;
        let filterToDate = null;
        const birthdaysData = <?php echo json_encode($birthdays); ?>;

        // Override openLogModal and openAbsenceModal to do nothing in supervisor view
        function openLogModal() {}
        function openAbsenceModal() {}

        // Custom function to load intern absences and render them with approval controls
        function loadInternAbsenceHistory() {
            fetch(`../../../api/absences.php?userId=${userId}`)
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('intern-absences-list');
                    if (data.success) {
                        if (data.absences.length === 0) {
                            list.innerHTML = '<p class="text-gray-500">No absence requests found for this intern.</p>';
                            return;
                        }

                        list.innerHTML = '';
                        data.absences.forEach(abs => {
                            const card = document.createElement('div');
                            card.className = 'border-l-4 p-4 bg-gray-50 rounded shadow-sm flex justify-between items-center';
                            const statusColor = abs.status === 'Approved' ? 'border-green-500' : (abs.status === 'Rejected' ? 'border-red-500' : 'border-yellow-500');
                            card.classList.add(statusColor);

                            card.innerHTML = `
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-800">${formatDate(abs.date)}</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full ${getStatusClass(abs.status)}">${abs.status}</span>
                                    </div>
                                    <p class="text-gray-600 text-sm mt-1">${abs.reason || 'No reason provided'}</p>
                                </div>
                                ${abs.status === 'Pending' ? `
                                <div class="flex gap-2">
                                    <button onclick="handleAbsenceAction(${abs.absences_id}, 'approve')" class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition">Approve</button>
                                    <button onclick="handleAbsenceAction(${abs.absences_id}, 'reject')" class="px-3 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition">Reject</button>
                                </div>
                                ` : ''}
                            `;
                            list.appendChild(card);
                        });
                    }
                });
        }

        function getStatusClass(status) {
            switch(status) {
                case 'Approved': return 'bg-green-100 text-green-700';
                case 'Rejected': return 'bg-red-100 text-red-700';
                default: return 'bg-yellow-100 text-yellow-700';
            }
        }

        function formatDate(dateStr) {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const [y, m, d] = dateStr.split('-');
            return `${months[parseInt(m)-1]} ${parseInt(d)}, ${y}`;
        }

        function handleAbsenceAction(id, action) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('id', id);

            fetch('../../../api/absences.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadInternAbsenceHistory();
                    loadAbsences(); // Update calendar
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadInternAbsenceHistory();
        });
    </script>
    <script src="../../../assets/js/dashboard.js"></script>
    <?php require_once '../../components/footer.php'; ?>
</body>
</html>
