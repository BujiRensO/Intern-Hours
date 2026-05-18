<?php
require_once __DIR__ . '/../config.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please log in.']);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');

    // Validation
    if (empty($contact) || empty($birthdate) || empty($region) || empty($province) || empty($city) || empty($barangay) || empty($address) || empty($postal_code)) {
        echo json_encode(['success' => false, 'error' => 'All profile fields (Contact, Birthdate, Region, Province, City, Barangay, Address, and Zip Code) are required.']);
        exit;
    }

    // Clean phone number (keep only digits)
    $contact_clean = preg_replace('/[^0-9]/', '', $contact);
    if (strlen($contact_clean) !== 11) {
        echo json_encode(['success' => false, 'error' => 'Please enter a valid 11-digit contact number (e.g., 09123456789).']);
        exit;
    }

    // Clean postal code
    $postal_clean = preg_replace('/[^0-9]/', '', $postal_code);
    if (empty($postal_clean)) {
        echo json_encode(['success' => false, 'error' => 'Please enter a valid numeric ZIP / Postal code.']);
        exit;
    }

    try {
        // Construct the SQL update statement
        // Note: We also update the nickname if provided (since it is NOT NULL in the schema)
        $sql = "UPDATE users SET 
                contact = ?, 
                birthdate = ?, 
                region = ?,
                province = ?, 
                city = ?, 
                barangay = ?,
                address = ?, 
                postal_code = ?";
        
        $params = [
            $contact_clean,
            $birthdate,
            $region,
            $province,
            $city,
            $barangay,
            $address,
            (int)$postal_clean
        ];

        if (!empty($nickname)) {
            $sql .= ", nickname = ?";
            $params[] = $nickname;
        }

        $sql .= " WHERE id = ?";
        $params[] = $user_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode([
            'success' => true, 
            'message' => 'Profile information completed successfully!'
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'error' => 'Database update failed: ' . $e->getMessage()
        ]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
exit;
?>
