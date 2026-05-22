<?php
require_once __DIR__ . '/../config.php';
session_start();

$needs_profile_completion = false;
$user_profile = null;

// Redirect logic
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    // Check if profile is complete
    try {
        $stmt = $pdo->prepare("SELECT contact, birthdate, region, province, city, barangay, address, postal_code, nickname FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_profile = $stmt->fetch();
        
        if ($user_profile) {
            $needs_profile_completion = empty($user_profile['contact']) || 
                                        empty($user_profile['birthdate']) || 
                                        empty($user_profile['region']) ||
                                        empty($user_profile['province']) || 
                                        empty($user_profile['city']) || 
                                        empty($user_profile['barangay']) || 
                                        empty($user_profile['address']) ||
                                        empty($user_profile['postal_code']);
        }
    } catch (Exception $e) {
        // Fallback if schema changes have not been fully applied yet
    }

    $requested_page = $_GET['page'] ?? '';
    // If logged in and on landing or login page, go to dashboard
    if (empty($requested_page) || $requested_page === 'login') {
        header("Location: feed.php?page=dashboard");
        exit;
    }
} else {
    // If not logged in and trying to access restricted page, go to login
    $requested_page = $_GET['page'] ?? 'login';
    if (!in_array($requested_page, ['login', 'register', 'terms', 'privacy'])) {
        header("Location: feed.php?page=login");
        exit;
    }
}

$page = $_GET['page'] ?? 'login';
$showNavbar = true;
?>

<?php 
$base_url = "../";
require_once __DIR__ . '/components/header.php'; 
?>
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? '' : 'bg-gray-50'; ?>">

<?php if ($showNavbar): ?>
    <?php require_once __DIR__ . '/components/navbar.php'; ?>
<?php endif; ?>

<main>
    <?php
    switch ($page) {
        case 'register':
            require_once __DIR__ . '/pages/auth/registry.php';
            break;
        case 'dashboard':
            if ($_SESSION['user_role'] === 'Admin') {
                require_once __DIR__ . '/pages/supervisor/dashboard.php';
            } else {
                require_once __DIR__ . '/pages/intern/dashboard.php';
            }
            break;
        case 'colleagues':
            require_once __DIR__ . '/pages/intern/colleagues.php';
            break;
        case 'compare':
            require_once __DIR__ . '/pages/intern/compare.php';
            break;
        case 'settings':
            require_once __DIR__ . '/pages/intern/settings.php';
            break;
        case 'profile':
            require_once __DIR__ . '/pages/intern/settings.php';
            break;
        case 'terms':
            require_once __DIR__ . '/pages/termsofservices.php';
            break;
        case 'privacy':
            require_once __DIR__ . '/pages/privacypolicy.php';
            break;
        case 'login':
        default:
            require_once __DIR__ . '/pages/auth/login.php';
            break;
    }
    ?>
</main>

<?php require_once __DIR__ . '/components/footer.php'; ?>

<?php if ($needs_profile_completion): ?>
    <?php require_once __DIR__ . '/components/profile_completion_modal.php'; ?>
<?php endif; ?>

</body>
</html>
