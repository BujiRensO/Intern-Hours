<?php
// Ensure this is included through feed.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("Location: ../../feed.php?page=settings");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Fetch latest user data including is_public, is_darkmode and total hours
$stmt = $pdo->prepare("
    SELECT u.*, 
    (SELECT SUM(hours) FROM hours_log WHERE user_id = u.id) as total_hours
    FROM users u WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$user_name = $user['name'];
$user_email = $user['email'];
$user_role = $user['role'];
$is_public = (bool)$user['is_public'];
$is_darkmode = (bool)$user['is_darkmode'];
$total_hours = $user['total_hours'] ?? 0;

$office_name = $_SESSION['office_name'] ?? 'Not Assigned';
$organization_name = $_SESSION['organization_name'] ?? 'Not Assigned';
$office_id = $_SESSION['office_id'];
$organization_id = $_SESSION['organization_id'];

// Fetch recent login sessions
$stmt = $pdo->prepare("SELECT ip_address, browser, device, created_at FROM login_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for relative time
if (!function_exists('get_relative_time_str')) {
    function get_relative_time_str($datetime) {
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        if ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        }
        if ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        }
        if ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        }
        return 'just now';
    }
}

// Base URL for assets
$base_url = "../";
?>

<link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/profile.css">
<link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/colleagues.css">

<style>
.settings-tab.active {
    background-color: #111827 !important;
    color: #ffffff !important;
}

.dark-mode .settings-tab.active {
    background-color: #3b82f6 !important;
    color: #ffffff !important;
}

.settings-section.hidden {
    display: none;
}

.dark-mode .settings-tab:hover:not(.active) {
    background-color: #374151 !important;
    color: #f9fafb !important;
}

/* Custom form-input compatibility with dark mode */
.dark-mode .form-input {
    background-color: #111827 !important;
    border-color: #374151 !important;
    color: #ffffff !important;
}

.dark-mode .settings-title {
    color: #ffffff !important;
}
.dark-mode .settings-title svg {
    color: #cbd5e1 !important;
}
</style>

<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Left Sidebar: Identity & Settings Navigation -->
        <div class="lg:w-1/3 space-y-6">
            <!-- Identity Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 text-center lg:text-left glass-card">
                <div id="sidebar-avatar-container" class="inline-flex lg:flex items-center justify-center w-20 h-20 bg-gray-900 rounded-2xl mb-6 text-white text-3xl font-bold shadow-lg overflow-hidden">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img id="sidebar-profile-pic" src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span id="sidebar-initials"><?php echo strtoupper(substr($user_name, 0, 1)); ?></span>
                    <?php endif; ?>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight"><?php echo htmlspecialchars($user_name); ?></h1>
                <p class="text-blue-600 font-semibold text-sm mt-1 uppercase tracking-widest"><?php echo htmlspecialchars($user_role); ?></p>
            </div>

            <!-- Settings Navigation -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-4 space-y-2 glass-card">
                <button data-target="account" class="settings-tab active w-full flex items-center gap-3 px-4 py-3 bg-gray-900 text-white rounded-xl transition font-bold text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Profile & Account
                </button>
                <button data-target="privacy" class="settings-tab w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-xl transition font-bold text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Privacy & Network
                </button>
            </div>

            <div class="px-4 py-2">
                <a href="feed.php?page=dashboard" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-xl transition font-bold text-sm group">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Right Content: Dynamic Settings Sections -->
        <div class="lg:w-2/3 space-y-8">
            
            <!-- Notification Toast -->
            <div class="flex justify-between items-center h-8">
                <div>
                    <?php if (!empty($success_msg)): ?>
                        <span class="text-xs font-bold text-green-600 px-3 py-1 bg-green-50 rounded-full"><?php echo $success_msg; ?></span>
                    <?php elseif (!empty($error_msg)): ?>
                        <span class="text-xs font-bold text-red-600 px-3 py-1 bg-red-50 rounded-full"><?php echo $error_msg; ?></span>
                    <?php endif; ?>
                </div>
                <span id="save-status" class="text-xs font-bold text-green-600 px-3 py-1 bg-green-50 rounded-full opacity-0 transition-opacity">SAVED</span>
            </div>

            <!-- Section: Profile & Account -->
            <div id="section-account" class="settings-section space-y-8">
                <!-- Appearance Settings -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 glass-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Appearance</h2>
                            <p class="text-sm text-gray-500 mt-1">Toggle between light and dark visual themes.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="darkmode-toggle" class="sr-only peer" <?php echo $is_darkmode ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                </div>

                <!-- Profile Picture Settings -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 glass-card">
                    <div id="profile-picture-alert" class="hidden"></div>
                    <form id="profile-picture-form" class="space-y-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Profile Picture</h2>
                            <p class="text-sm text-gray-500 mt-1">Enter a public URL to display a profile picture.</p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row items-center gap-6 pt-2">
                            <!-- Live Preview -->
                            <div class="relative w-20 h-20 bg-gray-900 rounded-2xl flex items-center justify-center text-white text-3xl font-bold shadow-lg overflow-hidden shrink-0">
                                <img id="preview-profile-pic" src="<?php echo !empty($user['profile_picture']) ? htmlspecialchars($user['profile_picture']) : ''; ?>" alt="Preview" class="w-full h-full object-cover <?php echo empty($user['profile_picture']) ? 'hidden' : ''; ?>">
                                <span id="preview-initials" class="<?php echo !empty($user['profile_picture']) ? 'hidden' : ''; ?>"><?php echo strtoupper(substr($user_name, 0, 1)); ?></span>
                            </div>
                            
                            <!-- Input Field -->
                            <div class="flex-1 w-full space-y-2">
                                <label class="text-sm font-semibold text-gray-700">Image URL</label>
                                <input type="url" id="profile_picture_url" placeholder="https://example.com/avatar.jpg" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition form-input" value="<?php echo htmlspecialchars($user['profile_picture'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" class="px-8 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-black transition shadow-lg shadow-gray-100">
                            Save Profile Picture
                        </button>
                    </form>
                </div>

                <!-- Account Details -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden glass-card">
                    <div class="py-6 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900">Account Details</h2>
                    </div>
                    <div class="py-8 grid md:grid-cols-2 gap-x-12 gap-y-10">
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Full Name</label>
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($user_name); ?></p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Nickname</label>
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($user['nickname'] ?? 'Not Set'); ?></p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Email Identity</label>
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($user_email); ?></p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Contact Number</label>
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($user['contact'] ?? 'Not Set'); ?></p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Birthdate</label>
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($user['birthdate'] ?? 'Not Set'); ?></p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">ZIP / Postal Code</label>
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($user['postal_code'] ?? 'Not Set'); ?></p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Office / Department</label>
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($office_name); ?></p>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Assigned Organization</label>
                            <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($organization_name); ?></p>
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Geographic Address</label>
                            <p class="text-lg font-medium text-gray-900">
                                <?php 
                                echo htmlspecialchars(implode(', ', array_filter([
                                    $user['address'] ?? '',
                                    !empty($user['barangay']) ? 'Brgy. ' . $user['barangay'] : '',
                                    $user['city'] ?? '',
                                    $user['province'] ?? '',
                                    $user['region'] ?? ''
                                ]))); 
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Password Change -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden glass-card">
                    <div class="py-6 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900">Security & Password</h2>
                    </div>
                    <div class="py-8">
                        <div id="password-alert" class="hidden"></div>
                        <form id="password-form" class="space-y-6">
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">New Password</label>
                                    <input type="password" id="new_password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition form-input">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Confirm New Password</label>
                                    <input type="password" id="confirm_password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none transition form-input">
                                </div>
                            </div>
                            <button type="submit" class="px-8 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-black transition shadow-lg shadow-gray-100">
                                Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Section 3: Privacy & Network -->
            <div id="section-privacy" class="settings-section hidden space-y-8">
                <!-- Privacy Controls -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 glass-card">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Privacy Controls</h2>
                            <p class="text-sm text-gray-500 mt-1">Control how your data is shared with others.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="privacy-toggle" class="sr-only peer" <?php echo $is_public ? 'checked' : ''; ?>>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div class="space-y-4 pt-4 border-t border-gray-100">
                        <div class="flex gap-4">
                            <div class="p-2 bg-blue-50 text-blue-600 rounded-lg h-fit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">Public Visibility</h4>
                                <p class="text-sm text-gray-600 mt-1">When enabled, other interns in your organization can see your total hours and daily logs. Supervisors can always see your activity regardless of this setting.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Device Sessions -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 glass-card">
                    <h2 class="settings-title font-bold text-xl text-gray-900 flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Active Sessions
                    </h2>
                    <p style="font-size: 12px; color: #6b7280; margin-bottom: 16px;">Audit recent device login events to keep your account secure.</p>
                    
                    <div class="sessions-list">
                        <?php if (empty($sessions)): ?>
                            <p style="font-size: 13px; color: #6b7280; font-style: italic;">No login logs recorded yet.</p>
                        <?php else: ?>
                            <?php 
                            $current_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
                            foreach ($sessions as $index => $sess): 
                                $is_current = ($index === 0 && $sess['ip_address'] === $current_ip);
                            ?>
                                <div class="session-item <?php echo $is_current ? 'current' : ''; ?>" style="display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #f1f5f9;">
                                    <div class="session-details">
                                        <div class="session-device font-bold text-sm text-gray-800">
                                            <?php echo htmlspecialchars($sess['device'] ?: 'Unknown Device'); ?>
                                        </div>
                                        <div class="session-meta text-xs text-gray-500">
                                            <?php echo htmlspecialchars($sess['browser'] ?: 'Unknown Browser'); ?> | <?php echo htmlspecialchars($sess['ip_address']); ?>
                                        </div>
                                        <div class="session-meta text-[10px] text-gray-400 mt-1">
                                            <?php echo get_relative_time_str($sess['created_at']); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <?php if ($is_current): ?>
                                            <span class="px-2 py-1 bg-green-50 text-green-700 text-xs font-bold rounded-lg border border-green-200">Active Now</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 bg-gray-50 text-gray-600 text-xs font-bold rounded-lg border border-gray-200">Logged In</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Colleagues Network Section -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 glass-card">
                    <div class="flex items-center justify-between mb-8">
                        <h2 class="text-xl font-bold text-gray-900">Your Network</h2>
                        <p class="text-sm text-gray-500 font-medium">Visible colleagues</p>
                    </div>
                    <div id="interns-list" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div class="animate-pulse bg-gray-50 h-24 rounded-2xl"></div>
                        <div class="animate-pulse bg-gray-50 h-24 rounded-2xl"></div>
                        <div class="animate-pulse bg-gray-50 h-24 rounded-2xl"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Container (for colleagues view) -->
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
        <div id="intern-modal-body">
            <!-- Calendar will render here dynamically -->
        </div>
    </div>
</div>

<script>
    const userName = <?php echo json_encode($user_name); ?>;
    const userEmail = <?php echo json_encode($user_email); ?>;
    const officeId = <?php echo json_encode($office_id); ?>;
    const organizationId = <?php echo json_encode($organization_id); ?>;
    const currentUserId = <?php echo $user_id; ?>;
    const apiBasePath = '<?php echo $base_url; ?>';
</script>
<script src="<?php echo $base_url; ?>assets/js/colleagues.js"></script>
<script src="<?php echo $base_url; ?>assets/js/profile.js"></script>
