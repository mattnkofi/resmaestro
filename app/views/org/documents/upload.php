<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// --- 1. Page Specific Variables ---
// Set the title for layout_start.php
$title = 'Upload Document - Maestro';

// Data variables passed from the controller (retained)
$reviewers = $reviewers ?? [];
$recent_uploads = $recent_uploads ?? [];

// Helper function definitions are handled by layout_start.php.
// Sidebar state logic is handled by sidebar.php and is removed from here.

// --- 2. TEMPLATE INCLUSION (Using requested absolute paths) ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" x-data="{}">
    
    <h1 class="text-3xl font-bold text-green-400 mb-8">Upload New Document</h1>
    
    <?php if (function_exists('flash_alert')) flash_alert(); ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <div>
            <form action="<?=BASE_URL?>/org/documents/store" method="POST" enctype="multipart/form-data" 
                class="bg-green-950/50 p-8 rounded-xl space-y-6 max-w-2xl border border-green-800 shadow-2xl shadow-green-900/10">

                <?php if (function_exists('csrf_field')) csrf_field(); ?>

                <div>
                    <label for="title" class="block mb-2 text-sm font-medium text-gray-300">Document Title</label>
                    <input type="text" id="title" name="title" 
                           class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-green-100" 
                           placeholder="e.g., Q4 Financial Report, Internal Policy Update v2.0" required>
                </div>

                <div>
                    <label for="type" class="block mb-2 text-sm font-medium text-gray-300">Document Type</label>
                    <select id="type" name="type" 
                            class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-green-500 focus:border-green-500 transition text-green-100">
                        <option value="Finance">Financial Report</option>
                        <option value="Budget">Budgetary Report</option>
                        <option value="Accomplishment">Accomplishment Report</option>
                        <option value="Proposal">Project Proposal</option>
                        <option value="Legal">Legal Document</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div>
                    <label for="file" class="block mb-2 text-sm font-medium text-gray-300">Select File (PDF, DOCX, XLSX, JPG)</label>
                    <input type="file" id="file" name="document_file" required
                           class="w-full text-sm text-green-100 file:mr-4 file:py-2.5 file:px-4 
                                  file:rounded-lg file:border-0 file:text-sm file:font-semibold
                                  file:bg-green-700 file:text-white hover:file:bg-green-600 transition duration-150">
                </div>

                <div>
                    <label for="tags" class="block mb-2 text-sm font-medium text-gray-300">Tags / Keywords (comma-separated)</label>
                    <input type="text" id="tags" name="tags" 
                           class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-green-100" 
                           placeholder="e.g., Q4, Finance, 2024, Report">
                </div>

                <div>
                    <label for="reviewer" class="block mb-2 text-sm font-medium text-gray-300">Assign Reviewer (Optional)</label>
                    <select id="reviewer" name="reviewer" 
                            class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-green-500 focus:border-green-500 transition text-green-100">
                        <option value="">No Reviewer Assigned</option>
                        <?php 
                        // Loop over the $reviewers array passed from the controller
                        if (!empty($reviewers)): 
                            foreach($reviewers as $reviewer): ?>
                                <option value="<?= $reviewer['id'] ?>">
                                    <?= htmlspecialchars($reviewer['fname'] . ' ' . $reviewer['lname']) ?> (<?= htmlspecialchars($reviewer['email']) ?>)
                                </option>
                            <?php endforeach; 
                        endif; ?>
                    </select>
                </div>

                <div>
                    <label for="description" class="block mb-2 text-sm font-medium text-gray-300">Description / Summary</label>
                    <textarea id="description" name="description" 
                              class="w-full bg-green-900 border border-green-800 p-3 rounded-lg focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-green-100" 
                              rows="4" placeholder="Provide a brief summary and context for this document, including any important notes for reviewers."></textarea>
                </div>
                
                <div class="pt-2">
                    <button type="submit" class="w-full bg-green-700 px-6 py-3 rounded-xl hover:bg-green-600 font-bold text-lg transition shadow-lg shadow-green-900/40">
                        <i class="fa-solid fa-cloud-arrow-up mr-2"></i> Upload Document
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-8">
            <div class="bg-green-950/50 p-8 rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
                <h2 class="text-xl font-bold text-green-300 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-green-500"></i> Upload Guidelines
                </h2>
                <ul class="list-disc list-inside space-y-2 text-gray-300">
                    <li>File size limit: 25MB per document.</li>
                    <li>Supported formats: PDF, DOCX, XLSX, JPG, PNG.</li>
                    <li>Ensure document titles are descriptive and unique.</li>
                    <li>Add relevant tags for easy search and categorization.</li>
                    <li>Assign a reviewer if the document requires immediate attention.</li>
                    <li>All uploads are subject to review by organization admins.</li>
                </ul>
                <p class="text-sm text-gray-500 mt-4">
                    Having trouble? Contact support for assistance.
                </p>
            </div>

            <div class="bg-green-950/50 p-8 rounded-xl border border-green-800 shadow-2xl shadow-green-900/10">
                <h2 class="text-xl font-bold text-green-300 mb-5 flex items-center gap-2"> 
                    <i class="fa-solid fa-history text-green-500"></i> Your Recent Uploads / Drafts
                </h2>
                <ul class="space-y-3 text-gray-300">
                    <?php 
                    $recent_uploads = $recent_uploads ?? []; 
                    if (empty($recent_uploads)): ?>
                        <div class="p-4 text-center border border-dashed border-green-700/50 rounded-lg text-gray-500">
                            <i class="fa-solid fa-file-circle-exclamation text-2xl mb-2"></i>
                            <p class="text-sm">No recent uploads or drafts found.</p>
                            <p class="text-xs mt-1">Start by uploading your first document above.</p>
                        </div>
                    <?php else: 
                        foreach ($recent_uploads as $upload):
                            $icon = match($upload['type'] ?? '') { 
                                'report' => 'fa-file-excel text-green-400',
                                'policy' => 'fa-file-word text-blue-400',
                                'legal' => 'fa-file-gavel text-red-400',
                                'proposal' => 'fa-file-alt text-yellow-400',
                                default => 'fa-file text-gray-400',
                            };
                            $status_color = match($upload['status'] ?? '') {
                                'Pending Review' => 'text-yellow-400',
                                'Approved' => 'text-green-400',
                                'Draft' => 'text-gray-400',
                                default => 'text-red-400',
                            };
                        ?>
                        <li class="flex justify-between items-center bg-green-900/30 p-3 rounded-lg hover:bg-green-900/50 transition">
                            <span><i class="fa-solid <?= $icon ?> mr-2"></i> <?= htmlspecialchars($upload['title']) ?></span>
                            <span class="text-sm <?= $status_color ?>"><?= htmlspecialchars($upload['status']) ?></span>
                        </li>
                        <?php endforeach; 
                    endif; ?>
                </ul>
                <a href="<?=BASE_URL?>/org/documents/all" class="mt-4 inline-block text-green-400 hover:text-green-300 text-sm">View all my documents <i class="fa-solid fa-arrow-right ml-1"></i></a>
            </div>
        </div>

    </div> 
</div> 

<?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>