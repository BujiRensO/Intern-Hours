<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("Location: ../../feed.php?page=accomplishments");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch accomplishments for the current month
$month = (int)($_GET['month'] ?? date('m'));
$year  = (int)($_GET['year']  ?? date('Y'));
$from  = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . '-01';
$to    = date('Y-m-t', strtotime($from));

$stmt = $pdo->prepare("SELECT date, accomplishment FROM accomplishment WHERE user_id = ? AND date BETWEEN ? AND ? ORDER BY date DESC");
$stmt->execute([$user_id, $from, $to]);
$accomplishments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $pdo->prepare("SELECT SUM(hours) as total FROM hours_log WHERE user_id = ? AND date BETWEEN ? AND ?");
$stmt2->execute([$user_id, $from, $to]);
$monthHours = floatval($stmt2->fetch()['total'] ?? 0);

$base_url = '../';
?>

<link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/dashboard.css">

<style>
/* ─── Page Layout ─────────────────────────────────────── */
.acc-container {
    max-width: 1100px;
    margin: 0 auto 60px auto;
    padding: 0 8px;
    display: flex;
    flex-direction: column;
    gap: 24px;
}

/* ─── Header ──────────────────────────────────────────── */
.acc-header {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 60%, #7c3aed 100%);
    border-radius: 20px;
    padding: 28px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: 0 8px 32px rgba(37, 99, 235, 0.18);
}
.acc-header-left h2 {
    font-size: 22px;
    font-weight: 800;
    color: #fff;
    margin: 0 0 4px;
    letter-spacing: -0.03em;
}
.acc-header-left p {
    font-size: 12px;
    color: rgba(255,255,255,0.7);
    margin: 0;
    font-weight: 500;
}
.acc-header-stats {
    display: flex;
    gap: 16px;
}
.acc-stat-pill {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 12px;
    padding: 10px 18px;
    text-align: center;
    min-width: 90px;
    backdrop-filter: blur(6px);
}
.acc-stat-pill .val { font-size: 20px; font-weight: 900; color: #fff; line-height: 1; }
.acc-stat-pill .lbl { font-size: 10px; color: rgba(255,255,255,0.65); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; }

/* ─── Tabs ────────────────────────────────────────────── */
.acc-tabs {
    display: flex;
    gap: 4px;
    background: #f1f5f9;
    border-radius: 14px;
    padding: 4px;
    width: fit-content;
}
.acc-tab {
    padding: 8px 22px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    border: none;
    background: transparent;
    transition: all 0.2s;
}
.acc-tab.active {
    background: #fff;
    color: #1e3a8a;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.dark-mode .acc-tabs { background: #1e293b; }
.dark-mode .acc-tab.active { background: #0f172a; color: #60a5fa; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
.dark-mode .acc-tab { color: #94a3b8; }

/* ─── Tab Panels ──────────────────────────────────────── */
.acc-panel { display: none; }
.acc-panel.active { display: block; }

/* ─── Log Table ───────────────────────────────────────── */
.acc-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    overflow: hidden;
}
.dark-mode .acc-card { background: #1e293b; border-color: #374151; }

.acc-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}
.dark-mode .acc-card-header { border-bottom-color: #374151; }

.acc-card-title {
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.01em;
}
.dark-mode .acc-card-title { color: #f1f5f9; }

.acc-nav-btn {
    display: flex;
    align-items: center;
    gap: 8px;
}
.acc-nav-btn button {
    padding: 6px 14px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}
.acc-nav-btn button:hover { background: #f1f5f9; }
.dark-mode .acc-nav-btn button { background: #1e293b; border-color: #374151; color: #94a3b8; }
.dark-mode .acc-nav-btn button:hover { background: #0f172a; }

.acc-month-label { font-size: 13px; font-weight: 700; color: #1e3a8a; min-width: 110px; text-align: center; }
.dark-mode .acc-month-label { color: #60a5fa; }

.acc-table { width: 100%; border-collapse: collapse; }
.acc-table th {
    padding: 10px 16px;
    text-align: left;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.dark-mode .acc-table th { background: #0f172a; color: #94a3b8; border-bottom-color: #374151; }
.acc-table td {
    padding: 12px 16px;
    font-size: 13px;
    color: #374151;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}
.dark-mode .acc-table td { color: #cbd5e1; border-bottom-color: #1e293b; }
.acc-table tr:last-child td { border-bottom: none; }
.acc-table tr:hover td { background: #f8fafc; }
.dark-mode .acc-table tr:hover td { background: #0f172a; }

.acc-empty {
    text-align: center;
    padding: 48px 24px;
    color: #94a3b8;
    font-size: 13px;
    font-style: italic;
}

.acc-day-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
    background: #eff6ff;
    color: #2563eb;
}
.dark-mode .acc-day-badge { background: rgba(37,99,235,0.2); color: #60a5fa; }
.acc-edit-btn {
    font-size: 11px;
    font-weight: 700;
    color: #2563eb;
    background: none;
    border: none;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 6px;
    transition: background 0.15s;
}
.acc-edit-btn:hover { background: #eff6ff; }

/* ─── Report Builder ──────────────────────────────────── */
.report-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 700px) { .report-grid { grid-template-columns: 1fr; } }

.report-field label {
    display: block;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    margin-bottom: 6px;
}
.dark-mode .report-field label { color: #94a3b8; }
.report-field input, .report-field textarea, .report-field select {
    width: 100%;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    font-size: 13px;
    font-family: inherit;
    background: #fff;
    color: #1e293b;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
}
.dark-mode .report-field input, .dark-mode .report-field textarea, .dark-mode .report-field select {
    background: #0f172a;
    border-color: #374151;
    color: #e2e8f0;
}
.report-field input:focus, .report-field textarea:focus, .report-field select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
}

.ai-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    background: linear-gradient(135deg, #eff6ff, #f0fdf4);
    border-radius: 12px;
    border: 1px solid #bfdbfe;
}
.dark-mode .ai-toggle-row { background: rgba(37,99,235,0.1); border-color: rgba(37,99,235,0.3); }
.ai-toggle-info { font-size: 13px; font-weight: 700; color: #1e3a8a; }
.dark-mode .ai-toggle-info { color: #60a5fa; }
.ai-toggle-sub { font-size: 11px; font-weight: 500; color: #64748b; margin-top: 2px; }
.dark-mode .ai-toggle-sub { color: #94a3b8; }

.report-btn {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: none;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    color: #fff;
    box-shadow: 0 4px 14px rgba(37,99,235,0.25);
}
.report-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(37,99,235,0.35); }
.report-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.preset-btns {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}
.preset-btn {
    padding: 5px 12px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    background: #fff;
    color: #475569;
    transition: all 0.2s;
}
.preset-btn:hover, .preset-btn.active { background: #2563eb; color: #fff; border-color: #2563eb; }
.dark-mode .preset-btn { background: #1e293b; border-color: #374151; color: #94a3b8; }
.dark-mode .preset-btn:hover, .dark-mode .preset-btn.active { background: #2563eb; color: #fff; }
</style>

<div class="acc-container">

    <!-- Header -->
    <div class="acc-header">
        <div class="acc-header-left">
            <h2>📋 Accomplishments</h2>
            <p>Track daily progress · Generate professional reports</p>
        </div>
        <div class="acc-header-stats">
            <div class="acc-stat-pill">
                <div class="val"><?php echo count($accomplishments); ?></div>
                <div class="lbl">Logged</div>
            </div>
            <div class="acc-stat-pill">
                <div class="val"><?php echo number_format($monthHours, 1); ?></div>
                <div class="lbl">Hrs / Month</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="acc-tabs">
        <button class="acc-tab active" onclick="switchTab('log', this)" id="tab-log">📅 Daily Log</button>
        <button class="acc-tab" onclick="switchTab('report', this)" id="tab-report">📊 Generate Report</button>
    </div>

    <!-- ══ TAB 1: Daily Log ══════════════════════════════════════════════════ -->
    <div class="acc-panel active" id="panel-log">
        <div class="acc-card">
            <div class="acc-card-header">
                <div class="acc-card-title">Daily Accomplishments</div>
                <div class="acc-nav-btn">
                    <button onclick="shiftMonth(-1)">← Prev</button>
                    <span class="acc-month-label" id="log-month-label">
                        <?php echo date('F Y', strtotime($from)); ?>
                    </span>
                    <button onclick="shiftMonth(1)">Next →</button>
                </div>
            </div>

            <table class="acc-table" id="acc-log-table">
                <thead>
                    <tr>
                        <th style="width:110px">Date</th>
                        <th style="width:80px">Day</th>
                        <th style="width:80px">Hours</th>
                        <th>Accomplishment</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>
                <tbody id="acc-log-body">
                    <?php if (empty($accomplishments)): ?>
                        <tr><td colspan="5" class="acc-empty">No accomplishments logged for <?php echo date('F Y', strtotime($from)); ?>.</td></tr>
                    <?php else: ?>
                        <?php foreach ($accomplishments as $a): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($a['date'])); ?></td>
                                <td><span class="acc-day-badge"><?php echo date('D', strtotime($a['date'])); ?></span></td>
                                <td>—</td>
                                <td><?php echo nl2br(htmlspecialchars($a['accomplishment'])); ?></td>
                                <td>
                                    <button class="acc-edit-btn" onclick="editAccomplishment('<?php echo $a['date']; ?>', <?php echo json_encode($a['accomplishment']); ?>)">✏️ Edit</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══ TAB 2: Generate Report ════════════════════════════════════════════ -->
    <div class="acc-panel" id="panel-report">
        <div class="acc-card">
            <div class="acc-card-header" style="justify-content: space-between;">
                <div class="acc-card-title">📊 Accomplishment Report Builder</div>
                <button class="preset-btn" style="background:#2563eb;color:#fff;border:none;" onclick="autoFillWithAI()" id="btn-ai-autofill">
                    ✨ Auto-fill with AI
                </button>
            </div>
            
            <!-- STEP 1: Content -->
            <div id="step-1" style="padding: 24px; display: flex; flex-direction: column; gap: 20px;">
                <!-- Date Presets -->
                <div>
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; margin-bottom: 8px;">Quick Date Range</div>
                    <div class="preset-btns">
                        <button class="preset-btn" onclick="setPreset('this_week')">This Week</button>
                        <button class="preset-btn" onclick="setPreset('last_week')">Last Week</button>
                        <button class="preset-btn active" onclick="setPreset('this_month')">This Month</button>
                        <button class="preset-btn" onclick="setPreset('last_month')">Last Month</button>
                        <button class="preset-btn" onclick="setPreset('custom')">Custom Range</button>
                    </div>
                </div>

                <!-- Date Range Inputs -->
                <div class="report-grid">
                    <div class="report-field">
                        <label>From Date</label>
                        <input type="date" id="report-from" value="<?php echo $from; ?>">
                    </div>
                    <div class="report-field">
                        <label>To Date</label>
                        <input type="date" id="report-to" value="<?php echo $to; ?>">
                    </div>
                </div>

                <hr style="border:none;border-top:1px solid #e2e8f0;margin:10px 0;">

                <div class="report-field">
                    <label>Objectives (Up to 5)</label>
                    <input type="text" id="obj-1" placeholder="Objective 1..." style="margin-bottom:8px;">
                    <input type="text" id="obj-2" placeholder="Objective 2..." style="margin-bottom:8px;">
                    <input type="text" id="obj-3" placeholder="Objective 3..." style="margin-bottom:8px;">
                    <input type="text" id="obj-4" placeholder="Objective 4..." style="margin-bottom:8px;">
                    <input type="text" id="obj-5" placeholder="Objective 5...">
                </div>

                <div class="report-field">
                    <label>Activities</label>
                    <textarea id="report-activities" rows="4" placeholder="Summarize your daily activities here..."></textarea>
                </div>

                <div class="report-field">
                    <label>Reflections</label>
                    <textarea id="report-reflections" rows="4" placeholder="Write your reflections, learnings, and challenges here..."></textarea>
                </div>

                <button class="report-btn" onclick="goToStep(2)">Next Step (Upload Photos) →</button>
            </div>

            <!-- STEP 2: Photos -->
            <div id="step-2" style="padding: 24px; display: none; flex-direction: column; gap: 20px;">
                <div class="report-field">
                    <label>Documentation / Images</label>
                    <div style="border: 2px dashed #cbd5e1; border-radius: 12px; padding: 32px; text-align: center; background: #f8fafc;">
                        <input type="file" id="report-images" multiple accept="image/*" style="display:none;" onchange="updateImagePreview(this)">
                        <button class="preset-btn" style="margin-bottom:12px;" onclick="document.getElementById('report-images').click()">📁 Select Images</button>
                        <div style="font-size:12px;color:#64748b;">You can select multiple images. They will be placed under the Documentation section.</div>
                        <div id="image-preview" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:20px; justify-content:center;"></div>
                    </div>
                </div>

                <div style="display:flex; gap:12px;">
                    <button class="report-btn" style="background:#64748b;flex:1;" onclick="goToStep(1)">← Back</button>
                    <button class="report-btn" id="generate-btn" style="flex:2;" onclick="generateReport()">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download PDF Report
                    </button>
                </div>
                
                <div id="report-status" style="display:none; text-align:center; font-size:12px; font-weight:600; color:#64748b; padding:8px; border-radius:8px; background:#f8fafc;"></div>
            </div>

        </div>
    </div>

</div>

<!-- Edit Accomplishment Modal (reuse existing styles) -->
<div class="modal" id="acc-edit-modal">
    <div class="modal-content" style="max-width: 480px;">
        <div class="modal-header">Edit Accomplishment</div>
        <div class="form-group" style="margin-bottom:12px;">
            <label>Date</label>
            <input type="text" id="acc-edit-date" readonly style="background:#f8fafc;border:1px solid #cbd5e1;padding:8px 12px;font-weight:600;color:#475569;">
        </div>
        <div class="form-group">
            <label>Accomplishment</label>
            <textarea id="acc-edit-text" rows="4" style="width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;font-family:inherit;font-size:14px;box-sizing:border-box;resize:vertical;"></textarea>
        </div>
        <div class="modal-buttons">
            <button class="btn-save" onclick="saveEditedAccomplishment()">Save</button>
            <button class="btn-cancel" onclick="document.getElementById('acc-edit-modal').classList.remove('active')">Cancel</button>
        </div>
    </div>
</div>

<script>
const accApiBase = '../api/';
let logMonth = <?php echo $month; ?>;
let logYear  = <?php echo $year; ?>;

function switchTab(name, btn) {
    document.querySelectorAll('.acc-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.acc-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('panel-' + name).classList.add('active');
}

function goToStep(step) {
    document.getElementById('step-1').style.display = step === 1 ? 'flex' : 'none';
    document.getElementById('step-2').style.display = step === 2 ? 'flex' : 'none';
}

function updateImagePreview(input) {
    const preview = document.getElementById('image-preview');
    preview.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.height = '60px';
                img.style.borderRadius = '6px';
                img.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
                preview.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }
}

// ── Month navigation ─────────────────────────────────────────────────────────
function shiftMonth(dir) {
    logMonth += dir;
    if (logMonth > 12) { logMonth = 1;  logYear++; }
    if (logMonth < 1)  { logMonth = 12; logYear--; }
    loadLogTable();
}

function loadLogTable() {
    const label = document.getElementById('log-month-label');
    const body  = document.getElementById('acc-log-body');
    const monthStr = String(logMonth).padStart(2, '0');
    const from  = `${logYear}-${monthStr}-01`;
    
    // Display human label
    const d = new Date(logYear, logMonth - 1, 1);
    label.textContent = d.toLocaleString('default', { month: 'long', year: 'numeric' });

    body.innerHTML = '<tr><td colspan="5" class="acc-empty">Loading...</td></tr>';

    fetch(accApiBase + `accomplishments.php?range=month&from=${from}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.accomplishments || data.accomplishments.length === 0) {
                body.innerHTML = `<tr><td colspan="5" class="acc-empty">No accomplishments logged for ${label.textContent}.</td></tr>`;
                return;
            }
            body.innerHTML = data.accomplishments.map(a => {
                const dt  = new Date(a.date + 'T00:00:00');
                const day = dt.toLocaleString('default', { weekday: 'short' });
                const dateStr = dt.toLocaleString('default', { month: 'short', day: '2-digit', year: 'numeric' });
                return `<tr>
                    <td>${dateStr}</td>
                    <td><span class="acc-day-badge">${day}</span></td>
                    <td>—</td>
                    <td>${a.accomplishment.replace(/\n/g, '<br>')}</td>
                    <td><button class="acc-edit-btn" onclick="editAccomplishment('${a.date}', ${JSON.stringify(a.accomplishment)})">✏️ Edit</button></td>
                </tr>`;
            }).join('');
        })
        .catch(() => { body.innerHTML = '<tr><td colspan="5" class="acc-empty">Error loading data.</td></tr>'; });
}

// ── Edit accomplishment ───────────────────────────────────────────────────────
function editAccomplishment(date, text) {
    document.getElementById('acc-edit-date').value = date;
    document.getElementById('acc-edit-text').value = text;
    document.getElementById('acc-edit-modal').classList.add('active');
}

function saveEditedAccomplishment() {
    const date = document.getElementById('acc-edit-date').value;
    const text = document.getElementById('acc-edit-text').value;
    if (!text.trim()) { alert('Please enter an accomplishment.'); return; }
    const fd = new FormData();
    fd.append('date', date);
    fd.append('accomplishment', text);
    fetch(accApiBase + 'accomplishments.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                document.getElementById('acc-edit-modal').classList.remove('active');
                loadLogTable();
            } else { alert('Error: ' + (d.error || 'Save failed')); }
        });
}

// ── Date presets ─────────────────────────────────────────────────────────────
function setPreset(type) {
    document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
    event.target.classList.add('active');

    const today = new Date();
    const pad = n => String(n).padStart(2, '0');
    const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
    const dow = today.getDay(); // 0=Sun

    let from, to;

    if (type === 'this_week') {
        const mon = new Date(today); mon.setDate(today.getDate() - (dow === 0 ? 6 : dow - 1));
        const sun = new Date(mon);   sun.setDate(mon.getDate() + 6);
        from = fmt(mon); to = fmt(sun);
    } else if (type === 'last_week') {
        const mon = new Date(today); mon.setDate(today.getDate() - (dow === 0 ? 6 : dow - 1) - 7);
        const sun = new Date(mon);   sun.setDate(mon.getDate() + 6);
        from = fmt(mon); to = fmt(sun);
    } else if (type === 'this_month') {
        from = `${today.getFullYear()}-${pad(today.getMonth()+1)}-01`;
        const last = new Date(today.getFullYear(), today.getMonth()+1, 0);
        to = fmt(last);
    } else if (type === 'last_month') {
        const lm = new Date(today.getFullYear(), today.getMonth(), 0);
        to = fmt(lm);
        from = `${lm.getFullYear()}-${pad(lm.getMonth()+1)}-01`;
    } else {
        // custom: just focus from field
        document.getElementById('report-from').focus();
        return;
    }

    document.getElementById('report-from').value = from;
    document.getElementById('report-to').value   = to;
}

// ── AI Auto-fill ─────────────────────────────────────────────────────────────
function autoFillWithAI() {
    const from = document.getElementById('report-from').value;
    const to = document.getElementById('report-to').value;
    if (!from || !to) { alert('Please select a date range first.'); return; }

    const btn = document.getElementById('btn-ai-autofill');
    btn.innerHTML = '⏳ Generating...';
    btn.disabled = true;

    const fd = new FormData();
    fd.append('from_date', from);
    fd.append('to_date', to);

    fetch(accApiBase + 'ai_accomplishment_fill.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            btn.innerHTML = '✨ Auto-fill with AI';
            btn.disabled = false;
            
            if (data.success) {
                const ai = data.data;
                for (let i = 0; i < 5; i++) {
                    document.getElementById(`obj-${i+1}`).value = ai.objectives[i] || '';
                }
                document.getElementById('report-activities').value = ai.activities || '';
                document.getElementById('report-reflections').value = ai.reflections || '';
            } else {
                alert('AI Error: ' + data.error);
            }
        })
        .catch(err => {
            btn.innerHTML = '✨ Auto-fill with AI';
            btn.disabled = false;
            alert('Failed to connect to AI service.');
        });
}

// ── Generate Report ───────────────────────────────────────────────────────────
function generateReport() {
    const from  = document.getElementById('report-from').value;
    const to    = document.getElementById('report-to').value;
    
    if (!from || !to) { alert('Please select a date range.'); return; }
    if (from > to)    { alert('From date must be before To date.'); return; }

    const btn   = document.getElementById('generate-btn');
    const status = document.getElementById('report-status');

    btn.disabled = true;
    btn.innerHTML = '⏳ Generating report...';
    status.style.display = 'block';
    status.textContent = '📊 Building Excel report with images...';

    const fd = new FormData();
    fd.append('from_date', from);
    fd.append('to_date', to);
    for (let i = 1; i <= 5; i++) {
        fd.append(`obj_${i}`, document.getElementById(`obj-${i}`).value);
    }
    fd.append('activities', document.getElementById('report-activities').value);
    fd.append('reflections', document.getElementById('report-reflections').value);

    const imageInput = document.getElementById('report-images');
    if (imageInput.files.length > 0) {
        Array.from(imageInput.files).forEach((file, idx) => {
            fd.append(`images[]`, file);
        });
    }

    fetch('../api/generate_report.php', { method: 'POST', body: fd })
        .then(res => {
            if (!res.ok) throw new Error('Server error');
            return res.blob();
        })
        .then(blob => {
            if (blob.type === 'application/json') {
                const reader = new FileReader();
                reader.onload = function() {
                    alert('Error: ' + JSON.parse(this.result).error);
                };
                reader.readAsText(blob);
                throw new Error('JSON error returned');
            }
            
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = `Accomplishment_Report_${from}_to_${to}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            
            btn.disabled = false;
            btn.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Download PDF Report';
            status.textContent = '✅ Download started! Check your downloads folder.';
            setTimeout(() => { status.style.display = 'none'; }, 4000);
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = 'Download PDF Report';
            status.textContent = '❌ Generation failed.';
            console.error(err);
        });
}
</script>

