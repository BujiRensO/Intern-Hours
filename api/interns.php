<?php
require_once __DIR__ . '/../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized', 'success' => false]);
    exit;
}

$office_id = $_SESSION['office_id'];
$organization_id = $_SESSION['organization_id'];

try {
    // Fetch interns in the same office and organization, including hours if public
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.is_public, o.office_name, org.organization_name,
        CASE WHEN u.is_public = 1 THEN (SELECT SUM(hours) FROM hours_log WHERE user_id = u.id) ELSE NULL END as total_hours
        FROM users u
        JOIN office o ON u.office_id = o.id
        JOIN organization org ON u.organization_id = org.id
        WHERE u.office_id = ? AND u.organization_id = ? AND u.role = 'Intern'
        ORDER BY u.name ASC
    ");
    $stmt->execute([$office_id, $organization_id]);
    $interns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'interns' => $interns]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'success' => false]);
}
?>
