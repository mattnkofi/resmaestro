<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
// --- PHP Helper Functions ---
if (!defined('BASE_URL')) define('BASE_URL', '/maestro');
if (!function_exists('html_escape')) {
    function html_escape($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('csrf_field')) {
    function csrf_field() {
        echo '<input type="hidden" name="csrf_token" value="MOCK_CSRF_TOKEN">';
    }
}
// --- End Helper Functions ---

$reviewed_docs = $reviewed_docs ?? [];
$q = $q ?? '';
$status = $status ?? '';

// MOCKING CURRENT URI FOR SIDEBAR: 
$current_uri = $_SERVER['REQUEST_URI'] ?? '/org/review/comments'; 
$is_documents_open = str_contains($current_uri, '/org/documents/');
$is_review_open = str_contains($current_uri, '/org/review/');
$is_organization_open = str_contains($current_uri, '/org/members/') || str_contains($current_uri, '/org/departments') || str_contains($current_uri, '/org/roles');
$is_reports_open = str_contains($current_uri, '/org/reports/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment Threads - Maestro UI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .maestro-bg { background-color: #0b0f0c; } 
    </style>
</head>
<body class="bg-maestro-bg text-white font-poppins" 
    x-data="{ 
        modalOpen: false, 
        modalDoc: { id: 0, title: 'Loading...', status: '', reviewer: '', file_name: '', type: '' },
        comments: [],
        commentText: '',
        loading: false,

        toSentenceCase(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        },
        
        async fetchComments(docId) {
            this.modalOpen = true;
            this.loading = true;
            this.comments = [];
            this.modalDoc = { id: docId, title: 'Loading...', status: '', reviewer: '', file_name: '', type: '' }; // Reset doc
            this.commentText = '';
            
            try {
                // Fetch data using the AJAX endpoint
                const response = await fetch(`<?= BASE_URL ?>/org/review/fetch_comments/${docId}`);
                const data = await response.json();

                if (data.success && data.doc) {
                    // Update modalDoc with all fields, including status/title for the forms
                    this.modalDoc = {
                        id: data.doc.id,
                        title: data.doc.title,
                        status: data.doc.status,
                        reviewer: data.doc.reviewer_fname ? data.doc.reviewer_fname + ' ' + data.doc.reviewer_lname : 'N/A Reviewer',
                        file_name: data.doc.file_name,
                        type: data.doc.type,
                    };
                    this.comments = data.comments;
                } else {
                    this.modalDoc.title = 'Error loading document.';
                }

            } catch (error) {
                console.error('Error fetching comments:', error);
                this.modalDoc.title = 'Network Error';
            } finally {
                this.loading = false;
            }
        },

        formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true });
        }
    }" 
    @keydown.escape="modalOpen = false">

    <aside class="fixed top-0 left-0 h-full w-64 bg-[#0b0f0c] border-r border-green-900 text-white shadow-2xl flex flex-col transition-all duration-300 z-10">
        <div class="flex items-center justify-center py-6 border-b border-green-800">
            <img src="/public/maestrologo.png" alt="Logo" class="h-10 mr-8">
            <h1 class="text-2xl font-bold text-green-400 tracking-wider">MAESTRO</h1>
        </div>
        <nav class="flex-1 overflow-y-auto px-4 py-3 space-y-4">
            <div><h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">Main</h2>
                <a href="<?=BASE_URL?>/org/dashboard" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/50 transition">
                    <i class="fa-solid fa-gauge w-5 text-center"></i><span>Dashboard</span></a>
            </div>
            <div x-data='{ open: <?= $is_documents_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-file-lines w-5 text-center"></i><span>Documents</span></span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/documents/all" class="block p-2 rounded hover:bg-green-700/40 transition">All Documents</a>
                    <a href="<?=BASE_URL?>/org/documents/upload" class="block p-2 rounded hover:bg-green-700/40 transition">Upload New</a>
                    <a href="<?=BASE_URL?>/org/documents/pending" class="block p-2 rounded hover:bg-green-700/40 transition">Pending Review</a>
                    <a href="<?=BASE_URL?>/org/documents/approved" class="block p-2 rounded hover:bg-green-700/40 transition">Approved / Noted</a>
                    <a href="<?=BASE_URL?>/org/documents/rejected" class="block p-2 rounded hover:bg-green-700/40 transition">Rejected</a>
                    <a href="<?=BASE_URL?>/org/documents/archived" class="block p-2 rounded hover:bg-green-700/40 transition">Archived</a>
                </div>
            </div>
            <div x-data='{ open: <?= $is_review_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-clipboard-check w-5 text-center"></i><span>Reviews</span></span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/review/queue" class="block p-2 rounded hover:bg-green-700/40 transition">Pending Reviews</a>
                    <a href="<?=BASE_URL?>/org/review/history" class="block p-2 rounded hover:bg-green-700/40 transition">Review History</a>
                    <a href="<?=BASE_URL?>/org/review/comments" class="block p-2 rounded hover:bg-green-700/40 transition <?= str_contains($current_uri, '/org/review/comments') ? 'text-green-400 font-semibold' : '' ?>">Comment Threads</a>
                </div>
            </div>
            <div x-data='{ open: <?= $is_organization_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-users w-5 text-center"></i><span>Organization</span></span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/members/list" class="block p-2 rounded hover:bg-green-700/40 transition">Members</a>
                    <a href="<?=BASE_URL?>/org/members/add" class="block p-2 rounded hover:bg-green-700/40 transition">Add Member</a>
                    <a href="<?=BASE_URL?>/org/departments" class="block p-2 rounded hover:bg-green-700/40 transition">Departments</a>
                    <a href="<?=BASE_URL?>/org/roles" class="block p-2 rounded hover:bg-green-700/40 transition">Roles & Permissions</a>
                </div>
            </div>
            
            <div x-data='{ open: <?= $is_reports_open ? 'true' : 'false' ?> }' class="space-y-1">
                <button @click="open = !open" :class="open ? 'bg-green-900/30 text-green-300' : ''" class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-green-700/30 transition">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-chart-line w-5 text-center"></i><span>Analytics</span></span>
                    <i :class="open ? 'fa-chevron-up' : 'fa-chevron-down'" class="fa-solid text-xs transition-transform"></i>
                </button>
                <div x-show="open" x-transition.duration.300ms class="ml-6 mt-1 space-y-1 text-gray-300 text-sm border-l border-green-700/50 pl-2">
                    <a href="<?=BASE_URL?>/org/reports/overview" class="block p-2 rounded hover:bg-green-700/40 transition">Overview</a>
                    <a href="<?=BASE_URL?>/org/reports/documents" class="block p-2 rounded hover:bg-green-700/40 transition">Document Analytics</a>
                    <a href="<?=BASE_URL?>/org/reports/reviewers" class="block p-2 rounded hover:bg-green-700/40 transition">Reviewer Activity</a>
                    <a href="<?=BASE_URL?>/org/reports/storage" class="block p-2 rounded hover:bg-green-700/40 transition">Storage Usage</a>
                </div>
            </div>

            <div class="pt-4"><h2 class="text-xs font-semibold text-gray-500 uppercase mb-2 ml-2 tracking-wider">System</h2></div>
            <div>
                <a href="<?=BASE_URL?>/org/settings" class="flex items-center gap-3 p-3 rounded-lg hover:bg-green-700/30 transition">
                    <i class="fa-solid fa-gear w-5 text-center"></i><span>Settings</span></a>
            </div>
        </nav>
        <div class="border-t border-green-800 px-4 py-4">
             </div>
        <div class="border-t border-green-800 p-3 text-xs text-gray-500 text-center">
            Maestro Organization © <?=date('Y')?>
        </div>
    </aside>

    <div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white">
        
        <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
            Comment Threads Overview
        </h1>

        <?php if (function_exists('flash_alert')) flash_alert(); // Display flash messages ?>

        <form method="GET" action="<?= BASE_URL ?>/org/review/comments" class="flex flex-col md:flex-row gap-4 mb-8">
            <input type="text" name="q" placeholder="Search by document title or reviewer..." 
                   value="<?= $q ?>"
                   class="w-full md:w-1/3 bg-green-900/50 border border-green-800 p-3 rounded-xl focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-white">
            <select name="status" class="w-full md:w-1/6 bg-green-900/50 border border-green-800 p-3 rounded-xl text-white">
                <option value="">Filter by Status (All Commented)</option>
                <option value="Pending Review" <?= $status === 'Pending Review' ? 'selected' : '' ?>>Pending Review (Commented)</option>
                <option value="Approved" <?= $status === 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit" class="bg-green-700 hover:bg-green-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-green-900/40">
                <i class="fa-solid fa-filter mr-2"></i> Apply Filters
            </button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
            <table class="w-full text-left">
                
                <thead class="bg-yellow-900/40 text-gray-200 uppercase text-sm tracking-wider">
                    <tr>
                        <th class="p-4 border-b border-green-800">Document Title</th>
                        <th class="p-4 border-b border-green-800">Status</th>
                        <th class="p-4 border-b border-green-800">Final Reviewer</th>
                        <th class="p-4 border-b border-green-800">Review Date</th>
                        <th class="p-4 border-b border-green-800 text-center">Action</th>
                    </tr>
                </thead>
                
                <tbody class="bg-[#0f1511] text-gray-300">
                    
                    <?php if (!empty($reviewed_docs)): 
                    foreach($reviewed_docs as $doc): 
                        $doc_status = htmlspecialchars($doc['status']);
                        $status_class = $doc_status == 'Approved' ? 'text-green-400' : ($doc_status == 'Rejected' ? 'text-red-400' : 'text-yellow-400');
                        
                        // Determine the review date
                        if ($doc_status == 'Approved' && !empty($doc['approved_at'])) {
                            $review_date = date('M j, Y', strtotime($doc['approved_at']));
                        } elseif ($doc_status == 'Rejected' && !empty($doc['rejected_at'])) {
                            $review_date = date('M j, Y', strtotime($doc['rejected_at']));
                        } else {
                            // If pending/archived, show submission date
                            $review_date = date('M j, Y', strtotime($doc['created_at']));
                        }
                        
                        // Safely access reviewer names
                        $reviewer_name = trim(($doc['reviewer_fname'] ?? '') . ' ' . ($doc['reviewer_lname'] ?? ''));
                        $reviewer_name = $reviewer_name ?: 'N/A'; // Only show if Approved/Rejected
                    ?>
                    <tr class="border-b border-green-800 transition hover:bg-green-700/10">
                        <td class="p-4 font-medium text-green-200"><?= html_escape($doc['title']) ?></td>
                        <td class="p-4 <?= $status_class ?> font-medium"><?= $doc_status ?></td>
                        <td class="p-4"><?= html_escape($reviewer_name) ?></td>
                        <td class="p-4 text-sm text-gray-400"><?= $review_date ?></td>
                        <td class="p-4 text-center">
                            <button @click="fetchComments(<?= $doc['id'] ?>)" 
                                class="bg-yellow-700 hover:bg-yellow-600 px-4 py-2 rounded-lg text-sm font-semibold transition shadow-md shadow-yellow-900/30">
                                <i class="fa-solid fa-comment-dots mr-1"></i> View/Update
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; 
                    else: ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            <i class="fa-solid fa-list-check text-4xl mb-3 text-green-500"></i>
                            <p class="text-lg">No documents match the current criteria.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                </tbody>
            </table>
        </div>

    </div>

    <div x-show="modalOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:leave="ease-in duration-200"
        class="fixed inset-0 z-50 overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center" 
        style="display: none;">

        <div @click.outside="modalOpen = false" class="w-full max-w-4xl mx-auto bg-[#0f1511] rounded-xl shadow-2xl border border-green-800 h-[80vh] flex flex-col">
            
            <header class="p-4 border-b border-green-800 flex justify-between items-center bg-sidebar-dark">
                <h3 class="text-xl font-bold text-green-300" x-text="'Comments for: ' + modalDoc.title"></h3>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </header>

            <div class="flex-1 overflow-y-auto p-6 grid grid-cols-3 gap-6"> 
                
                <div class="col-span-1 space-y-4">
                    <div class="bg-green-950/50 p-4 rounded-lg border border-green-800 text-sm text-gray-300">
                        <p>Status: <span :class="modalDoc.status === 'Approved' ? 'text-green-300' : (modalDoc.status === 'Rejected' ? 'text-red-300' : 'text-yellow-300')" x-text="toSentenceCase(modalDoc.status)"></span></p>
                        <p>Final Reviewer: <span x-text="modalDoc.reviewer"></span></p>
                    </div>

                    <h5 class="text-md font-bold text-yellow-300 border-b border-green-800 pb-2">Update Status:</h5>

                    <div class="space-y-3">
                        <form method="POST" action="<?= BASE_URL ?>/org/documents/update_status">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="document_id" :value="modalDoc.id">
                            <input type="hidden" name="new_status" value="Approved">
                            <input type="hidden" name="document_title" :value="modalDoc.title">

                            <button type="submit" class="w-full bg-green-700 hover:bg-green-600 px-3 py-2 rounded-lg font-medium transition text-sm"
                                :disabled="modalDoc.status !== 'Pending Review'"
                                :class="modalDoc.status !== 'Pending Review' ? 'opacity-50 cursor-not-allowed' : ''">
                                <i class="fa-solid fa-thumbs-up mr-2"></i> Approve
                            </button>
                        </form>

                        <form method="POST" action="<?= BASE_URL ?>/org/documents/update_status">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="document_id" :value="modalDoc.id">
                            <input type="hidden" name="new_status" value="Rejected">
                            <input type="hidden" name="document_title" :value="modalDoc.title">
                            
                            <button type="submit" class="w-full bg-red-700 hover:bg-red-600 px-3 py-2 rounded-lg font-medium transition text-sm"
                                :disabled="modalDoc.status !== 'Pending Review'"
                                :class="modalDoc.status !== 'Pending Review' ? 'opacity-50 cursor-not-allowed' : ''">
                                <i class="fa-solid fa-thumbs-down mr-2"></i> Reject
                            </button>
                        </form>

                        <form method="POST" action="<?= BASE_URL ?>/org/documents/update_status">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="document_id" :value="modalDoc.id">
                            <input type="hidden" name="new_status" value="Archived">
                            <input type="hidden" name="document_title" :value="modalDoc.title">

                            <button type="submit" onclick="return confirm('Confirm archive?')"
                                class="w-full bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded-lg font-medium transition text-sm"
                                :disabled="modalDoc.status === 'Archived'"
                                :class="modalDoc.status === 'Archived' ? 'opacity-50 cursor-not-allowed' : ''">
                                <i class="fa-solid fa-box-archive mr-2"></i> Archive
                            </button>
                        </form>

                        <a :href="'<?= BASE_URL ?>/public/uploads/documents/' + modalDoc.file_name" target="_blank" class="w-full inline-block text-center bg-gray-800 hover:bg-gray-700 px-3 py-2 rounded-lg font-medium transition text-sm">
                            <i class="fa-solid fa-file-download mr-1"></i> Download File
                        </a>
                    </div>
                </div>

                <div class="col-span-2 space-y-4">
                    
                    <h4 class="text-lg font-semibold text-green-400 border-b border-green-800 pb-2">Discussion:</h4>

                    <div x-show="loading" class="text-center py-12 text-yellow-400">
                        <i class="fa-solid fa-spinner fa-spin-pulse text-3xl mb-3"></i>
                        <p>Fetching comments...</p>
                    </div>

                    <template x-if="comments.length > 0 && !loading">
                        <div class="space-y-3 max-h-[30vh] overflow-y-auto">
                            <template x-for="comment in comments" :key="comment.id">
                                <div class="bg-green-950/40 p-4 rounded-lg border border-green-800 shadow-md">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-semibold text-yellow-300" x-text="comment.fname + ' ' + comment.lname"></span>
                                        <span class="text-xs text-gray-500" x-text="formatDate(comment.created_at)"></span>
                                    </div>
                                    <p class="text-sm text-gray-200 leading-relaxed" x-text="comment.comment"></p>
                                </div>
                            </template>
                        </div>
                    </template>
                    
                    <template x-if="comments.length === 0 && !loading">
                        <div class="p-5 text-center text-gray-500 bg-green-950/20 rounded-xl border border-green-800">
                            <i class="fa-solid fa-comment-slash text-3xl mb-2 text-gray-500"></i>
                            <p>No comments have been added for this document yet.</p>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="!loading" class="p-6 border-t border-green-800 bg-sidebar-dark">
                <h4 class="text-md font-bold text-green-300 mb-3">Add New Comment:</h4>
                <form method="POST" :action="'<?= BASE_URL ?>/org/review/add_comment'" class="space-y-3">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="document_id" :value="modalDoc.id">
                    
                    <textarea name="comment_text" rows="2" placeholder="Enter your feedback here..." 
                        x-model="commentText"
                        class="w-full bg-gray-900 border border-green-800 p-3 rounded-lg text-sm text-white focus:ring-green-500 focus:border-green-500"></textarea>
                    
                    <button type="submit" 
                        :disabled="commentText.trim().length === 0"
                        :class="commentText.trim().length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-600'"
                        class="w-full bg-green-700 px-5 py-2 rounded-lg font-medium transition">
                        <i class="fa-solid fa-comment-dots mr-1"></i> Post Comment
                    </button>
                </form>
            </div>
        </div>
    </div>
    </body>
</html>