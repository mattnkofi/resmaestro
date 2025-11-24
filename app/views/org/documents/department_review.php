<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// --- PHP Helper Functions ---
if (!defined('BASE_URL')) define('BASE_URL', '/maestro');
if (!function_exists('html_escape')) {
    function html_escape($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('csrf_field')) {
    function csrf_field() { echo '<input type="hidden" name="csrf_token" value="MOCK_CSRF_TOKEN">'; }
}
// --- End Helper Functions ---

$q = $q ?? '';
$dept_name = $dept_name ?? 'Department';
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/documents/department_review'; 
$is_documents_open = str_contains($current_uri, '/org/documents/');
$is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Review - Maestro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .maestro-bg { background-color: #0b0f0c; } 
    </style>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sidebar-dark': '#0f1511',
                        'maestro-bg': '#0b0f0c',
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                        sans: ['Poppins', 'sans-serif'], 
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-maestro-bg text-white font-poppins" x-data="{ modalOpen: false, currentDoc: {} }" @keydown.escape="modalOpen = false">

    <aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col transition-all duration-300 z-10">
        <div class="flex items-center justify-center py-6 border-b border-green-800">
            <img src="/public/maestrologo.png" alt="Logo" class="h-10 mr-8">
            <h1 class="text-2xl font-bold text-green-400 tracking-wider">MAESTRO</h1>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-4">
            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">Main</h2>
                <a href="<?=BASE_URL?>/org/dashboard" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/50 transition
                    <?= $current_uri == BASE_URL.'/org/dashboard' ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">
                    <i class="fa-solid fa-gauge w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <div class="space-y-1">
                <div class="w-full flex items-center justify-between p-3 rounded-lg bg-green-900/10 text-green-300">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-file-lines w-5 text-center"></i>
                        <span><b>Documents</b></span>
                    </span>
                </div>
                <div class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/documents/all" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/all') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">All Documents</a>
                    <a href="<?=BASE_URL?>/org/documents/department_review" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/department_review') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Dept. Documents</a>
                    <a href="<?=BASE_URL?>/org/documents/upload" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/upload') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Upload New</a>
                    <a href="<?=BASE_URL?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/approved') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Approved / Noted</a>
                    <a href="<?=BASE_URL?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/documents/rejected') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Rejected</a>
                </div>

                <div class="h-4"></div>

                <div class="w-full flex items-center justify-between p-3 rounded-lg bg-green-900/10 text-green-300">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-users w-5 text-center"></i>
                        <span><b>Organization</b></span>
                    </span>
                </div>
                <div class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/list') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Members</a>
                    <a href="<?=BASE_URL?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/members/add') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Add Member</a>
                    <a href="<?=BASE_URL?>/org/departments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/departments') ? 'text-green-400 font-semibold bg-green-900/40' : '' ?>">Departments</a>
                </div>
            </div>
            
            <div class="pt-4">
                <h2 class="text-xs text-gray-500 uppercase mb-2 ml-2 tracking-wider font-semibold">System</h2>
            </div>
        </nav>

        <div class="border-t border-green-800 px-4 py-4">
            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <button @click="open = !open" class="flex items-center justify-between w-full p-2 bg-green-900/30 rounded-lg hover:bg-green-700/40 transition">
                    <div class="flex items-center gap-3">
                        <img src="https://placehold.co/32x32/0b0f0c/10b981?text=U" alt="User" class="h-8 w-8 rounded-full border-2 border-green-600 ring-1 ring-green-400 object-cover">
                        <div class="text-left">
                            <p class="text-sm font-semibold text-green-300 truncate max-w-[100px]"><?= $_SESSION['user_name'] ?? 'User Name' ?></p>
                            <p class="text-xs text-gray-400"><?= $_SESSION['user_role'] ?? 'Organization Admin' ?></p>
                        </div>
                    </div>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs text-gray-400 ml-2"></i>
                </button>

                <div x-show="open" x-transition.duration.200ms class="absolute bottom-full mb-3 left-0 w-full bg-[#151a17] border border-green-700 rounded-lg shadow-2xl text-sm z-20">
                    <a href="<?=BASE_URL?>/org/profile" class="block px-4 py-2 hover:bg-green-700/30 rounded-t-lg transition">View Profile</a>
                    <a href="<?=BASE_URL?>/logout" class="block px-4 py-2 text-red-400 hover:bg-red-700/30 rounded-b-lg transition">Logout</a>
                </div>
            </div>
        </div>

        <div class="border-t border-green-800 p-3 text-xs text-gray-500 text-center">
            Maestro Organization © <?=date('Y')?>
        </div>
    </aside>

    <div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white">
        
        <h1 class="text-3xl font-bold text-green-400 mb-2 tracking-wide">
            Department Documents: <?= html_escape($dept_name) ?>
        </h1>
        <p class="text-gray-500 mb-6">Documents uploaded by members in your department, including reviewer feedback.</p>

        <?php if (function_exists('flash_alert')) flash_alert(); ?>

        <?php 
        if (!($is_member_assigned ?? true)): 
        ?>
            <div class="p-8 text-center text-green-400 bg-green-950/20 rounded-xl border-2 border-green-700 shadow-xl mt-8">
                <i class="fa-solid fa-users-slash text-5xl mb-4"></i>
                <p class="text-xl font-bold">You are not a member of any department.</p>
                <p class="text-gray-400 mt-2">
                    To access Department Documents, you must be assigned to a department by an Organization Admin.
                </p>
                <p class="text-sm text-gray-500 mt-4">
                    Contact your organization administrator for assistance with membership assignment.
                </p>
            </div>
        <?php else: ?>
        
            <form method="GET" action="<?= BASE_URL ?>/org/documents/department_review">
                <div class="flex flex-col md:flex-row gap-4 mb-8 items-center">
                    <input type="text" name="q" value="<?= html_escape($q) ?>"
                            placeholder="Search by title or submitter name..." 
                            class="w-full md:w-1/3 bg-green-900/50 border border-green-800 p-3 rounded-xl focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-white">
                    
                    <button type="submit" class="bg-green-700 hover:bg-green-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-green-900/40">
                        <i class="fa-solid fa-search mr-2"></i> Search
                    </button>
                    
                    <?php if (!empty($q)): ?>
                        <a href="<?= BASE_URL ?>/org/documents/department_review" class="bg-gray-700 hover:bg-gray-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-gray-900/40">
                            <i class="fa-solid fa-xmark mr-2"></i> Clear Search
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="space-y-4">
                <?php
                $docs = $docs ?? [];
                if (!empty($docs)):
                    foreach($docs as $doc): 

                        $doc_status = $doc['status'] ?? 'N/A';
                        $submitter_name = html_escape(($doc['fname'] ?? '') . ' ' . ($doc['lname'] ?? ''));

                        // Determine status properties for styling
                        $status_class = match ($doc_status) {
                            'Approved' => 'border-green-500',
                            'Pending Review' => 'border-yellow-500',
                            'Rejected' => 'border-red-500',
                            default => 'border-gray-500',
                        };
                        $status_text_class = match ($doc_status) {
                            'Approved' => 'text-green-300',
                            'Pending Review' => 'text-yellow-300',
                            'Rejected' => 'text-red-300',
                            default => 'text-gray-300',
                        };
                        $status_icon = match ($doc_status) {
                            'Approved' => 'fa-circle-check',
                            'Pending Review' => 'fa-hourglass-half',
                            'Rejected' => 'fa-circle-xmark',
                            default => 'fa-file',
                        };
                        
                        $review_comment = $doc['review_comment'] ?? 'No formal feedback provided yet.';
                    ?>
                    <div class="bg-green-950/50 p-5 rounded-xl border-l-4 <?= $status_class ?> flex flex-col md:flex-row justify-between items-start md:items-center shadow-lg hover:bg-green-900/40 transition">
                        <div class="flex flex-col mb-2 md:mb-0 w-full md:w-3/5">
                            <span class="text-lg font-bold text-white"><?= html_escape($doc['title']) ?></span>
                            <div class="flex items-center space-x-4 text-sm text-gray-400 mt-1">
                                <span><i class="fa-solid fa-user-edit mr-1"></i> Submitted by: <?= $submitter_name ?></span>
                                <span><i class="fa-solid fa-calendar-alt mr-1"></i> Date: <?= date('M d, Y', strtotime($doc['created_at'])) ?></span>
                            </div>
                            
                            <div class="mt-3 p-3 rounded-lg bg-green-900/70 border border-green-800">
                                <span class="text-xs font-semibold text-gray-400 block mb-1">Reviewer Feedback:</span>
                                <p class="text-sm text-white italic"><?= html_escape($review_comment) ?></p>
                            </div>
                        </div>
                        
                        <div class="flex flex-col items-start md:items-end space-y-2 w-full md:w-2/5">
                            <span class="text-md font-bold <?= $status_text_class ?>">
                                <i class="fa-solid <?= $status_icon ?> mr-2"></i> <?= html_escape($doc_status) ?>
                            </span>
                            
                            <a href="<?=BASE_URL?>/public/uploads/documents/<?= urlencode($doc['file_name']) ?>" 
                               target="_blank" 
                               class="bg-green-700 hover:bg-green-600 px-4 py-2 rounded-lg text-sm font-medium transition">
                                <i class="fa-solid fa-download mr-1"></i> Download File
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="p-8 text-center text-gray-500 bg-green-950/50 rounded-xl border border-green-800">
                    <i class="fa-solid fa-folder-open text-4xl mb-3 text-yellow-500"></i>
                    <p class="text-lg">No documents found for your department (<?= html_escape($dept_name) ?>).</p>
                    <p class="text-sm mt-2">Documents will appear here after a member of your department uploads them.</p>
                </div>
                <?php endif; ?>
            </div>
        <?php endif;