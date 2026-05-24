<?php
require_once __DIR__ . '/../config.php';

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized', 'success' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Ensure absences table exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS absences (
            absences_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            date DATE NOT NULL,
            reason TEXT,
            status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_date_absence (user_id, date),
            FOREIGN KEY(user_id) REFERENCES users(id)
        )
    ");
} catch (PDOException $e) {
    error_log('Absences table creation error: ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $target_user_id = $user_id;
    if ($user_role === 'Admin' && isset($_GET['userId'])) {
        $target_user_id = $_GET['userId'];
    }

    // If admin/supervisor is looking for pending requests (global list)
    if ($user_role === 'Admin' && isset($_GET['pending']) && $_GET['pending'] === 'true') {
        $stmt = $pdo->prepare("
            SELECT a.*, u.name as intern_name 
            FROM absences a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.status = 'Pending' 
            ORDER BY a.date ASC
        ");
        $stmt->execute();
    } 
    // Monthly view (for calendar)
    elseif (isset($_GET['month']) && isset($_GET['year'])) {
        $month = $_GET['month'];
        $year = $_GET['year'];
        $startDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $stmt = $pdo->prepare("
            SELECT * FROM absences 
            WHERE user_id = ? AND date BETWEEN ? AND ?
        ");
        $stmt->execute([$target_user_id, $startDate, $endDate]);
    }
    // Single user history
    elseif ($user_role === 'Admin' && isset($_GET['userId'])) {
        $stmt = $pdo->prepare("
            SELECT a.*, u.name as intern_name 
            FROM absences a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.user_id = ?
            ORDER BY a.date DESC
        ");
        $stmt->execute([$target_user_id]);
    }
    // Default: current user's history
    else {
        $stmt = $pdo->prepare("SELECT * FROM absences WHERE user_id = ? ORDER BY date DESC");
        $stmt->execute([$user_id]);
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'absences' => $results]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'apply'; // apply, approve, reject, delete

    if ($action === 'apply') {
        $date = $_POST['date'] ?? '';
        $reason = $_POST['reason'] ?? '';

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode(['error' => 'Invalid date format', 'success' => false]);
            exit;
        }



        try {
            $stmt = $pdo->prepare("
                INSERT INTO absences (user_id, date, reason, status) 
                VALUES (?, ?, ?, 'Pending')
                ON DUPLICATE KEY UPDATE reason = VALUES(reason), status = 'Pending'
            ");
            $stmt->execute([$user_id, $date, $reason]);
            echo json_encode(['success' => true, 'message' => 'Absence request submitted']);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage(), 'success' => false]);
        }
    } elseif ($action === 'approve' || $action === 'reject') {
        if ($user_role !== 'Admin') {
            echo json_encode(['error' => 'Unauthorized', 'success' => false]);
            exit;
        }

        $id = $_POST['id'] ?? '';
        $status = ($action === 'approve') ? 'Approved' : 'Rejected';

        try {
            $stmt = $pdo->prepare("UPDATE absences SET status = ? WHERE absences_id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true, 'message' => 'Request ' . $status]);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage(), 'success' => false]);
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        
        try {
            // Interns can only delete their own pending/rejected requests? 
            // For now let's allow it if it's theirs
            $stmt = $pdo->prepare("DELETE FROM absences WHERE absences_id = ? AND (user_id = ? OR ? = 'Admin')");
            $stmt->execute([$id, $user_id, $user_role]);
            echo json_encode(['success' => true, 'message' => 'Request deleted']);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage(), 'success' => false]);
        }
    }
    exit;
}
?>
