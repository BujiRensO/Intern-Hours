<?php
/**
 * API: Smart PHP Rule-Based Accomplishment Auto-fill
 * No external API or key required. Works entirely from logged accomplishment data.
 */
session_start();
require_once '../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id  = $_SESSION['user_id'];
$fromDate = $_POST['from_date'] ?? '';
$toDate   = $_POST['to_date']   ?? '';

if (empty($fromDate) || empty($toDate)) {
    echo json_encode(['success' => false, 'error' => 'Missing date range.']);
    exit;
}

// ── Fetch logged accomplishments ───────────────────────────────────────────
$stmt = $pdo->prepare(
    "SELECT date, accomplishment FROM accomplishment
     WHERE user_id = ? AND date BETWEEN ? AND ?
     ORDER BY date ASC"
);
$stmt->execute([$user_id, $fromDate, $toDate]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo json_encode(['success' => false, 'error' => 'No accomplishments logged in this date range.']);
    exit;
}

// ── Combine all text ───────────────────────────────────────────────────────
$allText = implode(' ', array_column($rows, 'accomplishment'));
$allText = preg_replace('/\s+/', ' ', trim($allText));

// ── Helper: split into sentences ───────────────────────────────────────────
function splitSentences(string $text): array {
    $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    return array_filter(array_map('trim', $sentences));
}

// ── Helper: clean a sentence for use as an objective ──────────────────────
function toObjective(string $sentence): string {
    return ucfirst(trim(rtrim($sentence, '.,;:')));
}

// ── Extract action-verb clusters for objectives ────────────────────────────
$ACTION_VERBS = [
    'developed','created','implemented','built','designed','coded','programmed',
    'reviewed','tested','debugged','fixed','resolved','refactored','updated','deployed',
    'attended','participated','presented','discussed','collaborated','coordinated',
    'prepared','documented','wrote','drafted','edited','submitted','completed',
    'analyzed','researched','studied','learned','explored','investigated',
    'configured','set up','installed','integrated','migrated',
    'trained','mentored','assisted','supported','helped',
    'planned','organized','scheduled','managed',
];

$sentences     = array_values(splitSentences($allText));
$objectives    = [];
$usedSentences = [];

// Pass 1: sentences that START with an action verb
foreach ($sentences as $idx => $s) {
    if (count($objectives) >= 5) break;
    $lower = strtolower($s);
    foreach ($ACTION_VERBS as $verb) {
        if (str_starts_with($lower, $verb)) {
            $obj = toObjective($s);
            if (strlen($obj) > 15 && strlen($obj) < 150) {
                $objectives[]       = $obj;
                $usedSentences[$idx] = true;
                break;
            }
        }
    }
}

// Pass 2: sentences that CONTAIN an action verb anywhere
if (count($objectives) < 5) {
    foreach ($sentences as $idx => $s) {
        if (isset($usedSentences[$idx])) continue;
        if (count($objectives) >= 5) break;
        $lower = strtolower($s);
        foreach ($ACTION_VERBS as $verb) {
            if (str_contains($lower, ' ' . $verb . ' ')
                || str_contains($lower, ' ' . $verb . 'd ')
                || str_contains($lower, ' ' . $verb . 'ed ')) {
                $obj = toObjective($s);
                if (strlen($obj) > 15 && strlen($obj) < 150) {
                    $objectives[]       = $obj;
                    $usedSentences[$idx] = true;
                    break;
                }
            }
        }
    }
}

// Pass 3: fallback — take first unused sentences
if (count($objectives) < 3) {
    foreach ($sentences as $idx => $s) {
        if (isset($usedSentences[$idx])) continue;
        if (count($objectives) >= 5) break;
        $obj = toObjective($s);
        if (strlen($obj) > 15 && strlen($obj) < 150) {
            $objectives[] = $obj;
        }
    }
}

// Deduplicate by first 40 chars
$seen = [];
$unique = [];
foreach ($objectives as $obj) {
    $key = strtolower(substr($obj, 0, 40));
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[] = $obj;
    }
}
$objectives = array_slice($unique, 0, 5);

// ── Build Activities narrative ─────────────────────────────────────────────
$actParts = [];
foreach ($rows as $row) {
    $dateLabel = date('l, F j', strtotime($row['date']));
    $text      = rtrim(trim($row['accomplishment']), '.') . '.';
    $actParts[] = "On {$dateLabel}: {$text}";
}

$activities = implode(' ', $actParts);
if (strlen($activities) > 1200) {
    $activities  = substr($activities, 0, 1200);
    $lastPeriod  = strrpos($activities, '.');
    if ($lastPeriod !== false) $activities = substr($activities, 0, $lastPeriod + 1);
}

// ── Build Reflections from theme detection ─────────────────────────────────
$themes = [
    'technical'     => ['code','coding','develop','implement','bug','fix','debug','program','software','test','deploy','api','database','feature'],
    'teamwork'      => ['team','collaborate','meeting','discussion','coordinate','colleague','group','standup','sprint'],
    'learning'      => ['learn','study','research','explore','understand','read','documentation','training','seminar'],
    'design'        => ['design','ui','ux','layout','wireframe','prototype','figma','interface','visual'],
    'management'    => ['plan','organize','schedule','manage','report','submit','deadline','task','priority'],
    'communication' => ['present','explain','brief','email','report','communicate','discuss','feedback'],
];

$detectedThemes = [];
$lowerAll       = strtolower($allText);
foreach ($themes as $theme => $keywords) {
    foreach ($keywords as $kw) {
        if (str_contains($lowerAll, $kw)) {
            $detectedThemes[$theme] = true;
            break;
        }
    }
}

$reflectionParts = ["During this period, I gained valuable experience and made meaningful contributions to my assigned tasks."];

if (isset($detectedThemes['technical']))     $reflectionParts[] = "Working on technical tasks deepened my understanding of software development practices and improved my problem-solving capabilities.";
if (isset($detectedThemes['teamwork']))      $reflectionParts[] = "Collaborating with the team enhanced my communication skills and gave me insights into how professional teams operate in a real-world setting.";
if (isset($detectedThemes['learning']))      $reflectionParts[] = "The research and learning activities I engaged in expanded my knowledge base and gave me a stronger foundation for future tasks.";
if (isset($detectedThemes['design']))        $reflectionParts[] = "Working on design-related tasks helped me appreciate the importance of user experience and visual clarity in software products.";
if (isset($detectedThemes['management']))    $reflectionParts[] = "Planning and managing tasks taught me the value of prioritization and meeting deadlines in a professional environment.";
if (isset($detectedThemes['communication'])) $reflectionParts[] = "Presenting and communicating progress helped me become more confident in expressing ideas clearly and professionally.";

$reflectionParts[] = "Overall, this training period was a rewarding experience that bridged the gap between academic learning and industry practice.";

$reflections = implode(' ', $reflectionParts);

// ── Respond ────────────────────────────────────────────────────────────────
echo json_encode([
    'success' => true,
    'data' => [
        'objectives'  => $objectives,
        'activities'  => $activities,
        'reflections' => $reflections,
    ]
]);
