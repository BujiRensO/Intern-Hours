<?php
/**
 * Intern Hours Log Retrieval Endpoint
 *
 * Fetches log details and total hours for a specific colleague (intern_id).
 *
 * Parameters:
 * - intern_id (int, required): Target user ID.
 * - month (int, optional): Log month.
 * - year (int, optional): Log year.
 * - all (string, optional): "true" to bypass month filters and retrieve all history.
 *
 * Security & Data Privacy:
 * - Requires explicit session authorization.
 * - Validates that the viewer and the target intern belong to the same office and organization.
 * - Enforces privacy controls: If the target profile is private, returns a redacted payload
 *   (excluding detailed logs and total hours) unless the viewer has Admin privileges.
 */
require_once __DIR__ . '/../config.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized', 'success' => false]);
    exit;
}

$viewer_id = $_SESSION['user_id'];
$viewer_role = $_SESSION['user_role'] ?? 'Intern';
$viewer_office_id = $_SESSION['office_id'];
$viewer_organization_id = $_SESSION['organization_id'];

// Get the target intern's ID
$intern_id = $_GET['intern_id'] ?? '';

if (empty($intern_id)) {
    echo json_encode(['error' => 'Intern ID is required', 'success' => false]);
    exit;
}

try {
    // Fetch the target intern's info
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.is_public, u.office_id, u.organization_id, u.role,
               u.profile_picture, o.office_name, org.organization_name
        FROM users u
        LEFT JOIN office o ON u.office_id = o.id
        LEFT JOIN organization org ON u.organization_id = org.id
        WHERE u.id = ?
    ");
    $stmt->execute([$intern_id]);
    $intern = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$intern) {
        echo json_encode(['error' => 'Intern not found', 'success' => false]);
        exit;
    }

    // Check access: must be same office+organization, OR viewer is Admin
    $sameOfficeOrg = ($intern['office_id'] == $viewer_office_id && $intern['organization_id'] == $viewer_organization_id);
    $isAdmin = ($viewer_role === 'Admin');

    if (!$sameOfficeOrg && !$isAdmin) {
        echo json_encode(['error' => 'Access denied: not in the same office/organization', 'success' => false]);
        exit;
    }

    // Check privacy: intern must be public, OR viewer is Admin
    if (!$intern['is_public'] && !$isAdmin) {
        echo json_encode([
            'success' => true, 
            'intern' => [
                'id' => $intern['id'],
                'name' => $intern['name'],
                'profile_picture' => $intern['profile_picture'],
                'office_name' => $intern['office_name'],
                'organization_name' => $intern['organization_name'],
            ],
            'is_private' => true,
            'hours' => [],
            'total_hours' => null,
            'message' => 'This intern has set their profile to private.'
        ]);
        exit;
    }

    // Fetch hours data
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    $all = $_GET['all'] ?? 'false';

    if ($all === 'true') {
        // Get all hours
        $stmt = $pdo->prepare("
            SELECT date, hours 
            FROM hours_log 
            WHERE user_id = ?
            ORDER BY date ASC
        ");
        $stmt->execute([$intern_id]);
    } else {
        // Get hours for specific month
        $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $pdo->prepare("
            SELECT date, hours 
            FROM hours_log 
            WHERE user_id = ? AND date BETWEEN ? AND ?
            ORDER BY date ASC
        ");
        $stmt->execute([$intern_id, $startDate, $endDate]);
    }

    $hours = [];
    foreach ($stmt->fetchAll() as $row) {
        $hours[$row['date']] = $row['hours'];
    }

    // Get total hours
    $stmt = $pdo->prepare("SELECT SUM(hours) as total FROM hours_log WHERE user_id = ?");
    $stmt->execute([$intern_id]);
    $total = $stmt->fetchColumn() ?? 0;

    echo json_encode([
        'success' => true,
        'intern' => [
            'id' => $intern['id'],
            'name' => $intern['name'],
            'email' => $intern['email'],
            'profile_picture' => $intern['profile_picture'],
            'office_name' => $intern['office_name'],
            'organization_name' => $intern['organization_name'],
        ],
        'is_private' => false,
        'hours' => $hours,
        'total_hours' => $total
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'success' => false]);
}
?>
