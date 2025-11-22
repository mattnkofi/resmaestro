<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// --- PHP Helper Functions ---
// NOTE: Helper functions (html_escape, csrf_field, etc.) are assumed to be 
// handled by app/views/org/layout_start.php.
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

// --- 1. Page Specific Variables ---
$title = 'Comment Threads - Maestro UI';
$reviewed_docs = $reviewed_docs ?? [];
$q = $q ?? '';
$status = $status ?? '';

// The sidebar state logic is handled by the included sidebar.php and is redundant here.

// --- 2. TEMPLATE INCLUSION ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg text-white font-poppins min-h-screen" 
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
                // BASE_URL is available via layout_start.php's Alpine scope
                const response = await fetch(`${BASE_URL}/org/review/fetch_comments/${docId}`);
                const data = await response.json();

                if (data.success && data.doc) {
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

<?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>