<?php
// Ensure this is included through feed.php and user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../feed.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];

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

// Base URL for assets
$base_url = "../";
?>

<link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/profile.css">
<link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/colleagues.css">

<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Left Sidebar: Identity & Settings Navigation -->
        <div class="lg:w-1/3 space-y-6">
            <!-- Identity Card -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 text-center lg:text-left">
                <div class="inline-flex lg:flex items-center justify-center w-20 h-20 bg-gray-900 rounded-2xl mb-6 text-white text-3xl font-bold shadow-lg">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight"><?php echo htmlspecialchars($user_name); ?></h1>
                <p class="text-blue-600 font-semibold text-sm mt-1 uppercase tracking-widest"><?php echo htmlspecialchars($user_role); ?></p>
            </div>

            <!-- Settings Navigation -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-4 space-y-2">
                <button data-target="general" class="settings-tab active w-full flex items-center gap-3 px-4 py-3 bg-gray-900 text-white rounded-xl transition font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    General
                </button>
                <button data-target="account" class="settings-tab w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-xl transition font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Account
                </button>
                <button data-target="privacy" class="settings-tab w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-xl transition font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Privacy
                </button>
                <button data-target="accessibility" class="settings-tab w-full flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-xl transition font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    Accessibility
                </button>
            </div>

            <div class="px-4 py-2">
                <a href="feed.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-xl transition font-medium group">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Right Content: Dynamic Settings Sections -->
        <div class="lg:w-2/3 space-y-8">
            
            <!-- Notification Toast -->
            <div class="flex justify-end h-8">
                <span id="save-status" class="text-xs font-bold text-green-600 px-3 py-1 bg-green-50 rounded-full opacity-0 transition-opacity">SAVED</span>
            </div>

            <!-- Section: General -->
            <div id="section-general" class="settings-section space-y-8">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-10 text-center">
                    <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">General Settings</h2>
                    <p class="text-gray-500 mt-2">Coming soon! We're working on new ways to customize your experience.</p>
                </div>

                <!-- Stats Preview (Always visible in General) -->
                <div class="bg-gray-900 rounded-3xl p-10 text-white shadow-xl overflow-hidden relative flex flex-col md:flex-row md:items-center justify-between gap-8">
                    <div class="relative z-10">
                        <p class="text-gray-400 text-xs font-bold uppercase tracking-[0.2em]">Total Progress</p>
                        <div class="flex items-baseline gap-3 mt-4">
                            <h2 class="text-7xl font-black tracking-tighter text-white"><?php echo number_format($total_hours, 1); ?></h2>
                            <span class="text-2xl font-bold text-blue-500 uppercase">Hours</span>
                        </div>
                    </div>
                    <svg class="absolute -right-10 -bottom-10 w-64 h-64 text-white/5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>

            <!-- Section: Account -->
            <div id="section-account" class="settings-section hidden space-y-8">
                <!-- Account Details -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-50">
                        <h2 class="text-xl font-bold text-gray-900">Account Details</h2>
                    </div>
                    <div class="p-8 grid md:grid-cols-2 gap-x-12 gap-y-10">
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
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-50">
                        <h2 class="text-xl font-bold text-gray-900">Security & Password</h2>
                    </div>
                    <div class="p-8">
                        <div id="password-alert" class="hidden"></div>
                        <form id="password-form" class="space-y-6">
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">New Password</label>
                                    <input type="password" id="new_password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent outline-none transition">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-semibold text-gray-700">Confirm New Password</label>
                                    <input type="password" id="confirm_password" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent outline-none transition">
                                </div>
                            </div>
                            <button type="submit" class="px-8 py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-black transition shadow-lg shadow-gray-100">
                                Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Section: Privacy -->
            <div id="section-privacy" class="settings-section hidden space-y-8">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Privacy Controls</h2>
                            <p class="text-sm text-gray-500 mt-1">Control how your data is shared with others.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="privacy-toggle" class="sr-only peer" <?php echo $is_public ? 'checked' : ''; ?>>
                            <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div class="space-y-4 pt-4 border-t border-gray-50">
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

                <!-- Colleagues Section (Moved here) -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
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

            <!-- Section: Accessibility -->
            <div id="section-accessibility" class="settings-section hidden space-y-8">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Appearance</h2>
                            <p class="text-sm text-gray-500 mt-1">Customize the visual experience of OurTracker.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="darkmode-toggle" class="sr-only peer" <?php echo $is_darkmode ? 'checked' : ''; ?>>
                            <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mt-8">
                        <div class="p-4 rounded-2xl border-2 border-blue-500 bg-blue-50/30 flex flex-col items-center gap-3">
                            <div class="w-full h-12 bg-white rounded-lg shadow-sm"></div>
                            <span class="text-xs font-bold text-gray-900">Light Mode</span>
                        </div>
                        <div class="p-4 rounded-2xl border-2 border-transparent bg-gray-900 flex flex-col items-center gap-3">
                            <div class="w-full h-12 bg-gray-800 rounded-lg shadow-sm"></div>
                            <span class="text-xs font-bold text-gray-400">Dark Mode</span>
                        </div>
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
            <div class="intern-modal-stat"><div class="value" id="intern-stat-total">—</div><div class="label">Total Hours</div></div>
            <div class="intern-modal-stat"><div class="value" id="intern-stat-days">—</div><div class="label">Days Logged</div></div>
            <div class="intern-modal-stat"><div class="value" id="intern-stat-avg">—</div><div class="label">Avg/Day</div></div>
        </div>
        <div id="intern-modal-body"></div>
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

<!-- Intern Hours Detail Modal -->
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
            <!-- Calendar or private notice will render here -->
        </div>
    </div>
</div>
