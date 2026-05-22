<?php
// Ensure this is included through feed.php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    header("Location: ../../feed.php?page=dashboard");
    exit;
}

$base_url = "../";
?>
<link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/supervisor-dashboard.css">

<div class="dashboard-container">
        <div class="welcome-card">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Welcome, Supervisor <?php echo htmlspecialchars($_SESSION['user_name']); ?></h1>
            <p class="text-gray-600 mb-6">Managing interns for <strong><?php echo htmlspecialchars($_SESSION['office_name'] ?? 'N/A'); ?></strong> | <?php echo htmlspecialchars($_SESSION['organization_name'] ?? 'N/A'); ?></p>
        </div>

        <div class="absence-requests-section mt-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Pending Absence Requests</h2>
            <div id="absence-requests-list" class="grid gap-4">
                <p class="text-gray-500 italic">Loading requests...</p>
            </div>
        </div>

        <div class="interns-section mt-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Interns in Your Office</h2>
            <div id="interns-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <p class="text-gray-500 italic">Loading interns...</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadPendingAbsences();
            loadInterns();
        });

        function loadInterns() {
            fetch('<?php echo $base_url; ?>api/interns.php')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('interns-list');
                    if (data.success) {
                        if (data.interns.length === 0) {
                            list.innerHTML = '<div class="welcome-card"><p class="text-gray-500">No interns found in your office.</p></div>';
                            return;
                        }

                        list.innerHTML = '';
                        data.interns.forEach(intern => {
                            const card = document.createElement('div');
                            card.className = 'welcome-card text-left';
                            card.style.textAlign = 'left';
                            card.innerHTML = `
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold text-xl">
                                        ${intern.name.charAt(0)}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg">${intern.name}</h3>
                                        <p class="text-gray-600 text-sm">${intern.email}</p>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-xs text-gray-500">Intern</span>
                                    <a href="intern-logs.php?id=${intern.id}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">View Logs →</a>
                                </div>
                            `;
                            list.appendChild(card);
                        });
                    } else {
                        list.innerHTML = '<p class="text-red-500">Error loading interns.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('interns-list').innerHTML = '<p class="text-red-500">Failed to connect to server.</p>';
                });
        }

        function loadPendingAbsences() {
            fetch('<?php echo $base_url; ?>api/absences.php?pending=true')
                .then(response => response.json())
                .then(data => {
                    const list = document.getElementById('absence-requests-list');
                    if (data.success) {
                        if (data.absences.length === 0) {
                            list.innerHTML = '<div class="welcome-card"><p class="text-gray-500">No pending absence requests.</p></div>';
                            return;
                        }

                        list.innerHTML = '';
                        data.absences.forEach(abs => {
                            const card = document.createElement('div');
                            card.className = 'welcome-card flex flex-col md:flex-row justify-between items-center text-left';
                            card.style.textAlign = 'left';
                            card.innerHTML = `
                                <div class="flex-1">
                                    <h3 class="font-bold text-lg">${abs.intern_name}</h3>
                                    <p class="text-gray-600"><strong>Date:</strong> ${formatDate(abs.date)}</p>
                                    <p class="text-gray-700 mt-2"><strong>Reason:</strong> ${abs.reason || 'No reason provided'}</p>
                                </div>
                                <div class="flex gap-2 mt-4 md:mt-0">
                                    <button onclick="handleAbsence(${abs.absences_id}, 'approve')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition font-bold">Approve</button>
                                    <button onclick="handleAbsence(${abs.absences_id}, 'reject')" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition font-bold">Reject</button>
                                </div>
                            `;
                            list.appendChild(card);
                        });
                    } else {
                        list.innerHTML = '<p class="text-red-500">Error loading requests.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('absence-requests-list').innerHTML = '<p class="text-red-500">Failed to connect to server.</p>';
                });
        }

        function handleAbsence(id, action) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('id', id);

            fetch('<?php echo $base_url; ?>api/absences.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadPendingAbsences();
                } else {
                    alert(data.error || 'Error updating request');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Connection error');
            });
        }

        function formatDate(dateStr) {
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const [y, m, d] = dateStr.split('-');
            return `${months[parseInt(m) - 1]} ${parseInt(d)}, ${y}`;
        }
    </script>
