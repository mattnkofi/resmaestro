<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// --- 1. Page Specific Variables ---
$title = 'Pending Documents - Maestro';

// Filter variables passed from the controller (retained)
$q = $q ?? '';
$type = $type ?? '';
$status = $status ?? '';
$docs = $docs ?? []; 

// All helper functions (html_escape, csrf_field, etc.) are assumed to be 
// handled by app/views/org/layout_start.php.

// --- 2. TEMPLATE INCLUSION (Using requested absolute paths) ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" 
    x-data="{ 
        // Page-specific Alpine data for document viewing/archiving
        modalOpen: false, 
        currentDoc: { id: 0, title: '', file_name: '', status: '', submitter: '', type: '', created_at: '' },
        
        archiveModalOpen: false,
        docToArchive: { id: 0, title: '' },

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
            // BASE_URL is available from layout_start.php scope
            const publicUrl = BASE_URL + '/public/uploads/documents/' + fileName;
            const absoluteUrl = window.location.origin + publicUrl;

            if (this.isPDF(fileName)) {
                return publicUrl;
            }
            if (this.isImage(fileName)) {
                return publicUrl;
            }
            
            return 'https://docs.google.com/gview?url=' + encodeURIComponent(absoluteUrl) + '&embedded=true';
        },
        
        setDoc(doc) { 
            this.currentDoc = doc; 
            this.modalOpen = true; 
        },
        setArchiveDoc(doc) {
            this.docToArchive = { id: doc.id, title: doc.title };
            this.modalOpen = false;
            this.archiveModalOpen = true;
        }
    }" 
    @keydown.escape="modalOpen = false; archiveModalOpen = false">

    <h1 class="text-3xl font-bold text-yellow-400 mb-6 tracking-wide">
        Pending Documents
    </h1>

    <?php if (function_exists('flash_alert')) flash_alert(); ?>

    <form method="GET" action="<?= BASE_URL ?>/org/documents/pending">
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            
            <input type="text" name="q" placeholder="Search by title, author, or date..." 
                    value="<?= html_escape($q) ?>"
                    class="w-full md:w-1/3 bg-green-900 border border-green-800 p-3 rounded-xl focus:ring-yellow-500 focus:border-yellow-500 transition placeholder-gray-500 text-green-100">
            
            <select name="type" class="w-full md:w-1/6 bg-green-900 border border-green-800 p-3 rounded-xl text-green-100">
                <option value="">Filter by Type</option>
                <?php 
                $doc_types = ['Finance', 'Budget', 'Accomplishment', 'Proposal', 'Legal', 'Other'];
                foreach ($doc_types as $doc_type): ?>
                    <option value="<?= html_escape(strtolower($doc_type)) ?>" 
                        <?= (strtolower($doc_type) === strtolower($type)) ? 'selected' : '' ?>>
                        <?= $doc_type ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="bg-yellow-700 hover:bg-yellow-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-yellow-900/40">
                <i class="fa-solid fa-filter mr-2"></i> Apply Filters
            </button>
            
            <?php if (!empty($q) || !empty($type)): ?>
                <a href="<?= BASE_URL ?>/org/documents/pending" class="bg-gray-700 hover:bg-gray-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-gray-900/40">
                    <i class="fa-solid fa-xmark mr-2"></i> Clear
                </a>
            <?php endif; ?>
        </div>
    </form>


    <div class="overflow-x-auto rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
        <table class="w-full text-left">
            
            <thead class="bg-yellow-900/40 text-gray-200 uppercase text-sm tracking-wider">
                <tr>
                    <th class="p-4 border-b border-green-800">Title</th>
                    <th class="p-4 border-b border-green-800">Submitted By</th>
                    <th class="p-4 border-b border-green-800">Submission Date</th>
                    <th class="p-4 border-b border-green-800">Days Pending</th>
                    <th class="p-4 border-b border-green-800 text-center">Actions</th>
                </tr>
            </thead>
            
            <tbody class="bg-[#0f1511] text-gray-300">

<?php 
foreach($docs as $doc): 
    // Safely extract ALL data fields
    $doc_id = $doc->id ?? $doc['id'] ?? 0;
    $doc_title = $doc->title ?? $doc['title'] ?? 'Document';
    $doc_file_name = $doc->file_name ?? $doc['file_name'] ?? '';
    $doc_status = $doc->status ?? $doc['status'] ?? 'Pending Review';
    $doc_fname = $doc->fname ?? $doc['fname'] ?? '';
    $doc_lname = $doc->lname ?? $doc['lname'] ?? '';
    $doc_type = $doc->type ?? $doc['type'] ?? '';
    $doc_created_at = $doc->created_at ?? $doc['created_at'] ?? 'now';
    
    $submit_time = strtotime($doc_created_at);
    $days_pending = round((time() - $submit_time) / (60 * 60 * 24));
    
    $row_class = $days_pending > 7 ? 'bg-red-900/10 hover:bg-red-900/20' : 'hover:bg-green-700/10';
    $days_class = $days_pending > 7 ? 'text-red-400 font-bold' : 'text-yellow-400';
    $display_date = date('M d, Y', $submit_time);

    $js_doc = html_escape(json_encode([
        'id' => $doc_id, 
        'title' => html_escape($doc_title), 
        'file_name' => $doc_file_name, 
        'status' => $doc_status, 
        'submitter' => html_escape($doc_fname . ' ' . $doc_lname),
        'type' => $doc_type,
        'created_at' => $doc_created_at 
    ]));
?>

<tr class="border-b border-green-800 transition <?= $row_class ?>">
    <td class="p-4 font-medium text-green-200"><?= html_escape($doc_title) ?></td>
    <td class="p-4"><?= html_escape($doc_fname . ' ' . $doc_lname) ?></td>
    <td class="p-4 text-sm text-gray-400"><?= $display_date ?></td>
    <td class="p-4 <?= $days_class ?>"><?= $days_pending ?></td>
    <td class="p-4 text-center">
        <button @click="setDoc(<?= $js_doc ?>)" 
            class="text-yellow-400 hover:text-yellow-200 hover:underline transition font-medium mr-4">
            <i class="fa-solid fa-pen-to-square mr-1"></i> Review
        </button>
        <button class="text-gray-500 hover:text-gray-300 transition text-sm">
            <i class="fa-solid fa-clock mr-1"></i> Remind
        </button>
    </td>
</tr>
<?php endforeach; ?>

<?php if (empty($docs)): ?>
<tr>
    <td colspan="5" class="p-8 text-center text-gray-500">
        <i class="fa-solid fa-check-circle text-4xl mb-3 text-green-500"></i>
        <p class="text-lg">No documents are currently pending review!</p>
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
        
        <header class="p-4 border-b border-green-800 flex justify-between items-center bg-sidebar-dark" :class="currentDoc.status === 'Approved' ? 'border-green-500' : 'border-yellow-500'">
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

            <div class="pl-4 space-y-6 flex flex-col">
    <h4 class="text-lg font-semibold text-gray-400">Review Details & Status</h4>

    <div class="bg-green-950/50 p-4 rounded-lg border border-green-800">
        <p class="text-sm text-gray-400">Status: <span :class="currentDoc.status === 'Approved' ? 'text-green-300' : 'text-yellow-300'" x-text="toSentenceCase(currentDoc.status)"></span></p>
        <p class="text-sm text-gray-400">Type: <span x-text="toSentenceCase(currentDoc.type)"></span></p>
        <p class="text-sm text-gray-400">Submitted By: <span x-text="currentDoc.submitter"></span></p>
        <p class="text-sm text-gray-400">Submitted On: <span x-text="currentDoc.created_at ? new Date(currentDoc.created_at).toLocaleDateString('en-US') : 'N/A'"></span></p>
    </div>

    <h5 class="text-md font-bold text-green-300">Update Status:</h5>
                    <div class="space-y-4">
                        
                        <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="document_id" :value="currentDoc.id">
                            <input type="hidden" name="new_status" value="Approved">
                            <input type="hidden" name="document_title" :value="currentDoc.title">

                            <button type="submit" class="w-full bg-green-700 hover:bg-green-600 px-5 py-2 rounded-lg font-medium transition"
                                :disabled="currentDoc.status === 'Approved' || currentDoc.status === 'Archived'"
                                :class="currentDoc.status === 'Approved' || currentDoc.status === 'Archived' ? 'opacity-50 cursor-not-allowed' : ''">
                                <i class="fa-solid fa-thumbs-up mr-2"></i> Approve
                            </button>
                        </form>

                        <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="document_id" :value="currentDoc.id">
                            <input type="hidden" name="new_status" value="Rejected">
                            <input type="hidden" name="document_title" :value="currentDoc.title">
                            
                            <button type="submit" class="w-full bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition"
                                :disabled="currentDoc.status === 'Rejected' || currentDoc.status === 'Archived'"
                                :class="currentDoc.status === 'Rejected' || currentDoc.status === 'Archived' ? 'opacity-50 cursor-not-allowed' : ''">
                                <i class="fa-solid fa-thumbs-down mr-2"></i> Reject
                            </button>
                        </form>

                        <button type="button" 
                            @click="setArchiveDoc(currentDoc)" 
                            class="w-full bg-gray-700 hover:bg-gray-600 px-5 py-2 rounded-lg font-medium transition"
                            :disabled="currentDoc.status === 'Archived'"
                            :class="currentDoc.status === 'Archived' ? 'opacity-50 cursor-not-allowed' : ''">
                            <i class="fa-solid fa-box-archive mr-2"></i> Archive
                        </button>
                        
                    </div>
                    
                    <button @click="modalOpen = false" class="w-full text-gray-500 hover:text-gray-300 transition mt-4">Close Review</button>

                </div>
            </div>

        </div>
    </div>
    <div x-show="archiveModalOpen" 
        x-transition:enter="ease-out duration-300"
        x-transition:leave="ease-in duration-200"
        class="fixed inset-0 z-[60] overflow-y-auto bg-maestro-bg bg-opacity-90 flex items-center justify-center" 
        style="display: none;">

        <div @click.outside="archiveModalOpen = false" class="w-full max-w-md mx-auto bg-[#0f1511] rounded-xl shadow-2xl border border-red-800">
            
            <header class="p-4 border-b border-red-800 flex justify-between items-center bg-sidebar-dark">
                <h3 class="text-xl font-bold text-red-400">Confirm Archive</h3>
                <button @click="archiveModalOpen = false" class="text-gray-400 hover:text-white transition">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </header>

            <div class="p-6 space-y-4">
                <p class="text-gray-300">
                    Are you sure you want to <b>Archive</b> the document: 
                    <span class="font-semibold text-yellow-300" x-text="docToArchive.title"></span>?
                </p>
                <p class="text-sm text-gray-500">
                    Archiving will remove this document from all active lists. It can be viewed and unarchived later from the "Archived" section.
                </p>

                <div class="flex justify-end gap-3 pt-4">
                    <button @click="archiveModalOpen = false" class="px-5 py-2 rounded-lg bg-gray-700 hover:bg-gray-600 font-medium transition">
                        Cancel
                    </button>

                    <form method="POST" :action="'<?= BASE_URL ?>/org/documents/update_status'">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="document_id" :value="docToArchive.id">
                        <input type="hidden" name="new_status" value="Archived">
                        <input type="hidden" name="document_title" :value="docToArchive.title">

                        <button type="submit" class="bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition shadow-lg shadow-red-900/40">
                            <i class="fa-solid fa-box-archive mr-2"></i> Confirm Archive
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php 

include 'app/views/org/layout_end.php';
?>