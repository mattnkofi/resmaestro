<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// --- 1. Page Specific Variables ---
$title = 'Archived Documents - Maestro';

// PHP GET variables for search/filter (retained from controller context)
$q = $_GET['q'] ?? ''; 
$year = $_GET['year'] ?? ''; // Kept for filter logic
$docs = $docs ?? [];
 
// All global helpers (html_escape, csrf_field, etc.) are assumed to be 
// defined in layout_start.php.

// --- 2. TEMPLATE INCLUSION ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" 
    x-data="{
        // Alpine data for modal functionality
        modalOpen: false, 
        confirmActionModalOpen: false, 
        
        currentDoc: { id: 0, title: '', file_name: '', status: '', submitter: '', type: '', created_at: '', deleted_at: '' },
        actionFormElement: null, 

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
            this.modalOpen = true; 
        },
        
        prepareConfirmation(form, doc) {
            this.actionFormElement = form;
            this.currentDoc = doc; 
            this.confirmActionModalOpen = true;
        },

        executeAction() {
            if (this.actionFormElement) {
                this.actionFormElement.submit(); 
            }
            this.confirmActionModalOpen = false;
        }
    }"
    @keydown.escape="modalOpen = false; confirmActionModalOpen = false">

    <h1 class="text-3xl font-bold text-blue-400 mb-6 tracking-wide">
        Archived Documents
    </h1>

    <?php if (function_exists('flash_alert')) flash_alert(); ?>

    <form method="GET" action="<?= BASE_URL ?>/org/documents/archived">
        <div class="flex flex-col md:flex-row gap-4 mb-8">
            <input type="text" name="q" value="<?= html_escape($q) ?>"
                   placeholder="Search archived documents by title or description..." 
                   class="w-full md:w-1/3 bg-blue-900/50 border border-blue-800 p-3 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition placeholder-gray-500 text-white">
            
            <button type="submit" class="bg-blue-700 hover:bg-blue-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-blue-900/40">
                <i class="fa-solid fa-search mr-2"></i> Search Documents
            </button>

            <?php if (!empty($q) || !empty($year)): ?>
                <a href="<?= BASE_URL ?>/org/documents/archived" class="bg-gray-700 hover:bg-gray-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-gray-900/40">
                    <i class="fa-solid fa-xmark mr-2"></i> Clear
                </a>
            <?php endif; ?>
        </div>
    </form>

    <div class="space-y-4">
        <?php
        if (!empty($docs)):
        foreach($docs as $doc): 
            // Safely extract data, treating object/array consistently
            $doc_id = $doc->id ?? $doc['id'] ?? 0;
            $doc_title = $doc->title ?? $doc['title'] ?? 'N/A';
            $doc_file_name = $doc->file_name ?? $doc['file_name'] ?? '';
            $doc_deleted_at = $doc->deleted_at ?? $doc['deleted_at'] ?? null;
            $submitter = html_escape(($doc->fname ?? $doc['fname'] ?? 'N/A') . ' ' . ($doc->lname ?? $doc['lname'] ?? ''));
            
            // Date formatting logic (retained)
            $archived_at_display = 'N/A';
            if (!empty($doc_deleted_at)) {
                try {
                    $date_obj = new DateTime($doc_deleted_at);
                    $date_obj->setTimezone(new DateTimeZone('Asia/Manila')); 
                    $archived_at_display = $date_obj->format('M d, Y');
                } catch (\Exception $e) {
                    $archived_at_display = date('M d, Y', strtotime($doc_deleted_at)); // Fallback
                }
            }
            $display_date = $archived_at_display; 

            // Prepare document data for Alpine.js modal (must be escaped for HTML attribute)
            $js_doc = html_escape(json_encode([
                'id' => $doc_id, 
                'title' => html_escape($doc_title), 
                'file_name' => $doc_file_name, 
                'status' => 'Archived', 
                'submitter' => $submitter,
                'type' => $doc->type ?? $doc['type'] ?? 'N/A',
                'deleted_at' => $doc_deleted_at,
                'deleted_at_display' => $archived_at_display 
            ]));
        ?>
        <div class="bg-blue-950/20 p-5 rounded-xl border-l-4 border-blue-500 flex flex-col md:flex-row justify-between items-start md:items-center shadow-lg hover:bg-blue-900/30 transition">
            <div class="flex flex-col mb-2 md:mb-0">
                <span class="text-lg font-semibold text-blue-200"><?= html_escape($doc_title) ?></span>
                <span class="text-sm text-gray-400">Archived By: <?= $submitter ?></span>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-right hidden sm:block">
                    <span class="block text-xs text-gray-300 uppercase tracking-wider">Archived Date:</span>
                    <span class="block text-sm text-blue-400 font-medium"><?= $display_date ?></span>
                </div>
                
                <button @click="setDoc(<?= html_escape($js_doc) ?>)" class="bg-blue-700 hover:bg-blue-600 px-4 py-2 rounded-lg text-sm transition">
                    <i class="fa-solid fa-download mr-1"></i> View/Download
                </button>
                
                <form method="POST" action="<?= BASE_URL ?>/org/documents/update_status" x-ref="unarchiveForm<?= $doc_id ?>">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="document_id" value="<?= $doc_id ?>">
                    <input type="hidden" name="new_status" value="Pending Review"> 
                    <input type="hidden" name="document_title" value="<?= html_escape($doc_title) ?>">
                    
                    <button type="button" @click="prepareConfirmation($refs.unarchiveForm<?= $doc_id ?>, <?= html_escape($js_doc) ?>)"
                        class="text-gray-500 hover:text-blue-300 transition text-lg p-2 leading-none">
                        <i class="fa-solid fa-box-open"></i>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; 
        else: ?>
        <div class="p-8 text-center text-gray-500 bg-blue-950/20 rounded-xl border border-blue-800">
            <i class="fa-solid fa-box-archive text-4xl mb-3 text-blue-500"></i>
            <p class="text-lg">No documents are currently in the archive.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<div x-show="modalOpen" 
    x-transition:enter="ease-out duration-300"
    x-transition:leave="ease-in duration-200"
    class="fixed inset-0 z-50 overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center" 
    style="display: none;">

    <div @click.outside="modalOpen = false" class="w-full max-w-7xl mx-auto bg-[#0f1511] rounded-xl shadow-2xl border border-green-800 h-[90vh] flex flex-col">
        
        <header class="p-4 border-b border-green-800 flex justify-between items-center bg-sidebar-dark border-blue-500">
            <h3 class="text-xl font-bold" x-text="'Viewing Archived: ' + currentDoc.title">Viewing Archived Document</h3>
            <button @click="modalOpen = false" class="text-gray-400 hover:text-white transition">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </header>

        <div class="review-content-grid flex-1 overflow-y-auto"> 
            
            <div class="pr-4 border-r border-green-800 flex flex-col">
                <h4 class="text-lg font-semibold text-gray-400 mb-3">Document Content 
                    <span x-text="'(' + getFileExtension(currentDoc.file_name).toUpperCase() + ' Viewer)'" class="text-blue-400"></span>
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
                               class="text-blue-400 hover:text-blue-200 underline">
                                <i class="fa-solid fa-download mr-1"></i> Direct Download (<span x-text="toSentenceCase(getFileExtension(currentDoc.file_name))"></span>)
                            </a>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pl-4 space-y-6 flex flex-col">
                <h4 class="text-lg font-semibold text-gray-400">Archive Details</h4>

                <div class="bg-blue-950/50 p-4 rounded-lg border border-blue-800">
                    <p class="text-sm text-gray-400">Status: <span class="text-blue-300" x-text="toSentenceCase(currentDoc.status)"></span></p>
                    <p class="text-sm text-gray-400">Type: <span x-text="toSentenceCase(currentDoc.type)"></span></p>
                    <p class="text-sm text-gray-400">Submitted By: <span x-text="currentDoc.submitter"></span></p>
                    <p class="text-sm text-gray-400">Archived On: <span x-text="currentDoc.deleted_at_display"></span></p>
                </div>

                <h5 class="text-md font-bold text-blue-300">Action:</h5>
                <div class="space-y-4">
                    
                    <form method="POST" action="<?= BASE_URL ?>/org/documents/update_status" x-ref="unarchiveFormViewer">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="document_id" :value="currentDoc.id">
                        <input type="hidden" name="new_status" value="Pending Review">
                        <input type="hidden" name="document_title" :value="currentDoc.title">

                        <button type="button" @click="prepareConfirmation($refs.unarchiveFormViewer, currentDoc)"
                            class="w-full bg-green-700 hover:bg-green-600 px-5 py-2 rounded-lg font-medium transition">
                            <i class="fa-solid fa-box-open mr-2"></i> Restore to Pending Review
                        </button>
                    </form>

                </div>
                
                <button @click="modalOpen = false" class="w-full text-gray-500 hover:text-gray-300 transition mt-4">Close Viewer</button>

            </div>
        </div>

    </div>
</div>
<div x-show="confirmActionModalOpen" 
    x-transition:enter="ease-out duration-300"
    x-transition:leave="ease-in duration-200"
    class="fixed inset-0 z-[60] overflow-y-auto bg-maestro-bg bg-opacity-95 flex items-center justify-center" 
    style="display: none;">

    <div @click.outside="confirmActionModalOpen = false" class="w-full max-w-md mx-auto bg-[#151a17] rounded-xl shadow-2xl border border-red-800 p-6">
        
        <div class="text-center">
            <i class="fa-solid fa-triangle-exclamation text-4xl text-yellow-500 mb-4"></i>
            <h3 class="text-xl font-bold text-white mb-2">Confirm Restore Action</h3>
            
            <p class="text-gray-400 mb-6">
                Are you sure you want to restore "<strong x-text="currentDoc.title">Document Title</strong>" from the archive? 
                It will be set back to <b>Pending Review</b>.
            </p>
        </div>

        <div class="flex justify-center gap-4">
            <button @click="confirmActionModalOpen = false" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition w-full">
                Cancel
            </button>
            <button @click="executeAction()" class="bg-green-700 hover:bg-green-600 text-white font-medium px-4 py-2 rounded-lg transition w-full">
                <i class="fa-solid fa-box-open mr-1"></i> Confirm Restore
            </button>
        </div>

    </div>
</div>

<?php
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>