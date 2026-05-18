<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../feed.php?page=login");
    exit;
}

require_once '../../config.php';
$base_url = "../../";
require_once '../components/header.php';

// Fetch offices and organizations for dropdowns
$offices = $pdo->query("SELECT * FROM office ORDER BY office_name")->fetchAll();
$orgs = $pdo->query("SELECT * FROM organization ORDER BY organization_name")->fetchAll();

// Fetch current user data
$stmt = $pdo->prepare("SELECT name, email, office_id, organization_id, nickname, contact, birthdate, region, province, city, barangay, address, postal_code FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
</head>
<body class="bg-gray-50">
    <?php require_once '../components/navbar.php'; ?>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-16 h-16 bg-gray-900 text-white rounded-full flex items-center justify-center text-2xl font-bold">
                    <?php echo substr($user['name'], 0, 1); ?>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
                    <p class="text-gray-500">Update your personal information and preferences</p>
                </div>
            </div>

            <form id="profile-form" class="space-y-6">
                <div id="alert" class="hidden p-4 rounded-lg text-sm font-medium"></div>

                <!-- Personal Details Section -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100">Personal Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nickname</label>
                            <input type="text" name="nickname" value="<?php echo htmlspecialchars($user['nickname'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Contact Number</label>
                            <input type="tel" name="contact" maxlength="11" value="<?php echo htmlspecialchars($user['contact'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Birthdate</label>
                            <input type="date" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                    </div>
                </div>

                <!-- Affiliation Section -->
                <div class="pt-6 border-t border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Affiliation</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Office</label>
                            <select name="office_id" id="office_id" required onchange="toggleNewInput('office')"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                                <?php foreach ($offices as $o): ?>
                                    <option value="<?php echo $o['id']; ?>" <?php echo $o['id'] == $user['office_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($o['office_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="new">+ Add New Office</option>
                            </select>
                            <input type="text" id="new_office_name" name="new_office_name" placeholder="Enter new office name" 
                                class="hidden mt-3 w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Organization</label>
                            <select name="organization_id" id="organization_id" required onchange="toggleNewInput('organization')"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                                <?php foreach ($orgs as $org): ?>
                                    <option value="<?php echo $org['id']; ?>" <?php echo $org['id'] == $user['organization_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($org['organization_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="new">+ Add New Organization</option>
                            </select>
                            <input type="text" id="new_organization_name" name="new_organization_name" placeholder="Enter new organization name" 
                                class="hidden mt-3 w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                    </div>
                </div>

                <!-- Address Details Section -->
                <div class="pt-6 border-t border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Address Details (Philippines)</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Region</label>
                            <input type="text" name="region" value="<?php echo htmlspecialchars($user['region'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Province</label>
                            <input type="text" name="province" value="<?php echo htmlspecialchars($user['province'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">City / Municipality</label>
                            <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Barangay</label>
                            <input type="text" name="barangay" value="<?php echo htmlspecialchars($user['barangay'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Street Address / Unit Number</label>
                            <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">ZIP / Postal Code</label>
                            <input type="text" name="postal_code" maxlength="6" value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>" required
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Change Password</h2>
                    <p class="text-sm text-gray-500 mb-6">Leave blank if you don't want to change it</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
                            <input type="password" name="password" id="password"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Confirm New Password</label>
                            <input type="password" id="confirm_password"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-gray-900 focus:border-transparent transition outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-8">
                    <button type="button" onclick="window.history.back()" class="px-6 py-3 rounded-xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="px-8 py-3 rounded-xl bg-gray-900 text-white font-bold hover:bg-black transition shadow-lg shadow-gray-200">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleNewInput(type) {
            const select = document.getElementById(type + '_id');
            const input = document.getElementById('new_' + type + '_name');
            if (select.value === 'new') {
                input.classList.remove('hidden');
                input.required = true;
            } else {
                input.classList.add('hidden');
                input.required = false;
            }
        }

        document.getElementById('profile-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const alert = document.getElementById('alert');

            if (password && password !== confirm) {
                alert.textContent = "Passwords do not match.";
                alert.className = "p-4 rounded-lg text-sm font-medium bg-red-50 text-red-600 block mb-6";
                return;
            }

            const formData = new FormData(this);
            
            fetch('../../api/profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert.textContent = data.message;
                    alert.className = "p-4 rounded-lg text-sm font-medium bg-green-50 text-green-600 block mb-6";
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    alert.textContent = data.error || "An error occurred.";
                    alert.className = "p-4 rounded-lg text-sm font-medium bg-red-50 text-red-600 block mb-6";
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert.textContent = "Connection error.";
                alert.className = "p-4 rounded-lg text-sm font-medium bg-red-50 text-red-600 block mb-6";
            });
        });
    </script>

    <?php require_once '../components/footer.php'; ?>
</body>
</html>
