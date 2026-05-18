<?php
require_once __DIR__ . '/../config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Check if login is blocked due to too many attempts
    if (isset($_SESSION['login_blocked_until']) && time() < $_SESSION['login_blocked_until']) {
        $remaining_time = $_SESSION['login_blocked_until'] - time();
        echo json_encode(['error' => 'Too many failed attempts. Please wait ' . $remaining_time . ' seconds.']);
        exit;
    }

    try {
        // Validation
        if (empty($email) || empty($password)) {
            echo json_encode(['error' => "Email and password are required."]);
            exit;
        }

        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $secret_key = $_ENV['SECRET_KEY'] ?? $_SERVER['SECRET_KEY'] ?? getenv('SECRET_KEY') ?: 'default-secret-key';
            $hashed_password = hash_hmac('sha256', $password, $secret_key);
            
            // Try matching with current key
            $is_correct = hash_equals($user['password'], $hashed_password);
            
            // If it fails, try the fallback key for legacy users
            if (!$is_correct && $secret_key !== 'default-secret-key') {
                $fallback_hashed = hash_hmac('sha256', $password, 'default-secret-key');
                $is_correct = hash_equals($user['password'], $fallback_hashed);
            }
            
            if ($is_correct) {
                // Start session and store user info
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['office_id'] = $user['office_id'];
                $_SESSION['organization_id'] = $user['organization_id'];
                $_SESSION['is_darkmode'] = (bool)($user['is_darkmode'] ?? false);

                // Fetch names for session
                $stmt = $pdo->prepare("SELECT office_name FROM office WHERE id = ?");
                $stmt->execute([$user['office_id']]);
                $_SESSION['office_name'] = $stmt->fetchColumn();

                $stmt = $pdo->prepare("SELECT organization_name FROM organization WHERE id = ?");
                $stmt->execute([$user['organization_id']]);
                $_SESSION['organization_name'] = $stmt->fetchColumn();

                // Reset failed attempts on successful login
                unset($_SESSION['failed_attempts']);
                unset($_SESSION['login_blocked_until']);

                // Log login to login_logs table
                $log_stmt = $pdo->prepare("INSERT INTO login_logs (user_id) VALUES (?)");
                $log_stmt->execute([$user['id']]);

                // Return success response with redirect URL
                $redirectUrl = '../views/feed.php?page=dashboard';
                echo json_encode(['success' => true, 'redirect' => $redirectUrl]);
                exit;
            }
        }
        
        // Increment failed attempts
        $_SESSION['failed_attempts'] = ($_SESSION['failed_attempts'] ?? 0) + 1;
        
        // Block login if 3 failed attempts
        if ($_SESSION['failed_attempts'] >= 3) {
            $_SESSION['login_blocked_until'] = time() + 30;
            echo json_encode(['error' => 'Too many failed attempts. Please wait 30 seconds.', 'blocked' => true, 'blocked_until' => $_SESSION['login_blocked_until']]);
            exit;
        }
        
        echo json_encode(['error' => "Invalid email or password.", 'attempts' => $_SESSION['failed_attempts']]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['error' => 'An internal error occurred: ' . $e->getMessage()]);
        exit;
    }
}

// Return current attempt count for frontend check
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = [
        'attempts' => $_SESSION['failed_attempts'] ?? 0,
        'blocked' => isset($_SESSION['login_blocked_until']) && time() < $_SESSION['login_blocked_until'],
        'blocked_until' => $_SESSION['login_blocked_until'] ?? null
    ];
    echo json_encode($response);
    exit;
}
?>
