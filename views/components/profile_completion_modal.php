<!-- Profile Completion Modal Overlay -->
<div id="profile-completion-overlay" class="fixed inset-0 bg-gray-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-xl w-full p-6 md:p-8 transform scale-100 transition-all duration-300 border border-gray-100 dark:border-gray-700 max-h-[95vh] overflow-y-auto my-auto">
        
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-full mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Complete Your Profile</h2>
            <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Please fill out your remaining profile details to access the tracker dashboard.</p>
        </div>

        <!-- Alert Message Container -->
        <div id="profile-completion-alert" class="hidden mb-4 p-4 rounded-xl text-sm font-medium border"></div>

        <!-- Form -->
        <form id="profile-completion-form" class="space-y-4" onsubmit="submitProfileCompletion(event)">
            
            <!-- Nickname & Contact Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nickname -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1" for="pc-nickname">Nickname <span class="text-red-500">*</span></label>
                    <input type="text" id="pc-nickname" name="nickname" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 outline-none placeholder-gray-400"
                           placeholder="Your preferred short name" value="<?php echo htmlspecialchars($user_profile['nickname'] ?? ''); ?>">
                </div>
                
                <!-- Contact Number -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1" for="pc-contact">Contact Number <span class="text-red-500">*</span></label>
                    <input type="tel" id="pc-contact" name="contact" required maxlength="11"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 outline-none placeholder-gray-400"
                           placeholder="09XXXXXXXXX" value="<?php echo htmlspecialchars($user_profile['contact'] ?? ''); ?>">
                </div>
            </div>

            <!-- Birthdate -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1" for="pc-birthdate">Birthdate <span class="text-red-500">*</span></label>
                <input type="date" id="pc-birthdate" name="birthdate" required
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 outline-none"
                       value="<?php echo htmlspecialchars($user_profile['birthdate'] ?? ''); ?>">
            </div>

            <!-- Location Settings Header -->
            <div class="border-t border-gray-100 dark:border-gray-700 pt-4 mt-2">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 mb-3 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Address Details (Philippines)
                </h3>
            </div>

            <!-- Region & Province Selects -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Region Dropdown -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1" for="pc-region">Region <span class="text-red-500">*</span></label>
                    <select id="pc-region" required
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 outline-none appearance-none cursor-pointer">
                        <option value="">Loading regions...</option>
                    </select>
                </div>

                <!-- Province Dropdown -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1" for="pc-province">Province <span class="text-red-500">*</span></label>
                    <select id="pc-province" required disabled
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 outline-none appearance-none cursor-pointer disabled:opacity-50">
                        <option value="">Select Region first</option>
                    </select>
                </div>
            </div>

            <!-- City / Municipality Dropdown -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1" for="pc-city">City / Municipality <span class="text-red-500">*</span></label>
                <select id="pc-city" required disabled
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 outline-none appearance-none cursor-pointer disabled:opacity-50">
                    <option value="">Select Region / Province first</option>
                </select>
            </div>

            <!-- Barangay Dropdown -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1" for="pc-barangay">Barangay <span class="text-red-500">*</span></label>
                <select id="pc-barangay" required disabled
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 outline-none appearance-none cursor-pointer disabled:opacity-50">
                    <option value="">Select City / Municipality first</option>
                </select>
            </div>

            <!-- Detailed Street Address & Zip Code -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Street Address -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1" for="pc-address">Street Address / Unit Number <span class="text-red-500">*</span></label>
                    <input type="text" id="pc-address" name="address" required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 outline-none placeholder-gray-400"
                           placeholder="House number, Street, Subd" value="<?php echo htmlspecialchars($user_profile['address'] ?? ''); ?>">
                </div>

                <!-- ZIP / Postal Code -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1" for="pc-zip">ZIP / Postal Code <span class="text-red-500">*</span></label>
                    <input type="text" id="pc-zip" name="postal_code" required maxlength="6"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200 outline-none placeholder-gray-400"
                           placeholder="e.g. 4027" value="<?php echo htmlspecialchars($user_profile['postal_code'] ?? ''); ?>">
                </div>
            </div>

            <!-- Action Button -->
            <div class="pt-4">
                <button type="submit" id="profile-completion-btn"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-lg transition duration-200 flex items-center justify-center gap-2 transform active:scale-[0.98] outline-none">
                    <span id="btn-text">Save and Proceed</span>
                    <svg id="btn-spinner" class="hidden animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initGeographicApi();
});

// PSGC API Elements
const regionSelect = document.getElementById('pc-region');
const provinceSelect = document.getElementById('pc-province');
const citySelect = document.getElementById('pc-city');
const barangaySelect = document.getElementById('pc-barangay');

// Dynamic Location Data Fetching
async function initGeographicApi() {
    try {
        const response = await fetch('https://psgc.gitlab.io/api/regions/');
        const regions = await response.json();

        regionSelect.innerHTML = '<option value="">Select Region</option>';
        
        // Sort regions alphabetically or by code order
        regions.forEach(region => {
            const opt = document.createElement('option');
            opt.value = region.code;
            opt.textContent = region.name;
            regionSelect.appendChild(opt);
        });

        // Trigger change handlers
        regionSelect.addEventListener('change', handleRegionChange);
        provinceSelect.addEventListener('change', handleProvinceChange);
        citySelect.addEventListener('change', handleCityChange);

    } catch (error) {
        console.error('Error fetching regions:', error);
        regionSelect.innerHTML = '<option value="">Error loading regions</option>';
        showAlert('Failed to connect to the PH geographic database. Please refresh the page.', 'error');
    }
}

async function handleRegionChange() {
    const regionCode = this.value;
    
    // Reset dependant selects
    provinceSelect.innerHTML = '<option value="">Select Province first</option>';
    citySelect.innerHTML = '<option value="">Select Region / Province first</option>';
    barangaySelect.innerHTML = '<option value="">Select City / Municipality first</option>';
    provinceSelect.disabled = true;
    citySelect.disabled = true;
    barangaySelect.disabled = true;

    if (!regionCode) return;

    try {
        provinceSelect.innerHTML = '<option value="">Loading provinces...</option>';
        const response = await fetch(`https://psgc.gitlab.io/api/regions/${regionCode}/provinces/`);
        const provinces = await response.json();

        if (provinces && provinces.length > 0) {
            provinceSelect.innerHTML = '<option value="">Select Province</option>';
            provinces.forEach(province => {
                const opt = document.createElement('option');
                opt.value = province.code;
                opt.textContent = province.name;
                provinceSelect.appendChild(opt);
            });
            provinceSelect.disabled = false;
        } else {
            // Region has no provinces (NCR / Metro Manila)
            provinceSelect.innerHTML = '<option value="NCR" selected>Metro Manila (NCR)</option>';
            provinceSelect.disabled = true;
            
            // Fetch cities directly from the region
            loadCitiesForRegion(regionCode);
        }
    } catch (error) {
        console.error('Error fetching provinces:', error);
        provinceSelect.innerHTML = '<option value="">Error loading provinces</option>';
    }
}

async function loadCitiesForRegion(regionCode) {
    citySelect.innerHTML = '<option value="">Loading cities...</option>';
    barangaySelect.innerHTML = '<option value="">Select City / Municipality first</option>';
    citySelect.disabled = true;
    barangaySelect.disabled = true;

    try {
        const response = await fetch(`https://psgc.gitlab.io/api/regions/${regionCode}/cities-municipalities/`);
        const cities = await response.json();

        citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
        cities.forEach(city => {
            const opt = document.createElement('option');
            opt.value = city.code;
            opt.textContent = city.name;
            citySelect.appendChild(opt);
        });
        citySelect.disabled = false;
    } catch (error) {
        console.error('Error fetching cities for region:', error);
        citySelect.innerHTML = '<option value="">Error loading cities</option>';
    }
}

async function handleProvinceChange() {
    const provinceCode = this.value;
    citySelect.innerHTML = '<option value="">Select Province first</option>';
    barangaySelect.innerHTML = '<option value="">Select City / Municipality first</option>';
    citySelect.disabled = true;
    barangaySelect.disabled = true;

    if (!provinceCode) return;

    try {
        citySelect.innerHTML = '<option value="">Loading cities...</option>';
        const response = await fetch(`https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`);
        const cities = await response.json();

        citySelect.innerHTML = '<option value="">Select City / Municipality</option>';
        cities.forEach(city => {
            const opt = document.createElement('option');
            opt.value = city.code;
            opt.textContent = city.name;
            citySelect.appendChild(opt);
        });
        citySelect.disabled = false;
    } catch (error) {
        console.error('Error fetching cities for province:', error);
        citySelect.innerHTML = '<option value="">Error loading cities</option>';
    }
}

async function handleCityChange() {
    const cityCode = this.value;
    barangaySelect.innerHTML = '<option value="">Select City / Municipality first</option>';
    barangaySelect.disabled = true;

    if (!cityCode) return;

    try {
        barangaySelect.innerHTML = '<option value="">Loading barangays...</option>';
        const response = await fetch(`https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`);
        const barangays = await response.json();

        // Sort alphabetically
        barangays.sort((a, b) => a.name.localeCompare(b.name));

        barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
        barangays.forEach(brgy => {
            const opt = document.createElement('option');
            opt.value = brgy.code;
            opt.textContent = brgy.name;
            barangaySelect.appendChild(opt);
        });
        barangaySelect.disabled = false;
    } catch (error) {
        console.error('Error fetching barangays:', error);
        barangaySelect.innerHTML = '<option value="">Error loading barangays</option>';
    }
}

// Form Submission
async function submitProfileCompletion(event) {
    event.preventDefault();

    const alertBox = document.getElementById('profile-completion-alert');
    const submitBtn = document.getElementById('profile-completion-btn');
    const btnText = document.getElementById('btn-text');
    const btnSpinner = document.getElementById('btn-spinner');

    // Get selected text names instead of the PSGC code numbers
    const selectedRegion = regionSelect.options[regionSelect.selectedIndex].text;
    
    let selectedProvince = '';
    if (provinceSelect.disabled && provinceSelect.value === 'NCR') {
        selectedProvince = 'Metro Manila';
    } else if (provinceSelect.selectedIndex > 0) {
        selectedProvince = provinceSelect.options[provinceSelect.selectedIndex].text;
    }

    let selectedCity = '';
    if (citySelect.selectedIndex > 0) {
        selectedCity = citySelect.options[citySelect.selectedIndex].text;
    }

    let selectedBarangay = '';
    if (barangaySelect.selectedIndex > 0) {
        selectedBarangay = barangaySelect.options[barangaySelect.selectedIndex].text;
    }

    const nicknameVal = document.getElementById('pc-nickname').value.trim();
    const contactVal = document.getElementById('pc-contact').value.trim();
    const birthdateVal = document.getElementById('pc-birthdate').value;
    const addressVal = document.getElementById('pc-address').value.trim();
    const zipVal = document.getElementById('pc-zip').value.trim();

    // Validation checks
    if (!nicknameVal || !contactVal || !birthdateVal || !selectedRegion || !selectedCity || !selectedBarangay || !addressVal || !zipVal) {
        showAlert('Please fill out all required fields.', 'error');
        return;
    }

    // Limit contact number to exactly 11 digits
    const contactClean = contactVal.replace(/[^0-9]/g, '');
    if (contactClean.length !== 11) {
        showAlert('Contact number must be exactly 11 digits (e.g., 09123456789).', 'error');
        return;
    }

    // Prepare FormData
    const formData = new FormData();
    formData.append('nickname', nicknameVal);
    formData.append('contact', contactVal);
    formData.append('birthdate', birthdateVal);
    formData.append('region', selectedRegion);
    formData.append('province', selectedProvince);
    formData.append('city', selectedCity);
    formData.append('barangay', selectedBarangay);
    formData.append('address', addressVal);
    formData.append('postal_code', zipVal);

    // Show loading spinner
    submitBtn.disabled = true;
    btnText.textContent = 'Saving Profile...';
    btnSpinner.classList.remove('hidden');
    alertBox.classList.add('hidden');

    try {
        const response = await fetch('../api/profile_completion.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();

        if (data.success) {
            showAlert('Profile complete! Redirecting...', 'success');
            
            // Fade out overlay and reload page to unlock dashboard
            setTimeout(() => {
                const overlay = document.getElementById('profile-completion-overlay');
                overlay.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }, 1000);
        } else {
            showAlert(data.error || 'An error occurred. Please try again.', 'error');
            submitBtn.disabled = false;
            btnText.textContent = 'Save and Proceed';
            btnSpinner.classList.add('hidden');
        }
    } catch (error) {
        console.error('Error submitting profile completion:', error);
        showAlert('Connection error. Please check your network and try again.', 'error');
        submitBtn.disabled = false;
        btnText.textContent = 'Save and Proceed';
        btnSpinner.classList.add('hidden');
    }
}

// Alert utility function
function showAlert(message, type) {
    const alertBox = document.getElementById('profile-completion-alert');
    alertBox.textContent = message;
    alertBox.classList.remove('hidden', 'bg-red-50', 'text-red-800', 'border-red-200', 'bg-green-50', 'text-green-800', 'border-green-200', 'dark:bg-red-950/40', 'dark:text-red-400', 'dark:border-red-900/60', 'dark:bg-green-950/40', 'dark:text-green-400', 'dark:border-green-900/60');
    
    if (type === 'error') {
        alertBox.classList.add('bg-red-50', 'text-red-800', 'border-red-200', 'dark:bg-red-950/40', 'dark:text-red-400', 'dark:border-red-900/60');
    } else {
        alertBox.classList.add('bg-green-50', 'text-green-800', 'border-green-200', 'dark:bg-green-950/40', 'dark:text-green-400', 'dark:border-green-900/60');
    }
}
</script>
