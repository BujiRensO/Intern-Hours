<?php
// Ensure this is included through feed.php and user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../feed.php?page=login");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch latest user data including is_public and total hours
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
$total_hours = $user['total_hours'] ?? 0;

$office_name = $_SESSION['office_name'] ?? 'Not Assigned';
$organization_name = $_SESSION['organization_name'] ?? 'Not Assigned';

// Base URL for assets
$base_url = "../";
?>

<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Left Sidebar: Identity & Navigation -->
        <div class="lg:w-1/3 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 text-center lg:text-left">
                <div class="inline-flex lg:flex items-center justify-center w-20 h-20 bg-gray-900 rounded-2xl mb-6 text-white text-3xl font-bold shadow-lg">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight"><?php echo htmlspecialchars($user_name); ?></h1>
                <p class="text-blue-600 font-semibold text-sm mt-1 uppercase tracking-widest"><?php echo htmlspecialchars($user_role); ?></p>
                
                <div class="mt-8 space-y-3">
                    <a href="feed.php" class="flex items-center gap-3 px-4 py-3 text-gray-600 hover:bg-gray-50 rounded-xl transition font-medium group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Back to Dashboard
                    </a>
                    <a href="../api/logout.php" class="flex items-center gap-3 px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl transition font-medium group">
                        <svg class="w-5 h-5 text-red-300 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Sign Out
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Content: Detailed Info & Settings -->
        <div class="lg:w-2/3 space-y-8">
            
            <!-- Stats Card (Focal Point) -->
            <div class="bg-gray-900 rounded-3xl p-10 text-white shadow-xl overflow-hidden relative">
                <div class="relative z-10">
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-[0.2em]">Total Accumulated Progress</p>
                    <div class="flex items-baseline gap-3 mt-4">
                        <h2 class="text-7xl font-black tracking-tighter text-white"><?php echo number_format($total_hours, 1); ?></h2>
                        <span class="text-2xl font-bold text-blue-500 uppercase">Hours</span>
                    </div>
                    <div class="mt-6 flex items-center gap-2 text-gray-400 text-sm font-medium">
                        <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                        Verified and up to date
                    </div>
                </div>
                <svg class="absolute -right-10 -bottom-10 w-64 h-64 text-white/5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>

            <!-- Information Grid -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-50 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-gray-900">Professional Information</h2>
                    <span id="save-status" class="text-xs font-bold text-green-600 px-3 py-1 bg-green-50 rounded-full opacity-0 transition-opacity">SAVED</span>
                </div>
                <div class="p-8 grid md:grid-cols-2 gap-x-12 gap-y-10">
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Email Identity</label>
                        <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($user_email); ?></p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Assigned Organization</label>
                        <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($organization_name); ?></p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Office / Department</label>
                        <p class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($office_name); ?></p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest">Privacy Controls</label>
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-sm text-gray-600">Public visibility</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="privacy-toggle" class="sr-only peer" <?php echo $is_public ? 'checked' : ''; ?>>
                                <div class="w-10 h-5 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colleagues Section -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-bold text-gray-900">Your Network</h2>
                    <p class="text-sm text-gray-500 font-medium">Colleagues in your organization</p>
                </div>
                <div id="interns-list" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="animate-pulse bg-gray-50 h-24 rounded-2xl"></div>
                    <div class="animate-pulse bg-gray-50 h-24 rounded-2xl"></div>
                    <div class="animate-pulse bg-gray-50 h-24 rounded-2xl"></div>
                </div>
            </div>

            <!-- Security Note -->
            <div class="bg-blue-50/50 rounded-2xl p-6 border border-blue-100/50 flex items-center gap-4">
                <div class="p-3 bg-white rounded-xl shadow-sm">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <p class="text-sm text-blue-800 font-medium leading-relaxed">
                    Account security is important. Your profile data is encrypted and only shared with verified supervisors within your organization.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function loadColleagues() {
    const list = document.getElementById("interns-list");
    const currentUserId = <?php echo $user_id; ?>;

    fetch('../api/interns.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.interns.length <= 1) {
                    list.innerHTML = '<p class="text-gray-400 text-sm col-span-full py-4 text-center italic">No other colleagues found in your network.</p>';
                    return;
                }

                list.innerHTML = "";
                data.interns.forEach(intern => {
                    if (parseInt(intern.id) === currentUserId) return;

                    const div = document.createElement("div");
                    div.className = "flex items-center gap-4 bg-gray-50/50 p-4 rounded-2xl border border-gray-100 hover:border-blue-200 transition-all hover:bg-white hover:shadow-sm group";
                    
                    const hoursBadge = intern.total_hours !== null 
                        ? `<span class="text-[10px] font-bold text-blue-600 px-2 py-0.5 bg-blue-50 rounded-full">${parseFloat(intern.total_hours).toFixed(1)}h</span>` 
                        : '';
                        
                    div.innerHTML = `
                        <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-gray-400 font-bold text-sm border border-gray-50 group-hover:bg-gray-900 group-hover:text-white transition-colors">
                            ${intern.name.charAt(0)}
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-sm font-bold text-gray-900 truncate">${intern.name.split(" ")[0]}</span>
                            ${hoursBadge}
                        </div>
                    `;
                    list.appendChild(div);
                });
            }
        });
}

loadColleagues();

document.getElementById('privacy-toggle').addEventListener('change', function() {
    const isPublic = this.checked ? 1 : 0;
    const formData = new FormData();
    formData.append('is_public', isPublic);

    fetch('../api/profile_update.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const status = document.getElementById('save-status');
            status.style.opacity = '1';
            setTimeout(() => { status.style.opacity = '0'; }, 2000);
        }
    });
});
</script>
