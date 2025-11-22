<?php 
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// --- 1. Page Specific Variables and Data ---
$title = 'Roles & Permissions - Maestro UI';

// PHP variables/logic retained from original for data context/filter helpers.
// NOTE: Global helper functions (html_escape, csrf_field, etc.) are assumed to be available
// via app/views/org/layout_start.php.

// Mock data for the roles list (Retained for content functionality)
$roles = [
    ['name' => 'President', 'description' => 'Highest authority. Full control over users, documents, and system settings.', 'access' => 'Full Access', 'color' => 'red'],
    ['name' => 'Adviser', 'description' => 'Executive oversight. Full control over users, documents, and system settings.', 'access' => 'Full Access', 'color' => 'red'],
    ['name' => 'Secretary', 'description' => 'Document uploading, resubmission, management, and viewing of rejected documents.', 'access' => 'Upload & Manage', 'color' => 'blue'],
    ['name' => 'Executive Member', 'description' => 'Can review, comment on, approve, and reject documents.', 'access' => 'Review & Workflow', 'color' => 'yellow'],
    ['name' => 'Treasurer', 'description' => 'Can view Organization lists (Members, Departments) and the main Reports overview (financial/budget).', 'access' => 'View Org & Basic Reports', 'color' => 'purple'],
    ['name' => 'General Member', 'description' => 'Can upload/resubmit documents and view their status. Can only view/download Approved documents.', 'access' => 'Read-Only + Upload/Resubmit', 'color' => 'gray'],
];

// --- 2. TEMPLATE INCLUSION ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" x-data="{}">
    
    <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
        Roles & Permissions
    </h1>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <input type="text" placeholder="Search roles..." 
            class="w-full md:w-1/3 bg-green-900/50 border border-green-800 p-3 rounded-xl focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-white mb-4 sm:mb-0">
        <button class="bg-green-700 hover:bg-green-600 px-5 py-2.5 rounded-xl font-medium transition shadow-lg shadow-green-900/40">
            <i class="fa-solid fa-plus mr-2"></i> Add New Role
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-2 space-y-4">
            <?php
            foreach($roles as $role): 
                $border_color = match ($role['color']) {
                    'red' => 'border-red-500',
                    'yellow' => 'border-yellow-500',
                    'blue' => 'border-blue-500',
                    'purple' => 'border-purple-500',
                    default => 'border-gray-500',
                };
                $description_color = match ($role['color']) {
                    'red' => 'text-red-400',
                    'yellow' => 'text-yellow-400',
                    'blue' => 'text-blue-400',
                    'purple' => 'text-purple-400',
                    default => 'text-gray-400',
                };
            ?>
            <div class="bg-green-950/50 p-5 rounded-xl border-l-4 <?= $border_color ?> flex flex-col sm:flex-row justify-between items-start sm:items-center shadow-lg hover:bg-green-900/40 transition cursor-pointer">
                <div class="flex flex-col mb-2 sm:mb-0">
                    <span class="text-lg font-semibold text-green-200"><i class="fa-solid fa-key mr-2"></i> <?= $role['name'] ?></span>
                    <div class="text-sm text-gray-500 mt-1 space-x-4">
                        <span title="Role description" class="text-gray-500"><?= $role['description'] ?></span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm font-medium <?= $description_color ?> hidden sm:block"><?= $role['access'] ?></span>
                    <button class="text-green-400 hover:text-green-300 transition text-sm">
                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($roles)): ?>
            <div class="p-8 text-center text-gray-500 bg-green-950/20 rounded-xl border border-green-800">
                <i class="fa-solid fa-lock text-4xl mb-3 text-green-500"></i>
                <p class="text-lg">No custom roles have been defined yet.</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-2xl h-full">
                <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2 border-b border-green-800/50 pb-2">
                    <i class="fa-solid fa-shield-alt text-lg"></i> Permissions Matrix
                </h2>
                <p class="text-sm text-gray-400 mb-4">Current Role: <span class="font-semibold text-red-400">Administrator</span></p>

                <div class="max-h-[500px] overflow-y-auto pr-2">
                    
                    <ul class="space-y-3 text-sm">
                        <li class="font-semibold text-green-200">Document Management</li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>View All Documents</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>Upload/Edit Any Document</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>Archive/Delete Documents</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>Manage Document Types</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                    </ul>

                    <ul class="space-y-3 text-sm pt-4">
                        <li class="font-semibold text-green-200">Review & Workflow</li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>Approve/Reject All</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>Assign/Reassign Reviews</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>View All Comments</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>Set Review Deadlines</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                    </ul>
                    
                    <ul class="space-y-3 text-sm pt-4">
                        <li class="font-semibold text-green-200">Organization & Reports</li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>Manage Members/Roles</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>Manage Departments</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>View All Reports</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                        <li class="flex justify-between border-b border-green-900/30 pb-1 text-gray-300">
                            <span>System Settings Access</span>
                            <i class="fa-solid fa-check text-green-500"></i>
                        </li>
                    </ul>
                </div>

                <div class="pt-6">
                    <button class="w-full bg-green-700 px-4 py-2 rounded-xl hover:bg-green-600 font-bold text-sm transition">
                        Save Role Permissions
                    </button>
                </div>
            </div>
        </div>

    </div> </div>

<?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>