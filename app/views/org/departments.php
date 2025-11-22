<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// The necessary helper functions (html_escape, csrf_field, set_value) 
// are now assumed to be available via layout_start.php.

// --- Page Specific Variables ---
$title = 'Departments - Maestro';
$depts = $depts ?? [];
$potential_members = $potential_members ?? [];

$total_departments = count($depts);
$total_members = array_sum(array_column($depts, 'members_count'));

// The logic for $is_documents_open, $is_review_open, etc., is now handled entirely
// by reusable_sidebar.php and is removed from this file.

// --- TEMPLATE INCLUSION ---
include 'layout_start.php'; 
include 'sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white"
    x-data="{ 
        // Re-defining this x-data on the main content div is optional 
        // if modalDept/actionType are only manipulated here, but ensures 
        // the scope is correct inside the page content.
        BASE_URL: '<?= $BASE_URL ?>', 
        isModalOpen: false, 
        modalDept: {}, 
        actionType: '' 
    }">
    
    <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
        Departments
    </h1>
    
    <?php if (function_exists('flash_alert')) flash_alert(); ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex flex-col items-center justify-center text-center">
            <i class="fa-solid fa-building text-3xl text-blue-400/80 mb-2"></i>
            <p class="text-sm text-gray-400 uppercase tracking-wider">Total Departments</p>
            <p class="text-2xl font-bold text-blue-400 mt-1"><?= $total_departments ?></p>
        </div>
        <div class="bg-green-950/50 p-5 rounded-xl border border-green-800 shadow-lg flex flex-col items-center justify-center text-center">
            <i class="fa-solid fa-users text-3xl text-green-400/80 mb-2"></i>
            <p class="text-sm text-gray-400 uppercase tracking-wider">Total Members</p>
            <p class="text-2xl font-bold text-green-400 mt-1"><?= $total_members ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2">
            <h2 class="text-xl font-semibold text-green-300 mb-4 border-b border-green-800/50 pb-2">Department Structure Overview</h2>

            <div class="space-y-4">
                <?php if (!empty($depts)): ?>
                <?php foreach($depts as $dept): 
                    $dept_id = $dept['id'];
                    $members_count = $dept['members_count'] ?? 0;
                    $assigned_members = $dept['assigned_members'] ?? [];
                    $js_assigned_members = html_escape(json_encode($assigned_members));
                    // $js_dept_name is correctly escaped for use in JS strings
                    $js_dept_name = html_escape($dept['name']); 
                ?>
                <div x-data="{ 
                            membersOpen: false, 
                            members: <?= $js_assigned_members ?>, 
                            loading: false, 
                            deptId: <?= $dept_id ?>,
                            deptName: '<?= $js_dept_name ?>',
                            membersCount: <?= $members_count ?>,
                            
                            toggleMembers() {
                                this.membersOpen = !this.membersOpen;
                            },
                            openEditModal() {
                                // Assign to global scope variables defined in layout_start.php body tag
                                isModalOpen = true;
                                modalDept = { id: this.deptId, name: this.deptName, membersCount: this.membersCount };
                                actionType = 'edit';
                            },
                            openDeleteModal() {
                                // Assign to global scope variables
                                isModalOpen = true;
                                modalDept = { id: this.deptId, name: this.deptName, membersCount: this.membersCount };
                                actionType = 'delete';
                            }
                        }" 
                    class="bg-green-950/50 p-5 rounded-xl border-l-4 border-green-500 flex flex-col shadow-lg hover:bg-green-900/40 transition">
                    
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                        <div class="flex flex-col mb-3 sm:mb-0">
                            <span class="text-lg font-bold text-green-200"><?= htmlspecialchars($dept['name']) ?></span>
                            
                            <div class="text-sm text-gray-500 mt-1 space-y-1">
                                <span title="Number of members in this department" class="inline-flex items-center gap-1 text-gray-300">
                                    <i class="fa-solid fa-users text-xs text-green-500"></i> <span x-text="membersCount"><?= $members_count ?></span> Members
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <button @click="toggleMembers()"
                                    :disabled="membersCount === 0"
                                    class="bg-green-700 hover:bg-green-600 px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                    :class="membersCount === 0 ? 'opacity-50 cursor-not-allowed' : ''">
                                <span x-text="membersOpen ? 'Hide Members' : 'View Members'">View Members</span>
                            </button>

                             <button @click="openEditModal()"
                                    class="bg-yellow-700 hover:bg-yellow-600 px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            
                            <button @click="openDeleteModal()"
                                    class="bg-red-700 hover:bg-red-600 px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>

                        </div>
                    </div>
                    
                    <div x-show="membersOpen" x-transition.duration.300ms class="mt-4 border-t border-green-800 pt-4">
                        <h4 class="text-sm font-semibold text-gray-400 mb-2">Assigned Members:</h4>
                        
                        <div x-show="members.length > 0" class="flex flex-wrap gap-2">
                            <template x-for="member in members" :key="member.id">
                                <span class="bg-green-800/50 text-xs text-green-200 px-3 py-1 rounded-full shadow-md" 
                                        x-text="member.fname + ' ' + member.lname"></span>
                            </template>
                        </div>
                        <div x-show="members.length === 0" class="text-gray-500 text-sm">No members currently assigned to this department.</div>
                    </div>

                </div>
                <?php endforeach; ?>
                
                <?php else: ?>
                <div class="p-8 text-center text-gray-500 bg-green-950/20 rounded-xl border border-green-800">
                    <i class="fa-solid fa-sitemap text-4xl mb-3 text-green-500"></i>
                    <p class="text-lg">No departments have been set up yet.</p>
                    <p class="text-sm mt-2">Use the form in the side panel to add your first department.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-8">
            
            <div class="bg-green-950/50 p-6 rounded-xl space-y-4 border border-green-800 shadow-2xl shadow-green-900/10 sticky top-4">
                <form method="POST" action="<?= $BASE_URL ?>/org/departments/store" class="space-y-4">
                    <?php csrf_field(); ?>
                    <h2 class="text-xl font-bold text-yellow-400 mb-4 border-b border-green-800/50 pb-2">
                        <i class="fa-solid fa-plus-circle mr-2"></i> Create New Department
                    </h2>

                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-400 mb-1">Department Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" 
                                class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-white placeholder-gray-500" 
                                value="<?= html_escape(set_value('name') ?? '') ?>" placeholder="Department Name (e.g., Marketing)" required>
                        </div>
                        
                        <div>
                            <label for="member_ids" class="block text-sm font-medium text-gray-400 mb-1">Assign Initial Members (Optional)</label>
                            <select name="member_ids[]" id="member_ids" multiple size="4" 
                                class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-white">
                                <?php if (empty($potential_members)): ?>
                                    <option value="" disabled>No unassigned members found.</option>
                                <?php else: ?>
                                    <?php foreach($potential_members as $member): ?>
                                        <option value="<?= $member['id'] ?>"><?= htmlspecialchars($member['fname'] . ' ' . $member['lname']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple members (only unassigned members appear).</p>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-yellow-700 px-6 py-3 rounded-xl hover:bg-yellow-600 font-bold text-lg transition shadow-lg shadow-yellow-900/40">
                        <i class="fa-solid fa-sitemap mr-2"></i> Create Department & Assign
                    </button>
                </form>
            </div>
        </div>
    </div> 

</div> 

<div x-show="isModalOpen" x-cloak x-transition.opacity class="fixed inset-0 bg-black bg-opacity-75 z-50 flex justify-center items-center p-4">
    <div x-show="isModalOpen" x-transition.scale.duration.300ms @click.outside="isModalOpen = false" class="bg-green-950/95 border border-green-700 rounded-xl p-8 w-full max-w-lg shadow-2xl">
        
        <div x-show="actionType === 'edit'">
            <h3 class="text-2xl font-bold text-yellow-400 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square"></i> Edit Department
            </h3>
            <form method="POST" :action="BASE_URL + '/org/departments/update'" class="space-y-4">
                <?php csrf_field(); ?>
                <input type="hidden" name="dept_id" :value="modalDept.id">
                
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-400 mb-1">Department Name <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_name" name="name" :value="modalDept.name" required
                        class="w-full p-3 bg-green-900 border border-green-800 rounded-lg focus:ring-yellow-500 focus:border-yellow-500 text-white">
                </div>
                
                <p class="text-sm text-gray-500 border-t border-green-800 pt-2">
                    To add/remove members, go to the <a :href="BASE_URL + '/org/members/list'" class="text-blue-400 hover:text-blue-300 underline">Members List</a> page.
                </p>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="isModalOpen = false" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded-lg text-white font-semibold transition">Cancel</button>
                    <button type="submit" class="bg-yellow-700 hover:bg-yellow-600 px-4 py-2 rounded-lg text-white font-semibold transition">Save Changes</button>
                </div>
            </form>
        </div>

        <div x-show="actionType === 'delete'">
            <h3 class="text-2xl font-bold text-red-400 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i> Confirm Deletion
            </h3>
            <form method="POST" :action="BASE_URL + '/org/departments/delete'">
                <?php csrf_field(); ?>
                <input type="hidden" name="dept_id" :value="modalDept.id">
                
                <p class="text-white mb-4">
                    Are you sure you want to delete the department 
                    <strong class="text-red-300" x-text="modalDept.name"></strong>?
                </p>
                
                <p class="text-sm text-orange-400 font-semibold" x-show="modalDept.membersCount > 0">
                    <i class="fa-solid fa-users-slash"></i> NOTE: This department has 
                    <span x-text="modalDept.membersCount"></span> members. 
                    Deleting it will set their department to <b>Unassigned</b>.
                </p>
                <p x-show="modalDept.membersCount === 0" class="text-sm text-green-400 mb-4">
                    This department has no members. Safe to delete.
                </p>
                
                <div class="bg-gray-800 p-4 rounded-lg border border-red-700/50 mb-6">
                    <label for="verification_code" class="block text-sm font-bold text-red-400 mb-2">
                        Type the verification code to confirm:
                    </label>
                    <div class="flex items-center gap-4">
                        <div class="text-2xl font-mono px-4 py-2 bg-red-900 rounded-lg select-none">
                            <span class="text-red-100">
                                <?= $_SESSION['dept_delete_code'] ?? 'N/A' ?>
                            </span>
                        </div>
                        <input type="text" id="verification_code" name="verification_code" 
                            class="w-full p-3 bg-red-900/50 border border-red-700 rounded-lg focus:ring-red-500 focus:border-red-500 text-white placeholder-gray-500 text-lg font-mono tracking-widest" 
                            placeholder="Enter code" required autocomplete="off">
                    </div>
                    <p class="text-xs text-gray-400 mt-2">The code changes every time you try to delete.</p>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="isModalOpen = false" class="bg-gray-600 hover:bg-gray-500 px-4 py-2 rounded-lg text-white font-semibold transition">Cancel</button>
                    <button type="submit" class="bg-red-700 px-4 py-2 rounded-lg hover:bg-red-600 font-bold text-white transition">Delete Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// --- TEMPLATE END ---
include 'layout_end.php';
?>