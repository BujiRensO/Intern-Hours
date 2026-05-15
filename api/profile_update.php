<?php
require_once __DIR__ . '/../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $is_public = isset($_POST['is_public']) ? (int)$_POST['is_public'] : 0;

    try {
        $stmt = $pdo->prepare("UPDATE users SET is_public = ? WHERE id = ?");
        $stmt->execute([$is_public, $user_id]);
        
        // Update session as well
        $_SESSION['is_public'] = $is_public;

        echo json_encode(['success' => true, 'message' => 'Privacy settings updated']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
