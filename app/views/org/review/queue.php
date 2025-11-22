<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// NOTE: Helper functions (html_escape, csrf_field, etc.) are assumed to be 
// handled by app/views/org/layout_start.php.

// --- 1. Page Specific Variables ---
$title = 'Review Queue - Maestro UI';

// Filter/data variables passed from the controller (retained)
$q = $q ?? '';
$sort = $sort ?? 'oldest';
$reviews = $reviews ?? []; // Use data passed from controller

// --- 2. TEMPLATE INCLUSION ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" 
    x-data="{ 
        // Page-specific Alpine data for document viewing/archiving
        modalOpen: false, 
        currentDoc: { id: 0, title: '', file_name: '', status: 'Pending Review', submitter: '', type: '', created_at: '' },
        commentText: '', // Bind to the comment textarea

        getFileExtension(fileName) {
            return fileName ? fileName.split('.').pop().toLowerCase() : '';
        },
        isImage(fileName) {
            const ext = this.getFileExtension(fileName);
            return ['jpg', 'jpeg', 'png'].includes(ext);
        },
        isPDF(fileName) {
            return this.getFileExtension(fileName) === 'pdf';
        },
        toSentenceCase(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        },
        getDocViewerURL(fileName) {
            if (!fileName) return '';
            const publicUrl = BASE_URL + '/public/uploads/documents/' + fileName;
            const absoluteUrl = window.location.origin + publicUrl;

            if (this.isPDF(fileName)) {
                return publicUrl;
            }
            return 'https://docs.google.com/gview?url=' + encodeURIComponent(absoluteUrl) + '&embedded=true';
        },
        
        setDoc(doc) { 
            this.currentDoc = doc; 
            this.commentText = ''; // Clear comment field on new doc
            this.modalOpen = true; 
        }
    }" 
    @keydown.escape="modalOpen = false">

    <h1 class="text-3xl font-bold text-yellow-400 mb-6 tracking-wide">
        Review Queue
    </h1>

    <form method="GET" action="<?=$BASE_URL?>/org/review/queue" class="flex flex-col md:flex-row gap-4 mb-8">
        <input type="text" name="q" placeholder="Search by document title or submitter..." 
               value="<?= $q ?? '' ?>"
               class="w-full md:w-1/3 bg-yellow-900/50 border border-yellow-800 p-3 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 transition placeholder-gray-500 text-white">
        <select name="sort" class="w-full md:w-1/6 bg-yellow-900/50 border border-yellow-800 p-3 rounded-xl text-white">
            <option value="oldest" <?= ($sort ?? 'oldest') === 'oldest' ? 'selected' : '' ?>>Sort by Oldest</option>
            <option value="newest" <?= ($sort ?? '') === 'newest' ? 'selected' : '' ?>>Sort by Newest</option>
        </select>
        <button type="submit" class="bg-yellow-700 hover:bg-yellow-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-yellow-900/40">
            <i class="fa-solid fa-filter mr-2"></i> Apply
        </button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
        <table class="w-full text-left">
            
            <thead class="bg-yellow-900/40 text-gray-200 uppercase text-sm tracking-wider">
                <tr>
                    <th class="p-4 border-b border-green-800">Document Title</th>
                    <th class="p-4 border-b border-green-800">Submitted By</th>
                    <th class="p-4 border-b border-green-800">Submission Date</th>
                    <th class="p-4 border-b border-green-800">Days Pending</th>
                    <th class="p-4 border-b border-green-800 text-center">Action</th>
                </tr>
            </thead>
            
            <tbody class="bg-[#0f1511] text-gray-300">
                
                <?php 
                if (!empty($review_queue)): 
                foreach($review_queue as $doc): 
                    // Calculate days pending
                    $submission_date = strtotime($doc['created_at']);
                    $current_date = time();
                    $days_pending = floor(($current_date - $submission_date) / (60 * 60 * 24));
                    
                    // Apply special overdue styling if pending for more than 7 days
                    $row_class = $days_pending > 7 ? 'bg-red-900/10 hover:bg-red-900/20' : 'hover:bg-green-700/10';
                    $days_class = $days_pending > 7 ? 'text-red-400 font-bold' : 'text-yellow-400';
                    $display_date = date('M j, Y', $submission_date);

                    // FIX: Use null coalescing to safely access keys.
                    $submitter_name = trim(($doc['submitter_fname'] ?? '') . ' ' . ($doc['submitter_lname'] ?? ''));

                    // Prepare document data for Alpine.js modal (must be escaped for HTML attribute)
                    $js_doc = html_escape(json_encode([
                        'id' => $doc['id'], 
                        'title' => html_escape($doc['title']), 
                        'file_name' => $doc['file_name'], 
                        'status' => $doc['status'], 
                        'submitter' => html_escape($submitter_name),
                        'type' => $doc['type'],
                        'created_at' => $doc['created_at'] 
                    ]));
                ?>
                <tr class="border-b border-green-800 transition <?= $row_class ?>">
                    <td class="p-4 font-medium text-green-200"><?= htmlspecialchars($doc['title']) ?></td>
                    <td class="p-4"><?= $submitter_name ?></td>
                    <td class="p-4 text-sm text-gray-400"><?= date('M j, Y', $submission_date) ?></td>
                    <td class="p-4 <?= $days_class ?>"><?= $days_pending ?></td>
                    <td class="p-4 text-center">
                        <button @click="setDoc(<?= $js_doc ?>)" 
                            class="bg-yellow-700 hover:bg-yellow-600 px-4 py-2 rounded-lg text-sm font-semibold transition shadow-md shadow-yellow-900/30">
                            <i class="fa-solid fa-pen-to-square mr-1"></i> Review Now
                        </button>
                    </td>
                </tr>
                <?php endforeach; 
                else: ?>
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-500">
                        <i class="fa-solid fa-check-circle text-4xl mb-3 text-green-500"></i>
                        <p class="text-lg">The review queue is empty!</p>
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

    <div @click.outside="modalOpen = false" class="w-full max-w-7xl mx-auto bg-[#0f1511] rounded-xl shadow-2xl border border-green-800 h-[90vh] flex flex-col">
        
        <header class="p-4 border-b border-green-800 flex justify-between items-center bg-sidebar-dark border-yellow-500">
            <h3 class="text-xl font-bold" x-text="'Review: ' + currentDoc.title">Review: Document Title</h3>
            <button @click="modalOpen = false" class="text-gray-400 hover:text-white transition">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </header>

        <div class="review-content-grid flex-1 overflow-y-auto"> 
            
            <div class="pr-4 border-r border-green-800 flex flex-col">
                <h4 class="text-lg font-semibold text-gray-400 mb-3">Document Content 
                    <span x-text="'(' + getFileExtension(currentDoc.file_name).toUpperCase() + ' Viewer)'" class="text-yellow-400"></span>
                </h4>
                
                <div x-show="isImage(currentDoc.file_name)" 
                     class="w-full flex-1 border border-gray-700 rounded-lg bg-gray-900 flex items-center justify-center overflow-hidden">
                    <img :src="'<?= BASE_URL ?>/public/uploads/documents/' + currentDoc.file_name" 
                         :alt="'Viewing ' + currentDoc.title"
                         class="max-w-full max-h-full object-contain">
                </div>

                <div x-show="!isImage(currentDoc.file_name)" class="w-full flex-1 flex flex-col">
                    <iframe 
                        :src="getDocViewerURL(currentDoc.file_name)" 
                        class="w-full flex-1 border border-gray-700 rounded-lg bg-gray-900" 
                        frameborder="0"
                        allowfullscreen>
                    </iframe>

                    <template x-if="!isPDF(currentDoc.file_name)">
                        <div class="mt-2 text-center text-sm text-gray-500">
                            If the document viewer is blank or fails, use the direct download:
                            <a :href="'<?= BASE_URL ?>/public/uploads/documents/' + currentDoc.file_name" download
                               class="text-yellow-400 hover:text-yellow-200 underline">
                                <i class="fa-solid fa-download mr-1"></i> Direct Download (<span x-text="toSentenceCase(getFileExtension(currentDoc.file_name))"></span>)
                            </a>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pl-4 space-y-4 flex flex-col">
                
                <h4 class="text-lg font-semibold text-gray-400 border-b border-green-800 pb-2">Review Details</h4>

                <div class="bg-green-950/50 p-3 rounded-lg border border-green-800">
                    <p class="text-sm text-gray-400">Status: <span class="text-yellow-300" x-text="toSentenceCase(currentDoc.status)"></span></p>
                    <p class="text-sm text-gray-400">Type: <span x-text="toSentenceCase(currentDoc.type)"></span></p>
                    <p class="text-sm text-gray-400">Submitted By: <span x-text="currentDoc.submitter"></span></p>
                    <p class="text-sm text-gray-400">Submitted On: <span x-text="currentDoc.created_at ? new Date(currentDoc.created_at).toLocaleDateString('en-US') : 'N/A'"></span></p>
                </div>

                <div class="space-y-3">
                    <h5 class="text-md font-bold text-green-300 flex justify-between items-center">
                        Add Review Comment
                        <a :href="'<?= BASE_URL ?>/org/review/comments/' + currentDoc.id" 
                            class="text-xs text-yellow-400 hover:underline" target="_blank">
                            <i class="fa-solid fa-comments mr-1"></i> View All Comments
                        </a>
                    </h5>

                    <form method="POST" :action="'<?= BASE_URL ?>/org/review/add_comment'" class="space-y-2">
                        <?php if (function_exists('csrf_field')) csrf_field(); ?>
                        <input type="hidden" name="document_id" :value="currentDoc.id">
                        
                        <textarea name="comment_text" rows="3" placeholder="Enter your feedback here..." 
                            x-model="commentText"
                            class="w-full bg-gray-900 border border-green-800 p-2 rounded-lg text-sm text-white focus:ring-green-500 focus:border-green-500"></textarea>
                        
                        <button type="submit" 
                            :disabled="commentText.trim().length === 0"
                            :class="commentText.trim().length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-600'"
                            class="w-full bg-green-700 px-3 py-1 rounded-lg text-sm font-medium transition">
                            <i class="fa-solid fa-comment-dots mr-1"></i> Post Comment
                        </button>
                    </form>
                </div>
                
                <h5 class="text-md font-bold text-green-300 border-t border-green-800 pt-4">Update Document Status:</h5>
                <div class="space-y-4">
                    
                    <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                        <?php if (function_exists('csrf_field')) csrf_field(); ?>
                        <input type="hidden" name="document_id" :value="currentDoc.id">
                        <input type="hidden" name="new_status" value="Approved">
                        <input type="hidden" name="document_title" :value="currentDoc.title">

                        <button type="submit" class="w-full bg-green-700 hover:bg-green-600 px-5 py-2 rounded-lg font-medium transition"
                            :disabled="currentDoc.status !== 'Pending Review'"
                            :class="currentDoc.status !== 'Pending Review' ? 'opacity-50 cursor-not-allowed' : ''">
                            <i class="fa-solid fa-thumbs-up mr-2"></i> Approve
                        </button>
                    </form>

                    <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                        <?php if (function_exists('csrf_field')) csrf_field(); ?>
                        <input type="hidden" name="document_id" :value="currentDoc.id">
                        <input type="hidden" name="new_status" value="Rejected">
                        <input type="hidden" name="document_title" :value="currentDoc.title">
                        
                        <button type="submit" class="w-full bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition"
                            :disabled="currentDoc.status !== 'Pending Review'"
                            :class="currentDoc.status !== 'Pending Review' ? 'opacity-50 cursor-not-allowed' : ''">
                            <i class="fa-solid fa-thumbs-down mr-2"></i> Reject
                        </button>
                    </form>

                    <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                        <?php if (function_exists('csrf_field')) csrf_field(); ?>
                        <input type="hidden" name="document_id" :value="currentDoc.id">
                        <input type="hidden" name="new_status" value="Archived">
                        <input type="hidden" name="document_title" :value="currentDoc.title">

                        <button type="submit" onclick="return confirm('Confirm archive?')"
                            class="w-full bg-gray-700 hover:bg-gray-600 px-5 py-2 rounded-lg font-medium transition">
                            <i class="fa-solid fa-box-archive mr-2"></i> Archive
                        </button>
                    </form>
                </div>
                
                <button @click="modalOpen = false" class="w-full text-gray-500 hover:text-gray-300 transition mt-4">Close Review</button>

            </div>
        </div>
    </div>
</div>

<?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>