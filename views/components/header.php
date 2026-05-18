<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OurTracker - Track Your Internship Time</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2240%22 stroke=%22%236366f1%22 stroke-width=%228%22 fill=%22none%22/><path d=%22M50 20 v30 l20 10%22 stroke=%22%236366f1%22 stroke-width=%228%22 stroke-linecap=%22round%22/></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/global.css">
    <link rel="stylesheet" href="<?php echo $base_url ?? ''; ?>assets/css/theme.css">
    
    <!-- PWA Mobile App Widget Integration -->
    <link rel="manifest" href="<?php echo $base_url ?? ''; ?>manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="OJT Tracker">
    <link rel="apple-touch-icon" href="<?php echo $base_url ?? ''; ?>assets/images/logo.svg">
    
    <script>
        // Configure Tailwind for class-based dark mode
        window.tailwind && (tailwind.config = { darkMode: 'class' });
        
        // Pass session dark mode preference to JS
        const sessionDarkMode = <?php echo isset($_SESSION['is_darkmode']) && $_SESSION['is_darkmode'] ? 'true' : 'false'; ?>;
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

        // Register Service Worker for mobile widget caching
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('<?php echo $base_url ?? ''; ?>sw.js')
                    .then(reg => console.log('PWA Service Worker registered:', reg.scope))
                    .catch(err => console.warn('PWA Service Worker failed:', err));
            });
        }
    </script>
    <script src="<?php echo $base_url ?? ''; ?>assets/js/theme.js"></script>
