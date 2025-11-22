<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// --- 1. Page Specific Variables (Must be defined BEFORE layout_start.php) ---
$title = 'Approved Documents - Maestro';

// Filter variables passed from the controller (retained)
$q = $q ?? '';
$type = $type ?? '';
$docs = $approved_docs ?? [];
 
// Helper function definitions (html_escape, csrf_field, etc.) and sidebar logic
// are now assumed to be handled by the included layout files.

// --- 2. TEMPLATE INCLUSION ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" x-data="{}">
    
    <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
        Approved Documents
    </h1>

    <?php if (function_exists('flash_alert')) flash_alert(); ?>

    <form method="GET" action="<?= BASE_URL ?>/org/documents/approved">
        <div class="flex flex-col md:flex-row gap-4 mb-8">
            <input type="text" name="q" placeholder="Search by title or approver..." 
                   value="<?= html_escape($q) ?>"
                   class="w-full md:w-1/3 bg-green-900 border border-green-800 p-3 rounded-xl focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-green-100">
            
            <select name="type" class="w-full md:w-1/6 bg-green-900 border border-green-800 p-3 rounded-xl text-green-100">
                <option value="">Filter by Type</option>
                <?php 
                $doc_types = ['Report', 'Policy', 'Legal', 'Project Proposal', 'HR Document', 'Marketing'];
                foreach ($doc_types as $doc_type): ?>
                    <option value="<?= html_escape(strtolower($doc_type)) ?>" 
                        <?= (strtolower($doc_type) === strtolower($type)) ? 'selected' : '' ?>>
                        <?= $doc_type ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="bg-green-700 hover:bg-green-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-green-900/40">
                <i class="fa-solid fa-filter mr-2"></i> Apply Filters
            </button>
            
            <?php if (!empty($q) || !empty($type)): ?>
                <a href="<?= BASE_URL ?>/org/documents/approved" class="bg-gray-700 hover:bg-gray-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-gray-900/40">
                    <i class="fa-solid fa-xmark mr-2"></i> Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
    <div class="space-y-4">
<?php
    foreach($docs as $doc): 
        // Safely access approver names
        $approver_fname = $doc['approver_fname'] ?? 'System'; 
        $approver_lname = $doc['approver_lname'] ?? '';
        
        $approver_name = html_escape(trim($approver_fname . ' ' . $approver_lname)); 
        
        $approved_date = date('M d, Y', strtotime($doc['created_at']));
    ?>
    <div class="bg-green-950/50 p-5 rounded-xl border-l-4 border-green-500 flex flex-col md:flex-row justify-between items-start md:items-center shadow-lg hover:bg-green-900/40 transition">
        <div class="flex flex-col mb-2 md:mb-0">
            <span class="text-lg font-semibold text-green-200"><?= html_escape($doc['title']) ?></span>
            <span class="text-sm text-gray-400">Type: <?= html_escape(ucwords($doc['type'])) ?></span>
        </div>
        <div class="flex items-center space-x-6">
            <div class="text-right hidden sm:block">
                <span class="block text-xs text-gray-500">Approved By: <?= $approver_name ?></span>
                <span class="block text-sm text-green-400 font-medium">Date: <?= $approved_date ?></span>
            </div>
            <a href="<?=BASE_URL?>/public/uploads/documents/<?= urlencode($doc['file_name']) ?>" 
               target="_blank" 
               class="bg-green-700 hover:bg-green-600 px-4 py-2 rounded-lg text-sm transition">
                <i class="fa-solid fa-download mr-1"></i> Download
            </a>
            <button class="text-gray-500 hover:text-green-300 transition text-sm">
                <i class="fa-solid fa-share-nodes"></i>
            </button>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($docs)): ?>
    <div class="p-8 text-center text-gray-500 bg-green-950/50 rounded-xl border border-green-800">
        <i class="fa-solid fa-check-circle text-4xl mb-3 text-green-500"></i>
        <p class="text-lg">No documents match the current filters or have been approved yet.</p>
    </div>
    <?php endif; ?>
</div>

</div>

<?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>