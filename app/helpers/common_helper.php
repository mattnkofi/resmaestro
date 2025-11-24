<?php

if (! function_exists('set_flash_alert')) {
    function set_flash_alert($alert, $message)
    {
        // FIX: Removed reference operator for PHP 8+ compatibility
        $LAVA = lava_instance();
        $LAVA->session->set_flashdata([
            'alert' => $alert,
            'message' => $message
        ]);
    }
}


if (! function_exists('flash_alert')) {
    function flash_alert()
    {
        // FIX: Changed output to a Toast Notification system using Alpine.js and Tailwind CSS.
        
        $LAVA = lava_instance();
        $alert_type = $LAVA->session->flashdata('alert');
        $message = $LAVA->session->flashdata('message');
        
        if ($alert_type !== null) {
            $is_success = ($alert_type === 'success');
            // --- NEW STYLING LOGIC ---
            $icon = $is_success ? 'fa-check-circle' : 'fa-triangle-exclamation';
            $icon_bg = $is_success ? 'bg-green-600' : 'bg-red-600';
            $text_color = $is_success ? 'text-green-300' : 'text-red-300';
            $ring_color = $is_success ? 'ring-green-500' : 'ring-red-500';
            $border_color = $is_success ? 'border-green-600' : 'border-red-600';
            // --- END NEW STYLING LOGIC ---

            // Toast HTML/Alpine.js Structure
            echo "
            <div x-data=\"{ show: true }\" 
                 x-show=\"show\" 
                 x-init=\"setTimeout(() => show = false, 5000)\"
                 x-transition:enter=\"transition ease-out duration-300\"
                 x-transition:enter-start=\"opacity-0 scale-95\"
                 x-transition:enter-end=\"opacity-100 scale-100\"
                 x-transition:leave=\"transition ease-in duration-200\"
                 x-transition:leave-start=\"opacity-100 scale-100\"
                 x-transition:leave-end=\"opacity-0 scale-95\"
                 class='fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/3 z-50 w-full max-w-lg'>
                
                <div class='p-6 rounded-md shadow-2xl flex items-center space-x-5 border-2 {$border_color} ring-opacity-50 {$ring_color} ring-offset-4 ring-offset-gray-900' style='background: rgba(11, 15, 12, 0.7); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);'>
                    <div class='flex-shrink-0 w-12 h-12 rounded-md flex items-center justify-center {$icon_bg}'>
                        <i class='fa-solid {$icon} text-xl text-white'></i>
                    </div>
                    <p class='text-lg font-extrabold flex-1 {$text_color}'>
                        " . htmlspecialchars($message) . "
                    </p>
                    <button @click='show = false' class='flex-shrink-0 text-white/70 hover:text-white transition'>
                        <i class='fa-solid fa-xmark text-xl'></i>
                    </button>
                </div>
            </div>";
        }
    }
}


if ( ! function_exists('xss_clean'))
{
    function xss_clean($string)
    {
        // FIX: Removed reference operator for PHP 8+ compatibility
        $LAVA = lava_instance();
        $LAVA->call->library('antixss');
        return $LAVA->antixss->xss_clean($string);
    }
}


if ( ! function_exists('logged_in'))
{
    //check if user is logged in
    function logged_in() {
        // FIX: Removed reference operator for PHP 8+ compatibility
        $LAVA = lava_instance();
        $LAVA->call->library('lauth');
        if($LAVA->lauth->is_logged_in())
            return true;
    }
}

// CRITICAL FIX: Re-adding the missing is_logged_in alias
if ( ! function_exists('is_logged_in'))
{
    // Alias/Wrapper for the primary logged-in check
    function is_logged_in() {
        return logged_in();
    }
}

if ( ! function_exists('get_user_id'))
{
    //get user id
    function get_user_id() {
        // FIX: Removed reference operator for PHP 8+ compatibility
        $LAVA = lava_instance();
        $LAVA->call->library('lauth');
        return $LAVA->lauth->get_user_id();
    }
}

if ( ! function_exists('get_username'))
{
    //get username
    function get_username($user_id) {
        // FIX: Removed reference operator for PHP 8+ compatibility
        $LAVA = lava_instance();
        $LAVA->call->library('lauth');
        return $LAVA->lauth->get_username($user_id);
    }
}

if ( ! function_exists('email_exist'))
{
    function email_exist($email) {
        // FIX: Removed reference operator for PHP 8+ compatibility
        $LAVA = lava_instance();
        $LAVA->db->table('users')->where('email', $email)->get();
        return ($LAVA->db->row_count() > 0) ? true : false;
    }
}