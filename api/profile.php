<?php
require_once __DIR__ . '/../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized', 'success' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $office_id = $_POST['office_id'] ?? '';
    $organization_id = $_POST['organization_id'] ?? '';
    $new_office_name = trim($_POST['new_office_name'] ?? '');
    $new_organization_name = trim($_POST['new_organization_name'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $region = trim($_POST['region'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');

    if (empty($name) || empty($email) || empty($office_id) || empty($organization_id) || empty($nickname) || empty($contact) || empty($birthdate) || empty($region) || empty($province) || empty($city) || empty($barangay) || empty($address) || empty($postal_code)) {
        echo json_encode(['error' => 'All profile fields are required.', 'success' => false]);
        exit;
    }

    // Clean phone number (keep only digits)
    $contact_clean = preg_replace('/[^0-9]/', '', $contact);
    if (strlen($contact_clean) !== 11) {
        echo json_encode(['error' => 'Please enter a valid 11-digit contact number.', 'success' => false]);
        exit;
    }

    // Clean postal code
    $postal_clean = preg_replace('/[^0-9]/', '', $postal_code);
    if (empty($postal_clean)) {
        echo json_encode(['error' => 'Please enter a valid numeric ZIP / Postal code.', 'success' => false]);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Handle new office
        if ($office_id === 'new' && !empty($new_office_name)) {
            $stmt = $pdo->prepare("SELECT id FROM office WHERE office_name = ?");
            $stmt->execute([$new_office_name]);
            $existing = $stmt->fetch();
            if ($existing) {
                $office_id = $existing['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO office (office_name) VALUES (?)");
                $stmt->execute([$new_office_name]);
                $office_id = $pdo->lastInsertId();
            }
        }

        // Handle new organization
        if ($organization_id === 'new' && !empty($new_organization_name)) {
            $stmt = $pdo->prepare("SELECT id FROM organization WHERE organization_name = ?");
            $stmt->execute([$new_organization_name]);
            $existing = $stmt->fetch();
            if ($existing) {
                $organization_id = $existing['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO organization (organization_name) VALUES (?)");
                $stmt->execute([$new_organization_name]);
                $organization_id = $pdo->lastInsertId();
            }
        }

        // Prepare update query
        $params = [
            $name, 
            $email, 
            $office_id, 
            $organization_id,
            $nickname,
            $contact_clean,
            $birthdate,
            $region,
            $province,
            $city,
            $barangay,
            $address,
            (int)$postal_clean
        ];
        $sql = "UPDATE users SET 
                name = ?, 
                email = ?, 
                office_id = ?, 
                organization_id = ?,
                nickname = ?,
                contact = ?,
                birthdate = ?,
                region = ?,
                province = ?,
                city = ?,
                barangay = ?,
                address = ?,
                postal_code = ?";
        
        if (!empty($password)) {
            if (strlen($password) < 6) {
                echo json_encode(['error' => 'Password must be at least 6 characters.', 'success' => false]);
                exit;
            }
            $secret_key = getenv('SECRET_KEY') ?: 'default-secret-key';
            $hashed_password = hash_hmac('sha256', $password, $secret_key);
            $sql .= ", password = ?";
            $params[] = $hashed_password;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $user_id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Update session
        $_SESSION['user_name'] = $name;
        $_SESSION['office_id'] = $office_id;
        $_SESSION['organization_id'] = $organization_id;
        
        // Fetch names for session
        $stmt = $pdo->prepare("SELECT office_name FROM office WHERE id = ?");
        $stmt->execute([$office_id]);
        $_SESSION['office_name'] = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT organization_name FROM organization WHERE id = ?");
        $stmt->execute([$organization_id]);
        $_SESSION['organization_name'] = $stmt->fetchColumn();

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['error' => 'Update failed: ' . $e->getMessage(), 'success' => false]);
    }
    exit;
}

// GET request to fetch current profile
try {
    $stmt = $pdo->prepare("SELECT name, email, office_id, organization_id, nickname, contact, birthdate, region, province, city, barangay, address, postal_code FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    echo json_encode(['success' => true, 'user' => $user]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'success' => false]);
}
?>
