<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// --- 1. Page Specific Variables ---
$title = 'Rejected Documents - Maestro';

// Helper function definitions (html_escape, csrf_field, etc.) are assumed to be 
// handled by app/views/org/layout_start.php.

// Filter/data variables passed from the controller (retained)
$docs = $docs ?? [];
$reviewers = $reviewers ?? []; 
$doc_types = $doc_types ?? ['Finance', 'Budget', 'Accomplishment', 'Proposal', 'Legal', 'Other']; 

// --- 2. TEMPLATE INCLUSION ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" 
    x-data="{ 
        resubmitModalOpen: false,
        currentDoc: { id: 0, title: '', description: '', type: '', reviewer_id: '' },
        setDocForResubmit(doc) {
            this.currentDoc = doc;
            this.resubmitModalOpen = true;
        }
    }"
    @keydown.escape="resubmitModalOpen = false">
    
    <h1 class="text-3xl font-bold text-red-400 mb-6 tracking-wide">
        Rejected Documents
    </h1>

    <?php if (function_exists('flash_alert')) flash_alert(); ?>

    <div class="space-y-4">
        <?php
        foreach($docs as $doc): 
            
            // Safely retrieve reviewer name and rejection date
            $reviewer_fname = $doc->reviewer_fname ?? $doc['reviewer_fname'] ?? 'System';
            $reviewer_lname = $doc->reviewer_lname ?? $doc['reviewer_lname'] ?? '';
            $reviewer_name = html_escape(trim($reviewer_fname . ' ' . $reviewer_lname));

            $rejection_date = date('M d, Y', strtotime($doc->created_at ?? $doc['created_at'] ?? 'now'));
            $reason = $doc->description ?? $doc['description'] ?? 'No reason provided';
            $doc_id = $doc->id ?? $doc['id'] ?? 0;
            $title = $doc->title ?? $doc['title'] ?? '';
            $type = $doc->type ?? $doc['type'] ?? '';
            $reviewer_id = $doc->reviewer_id ?? $doc['reviewer_id'] ?? '';
        ?>
        <div class="bg-red-950/20 p-5 rounded-xl border-l-4 border-red-500 flex flex-col md:flex-row justify-between items-start md:items-center shadow-lg hover:bg-red-900/30 transition">
            <div class="flex flex-col mb-2 md:mb-0">
                <span class="text-lg font-semibold text-red-200"><?= html_escape($title) ?></span>
                <span class="text-sm text-gray-400">Rejected By: <?= $reviewer_name ?> on <?= $rejection_date ?></span>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-right hidden sm:block">
                    <span class="block text-sm text-red-400 font-medium max-w-[200px] truncate" title="<?= html_escape($reason) ?>"><?= html_escape($reason) ?></span>
                </div>
                <button @click="setDocForResubmit({ 
                    id: <?= $doc_id ?>, 
                    title: '<?= html_escape($title) ?>', 
                    description: '<?= html_escape($reason) ?>', 
                    type: '<?= html_escape($type) ?>',
                    reviewer_id: '<?= html_escape($reviewer_id) ?>'
                })" 
                class="bg-red-700 hover:bg-red-600 px-4 py-2 rounded-lg text-sm transition">
                    <i class="fa-solid fa-paper-plane mr-1"></i> Resubmit
                </button>
                <button class="text-gray-500 hover:text-red-300 transition text-sm">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($docs)): ?>
        <div class="p-8 text-center text-gray-500 bg-red-950/20 rounded-xl border border-red-800">
            <i class="fa-solid fa-thumbs-down text-4xl mb-3 text-red-500"></i>
            <p class="text-lg">No documents have been rejected!</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<div x-show="resubmitModalOpen" 
    x-transition:enter="ease-out duration-300"
    x-transition:leave="ease-in duration-200"
    class="fixed inset-0 z-50 overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center" 
    style="display: none;">

    <div @click.outside="resubmitModalOpen = false" class="w-full max-w-2xl mx-auto bg-[#0f1511] rounded-xl shadow-2xl border border-red-800 flex flex-col">
        
        <header class="p-4 border-b border-red-800 flex justify-between items-center bg-sidebar-dark">
            <h3 class="text-xl font-bold text-red-300" x-text="'Resubmit: ' + currentDoc.title">Resubmit Document</h3>
            <button type="button" @click="resubmitModalOpen = false" class="text-gray-400 hover:text-white transition">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </header>

        <div class="p-6">
            <p class="text-sm text-gray-400 mb-4">Make the necessary corrections and resubmit this document for review.</p>
            
            <form method="POST" action="<?= BASE_URL ?>/org/documents/resubmit" enctype="multipart/form-data" class="space-y-4">
                
                <input type="hidden" name="document_id" :value="currentDoc.id">

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-300 mb-1">Document Title</label>
                    <input type="text" name="title" id="title" :value="currentDoc.title" required
                            class="w-full bg-gray-900 border border-gray-700 p-3 rounded-lg text-white focus:ring-red-500 focus:border-red-500">
                </div>

                <div class="flex space-x-4">
                    <div class="flex-1">
                        <label for="type" class="block text-sm font-medium text-gray-300 mb-1">Document Type</label>
                        <select name="type" id="type" :value="currentDoc.type" required
                                class="w-full bg-gray-900 border border-gray-700 p-3 rounded-lg text-white focus:ring-red-500 focus:border-red-500">
                            <template x-for="docType in <?= htmlspecialchars(json_encode($doc_types)) ?>" :key="docType">
                                <option :value="docType.toLowerCase()" :selected="docType.toLowerCase() === currentDoc.type.toLowerCase()" x-text="docType"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div class="flex-1">
                        <label for="reviewer" class="block text-sm font-medium text-gray-300 mb-1">Select Reviewer</label>
                        <select name="reviewer" id="reviewer" 
                                class="w-full bg-gray-900 border border-gray-700 p-3 rounded-lg text-white focus:ring-red-500 focus:border-red-500">
                            <option value="">Auto-Assign (Optional)</option>
                            <?php 
                            // Loop through dynamically provided reviewers
                            if (!empty($reviewers) && (is_array($reviewers) || is_object($reviewers))):
                                foreach($reviewers as $reviewer): 
                                    $rev_id = $reviewer->id ?? $reviewer['id'] ?? null;
                                    $rev_fname = $reviewer->fname ?? $reviewer['fname'] ?? '';
                                    $rev_lname = $reviewer->lname ?? $reviewer['lname'] ?? '';
                                    if ($rev_id !== null):
                            ?>
                                <option value="<?= $rev_id ?>" :selected="!isNaN(currentDoc.reviewer_id) && <?= $rev_id ?> == currentDoc.reviewer_id"><?= html_escape($rev_fname . ' ' . $rev_lname) ?></option>
                            <?php 
                                    endif; 
                                endforeach; 
                            endif; 
                            ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-1">Notes/Corrections Made</label>
                    <textarea name="description" id="description" rows="3" 
                            class="w-full bg-gray-900 border border-gray-700 p-3 rounded-lg text-white focus:ring-red-500 focus:border-red-500"
                            x-model="currentDoc.description"
                            placeholder="Explain the corrections made or the reason for resubmission."></textarea>
                </div>

                <div>
                    <label for="document_file" class="block text-sm font-medium text-gray-300 mb-1">Upload New File (Optional)</label>
                    <input type="file" name="document_file" id="document_file" 
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                    <p class="text-xs text-gray-500 mt-1">Leave blank to resubmit with the existing file.</p>
                </div>

                <div class="flex justify-end space-x-4 pt-2">
                    <button type="button" @click="resubmitModalOpen = false" class="text-gray-400 hover:text-white transition px-4 py-2">
                        Cancel
                    </button>
                    <button type="submit" class="bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition shadow-lg shadow-red-900/40">
                        <i class="fa-solid fa-paper-plane mr-2"></i> Confirm Resubmit
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>