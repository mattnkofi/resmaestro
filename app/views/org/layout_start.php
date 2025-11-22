<?php
// --- PHP Helper Functions (CRITICAL for form/URL) ---
if (!defined('BASE_URL')) define('BASE_URL', '/maestro');
if (!function_exists('html_escape')) {
    function html_escape($str) {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('csrf_field')) {
    function csrf_field() {
        echo '<input type="hidden" name="csrf_token" value="' . ($_SESSION['csrf_token'] ?? 'MOCK_CSRF_TOKEN') . '">';
    }
}
if (!function_exists('set_value')) {
    function set_value($field) {
        return $_POST[$field] ?? null; 
    }
}
if (!function_exists('flash_alert')) {
    function flash_alert() { /* Mock function as implementation is external to views */ }
}
// --- End Helper Functions ---

// Required variables for Alpine data and HTML
$BASE_URL = BASE_URL ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Project Maestro' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sidebar-dark': '#0f1511',
                        'maestro-bg': '#0b0f0c',
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                        sans: ['Poppins', 'sans-serif'], 
                    }
                }
            }
        }
    </script>
    <style>
        /* Explicitly apply Poppins font globally */
        body { font-family: 'Poppins', sans-serif; }
        .maestro-bg { background-color: #0b0f0c; }
        
        /* Custom styles merged from dashboard for consistency */
        .dashboard-card {
            background-color: rgba(16, 185, 129, 0.05); /* Very subtle green tint */
            border: 1px solid rgba(16, 185, 129, 0.2); /* Faint green border */
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-2px); /* Subtle lift on hover */
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1); /* Subtle green glow */
        }
    </style>
</head>
<body class="bg-maestro-bg text-white font-poppins" 
    x-data="{ BASE_URL: '<?= $BASE_URL ?>', isModalOpen: false, modalDept: {}, actionType: '' }">