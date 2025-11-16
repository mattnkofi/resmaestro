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
        // FIX: Removed reference operator for PHP 8+ compatibility
        $LAVA = lava_instance();
        $alert_type = $LAVA->session->flashdata('alert');
        $message = $LAVA->session->flashdata('message');
        
        if ($alert_type !== null) {

            // The target message is primarily for login, but all DANGER alerts 
            // on registration should use the highly visible style for consistency.
            if ($alert_type === 'danger') {
                // 1. CUSTOM NOTIFICATION UI for ALL DANGER Alerts (uses credential-error-notification CSS)
                echo '
                <div class="credential-error-notification">
                    <svg class="credential-error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <!-- Exclamation Triangle Icon -->
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.332 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span>' . htmlspecialchars($message) . '</span>
                </div>';
            } else {
                // 2. DEFAULT ALERT STRUCTURE for SUCCESS messages (uses the new alert-success CSS)
                echo '
                <div class="alert alert-' . $alert_type . ' p-4 rounded-lg mb-6 text-center text-sm">
                    ' . htmlspecialchars($message) . '
                </div>
                ';
            }
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