<?php
/**
 * API: Generate Accomplishment Report PDF from XLSX Template
 * Fills AccomplishmentReportTemplate.xlsx then renders it as PDF via PhpSpreadsheet + Dompdf.
 */
ob_start();

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf as SpreadsheetDompdf;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id  = $_SESSION['user_id'];
$fromDate = $_POST['from_date'] ?? '';
$toDate   = $_POST['to_date']   ?? '';

if (empty($fromDate) || empty($toDate)) {
    ob_end_clean();
    http_response_code(400);
    echo json_encode(['error' => 'Missing date range']);
    exit;
}

// ── Fetch user info ────────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT u.name, o.office_name, org.organization_name
    FROM users u
    LEFT JOIN office o ON u.office_id = o.id
    LEFT JOIN organization org ON u.organization_id = org.id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$internName = $user['name']              ?? 'Unknown Intern';
$officeName = $user['office_name']       ?? 'N/A';
$orgName    = $user['organization_name'] ?? 'N/A';
$dateFmt    = date('M d, Y', strtotime($fromDate)) . ' - ' . date('M d, Y', strtotime($toDate));

// ── Load template ──────────────────────────────────────────────────────────
$templateFile = __DIR__ . '/../context/AccomplishmentReportTemplate.xlsx';
if (!file_exists($templateFile)) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Template file not found']);
    exit;
}

try {
    $spreadsheet = IOFactory::load($templateFile);
    $sheet       = $spreadsheet->getActiveSheet();

    // ── Fill header info ───────────────────────────────────────────────────
    $sheet->setCellValue('D12', $internName);
    $sheet->setCellValue('D13', $officeName . ' | ' . $orgName);
    $sheet->setCellValue('D14', $dateFmt);

    // ── Fill objectives (rows 17–21, col C) ───────────────────────────────
    for ($i = 1; $i <= 5; $i++) {
        $val = trim($_POST["obj_$i"] ?? '');
        $sheet->setCellValue('C' . (16 + $i), $val);
    }

    // ── Fill activities & reflections (row 24) ─────────────────────────────
    $activities  = trim($_POST['activities']  ?? '');
    $reflections = trim($_POST['reflections'] ?? '');
    $sheet->setCellValue('B24', $activities);
    $sheet->setCellValue('E24', $reflections);
    $sheet->getStyle('B24')->getAlignment()->setWrapText(true);
    $sheet->getStyle('E24')->getAlignment()->setWrapText(true);

    // ── Embed uploaded images into K8:Q38 ──────────────────────────────────
    if (!empty($_FILES['images']['name'][0])) {
        $startRow = 8;
        foreach ($_FILES['images']['tmp_name'] as $idx => $tmpName) {
            if (!is_uploaded_file($tmpName)) continue;
            if (getimagesize($tmpName) === false) continue;
            if ($startRow > 35) break;

            $drawing = new Drawing();
            $drawing->setName('Doc Image ' . ($idx + 1));
            $drawing->setDescription('Uploaded documentation image');
            $drawing->setPath($tmpName);
            $drawing->setCoordinates('K' . $startRow);
            $drawing->setHeight(170);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);

            $startRow += 9; // advance ~9 rows per image
        }
    }

    // ── Log the generated report ──────────────────────────────────────────
    try {
        $objectives = [];
        for ($i = 1; $i <= 5; $i++) {
            $v = trim($_POST["obj_$i"] ?? '');
            if ($v !== '') $objectives[] = $v;
        }
        $logStmt = $pdo->prepare("
            INSERT INTO accomplishment_report_logs
            (user_id, from_date, to_date, objectives, activities, reflections)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $logStmt->execute([
            $user_id, $fromDate, $toDate,
            json_encode($objectives), $activities, $reflections
        ]);
    } catch (Throwable $e) { /* non-fatal */ }

    // ── Render the filled XLSX as PDF (respects your A4 setup & print area) -
    $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $internName);
    $filename = "Accomplishment_Report_{$safeName}_{$fromDate}.pdf";

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf($spreadsheet);

    ob_end_clean();
    header('Content-Type: application/pdf');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Report Error: ' . $e->getMessage()]);
    exit;
}
