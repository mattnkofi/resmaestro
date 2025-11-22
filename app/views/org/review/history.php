<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// NOTE: Helper functions (html_escape, csrf_field, etc.) are assumed to be 
// handled by app/views/org/layout_start.php.

// --- 1. Page Specific Variables ---
$title = 'Review History - Maestro UI';

// Filter variables passed from the controller (retained)
$q = $q ?? '';
$status = $status ?? '';
$BASE_URL = BASE_URL ?? '/maestro';

// Use data passed from controller
$review_history = $reviews ?? []; 

// --- 2. TEMPLATE INCLUSION ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" x-data="{}">
    
    <h1 class="text-3xl font-bold text-green-400 mb-6 tracking-wide">
        Review History
    </h1>

    <form method="GET" action="<?=$BASE_URL?>/org/review/history" class="flex flex-col md:flex-row gap-4 mb-8">
        <input type="text" name="q" placeholder="Search by document or reviewer name..." 
               value="<?= html_escape($q) ?>"
               class="w-full md:w-1/3 bg-green-900/50 border border-green-800 p-3 rounded-xl focus:ring-green-500 focus:border-green-500 transition placeholder-gray-500 text-white">
        <select name="status" class="w-full md:w-1/6 bg-green-900/50 border border-green-800 p-3 rounded-xl text-white">
            <option value="">Filter by Status (All)</option>
            <option value="Approved" <?= $status === 'Approved' ? 'selected' : '' ?>>Approved</option>
            <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
        <button type="submit" class="bg-green-700 hover:bg-green-600 px-5 py-3 rounded-xl font-medium transition shadow-lg shadow-green-900/40">
            <i class="fa-solid fa-history mr-2"></i> View History
        </button>
    </form>

    <div class="space-y-4">
        <?php
        if (!empty($review_history)):
        foreach($review_history as $doc): 
            $doc_status = htmlspecialchars($doc['status']);
            $status_color = $doc_status == 'Approved' ? 'text-green-400' : 'text-red-400';
            $border_color = $doc_status == 'Approved' ? 'border-green-500' : 'border-red-500';
            
            // Determine the date to display
            if ($doc_status == 'Approved' && !empty($doc['approved_at'])) {
                $review_date = date('M j, Y', strtotime($doc['approved_at']));
            } elseif ($doc_status == 'Rejected' && !empty($doc['rejected_at'])) {
                $review_date = date('M j, Y', strtotime($doc['rejected_at']));
            } else {
                $review_date = 'N/A';
            }
            
            $reviewer_name = trim(($doc['reviewer_fname'] ?? '') . ' ' . ($doc['reviewer_lname'] ?? ''));
            $reviewer_name = $reviewer_name ?: 'N/A Reviewer';
        ?>
        <div class="bg-green-950/50 p-5 rounded-xl border-l-4 <?= $border_color ?> flex flex-col md:flex-row justify-between items-start md:items-center shadow-lg hover:bg-green-900/40 transition">
            <div class="flex flex-col mb-2 md:mb-0">
                <span class="text-lg font-semibold text-green-200"><i class="fa-solid fa-file-alt mr-2"></i> <?= htmlspecialchars($doc['title']) ?></span>
                <span class="text-sm text-gray-400">Reviewed by: <?= htmlspecialchars($reviewer_name) ?></span>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-right hidden sm:block">
                    <span class="block text-xs text-gray-300 uppercase tracking-wider">Status:</span>
                    <span class="block text-sm <?= $status_color ?> font-medium"><?= $doc_status ?> on <?= $review_date ?></span>
                </div>
                <a href="<?=$BASE_URL?>/org/review/comments/<?= $doc['id'] ?>" class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg text-sm transition">
                    <i class="fa-solid fa-eye mr-1"></i> View Details
                </a>
            </div>
        </div>
        <?php endforeach;
        else: ?>
        <div class="p-8 text-center text-gray-500 bg-green-950/20 rounded-xl border border-green-800">
            <i class="fa-solid fa-list-check text-4xl mb-3 text-green-500"></i>
            <p class="text-lg">No review actions have been recorded yet.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>