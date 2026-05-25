<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized session. Please log in first.']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $date = $_GET['date'] ?? date('Y-m-d');
    
    $stmt = $pdo->prepare("SELECT accomplishment FROM accomplishment WHERE user_id = ? AND date = ?");
    $stmt->execute([$user_id, $date]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'accomplishment' => $row ? $row['accomplishment'] : null
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? date('Y-m-d');
    $accomplishment = trim($_POST['accomplishment'] ?? '');
    
    if (empty($accomplishment)) {
        echo json_encode(['success' => false, 'error' => 'Accomplishment text cannot be empty.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT accomplishment_id FROM accomplishment WHERE user_id = ? AND date = ?");
        $stmt->execute([$user_id, $date]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $updateStmt = $pdo->prepare("UPDATE accomplishment SET accomplishment = ?, updated_at = NOW() WHERE accomplishment_id = ?");
            $updateStmt->execute([$accomplishment, $row['accomplishment_id']]);
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO accomplishment (user_id, date, accomplishment) VALUES (?, ?, ?)");
            $insertStmt->execute([$user_id, $date, $accomplishment]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Accomplishment saved.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid method.']);
