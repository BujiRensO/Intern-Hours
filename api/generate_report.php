<?php
/**
 * API: Generate 2-page Accomplishment Report PDF (dompdf)
 */

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

$fromDate = $_POST['from_date'] ?? '';
$toDate   = $_POST['to_date'] ?? '';

if (empty($fromDate) || empty($toDate)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing date range']);
    exit;
}

// Fetch user info
$stmt = $pdo->prepare("
    SELECT u.name, o.office_name, org.organization_name
    FROM users u
    LEFT JOIN office o ON u.office_id = o.id
    LEFT JOIN organization org ON u.organization_id = org.id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$internName = htmlspecialchars($user['name'] ?? 'Unknown Intern');
$officeName = htmlspecialchars($user['office_name'] ?? 'N/A');
$orgName    = htmlspecialchars($user['organization_name'] ?? 'N/A');
$dateFmt    = date('M d, Y', strtotime($fromDate)) . ' – ' . date('M d, Y', strtotime($toDate));

// Collect form data
$objectives  = [];
for ($i = 1; $i <= 5; $i++) {
    $val = trim($_POST["obj_$i"] ?? '');
    if ($val !== '') $objectives[$i] = htmlspecialchars($val);
}
$activities  = nl2br(htmlspecialchars(trim($_POST['activities']  ?? '')));
$reflections = nl2br(htmlspecialchars(trim($_POST['reflections'] ?? '')));

// Log the report generation
try {
    $stmt = $pdo->prepare("
        INSERT INTO accomplishment_report_logs
        (user_id, from_date, to_date, objectives, activities, reflections)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user_id,
        $fromDate,
        $toDate,
        json_encode(array_values($objectives)),
        $_POST['activities'] ?? '',
        $_POST['reflections'] ?? '',
    ]);
} catch (Throwable $e) { /* non-fatal */ }

// Handle uploaded images → base64 embed
$imageBlocks = '';
if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['tmp_name'] as $idx => $tmpName) {
        if (!is_uploaded_file($tmpName)) continue;
        $mime = mime_content_type($tmpName);
        if (!in_array($mime, ['image/jpeg','image/png','image/gif','image/webp'])) continue;
        $b64  = base64_encode(file_get_contents($tmpName));
        $orig = htmlspecialchars($_FILES['images']['name'][$idx]);
        $imageBlocks .= "<div class='img-wrap'>
            <img src='data:{$mime};base64,{$b64}' alt='{$orig}'>
            <div class='img-caption'>{$orig}</div>
        </div>";
    }
}

// Build objectives HTML
$objHtml = '';
for ($i = 1; $i <= 5; $i++) {
    $text = $objectives[$i] ?? '';
    $objHtml .= "<tr>
        <td class='obj-num'>{$i}</td>
        <td class='obj-text'>{$text}</td>
    </tr>";
}

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 9.5pt;
    color: #1a1a1a;
    line-height: 1.4;
  }

  /* ═══ PAGE LAYOUT ═══════════════════════════════ */
  .page {
    width: 100%;
    padding: 22px 26px;
  }
  .page-break { page-break-after: always; }

  /* ═══ HEADER ════════════════════════════════════ */
  .report-header {
    border-bottom: 3px solid #1e3a8a;
    padding-bottom: 10px;
    margin-bottom: 14px;
  }
  .report-title-main {
    font-size: 13pt;
    font-weight: bold;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .report-title-sub {
    font-size: 10pt;
    font-weight: bold;
    color: #1e3a8a;
    margin-top: 1px;
  }
  .report-term {
    font-size: 8.5pt;
    color: #475569;
    margin-top: 2px;
  }

  /* ═══ INFO TABLE ════════════════════════════════ */
  .info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
  }
  .info-table td {
    padding: 4px 8px;
    font-size: 9pt;
    border: 1px solid #cbd5e1;
  }
  .info-table .lbl {
    font-weight: bold;
    width: 70px;
    background: #f1f5f9;
    color: #1e3a8a;
    text-transform: uppercase;
    font-size: 8pt;
    letter-spacing: 0.04em;
  }
  .info-table .val { color: #1e293b; }

  /* ═══ SECTION HEADING ═══════════════════════════ */
  .section-heading {
    font-size: 9pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #1e3a8a;
    border-bottom: 1.5px solid #bfdbfe;
    padding-bottom: 3px;
    margin-bottom: 8px;
  }

  /* ═══ OBJECTIVES TABLE ══════════════════════════ */
  .objectives-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
  }
  .objectives-table td {
    padding: 5px 8px;
    border: 1px solid #e2e8f0;
    font-size: 9pt;
    vertical-align: top;
  }
  .obj-num {
    width: 22px;
    text-align: center;
    font-weight: bold;
    background: #eff6ff;
    color: #1e3a8a;
  }
  .obj-text { color: #1e293b; min-height: 16px; }

  /* ═══ ACTIVITIES / REFLECTIONS ══════════════════ */
  .two-col {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
  }
  .two-col td {
    width: 50%;
    vertical-align: top;
    padding: 10px;
    border: 1px solid #e2e8f0;
    font-size: 9pt;
    min-height: 130px;
    color: #1e293b;
  }
  .two-col .col-head {
    font-size: 8.5pt;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #1e3a8a;
    background: #eff6ff;
    border-bottom: 1px solid #bfdbfe;
    padding: 5px 10px;
  }

  /* ═══ PAGE 2: DOCUMENTATION ═════════════════════ */
  .doc-title {
    font-size: 12pt;
    font-weight: bold;
    color: #1e3a8a;
    text-align: center;
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid #1e3a8a;
    padding-bottom: 8px;
  }
  .img-grid {
    display: block;
    width: 100%;
  }
  .img-wrap {
    display: block;
    margin-bottom: 18px;
    text-align: center;
    page-break-inside: avoid;
  }
  .img-wrap img {
    max-width: 100%;
    max-height: 220px;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
  }
  .img-caption {
    font-size: 7.5pt;
    color: #64748b;
    margin-top: 4px;
    font-style: italic;
  }
  .no-images {
    text-align: center;
    color: #94a3b8;
    font-style: italic;
    padding: 60px 0;
    font-size: 10pt;
  }

  /* ═══ SIGNATURE BLOCK ═══════════════════════════ */
  .sig-section {
    margin-top: 30px;
    border-top: 1px solid #e2e8f0;
    padding-top: 16px;
  }
  .sig-table {
    width: 100%;
    border-collapse: collapse;
  }
  .sig-table td {
    width: 33.33%;
    text-align: center;
    padding: 8px 12px;
    vertical-align: bottom;
    font-size: 8.5pt;
  }
  .sig-line {
    border-top: 1.5px solid #334155;
    margin: 40px auto 4px;
    width: 80%;
  }
  .sig-label {
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 8pt;
    color: #334155;
  }
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════ PAGE 1 -->
<div class="page page-break">

  <div class="report-header">
    <div class="report-title-main">On-The-Job Training Log Sheet</div>
    <div class="report-title-sub">Weekly Progress Report</div>
    <div class="report-term">Term: Second Semester AY 2025–2026</div>
  </div>

  <!-- Info -->
  <table class="info-table">
    <tr>
      <td class="lbl">Name</td>
      <td class="val">{$internName}</td>
    </tr>
    <tr>
      <td class="lbl">Office</td>
      <td class="val">{$officeName} | {$orgName}</td>
    </tr>
    <tr>
      <td class="lbl">Date</td>
      <td class="val">{$dateFmt}</td>
    </tr>
  </table>

  <!-- Objectives -->
  <div class="section-heading">Objective(s)</div>
  <table class="objectives-table">
    {$objHtml}
  </table>

  <!-- Activities & Reflections -->
  <div class="section-heading">Activities &amp; Reflections</div>
  <table class="two-col">
    <tr>
      <td class="col-head">Activities</td>
      <td class="col-head">Reflections</td>
    </tr>
    <tr>
      <td>{$activities}</td>
      <td>{$reflections}</td>
    </tr>
  </table>

</div>

<!-- ═══════════════════════════════════════════════════════ PAGE 2 -->
<div class="page">

  <div class="doc-title">Documentation</div>

  <div class="img-grid">
    {$imageBlocks}
  </div>

  {$imageBlocks === '' ? '<div class="no-images">No documentation images attached.</div>' : ''}

  <!-- Signatures -->
  <div class="sig-section">
    <table class="sig-table">
      <tr>
        <td>
          <div class="sig-line"></div>
          <div class="sig-label">Student Trainee</div>
        </td>
        <td>
          <div class="sig-line"></div>
          <div class="sig-label">HTE Supervisor</div>
        </td>
        <td>
          <div class="sig-line"></div>
          <div class="sig-label">OJT Coordinator</div>
        </td>
      </tr>
    </table>
  </div>

</div>

</body>
</html>
HTML;

// Generate PDF
$options = new Options();
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Arial');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $user['name'] ?? 'Intern');
$filename = "Accomplishment_Report_{$safeName}_{$fromDate}.pdf";

if (ob_get_length()) ob_clean();
$dompdf->stream($filename, ['Attachment' => true]);
exit;
