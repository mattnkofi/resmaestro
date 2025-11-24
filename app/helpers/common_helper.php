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
            $bg_color = $is_success ? 'bg-green-700' : 'bg-red-700'; // Determine background color
            $icon = $is_success ? 'fa-check-circle' : 'fa-triangle-exclamation'; // Determine icon

            // Toast HTML/Alpine.js Structure
            echo "
            <div x-data=\"{ show: true }\" 
                 x-show=\"show\" 
                 x-init=\"setTimeout(() => show = false, 5000)\"
                 x-transition:enter=\"transition ease-out duration-300\"
                 x-transition:enter-start=\"opacity-0 translate-y-full sm:translate-y-0 sm:translate-x-full\"
                 x-transition:enter-end=\"opacity-100 translate-y-0 sm:translate-x-0\"
                 x-transition:leave=\"transition ease-in duration-200\"
                 x-transition:leave-start=\"opacity-100 translate-y-0 sm:translate-x-0\"
                 x-transition:leave-end=\"opacity-0 translate-y-full sm:translate-y-0 sm:translate-x-full\"
                 class='fixed bottom-0 right-0 p-4 z-50 w-full max-w-sm'>
                
                <div class='{$bg_color} text-white p-4 rounded-lg shadow-2xl flex items-center space-x-3 border border-white/20'>
                    <i class='fa-solid {$icon} text-lg'></i>
                    <p class='text-sm font-medium flex-1'>
                        " . htmlspecialchars($message) . "
                    </p>
                    <button @click='show = false' class='flex-shrink-0 text-white opacity-75 hover:opacity-100 transition'>
                        <i class='fa-solid fa-xmark'></i>
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