<?php 
// Note: Helper functions are assumed to be handled by app/views/org/layout_start.php.

// --- 1. Page Specific Variables and Data ---
$title = 'Reports Overview - Maestro UI';

// Mock data (kept for content functionality)
$document_stats = [
    'total' => 340,
    'approved' => 210,
    'rejected' => 30,
    'pending' => 100,
];
$top_reviewers = [
    ['name' => 'Admin User', 'count' => 55],
    ['name' => 'Jane Doe', 'count' => 48],
    ['name' => 'John Smith', 'count' => 37],
    ['name' => 'Ellaine Cordero', 'count' => 31],
    ['name' => 'Reviewer 5', 'count' => 22],
];

// Calculation
$overall_approval_rate = $document_stats['total'] > 0 ? round(($document_stats['approved'] / $document_stats['total']) * 100) : 0;


// --- 2. TEMPLATE INCLUSION ---
include 'app/views/org/layout_start.php'; 
include 'app/views/org/sidebar.php'; 
?>

<div class="ml-64 p-8 bg-maestro-bg min-h-screen text-white" x-data="{}">
    
    <h1 class="text-3xl font-bold text-green-400 mb-8 tracking-wide">
        Reports Overview
    </h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <div class="dashboard-card bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl">
            <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice text-2xl"></i> Document Stats
            </h2>
            <div class="space-y-2 text-lg">
                <p class="flex justify-between items-center">
                    <span><i class="fa-solid fa-circle-check text-green-500 mr-2"></i> Approved:</span>
                    <span class="font-bold text-green-400">140</span>
                </p>
                <p class="flex justify-between items-center">
                    <span><i class="fa-solid fa-hourglass-half text-yellow-500 mr-2"></i> Pending:</span>
                    <span class="font-bold text-yellow-400">24</span>
                </p>
                <p class="flex justify-between items-center">
                    <span><i class="fa-solid fa-circle-xmark text-red-500 mr-2"></i> Rejected:</span>
                    <span class="font-bold text-red-400">12</span>
                </p>
            </div>
            <a href="<?=BASE_URL?>/org/reports/documents" class="mt-4 block text-sm text-green-400 hover:text-green-300">View detailed document analytics &rarr;</a>
        </div>

        <div class="dashboard-card bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl">
            <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-database text-2xl"></i> Storage Usage
            </h2>
            
            <div class="w-full bg-green-900 rounded-full h-4 mb-2">
                <div class="bg-green-500 h-4 rounded-full transition-all duration-700" style="width:70%"></div>
            </div>
            
            <p class="text-sm mt-2 text-gray-300 font-semibold">7.0 GB / 10.0 GB used</p>
            <p class="text-xs text-gray-500">70% of organizational quota used.</p>
            
            <a href="<?=BASE_URL?>/org/reports/storage" class="mt-4 block text-sm text-green-400 hover:text-green-300">Manage storage &rarr;</a>
        </div>

        <div class="dashboard-card bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl">
            <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user-check text-2xl"></i> Reviewer Activity
            </h2>
            <div class="space-y-2 text-lg">
                <p class="flex justify-between">
                    <span>Reviews Completed:</span>
                    <span class="font-bold text-green-400">180</span>
                </p>
                <p class="flex justify-between">
                    <span>Avg. Review Time:</span>
                    <span class="font-bold text-yellow-400">1.5 Days</span>
                </p>
                <p class="flex justify-between">
                    <span>Active Reviewers:</span>
                    <span class="font-bold text-blue-400">15</span>
                </p>
            </div>
            <a href="<?=BASE_URL?>/org/reports/reviewers" class="mt-4 block text-sm text-green-400 hover:text-green-300">View reviewer metrics &rarr;</a>
        </div>
        
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 dashboard-card bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl h-96">
            <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-chart-bar text-2xl"></i> Approval/Rejection Trends (Last 6 Months)
            </h2>
            <div class="h-64 flex items-center justify-center text-gray-500 border border-dashed border-green-800/50 rounded-lg">
                Document Flow Chart Placeholder (e.g., using D3.js or Recharts)
            </div>
        </div>

        <div class="lg:col-span-1 dashboard-card bg-green-950/50 p-6 rounded-xl border border-green-800 shadow-xl h-96">
            <h2 class="text-xl font-semibold text-green-300 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-star text-2xl text-yellow-400"></i> Top Reviewers (Volume)
            </h2>
            <ul class="space-y-3">
                <?php
                foreach($top_reviewers as $reviewer): ?>
                <li class="flex justify-between items-center p-3 bg-green-900/30 rounded-lg hover:bg-green-900/50 transition">
                    <span class="font-medium text-green-200"><?= $reviewer['name'] ?></span>
                    <span class="text-sm text-green-400 font-bold"><?= $reviewer['count'] ?> Reviews</span>
                </li>
                <?php endforeach; ?>
            </ul>
            <a href="<?=BASE_URL?>/org/reports/reviewers" class="mt-4 block text-sm text-green-400 hover:text-green-300">Full reviewer leaderboard &rarr;</a>
        </div>

    </div>

</div>

<?php 
// --- 3. TEMPLATE END ---
include 'app/views/org/layout_end.php';
?>