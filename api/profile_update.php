<?php
/**
 * Update Profile Settings Endpoint
 *
 * Handles partial updates for user preferences and profile parameters:
 * - is_darkmode: Boolean toggle for visual preference.
 * - is_public: Boolean toggle for logs visibility within the organization.
 * - profile_picture: HTTP/HTTPS URL containing user avatar photo.
 *
 * Enforces strict input validation and sanitization, uses database transactions
 * for fault tolerance, and keeps session data synchronized.
 */

require_once __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');

// Explicit authorization check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    $updates = [];
    $params = [];

    // 1. Dark Mode Update
    if (isset($_POST['is_darkmode'])) {
        $is_darkmode = $_POST['is_darkmode'] === '1' ? '1' : '0';
        $updates[] = "is_darkmode = ?";
        $params[] = $is_darkmode;
        $_SESSION['is_darkmode'] = ($is_darkmode === '1');
    }

    // 2. Privacy/Public Visibility Update
    if (isset($_POST['is_public'])) {
        $is_public = $_POST['is_public'] === '1' ? '1' : '0';
        $updates[] = "is_public = ?";
        $params[] = $is_public;
        $_SESSION['is_public'] = ($is_public === '1');
    }

    // 3. Profile Picture URL Update
    if (isset($_POST['profile_picture'])) {
        $profile_picture = trim($_POST['profile_picture']);
        if (!empty($profile_picture)) {
            // Strict server-side URL validation & sanitization (prevents javascript: XSS and bad values)
            $sanitized_url = filter_var($profile_picture, FILTER_SANITIZE_URL);
            if (!filter_var($sanitized_url, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//i', $sanitized_url)) {
                echo json_encode(['success' => false, 'error' => 'Please enter a valid HTTP/HTTPS profile picture URL.']);
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                exit;
            }
            $profile_picture = $sanitized_url;
        } else {
            $profile_picture = null;
        }
        $updates[] = "profile_picture = ?";
        $params[] = $profile_picture;
    }

    // 4. Nickname Update
    if (isset($_POST['nickname'])) {
        $nickname = trim($_POST['nickname']);
        if (empty($nickname)) {
            echo json_encode(['success' => false, 'error' => __('nickname_required', 'Nickname cannot be empty.')]);
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            exit;
        }
        $updates[] = "nickname = ?";
        $params[] = $nickname;
    }

    if (empty($updates)) {
        echo json_encode(['success' => false, 'error' => 'No fields specified for update.']);
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        exit;
    }

    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
    $params[] = $user_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $pdo->commit();

    $response = ['success' => true, 'message' => 'Profile updated successfully.'];
    if (isset($_SESSION['is_darkmode'])) {
        $response['is_darkmode'] = $_SESSION['is_darkmode'];
    }
    if (isset($_SESSION['is_public'])) {
        $response['is_public'] = $_SESSION['is_public'];
    }
    echo json_encode($response);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
