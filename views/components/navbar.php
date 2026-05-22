<div class="fixed top-6 left-0 right-0 z-[100] px-6">
    <nav class="max-w-7xl mx-auto bg-white/80 backdrop-blur-xl border border-white/20 shadow-2xl rounded-3xl px-6 py-3 flex justify-between items-center transition-all duration-300 relative">
        <!-- Brand -->
        <div class="flex items-center gap-2 group">
            <div class="w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-black group-hover:scale-110 transition-transform">
                O
            </div>
            <a href="<?php echo $base_url ?? ''; ?>index.php" class="text-xl font-bold text-gray-900 tracking-tight">OurTracker</a>
        </div>

        <!-- Desktop Navigation Links (hidden on mobile) -->
        <div class="hidden md:flex items-center gap-2">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="flex items-center bg-gray-100/50 p-1 rounded-2xl border border-gray-200/50">
                    <a href="<?php echo $base_url ?? ''; ?>views/feed.php?page=dashboard" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-white hover:shadow-sm transition-all <?php echo (!isset($_GET['page']) || $_GET['page'] === 'dashboard') ? 'bg-white text-gray-900 shadow-sm' : ''; ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Dashboard
                    </a>
                    
                    <?php if ($_SESSION['user_role'] !== 'Admin'): ?>
                        <a href="<?php echo $base_url ?? ''; ?>views/feed.php?page=colleagues" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-white hover:shadow-sm transition-all <?php echo (isset($_GET['page']) && $_GET['page'] === 'colleagues') ? 'bg-white text-gray-900 shadow-sm' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Colleagues
                        </a>
                        <a href="<?php echo $base_url ?? ''; ?>views/feed.php?page=compare" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-white hover:shadow-sm transition-all <?php echo (isset($_GET['page']) && $_GET['page'] === 'compare') ? 'bg-white text-gray-900 shadow-sm' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                            Compare
                        </a>
                        <a href="<?php echo $base_url ?? ''; ?>views/feed.php?page=settings" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-white hover:shadow-sm transition-all <?php echo (isset($_GET['page']) && $_GET['page'] === 'settings') ? 'bg-white text-gray-900 shadow-sm' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Settings
                        </a>
                    <?php endif; ?>

                    <?php if ($_SESSION['user_role'] === 'Admin'): ?>
                        <a href="<?php echo $base_url ?? ''; ?>views/pages/supervisor/all-hours.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-white hover:shadow-sm transition-all <?php echo (isset($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], 'all-hours.php') !== false) ? 'bg-white text-gray-900 shadow-sm' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            All Hours
                        </a>
                        <a href="<?php echo $base_url ?? ''; ?>views/pages/supervisor/user-management.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-white hover:shadow-sm transition-all <?php echo (isset($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], 'user-management.php') !== false) ? 'bg-white text-gray-900 shadow-sm' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Users
                        </a>
                        <a href="<?php echo $base_url ?? ''; ?>views/pages/supervisor/reports.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-white hover:shadow-sm transition-all <?php echo (isset($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], 'reports.php') !== false) ? 'bg-white text-gray-900 shadow-sm' : ''; ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Reports
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="h-6 w-[1px] bg-gray-200 mx-2"></div>
                
                <a href="<?php echo $base_url ?? ''; ?>api/logout.php" class="flex items-center gap-2 px-5 py-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all duration-300 font-bold text-sm shadow-sm hover:shadow-red-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Hamburger Button (visible on mobile only) -->
        <div class="flex md:hidden items-center">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button id="mobile-menu-btn" class="w-10 h-10 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl flex items-center justify-center text-gray-700 dark:text-gray-300 transition" aria-label="Toggle Menu">
                    <svg id="hamburger-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <svg id="close-icon" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            <?php endif; ?>
        </div>

        <!-- Mobile Dynamic Drawer Menu -->
        <div id="mobile-menu-drawer" class="hidden absolute top-20 left-0 right-0 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 shadow-2xl rounded-2xl p-6 flex-col space-y-4 z-[99] transition-all duration-300">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo $base_url ?? ''; ?>views/feed.php?page=dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100/50 dark:hover:bg-gray-800/50 transition">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                
                <?php if ($_SESSION['user_role'] !== 'Admin'): ?>
                    <a href="<?php echo $base_url ?? ''; ?>views/feed.php?page=colleagues" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100/50 dark:hover:bg-gray-800/50 transition">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Colleagues
                    </a>
                    <a href="<?php echo $base_url ?? ''; ?>views/feed.php?page=compare" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100/50 dark:hover:bg-gray-800/50 transition">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                        Compare
                    </a>
                    <a href="<?php echo $base_url ?? ''; ?>views/feed.php?page=settings" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100/50 dark:hover:bg-gray-800/50 transition">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Settings
                    </a>
                <?php endif; ?>

                <?php if ($_SESSION['user_role'] === 'Admin'): ?>
                    <a href="<?php echo $base_url ?? ''; ?>views/pages/supervisor/all-hours.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100/50 dark:hover:bg-gray-800/50 transition">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        All Hours
                    </a>
                    <a href="<?php echo $base_url ?? ''; ?>views/pages/supervisor/user-management.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100/50 dark:hover:bg-gray-800/50 transition">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Users
                    </a>
                    <a href="<?php echo $base_url ?? ''; ?>views/pages/supervisor/reports.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100/50 dark:hover:bg-gray-800/50 transition">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Reports
                    </a>
                <?php endif; ?>
                
                <hr class="border-gray-100 dark:border-gray-800 my-2">
                
                <a href="<?php echo $base_url ?? ''; ?>api/logout.php" class="flex items-center justify-center gap-2 px-4 py-3 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-xl font-bold text-sm hover:bg-red-100 dark:hover:bg-red-900/40 transition w-full text-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            <?php endif; ?>
        </div>
    </nav>
</div>

<!-- Spacer to prevent content from going under the fixed navbar -->
<div class="h-28"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const drawer = document.getElementById('mobile-menu-drawer');
    const hamburger = document.getElementById('hamburger-icon');
    const closeIcon = document.getElementById('close-icon');

    if (mobileBtn && drawer) {
        mobileBtn.addEventListener('click', function() {
            const isHidden = drawer.classList.contains('hidden');
            if (isHidden) {
                drawer.classList.remove('hidden');
                drawer.classList.add('flex');
                hamburger.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            } else {
                drawer.classList.add('hidden');
                drawer.classList.remove('flex');
                hamburger.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        });
    }
});
</script>