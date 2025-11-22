<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

$departments = $departments ?? [];
$roles = $roles ?? [];

// Set the title for layout_start.php
$title = 'Add Organization Member - Maestro UI';

include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" x-data="{}">
    
    <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
        Add Existing Member
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2">
            <form method="POST" action="<?= BASE_URL ?>/org/members/store" class="bg-green-950/50 p-8 rounded-xl space-y-6 border border-green-800 shadow-2xl shadow-green-900/10">
                <?php 
                // This section shows flash alerts (like validation errors)
                if (function_exists('flash_alert')) flash_alert(); 
                if (function_exists('csrf_field')) csrf_field();
                ?>
                
                <h2 class="text-xl font-semibold text-green-300 mb-4 border-b border-green-800/50 pb-2">Member Assignment</h2>

                <p class="text-sm text-gray-400">Enter the <b>registered email address</b> of the user you wish to add to the organization, then assign their role and department.</p>

                <div>
                    <label for="email" class="block text-sm font-medium mb-2 text-gray-300">Registered Email Address</label>
                    <input type="email" id="email" name="email" 
                        class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100" 
                        value="<?= (function_exists('set_value') ? html_escape(set_value('email') ?? '') : '') ?>" placeholder="user@example.com" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <label for="role_id" class="block text-sm font-medium mb-2 text-gray-300">Assign Role</label>
                        <select id="role_id" name="role_id" 
                            class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100" required>
                            <option value="">Select a Role</option>
                            <?php 
                            $selected_role = (function_exists('set_value') ? set_value('role_id') : '');
                            foreach($roles as $role): ?>
                                <option value="<?= html_escape($role['id']) ?>" <?= $selected_role == $role['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($roles)): ?><p class="mt-1 text-xs text-red-400">Warning: No Roles found in DB.</p><?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="dept_id" class="block text-sm font-medium mb-2 text-gray-300">Assign Department</label>
                        <select id="dept_id" name="dept_id" 
                            class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-green-500 focus:border-green-500 text-green-100" required>
                            <option value="">Select a Department</option>
                            <?php
                            $selected_dept = (function_exists('set_value') ? set_value('dept_id') : '');
                            foreach($departments as $dept): ?>
                                <option value="<?= html_escape($dept['id']) ?>" <?= $selected_dept == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($departments)): ?><p class="mt-1 text-xs text-red-400">Warning: No Departments found in DB.</p><?php endif; ?>
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="submit" class="w-full bg-green-700 px-6 py-3 rounded-xl hover:bg-green-600 font-bold text-lg transition shadow-lg shadow-green-900/40">
                        <i class="fa-solid fa-user-check mr-2"></i> Add Existing Member
                    </button>
                </div>
            </form>
        </div>

        <div class="lg:col-span-1 space-y-8">
            
            <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl shadow-green-900/10 h-full">
                <h2 class="text-xl font-bold text-green-300 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-handshake text-green-500"></i> Organization Enrollment
                </h2>
                <p class="text-sm text-gray-400 mb-4">This form is for adding users who have already completed the initial signup process on the public portal.</p>
                <ul class="list-disc list-inside space-y-2 text-gray-300 text-sm">
                    <li>The user must have an active, existing account.</li>
                    <li>This action only updates their **Role** and **Department**.</li>
                    <li>No password is changed or created by this form.</li>
                    <li>If the user does not exist, an error will be displayed.</li>
                </ul>
                <p class="mt-4 text-xs text-gray-500">
                    To register a brand new user, please direct them to the sign-up page (<?= BASE_URL ?>/register).
                </p>
            </div>

        </div>

    </div> 
</div> 

<?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>