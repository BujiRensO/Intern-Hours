<?php
/**
 * Interns List Endpoint
 *
 * Retrieves the list of colleagues (interns) belonging to the same office
 * and organization as the logged-in user.
 *
 * Security & Data Privacy:
 * - Requires explicit session authorization.
 * - Restricts lookup to the viewer's office and organization.
 * - Eagerly evaluates and includes `total_hours` only if the target intern's profile
 *   is public OR the viewer is an Admin.
 * - Fetches user `profile_picture` avatar URLs for rendering.
 */
require_once __DIR__ . '/../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized', 'success' => false]);
    exit;
}

$user_role = $_SESSION['user_role'] ?? 'Intern';
$office_id = $_SESSION['office_id'];
$organization_id = $_SESSION['organization_id'];

try {
    // Fetch interns in the same office and organization, including hours if public (or if user is Admin)
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.is_public, u.profile_picture, o.office_name, org.organization_name,
        CASE 
            WHEN ? = 'Admin' OR u.is_public = 1 THEN (SELECT SUM(hours) FROM hours_log WHERE user_id = u.id) 
            ELSE NULL 
        END as total_hours
        FROM users u
        JOIN office o ON u.office_id = o.id
        JOIN organization org ON u.organization_id = org.id
        WHERE u.office_id = ? AND u.organization_id = ? AND u.role = 'Intern'
        ORDER BY u.name ASC
    ");
    $stmt->execute([$user_role, $office_id, $organization_id]);
    $interns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'interns' => $interns]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'success' => false]);
}
?>
