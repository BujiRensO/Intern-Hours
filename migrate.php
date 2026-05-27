<?php
/**
 * Intern-Hours Database Migration Runner
 * Run via CLI: php migrate.php
 */

echo "=========================================\n";
echo "📊 Intern-Hours Database Migration Runner\n";
echo "=========================================\n\n";

// 1. Load environment variables manually from .env
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("❌ Error: .env file not found. Please copy .env.example to .env first!\n");
}

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
        
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

function get_config($key, $default = '') {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

$host = get_config('DB_HOST', 'localhost');
$port = get_config('DB_PORT', '3306');
$dbname = get_config('DB_NAME', 'intern_hours_db');
$username = get_config('DB_USER', 'root');
$password = get_config('DB_PASS', '');

echo "🔌 Connecting to MySQL server at {$host}:{$port}...\n";

// 2. Connect to MySQL (without database first, to ensure we can create it)
try {
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "❌ Connection failed!\n\n";
    if ($e->getCode() == 1045) {
        echo "💡 Advice: Access was denied for user '$username'.\n";
        echo "   Please open your '.env' file and make sure 'DB_PASS' is set to your correct MySQL root password!\n";
    } else {
        echo "💡 Advice: Could not connect to the MySQL service.\n";
        echo "   Please make sure your local MySQL/MariaDB server is running (e.g., MySQL80 service or XAMPP MySQL).\n";
        echo "   Error Details: " . $e->getMessage() . "\n";
    }
    exit(1);
}

// 3. Create the database if it doesn't exist
try {
    echo "📦 Creating database '{$dbname}' (if not exists)...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `$dbname`;");
} catch (PDOException $e) {
    die("❌ Error creating database: " . $e->getMessage() . "\n");
}

// 4. Read and parse SQL schema from assets/db/mysql.db
$schemaFile = __DIR__ . '/assets/db/mysql.db';
if (!file_exists($schemaFile)) {
    die("❌ Error: Schema file not found at assets/db/mysql.db!\n");
}

echo "📖 Reading schema file...\n";
$sqlContent = file_get_contents($schemaFile);

// Remove database creation and use statements if they exist, since we handled that safely
$sqlQueries = preg_replace('/^(Create database|use|CREATE DATABASE|USE)\s+[^;]+;/mi', '', $sqlContent);

// Split SQL queries by semicolon to execute individually for better error tracking
// Using a regex to split on semicolons that are not inside quotes or comments
$queries = array_filter(array_map('trim', preg_split('/;(?=(?:[^\'"]*\'[^\'"]*\')*[^\'"]*$)/', $sqlQueries)));

echo "🚀 Executing schema migrations...\n";
$successCount = 0;
$failCount = 0;

foreach ($queries as $query) {
    if (empty($query)) continue;
    
    // Get query preview for logs (first line or first 50 chars)
    $lines = explode("\n", $query);
    $preview = trim($lines[0]);
    if (strlen($preview) > 60) {
        $preview = substr($preview, 0, 57) . "...";
    }
    
    try {
        $pdo->exec($query);
        echo "   ✅ Success: $preview\n";
        $successCount++;
    } catch (PDOException $e) {
        echo "   ❌ Failed:  $preview\n";
        echo "      Error: " . $e->getMessage() . "\n";
        $failCount++;
    }
}

// 5. Dynamic schema sync (Add missing columns to existing tables)
echo "\n🔍 Checking for missing columns in existing tables...\n";
$alterCount = 0;
$alterFailCount = 0;

preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?([a-zA-Z0-9_]+)\s*\((.+)\)\s*ENGINE\s*=/isU', $sqlContent, $matches, PREG_SET_ORDER);

foreach ($matches as $match) {
    $tableName = $match[1];
    $body = $match[2];
    
    // Fetch existing columns in this table
    try {
        $existingColumns = [];
        $descStmt = $pdo->query("DESCRIBE `$tableName`");
        while ($row = $descStmt->fetch(PDO::FETCH_ASSOC)) {
            $existingColumns[] = strtolower($row['Field']);
        }
    } catch (PDOException $e) {
        // Table probably wasn't created, skip
        continue;
    }
    
    // Split lines inside parenthesis by comma, but be careful of commas inside things like DECIMAL(5,2) or ENUM('Intern','Admin')
    $lines = preg_split('/,(?=(?:[^\'"]*\'[^\'"]*\')*[^\'"]*$)(?=(?:[^\(\)]*\([^\(\)]*\))*[^\(\)]*$)/', $body);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Ignore constraints, primary keys, foreign keys
        if (preg_match('/^(CONSTRAINT|PRIMARY\s+KEY|FOREIGN\s+KEY|UNIQUE|KEY|INDEX)/i', $line)) {
            continue;
        }
        
        // Extract column name and definition
        if (preg_match('/^`?([a-zA-Z0-9_]+)`?\s+(.+)$/s', $line, $colMatch)) {
            $colName = $colMatch[1];
            $colDef = trim($colMatch[2]);
            
            if (!in_array(strtolower($colName), $existingColumns)) {
                echo "   ➕ Missing column detected: `$tableName`.`$colName`...\n";
                try {
                    $pdo->exec("ALTER TABLE `$tableName` ADD COLUMN `$colName` $colDef");
                    echo "      ✅ Added column successfully!\n";
                    $alterCount++;
                } catch (PDOException $e) {
                    echo "      ❌ Failed to add column: " . $e->getMessage() . "\n";
                    $alterFailCount++;
                }
            }
        }
    }
}

// 6. Final Report
echo "\n=========================================\n";
echo "🏁 Migration Completed!\n";
echo "=========================================\n";
echo "📂 Target Database: $dbname\n";
echo "✅ Successful Queries: $successCount\n";
if ($failCount > 0) {
    echo "❌ Failed Queries: $failCount\n";
}
echo "➕ Added Columns: $alterCount\n";
if ($alterFailCount > 0) {
    echo "❌ Failed Column Additions: $alterFailCount\n";
}
if ($failCount == 0 && $alterFailCount == 0) {
    echo "🎉 Database schema is completely up-to-date and synced!\n";
} else {
    echo "⚠️  Note: Some failures are normal if tables or constraints already exist.\n";
}
echo "=========================================\n";
