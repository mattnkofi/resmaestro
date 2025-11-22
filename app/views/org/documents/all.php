<?php
$title = 'All Documents - Maestro';

$q = $q ?? '';
$status = $status ?? '';
$docs = $docs ?? []; 

include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white"
    x-data="{ 
        // Initializing global state variables from layout_start.php scope
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
            // Construct the full, absolute public URL required by Google Viewer
            const publicUrl = BASE_URL + '/public/uploads/documents/' + fileName; // Use global BASE_URL
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

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <h1 class="text-3xl font-bold text-green-400 mb-4 sm:mb-0">All Documents</h1>
        <a href="<?=BASE_URL?>/org/documents/upload" class="bg-green-700 hover:bg-green-600 px-5 py-2.5 rounded-xl text-lg font-medium transition shadow-lg shadow-green-900/40">
            <i class="fa-solid fa-plus mr-2"></i> Upload Document
        </a>
    </div>

    <form method="GET" action="<?= BASE_URL ?>/org/documents/all">
        <div class="flex flex-col md:flex-row gap-4 mb-8 items-center">
            
            <input type="text" name="q" value="<?= html_escape($q) ?>"
                    placeholder="Search by title or description..." 
                    class="w-full md:w-1/3 bg-green-900/50 border border-green-800 p-3 rounded-xl focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-white">
            
            <select name="status" class="w-full md:w-1/6 bg-green-900/50 border border-green-800 p-3 rounded-xl text-white">
                <option value="">Filter by Status</option>
                <?php 
                $doc_statuses = ['Pending Review', 'Approved', 'Rejected', 'Archived'];
                foreach ($doc_statuses as $doc_status_item): 
                    $val = $doc_status_item;
                    $is_selected = ($status === $val);
                ?>
                    <option value="<?= html_escape($val) ?>" <?= $is_selected ? 'selected' : '' ?>>
                        <?= $doc_status_item ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="bg-green-700 hover:bg-green-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-green-900/40">
                <i class="fa-solid fa-filter mr-2"></i> Apply Filters
            </button>
            
            <?php if (!empty($q) || !empty($status)): ?>
                <a href="<?= BASE_URL ?>/org/documents/all" class="bg-gray-700 hover:bg-gray-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-gray-900/40">
                    <i class="fa-solid fa-xmark mr-2"></i> Clear
                </a>
            <?php endif; ?>
        </div>
    </form>

    <div class="overflow-x-auto document-table-container rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
        <table class="w-full text-left">
            <thead class="bg-green-900/40 text-gray-200 uppercase text-sm tracking-wider">
                <tr>
                    <th class="p-4 border-b border-green-800">Title</th>
                    <th class="p-4 border-b border-green-800">Type</th>
                    <th class="p-4 border-b border-green-800">Status</th>
                    <th class="p-4 border-b border-green-800 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-[#0f1511] text-gray-300">

            <?php 
            if (!empty($docs)):
            foreach($docs as $doc): 
                $doc_id = $doc['id'] ?? $doc->id ?? 0;
                $doc_title = $doc['title'] ?? $doc->title ?? '';
                $doc_file_name = $doc['file_name'] ?? $doc->file_name ?? '';
                $doc_status = $doc['status'] ?? $doc->status ?? '';
                $doc_type = $doc['type'] ?? $doc->type ?? '';
                $submitter = html_escape(($doc['fname'] ?? $doc->fname ?? '') . ' ' . ($doc['lname'] ?? $doc->lname ?? ''));
                $doc_created_at = $doc['created_at'] ?? $doc->created_at ?? '';

                $doc_created_at_display = 'N/A';
                if (!empty($doc_created_at)) {
                    try {
                        $date_obj = new DateTime($doc_created_at);
                        // NOTE: Timezone logic remains here as it is output formatting specific
                        $date_obj->setTimezone(new DateTimeZone('Asia/Manila')); 
                        $doc_created_at_display = $date_obj->format('M d, Y');
                    } catch (\Exception $e) {
                        $doc_created_at_display = date('M d, Y', strtotime($doc_created_at)); // Fallback
                    }
                }

                $status_class = match ($doc_status) {
                    'Approved' => 'text-green-400',
                    'Pending Review' => 'text-yellow-400',
                    'Rejected' => 'text-red-500',
                    default => 'text-gray-400',
                };

                $js_doc = json_encode([
                    'id' => $doc_id, 
                    'title' => $doc_title, 
                    'file_name' => $doc_file_name, 
                    'status' => $doc_status, 
                    'submitter' => $submitter,
                    'type' => $doc_type,
                    'created_at' => $doc_created_at_display
                ]);
            ?>
                <tr class="border-b border-green-800 hover:bg-green-700/10 transition">
                    <td class="p-4 font-medium text-green-200"><?= html_escape($doc_title) ?></td>
                    <td class="p-4"><?= html_escape($doc_type) ?></td>
                    <td class="p-4 font-semibold <?= $status_class ?>"><?= html_escape($doc_status) ?></td>
                    <td class="p-4 text-center">
                        <button @click="setDoc(<?= html_escape($js_doc) ?>)"class="text-yellow-400 hover:text-yellow-200 hover: font-xl mr-4">
                            <i class="fa-solid fa-eye mr-1"></i> View Details
                        </button>
                    </td>
                </tr>
                <?php endforeach; 
                else: ?>
                <tr>
                    <td colspan="4" class="p-8 text-center text-gray-500">
                        <i class="fa-solid fa-file-alt text-4xl mb-3 text-green-500"></i>
                        <p class="text-lg">No documents found matching your criteria.</p>
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
                                 <i class="fa-solid fa-download mr-1"></i> Direct Download (<span x-text="getFileExtension(currentDoc.file_name).toUpperCase()"></span>)
                            </a>
                        </div>
                    </template>
                </div>
            </div>

            <div class="pl-4 space-y-6 flex flex-col">
                <h4 class="text-lg font-semibold text-gray-400">Review Details & Status</h4>

                <div class="bg-green-950/50 p-4 rounded-lg border border-green-800">
                    <p class="text-sm text-gray-400">Status: <span :class="currentDoc.status === 'Approved' ? 'text-green-300' : (currentDoc.status === 'Rejected' ? 'text-red-400' : 'text-yellow-300')" x-text="toSentenceCase(currentDoc.status)"></span></p>
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