<?php
// Load environment variables
// Load environment variables manually if .env exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            if (!isset($_ENV[$name])) {
                $_ENV[$name] = $value;
            }
            if (!isset($_SERVER[$name])) {
                $_SERVER[$name] = $value;
            }
            // putenv might be disabled on some shared hosts
            @putenv("$name=$value");
        }
    }
}

// Function to get config with fallbacks
function get_config($key, $default = '') {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

$host = get_config('DB_HOST', 'localhost');
$port = get_config('DB_PORT', '3306');
$dbname = get_config('DB_CONFIG_NAME', get_config('DB_NAME', 'intern_hours_db'));
$username = get_config('DB_USER', 'root');
$password = get_config('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Auto-migration: Ensure columns exist (MySQL 5.7 compatible check)
    try {
        $columns = [];
        $stmt = $pdo->query("SHOW COLUMNS FROM users");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = strtolower($row['Field']);
        }

        if (!in_array('is_public', $columns)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN is_public BOOLEAN DEFAULT FALSE");
        }
        if (!in_array('is_darkmode', $columns)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN is_darkmode BOOLEAN DEFAULT FALSE");
        }
        if (!in_array('profile_picture', $columns)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(500) DEFAULT NULL AFTER email");
        }

        // Auto-migration: Create biometrics and attendance log tables
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS biometric_credentials (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                credential_id VARCHAR(255) NOT NULL UNIQUE,
                public_key TEXT NOT NULL,
                sign_count INT NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS attendance_logs (
                log_id INT AUTO_INCREMENT PRIMARY KEY,
                employee_id INT NOT NULL,
                device_token VARCHAR(255) NOT NULL,
                action_type ENUM('clock_in', 'clock_out') NOT NULL,
                gps_latitude DECIMAL(10, 8),
                gps_longitude DECIMAL(11, 8),
                server_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    } catch (PDOException $migrationException) {
        // Log migration error or handle it, but do not crash the application connection
        error_log("Database Auto-migration failed: " . $migrationException->getMessage());
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Parse Browser from User Agent
function get_browser_from_ua($user_agent) {
    if (empty($user_agent)) return 'Unknown Browser';
    
    if (preg_match('/chrome|crios/i', $user_agent) && !preg_match('/opr|opios/i', $user_agent) && !preg_match('/edge|edg/i', $user_agent)) {
        return 'Google Chrome';
    } elseif (preg_match('/safari/i', $user_agent) && !preg_match('/chrome|crios/i', $user_agent) && !preg_match('/android/i', $user_agent)) {
        return 'Apple Safari';
    } elseif (preg_match('/firefox|fxios/i', $user_agent)) {
        return 'Mozilla Firefox';
    } elseif (preg_match('/edge|edg|edgios|edga/i', $user_agent)) {
        return 'Microsoft Edge';
    } elseif (preg_match('/opera|opr|opios/i', $user_agent)) {
        return 'Opera';
    } elseif (preg_match('/msie|trident/i', $user_agent)) {
        return 'Internet Explorer';
    }
    
    return 'Other Browser';
}

// Parse Device from User Agent
function get_device_from_ua($user_agent) {
    if (empty($user_agent)) return 'Unknown Device';
    
    if (preg_match('/iphone/i', $user_agent)) {
        return 'iPhone';
    } elseif (preg_match('/ipad/i', $user_agent)) {
        return 'iPad';
    } elseif (preg_match('/android/i', $user_agent)) {
        return 'Android Device';
    } elseif (preg_match('/windows/i', $user_agent)) {
        return 'Windows PC';
    } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
        return 'Mac';
    } elseif (preg_match('/linux/i', $user_agent)) {
        return 'Linux PC';
    }
    
    return 'Desktop PC';
}
?>
