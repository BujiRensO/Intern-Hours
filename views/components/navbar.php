<div class="fixed top-6 left-0 right-0 z-[100] px-6">
    <nav class="max-w-7xl mx-auto bg-white/80 backdrop-blur-xl border border-white/20 shadow-2xl rounded-3xl px-6 py-3 flex justify-between items-center transition-all duration-300">
        <!-- Brand -->
        <div class="flex items-center gap-2 group">
            <div class="w-10 h-10 bg-gray-900 rounded-xl flex items-center justify-center text-white font-black group-hover:scale-110 transition-transform">
                O
            </div>
            <a href="<?php echo $base_url ?? ''; ?>index.php" class="text-xl font-bold text-gray-900 tracking-tight">OurTracker</a>
        </div>

        <!-- Navigation Links -->
        <div class="flex items-center gap-2">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="flex items-center bg-gray-100/50 p-1 rounded-2xl border border-gray-200/50">
                    <a href="<?php echo $base_url ?? ''; ?>views/feed.php" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-white hover:shadow-sm transition-all <?php echo !isset($_GET['page']) ? 'bg-white text-gray-900 shadow-sm' : ''; ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Dashboard
                    </a>
                    <a href="<?php echo $base_url ?? ''; ?>views/feed.php?page=profile" class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-gray-600 hover:text-gray-900 hover:bg-white hover:shadow-sm transition-all <?php echo (isset($_GET['page']) && $_GET['page'] === 'profile') ? 'bg-white text-gray-900 shadow-sm' : ''; ?>">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Profile
                    </a>
                </div>
                
                <div class="h-6 w-[1px] bg-gray-200 mx-2"></div>
                
                <a href="<?php echo $base_url ?? ''; ?>api/logout.php" class="flex items-center gap-2 px-5 py-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all duration-300 font-bold text-sm shadow-sm hover:shadow-red-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </a>
            <?php endif; ?>
        </div>
    </nav>
</div>

<!-- Spacer to prevent content from going under the fixed navbar -->
<div class="h-28"></div>