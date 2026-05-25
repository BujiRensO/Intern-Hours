<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../config.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$fromDate = $_POST['from_date'] ?? '';
$toDate = $_POST['to_date'] ?? '';

if (empty($fromDate) || empty($toDate)) {
    echo json_encode(['success' => false, 'error' => 'Missing date range']);
    exit;
}

// Fetch accomplishments
$stmt = $pdo->prepare("
    SELECT date, accomplishment
    FROM accomplishment
    WHERE user_id = ? AND date BETWEEN ? AND ?
    ORDER BY date ASC
");
$stmt->execute([$user_id, $fromDate, $toDate]);
$accomplishments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($accomplishments)) {
    echo json_encode(['success' => false, 'error' => 'No accomplishments found in the selected date range. Please log some first.']);
    exit;
}

// Fetch total hours
$stmt = $pdo->prepare("
    SELECT SUM(hours) as total_hours
    FROM hours_log
    WHERE user_id = ? AND date BETWEEN ? AND ?
");
$stmt->execute([$user_id, $fromDate, $toDate]);
$totalHours = floatval($stmt->fetch()['total_hours'] ?? 0);

$geminiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?? '';

if (empty($geminiKey)) {
    echo json_encode(['success' => false, 'error' => 'GEMINI_API_KEY is not configured in .env']);
    exit;
}

// Prepare the text of accomplishments
$bulletList = '';
foreach ($accomplishments as $a) {
    $bulletList .= '- ' . date('M d', strtotime($a['date'])) . ': ' . $a['accomplishment'] . "\n";
}

$prompt = "You are an assistant helping an intern write their weekly/monthly OJT progress report.
Based on the following raw daily accomplishments, generate three sections:
1. 'objectives': An array of up to 5 concise learning or operational objectives achieved during this period (e.g. 'Familiarize with the company UI framework').
2. 'activities': A professional cohesive paragraph summarizing the daily activities performed. Keep it concise but comprehensive.
3. 'reflections': A thoughtful paragraph on the skills learned, challenges overcome, and overall personal/professional growth during this period.

Raw Accomplishments (Total Hours: {$totalHours}):
{$bulletList}

Respond strictly with valid JSON only, using this exact schema, and NO markdown formatting (no ```json):
{
  \"objectives\": [\"string\", \"string\"],
  \"activities\": \"string\",
  \"reflections\": \"string\"
}";

$payload = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'responseMimeType' => 'application/json',
        'temperature' => 0.7
    ]
]);

$ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 30,
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode !== 200 || !$response) {
    echo json_encode(['success' => false, 'error' => 'Failed to reach AI service (HTTP ' . $httpcode . ')']);
    exit;
}

$decoded = json_decode($response, true);
$aiText = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';

$aiJson = json_decode($aiText, true);

if (!$aiJson) {
    echo json_encode(['success' => false, 'error' => 'AI returned an invalid format. Try again.']);
    exit;
}

echo json_encode([
    'success' => true,
    'data' => [
        'objectives' => $aiJson['objectives'] ?? [],
        'activities' => $aiJson['activities'] ?? '',
        'reflections' => $aiJson['reflections'] ?? ''
    ]
]);
