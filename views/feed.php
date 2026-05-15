<?php
require_once __DIR__ . '/../config.php';
session_start();

// If user is already logged in, redirect to their dashboard (unless they are trying to access a specific page)
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    $requested_page = $_GET['page'] ?? '';
    if (empty($requested_page) || $requested_page === 'login') {
        $role = $_SESSION['user_role'];
        if ($role === 'Admin') {
            header("Location: pages/supervisor/dashboard.php");
        } else {
            header("Location: pages/intern/dashboard.php");
        }
        exit;
    }
}

$page = $_GET['page'] ?? 'login';
$showNavbar = !in_array($page, ['login', 'register']);
?>

<?php 
$base_url = "../";
require_once __DIR__ . '/components/header.php'; 
?>
</head>
<body>

<?php if ($showNavbar): ?>
    <?php require_once __DIR__ . '/components/navbar.php'; ?>
<?php endif; ?>

<main>
    <?php
    switch ($page) {
        case 'register':
            require_once __DIR__ . '/pages/auth/registry.php';
            break;
        case 'profile':
            require_once __DIR__ . '/pages/intern/profile.php';
            break;
        case 'login':
        default:
            require_once __DIR__ . '/pages/auth/login.php';
            break;
    }
    ?>
</main>

<?php require_once __DIR__ . '/components/footer.php'; ?>
</body>
</html>
