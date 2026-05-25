<?php
require_once __DIR__ . '/../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$hour_goal = isset($_POST['hour_goal']) ? (int)$_POST['hour_goal'] : 0;
$starting_date = isset($_POST['starting_date']) ? trim($_POST['starting_date']) : '';
$duty_days = isset($_POST['duty_days']) ? trim($_POST['duty_days']) : '';
$duty_from = isset($_POST['duty_from']) ? trim($_POST['duty_from']) : '08:00';
$duty_to = isset($_POST['duty_to']) ? trim($_POST['duty_to']) : '17:00';
$has_lunch_break = isset($_POST['has_lunch_break']) ? 1 : 0;

// Validation
if ($hour_goal <= 0) {
    echo json_encode(['success' => false, 'error' => 'Target hours must be greater than 0.']);
    exit;
}

if (empty($starting_date) || !strtotime($starting_date)) {
    echo json_encode(['success' => false, 'error' => 'Please provide a valid starting date.']);
    exit;
}

if (empty($duty_days)) {
    echo json_encode(['success' => false, 'error' => 'Please select at least one duty day.']);
    exit;
}

// Ensure valid day names
$valid_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$input_days = explode(',', $duty_days);
foreach ($input_days as $day) {
    if (!in_array(trim($day), $valid_days)) {
        echo json_encode(['success' => false, 'error' => 'Invalid duty day name selected: ' . htmlspecialchars($day)]);
        exit;
    }
}

try {
    // Check if record exists
    $check_stmt = $pdo->prepare("SELECT id FROM burnout_counter WHERE user_id = ?");
    $check_stmt->execute([$user_id]);
    $exists = $check_stmt->fetch();

    if ($exists) {
        $stmt = $pdo->prepare("UPDATE burnout_counter SET hour_goal = ?, starting_date = ?, duty_days = ?, duty_from = ?, duty_to = ?, has_lunch_break = ? WHERE user_id = ?");
        $stmt->execute([$hour_goal, $starting_date, $duty_days, $duty_from, $duty_to, $has_lunch_break, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO burnout_counter (user_id, hour_goal, starting_date, duty_days, duty_from, duty_to, has_lunch_break) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $hour_goal, $starting_date, $duty_days, $duty_from, $duty_to, $has_lunch_break]);
    }

    echo json_encode(['success' => true, 'message' => 'Internship settings updated successfully.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
