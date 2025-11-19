<?php 
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
// Data assumption: $doc contains the document details fetched by OrgController::documents_review($doc_id)
// $doc contains: id, title, type, file_name, submitter_fname, submitter_lname, status

$document_id = $doc['id'] ?? 0;
$document_title = html_escape($doc['title'] ?? 'N/A');
$submitter_name = html_escape($doc['submitter_fname'] . ' ' . $doc['submitter_lname'] ?? 'N/A');
$file_path = BASE_URL . '/public/uploads/documents/' . urlencode($doc['file_name'] ?? '');
$current_status = $doc['status'] ?? 'Unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Document - <?= $document_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-maestro-bg text-white font-poppins" x-data="{ approveModalOpen: false, rejectModalOpen: false }">

    <div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white">

        <?php 
        $this->call->helper('common');
        // Assuming get_flash_alert() exists and renders the message
        // echo get_flash_alert(); 
        ?>

        <h1 class="text-3xl font-bold text-yellow-400 mb-2">Review Document</h1>
        <p class="text-xl text-green-300 mb-6"><?= $document_title ?></p>

        <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 mb-8">
            <p class="text-sm text-gray-400">Submitted By: <span class="text-green-300"><?= $submitter_name ?></span></p>
            <p class="text-sm text-gray-400">Document Type: <span class="text-green-300"><?= ucwords($doc['type'] ?? 'Unknown') ?></span></p>
            
            <p class="text-sm text-gray-400">Current Status: 
                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                    <?= $current_status === 'Approved' ? 'bg-green-800/50 text-green-300' : 
                      ($current_status === 'Rejected' ? 'bg-red-800/50 text-red-300' : 'bg-yellow-800/50 text-yellow-300') ?>">
                    <?= $current_status ?>
                </span>
            </p>
            
            <a href="<?= $file_path ?>" target="_blank" class="mt-4 inline-block text-yellow-400 hover:underline font-semibold">
                <i class="fa-solid fa-file-pdf mr-2"></i> View/Download Original File
            </a>
        </div>

        <h2 class="text-2xl font-semibold text-green-300 mb-4">Review Actions</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-lg">
                <h3 class="text-lg font-bold text-green-400 mb-3">Approve Document</h3>
                <p class="text-sm text-gray-400 mb-4">Approve this document, mark it as reviewed, and move it to the 'Approved' public list.</p>
                
                <button @click="approveModalOpen = true"
                        class="w-full bg-green-700 hover:bg-green-600 px-5 py-2 rounded-lg font-medium transition">
                    <i class="fa-solid fa-thumbs-up mr-2"></i> Approve
                </button>
            </div>

            <div class="bg-red-950/50 p-6 rounded-xl border border-red-800 shadow-lg">
                <h3 class="text-lg font-bold text-red-400 mb-3">Reject Document</h3>
                <p class="text-sm text-gray-400 mb-4">Reject this document, requiring the submitter to make revisions and resubmit.</p>
                <button @click="rejectModalOpen = true"
                        class="w-full bg-red-700 hover:bg-red-600 px-5 py-2 rounded-lg font-medium transition">
                    <i class="fa-solid fa-thumbs-down mr-2"></i> Reject
                </button>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/org/documents/update_status">
                <input type="hidden" name="document_id" value="<?= $document_id ?>">
                <input type="hidden" name="new_status" value="Archived">
                <input type="hidden" name="document_title" value="<?= $document_title ?>">

                <div class="bg-gray-950/50 p-6 rounded-xl border border-gray-800 shadow-lg h-full">
                    <h3 class="text-lg font-bold text-gray-400 mb-3">Archive Document</h3>
                    <p class="text-sm text-gray-400 mb-4">Move this document to the archive without explicit approval or rejection.</p>
                    <button type="submit" onclick="return confirm('Confirm: Archive this document?')"
                            class="w-full bg-gray-700 hover:bg-gray-600 px-5 py-2 rounded-lg font-medium transition">
                        <i class="fa-solid fa-box-archive mr-2"></i> Archive
                    </button>
                </div>
            </form>

        </div>
    </div>
    
    <div x-show="approveModalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:leave="ease-in duration-200"
         class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-75" 
         style="display: none;">

        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.outside="approveModalOpen = false" 
                 class="bg-sidebar-dark rounded-lg p-6 shadow-2xl border border-green-700 w-full max-w-md mx-auto transform transition-all">
                
                <h3 class="text-xl font-bold text-green-400 mb-4 flex items-center">
                    <i class="fa-solid fa-circle-check mr-2"></i> Confirm Approval
                </h3>

                <p class="text-gray-300 mb-6">
                    You are about to **APPROVE** the document: <br>
                    <span class="font-semibold text-white truncate max-w-xs block mt-1"><?= $document_title ?></span>
                </p>

                <form method="POST" action="<?= BASE_URL ?>/org/documents/update_status">
                    <input type="hidden" name="document_id" value="<?= $document_id ?>">
                    <input type="hidden" name="new_status" value="Approved">
                    <input type="hidden" name="document_title" value="<?= $document_title ?>">

                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="approveModalOpen = false"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-700 text-white rounded-lg hover:bg-green-600 font-semibold transition">
                            Finalize Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-show="rejectModalOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:leave="ease-in duration-200"
         class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-75" 
         style="display: none;">

        <div class="flex items-center justify-center min-h-screen p-4">
            <div @click.outside="rejectModalOpen = false" 
                 class="bg-sidebar-dark rounded-lg p-6 shadow-2xl border border-red-700 w-full max-w-md mx-auto transform transition-all">
                
                <h3 class="text-xl font-bold text-red-400 mb-4 flex items-center">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i> Confirm Rejection
                </h3>
                <p class="text-gray-300 mb-6">
                    You are about to **REJECT** the document: 
                    <span class="font-semibold text-white truncate max-w-xs block mt-1"><?= $document_title ?></span>
                </p>

                <form method="POST" action="<?= BASE_URL ?>/org/documents/update_status">
                    <input type="hidden" name="document_id" value="<?= $document_id ?>">
                    <input type="hidden" name="new_status" value="Rejected">
                    <input type="hidden" name="document_title" value="<?= $document_title ?>">
                    
                    <div class="mb-4">
                        <label for="rejection_notes" class="block text-sm font-medium text-gray-300 mb-1">Reason for Rejection (Recommended)</label>
                        <textarea id="rejection_notes" name="notes" rows="3" class="w-full bg-gray-800 border border-red-800 p-2 rounded-lg text-white"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="rejectModalOpen = false"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-500 transition">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-red-700 text-white rounded-lg hover:bg-red-600 font-semibold transition">
                            Finalize Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>