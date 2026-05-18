<?php
/**
 * DTR PDF Download API
 * Generates Daily Time Record (DTR) PDF using context/dtrtemplate.xlsx and Dompdf
 */

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf as PdfWriter;

session_start();

// 1. Authenticate user
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'success' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'Intern';

// Admins can download other users' DTR
if ($user_role === 'Admin' && isset($_GET['userId']) && !empty($_GET['userId'])) {
    $user_id = intval($_GET['userId']);
}

// 2. Parse and validate date filters
$fromDate = $_GET['from_date'] ?? '';
$toDate = $_GET['to_date'] ?? '';

if (empty($fromDate) || empty($toDate)) {
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    $fromDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
    $toDate = date('Y-m-t', strtotime($fromDate));
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format (must be YYYY-MM-DD)', 'success' => false]);
    exit;
}

if ($fromDate > $toDate) {
    http_response_code(400);
    echo json_encode(['error' => 'From date must be before or equal to to date', 'success' => false]);
    exit;
}

try {
    // 3. Fetch User profile details (Intern name, office, and organization)
    $stmt = $pdo->prepare("
        SELECT u.name, o.office_name, org.organization_name 
        FROM users u
        LEFT JOIN office o ON u.office_id = o.id
        LEFT JOIN organization org ON u.organization_id = org.id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Intern profile not found', 'success' => false]);
        exit;
    }

    $internName = $user['name'];
    $officeName = $user['office_name'] ?? 'N/A';
    $orgName = $user['organization_name'] ?? 'N/A';

    // 4. Fetch all attendance logs and hours for the date range
    $stmt = $pdo->prepare("
        SELECT date, morning_in, morning_out, afternoon_in, afternoon_out 
        FROM check_in 
        WHERE user_id = ? AND date BETWEEN ? AND ?
        ORDER BY date ASC
    ");
    $stmt->execute([$user_id, $fromDate, $toDate]);
    $checkIns = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $checkIns[$row['date']] = $row;
    }

    $stmt = $pdo->prepare("
        SELECT date, hours 
        FROM hours_log 
        WHERE user_id = ? AND date BETWEEN ? AND ?
        ORDER BY date ASC
    ");
    $stmt->execute([$user_id, $fromDate, $toDate]);
    $hoursLog = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $hoursLog[$row['date']] = floatval($row['hours']);
    }

    // Helper to format timestamps to H:i time
    function formatTimeVal($datetime) {
        if (!$datetime) return '';
        return date('H:i', strtotime($datetime));
    }

    // 5. Group dates by Year-Month to handle multi-month ranges cleanly (one page per month)
    $start = new DateTime($fromDate);
    $end = new DateTime($toDate);
    $interval = new DateInterval('P1D');
    
    // Modify end to include the last day in period
    $endForPeriod = clone $end;
    $endForPeriod->modify('+1 day');
    $period = new DatePeriod($start, $interval, $endForPeriod);

    $datesByMonth = [];
    foreach ($period as $dt) {
        $ym = $dt->format('Y-m');
        $datesByMonth[$ym][] = $dt->format('Y-m-d');
    }

    // 6. Load Excel template
    $templateFile = __DIR__ . '/../context/dtrtemplate.xlsx';
    if (!file_exists($templateFile)) {
        http_response_code(500);
        echo json_encode(['error' => 'DTR Excel template file is missing in context folder', 'success' => false]);
        exit;
    }

    $spreadsheet = IOFactory::load($templateFile);
    $templateSheet = $spreadsheet->getActiveSheet();

    $sheetIndex = 0;
    foreach ($datesByMonth as $ym => $dates) {
        if ($sheetIndex === 0) {
            $sheet = $templateSheet;
        } else {
            $sheet = clone $templateSheet;
            $sheet->setTitle('SheetTmp' . $sheetIndex);
            $spreadsheet->addSheet($sheet);
        }

        // Set Title as Month Year (e.g. "May 2026")
        $sheetTitle = date('M Y', strtotime($ym . '-01'));
        $sheet->setTitle($sheetTitle);

        // Populate header block
        $sheet->setCellValue('B2', 'Month of : ' . date('F Y', strtotime($ym . '-01')));
        $sheet->setCellValue('A3', $internName);
        $sheet->setCellValue('A4', $officeName . ' | ' . $orgName);

        // Clear all log row cells in template just in case
        for ($d = 1; $d <= 31; $d++) {
            $row = $d + 7;
            $sheet->setCellValue('B' . $row, '');
            $sheet->setCellValue('C' . $row, '');
            $sheet->setCellValue('D' . $row, '');
            $sheet->setCellValue('E' . $row, '');
            $sheet->setCellValue('F' . $row, '');
        }

        // Fill data for this month's active dates in range
        foreach ($dates as $dateStr) {
            $dayNum = (int)date('d', strtotime($dateStr));
            $row = $dayNum + 7;

            // Fill check-in hours
            if (isset($checkIns[$dateStr])) {
                $log = $checkIns[$dateStr];
                $sheet->setCellValue('B' . $row, formatTimeVal($log['morning_in']));
                $sheet->setCellValue('C' . $row, formatTimeVal($log['morning_out']));
                $sheet->setCellValue('D' . $row, formatTimeVal($log['afternoon_in']));
                $sheet->setCellValue('E' . $row, formatTimeVal($log['afternoon_out']));
            }

            // Fill computed hours decimal
            if (isset($hoursLog[$dateStr]) && $hoursLog[$dateStr] > 0) {
                $sheet->setCellValue('F' . $row, number_format($hoursLog[$dateStr], 2));
            }
        }

        // Ensure DTR sum formula is set at total row
        $sheet->setCellValue('F39', '=SUM(F8:F38)');

        // Format worksheet print styling to fit exactly on A4 portrait page
        $sheet->setShowGridlines(true);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LEGAL);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(1);

        $sheetIndex++;
    }

    $spreadsheet->setActiveSheetIndex(0);

    // 7. Stream PDF output to browser
    $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $internName);
    $filename = "DTR_{$safeName}_{$fromDate}_to_{$toDate}.pdf";

    // Clear output buffer to avoid any leading whitespace/notices polluting the binary stream
    if (ob_get_length()) {
        ob_clean();
    }
    flush();

    header('Content-Type: application/pdf');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    $writer = new PdfWriter($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred during DTR generation: ' . $e->getMessage(), 'success' => false]);
    exit;
}
