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
        // 1. Core Logic (Unchanged)
        $LAVA = lava_instance();
        $alert_type = $LAVA->session->flashdata('alert');
        $message = $LAVA->session->flashdata('message');
        
        if ($alert_type !== null) {
            $is_success = ($alert_type === 'success');
            
            // 2. STYLING (UNCHANGED)
            $icon = $is_success ? 'fa-circle-check' : 'fa-triangle-exclamation';
            $panel_bg = $is_success ? 'bg-green-700' : 'bg-red-700'; 
            $text_color = 'text-white'; 
            $icon_color = 'text-white';
            $progress_color = $panel_bg; 
            $title_text = $is_success ? 'OPERATION SUCCESS' : 'CRITICAL ERROR'; 
            
            // Re-using the user's specific sound URL
            $sound_url = BASE_URL."/fears-to-fathom-notification-sound.mp3";

            // 3. AUTO-DISMISS & NO COUNTDOWN STRUCTURE
            echo "
            <div x-data=\"{ 
                    show: true, 
                    played: false,
                    duration: 3000, 
                    progress: 100,
                    timer: null,
                    interval: null,
                    
                    playSound() { 
                        if (this.played) return;
                        const audio = document.getElementById('toast-sound-final'); 
                        if(audio) { 
                            audio.play().catch(e => console.log('Autoplay blocked: ' + e)); 
                            this.played = true;
                        } 
                    },
                    
                    // Progress bar and timer logic remains to control the visual bar and auto-close
                    startTimer() {
                        this.progress = 100;
                        this.timer = setTimeout(() => this.show = false, this.duration);
                        const updateFrequency = this.duration / 100; 

                        this.interval = setInterval(() => {
                            if (this.progress > 0) {
                                this.progress -= 1; 
                            } else {
                                clearInterval(this.interval);
                            }
                        }, updateFrequency);
                    },
                    
                    pauseTimer() {
                        clearTimeout(this.timer);
                        clearInterval(this.interval);
                    },

                    resumeTimer() {
                        const remainingMs = (this.progress / 100) * this.duration;
                        this.timer = setTimeout(() => this.show = false, remainingMs);
                        
                        const updateFrequency = this.duration / 100; 

                        this.interval = setInterval(() => {
                            if (this.progress > 0) {
                                this.progress -= 1;
                            } else {
                                clearInterval(this.interval);
                            }
                        }, updateFrequency);
                    }
                }\" 
                x-show=\"show\" 
                x-init=\"playSound(); startTimer()\"
                @mouseover=\"pauseTimer()\" 
                @mouseleave=\"resumeTimer()\"
                x-transition:enter=\"transition ease-out duration-500\"
                x-transition:enter-start=\"opacity-0 scale-95\"
                x-transition:enter-end=\"opacity-100 scale-100\"
                x-transition:leave=\"transition ease-in duration-300\"
                x-transition:leave-start=\"opacity-100 scale-100\"
                x-transition:leave-end=\"opacity-0 scale-95\"
                class='fixed inset-0 flex items-center justify-center z-50 pointer-events-none'>
                
                <audio id='toast-sound-final' src='{$sound_url}' preload='auto' style='display: none;'></audio>

                <div @click.self=\"show = false\" class='fixed inset-0 bg-black/95 pointer-events-auto' aria-hidden='true'></div> 
                
                <div class='w-full max-w-md {$panel_bg} rounded-xl overflow-hidden transform transition-all pointer-events-auto'>
                    
                    <div class='w-full h-1'>
                        <div class='h-full {$progress_color} transition-all duration-[30ms] ease-linear' :style=\"'width: ' + progress + '%'\"></div>
                    </div>

                    <div class='p-6 flex items-start space-x-4'>
                        <div class='flex-shrink-0 pt-0.5'>
                            <i class='fa-solid {$icon} text-3xl {$icon_color}'></i>
                        </div>
                        
                        <div class='flex-1 min-w-0'>
                            <p class='text-lg font-bold {$text_color}'>{$title_text}</p>
                            <p class='mt-1 text-base {$text_color} opacity-80 overflow-hidden whitespace-normal break-words'>
                                " . htmlspecialchars($message) . "
                            </p>
                        </div>
                        
                        <button @click='show = false' class='flex-shrink-0 text-white opacity-70 hover:opacity-100 transition duration-150 p-1 -mt-2 -mr-2'>
                            <i class='fa-solid fa-xmark text-xl'></i>
                        </button>
                    </div>

                </div>
            </div>";
        }
    }
}


if ( ! function_exists('xss_clean'))
{
    function xss_clean($string)
    {
        $LAVA = lava_instance();
        $LAVA->call->library('antixss');
        return $LAVA->antixss->xss_clean($string);
    }
}


if ( ! function_exists('logged_in'))
{
    function logged_in() {
        $LAVA = lava_instance();
        $LAVA->call->library('lauth');
        if($LAVA->lauth->is_logged_in())
            return true;
    }
}

if ( ! function_exists('is_logged_in'))
{
    function is_logged_in() {
        return logged_in();
    }
}

if ( ! function_exists('get_user_id'))
{
    function get_user_id() {
        $LAVA = lava_instance();
        $LAVA->call->library('lauth');
        return $LAVA->lauth->get_user_id();
    }
}

if ( ! function_exists('get_username'))
{
    function get_username($user_id) {
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