<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

/**
 * Controller: Auth
 * * Handles user authentication, registration, login, and email verification.
 */

class Auth extends Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // FIX: Changed 'email_helper' to 'email' so it correctly loads the email_helper.php file.
        $this->call->library('lauth');       
        $this->call->helper('email');    
        $this->call->model('AuthModel');        
        $this->call->model('OrgModel');
    }
    
    // ----------------------------------------------------------------------
    //  Registration Logic
    // ----------------------------------------------------------------------

    public function register()
    {
        // Prevent logged in users from registering
        if ($this->lauth->is_logged_in()) { 
             redirect(BASE_URL);
             return;
        }

        if ($this->io->method() == 'post') {
            $username = trim($this->io->post('username'));
            $email = trim($this->io->post('email'));
            $password = $this->io->post('password');
            $confirm = $this->io->post('password_confirmation');
            $fName = trim($this->io->post('fName'));
            $lName = trim($this->io->post('lName'));
            
            // Generate a secure token for email verification
            $email_token = bin2hex(random_bytes(32)); 

            // Basic validation
            $errors = [];
            
            // ENHANCED ERROR MESSAGE 1: Required fields
            if (empty($fName) || empty($lName) || empty($email) || empty($username) || empty($password)) {
                $errors[] = 'Please fill out all required fields: First Name, Last Name, Username, Email, and Password.';
            }

            // ENHANCED ERROR MESSAGE 2: Password mismatch
            if ($password !== $confirm) {
                $errors[] = 'The password and confirmation password do not match.';
            }

            // ENHANCED ERROR MESSAGE 3: Invalid email format
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                 $errors[] = 'The email address format is invalid. Please enter a proper email.';
            }
            
            // Check for existing user (uniqueness checks)
            if (empty($errors)) {
                
                // --- NEW RECAPTCHA CHECK START ---
                $recaptcha_response = $this->io->post('g-recaptcha-response');
                $recaptcha_secret = config_item('recaptcha_secret_key');
                
                if (empty($recaptcha_response)) {
                    $errors[] = 'Please complete the reCAPTCHA verification.';
                } elseif (!empty($recaptcha_secret)) {
                    $verification_url = 'https://www.google.com/recaptcha/api/siteverify';
                    
                    $url_data = http_build_query([
                        'secret'   => $recaptcha_secret,
                        'response' => $recaptcha_response,
                        'remoteip' => $this->io->ip_address()
                    ]);
                    
                    $client = curl_init($verification_url);
                    curl_setopt($client, CURLOPT_POST, true);
                    curl_setopt($client, CURLOPT_POSTFIELDS, $url_data);
                    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($client, CURLOPT_SSL_VERIFYPEER, false); 
                    $response = curl_exec($client);
                    curl_close($client);

                    $result = json_decode($response, true);

                    if (!$result || !($result['success'] ?? false)) {
                        $errors[] = 'reCAPTCHA verification failed. Please try again.';
                    }
                } else {
                    $errors[] = 'System Configuration Error: reCAPTCHA secret key is missing.';
                }

                if ($this->AuthModel->exists(['email' => $email])) {
                    $errors[] = 'This email address is already registered. Try logging in or recovering your password.';
                }
                // ENHANCED ERROR MESSAGE 5: Username exists
                if ($this->AuthModel->exists(['username' => $username])) {
                    $errors[] = 'The username is already taken. Please choose a different one.';
                }
            }
            
            // Re-check errors after validation and reCAPTCHA
            if (!empty($errors)) {
                set_flash_alert('danger', implode(' ', $errors));
                redirect('register');
                return;
            }
            
            // Register the user (lauth assumed)
            $user_id = $this->lauth->register($username, $email, $password, $email_token);
            
            if ($user_id) {
                
                $default_role = $this->OrgModel->getRoleIdByName('General Member');
                $role_id = $default_role['id'] ?? NULL;
                $this->AuthModel->filter(['id' => $user_id])->update([
                    'fname' => $fName,
                    'lname' => $lName,
                    'email_verification_token' => $email_token,
                    'role_id' => $role_id // <-- SET: Default role ID
                ]);

                $verify_link = BASE_URL . '/verify-email?token=' . $email_token;
                $subject = 'Please verify your email address for Maestro';
                $message = '
                    <div style="font-family: \'Poppins\', sans-serif; background-color: #0b0f0c; color: #ffffff; padding: 20px;">
                        <div style="max-width: 600px; margin: 0 auto; background-color: #151a17; border-radius: 12px; border: 1px solid #10b981; box-shadow: 0 4px 10px rgba(0,0,0,0.5); padding: 30px; text-align: center;">
                            
                            <div style="margin-bottom: 20px;">
                                <span style="font-size: 48px; line-height: 1; color: #10b981;">&#9993;</span>
                            </div>
                            
                            <h1 style="color: #10b981; font-size: 24px; margin: 0 0 10px 0;">Verify Your Maestro Account</h1>
                            
                            <p style="color: #e5e7eb; font-size: 16px; margin: 0 0 25px 0;">
                                Dear '.htmlspecialchars($fName).', thank you for registering! Please confirm your email address to activate your account.
                            </p>
                            
                            <a href="' . $verify_link . '" 
                                style="background-color: #10b981; color: #151a17; padding: 12px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px; display: inline-block; box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);">
                                ACTIVATE ACCOUNT
                            </a>
                            
                            <p style="font-size: 12px; color: #9ca3af; margin-top: 25px;">
                                If you did not sign up, please ignore this email.
                            </p>
                            <p style="font-size: 10px; color: #4b5563; word-break: break-all;">
                                Link: <a href="' . $verify_link . '" style="color: #9ca3af;">' . $verify_link . '</a>
                            </p>
                        </div>
                    </div>
                ';
                
                sendEmail($email, $subject, $message);
                
                set_flash_alert('success', 'Registration successful! An email verification link has been sent to ' . htmlspecialchars($email) . '.');
                redirect('login');
                return;
            } else {
                set_flash_alert('danger', 'Registration failed due to a server issue. Please try again.');
                redirect('register');
                return;
            }
        }
        
        $this->call->view('register');
    }
    
    // ----------------------------------------------------------------------
    //  Login Logic
    // ----------------------------------------------------------------------

    public function login()
    {
        // Redirect if already logged in
        if ($this->lauth->is_logged_in()) {
            redirect(BASE_URL . '/org/dashboard'); 
            return;
        }

        if ($this->io->method() == 'post') {

            $identifier = $this->io->post('username'); 
            $password   = $this->io->post('password');

            $user = $this->AuthModel->get_user_by_username_or_email($identifier);

            if (!is_object($user)) {
                set_flash_alert('danger', 'Authentication failed. Invalid username/email or password.');
                redirect('login');
                return;
            }
            
            if (!isset($user->email_verified) || $user->email_verified != 1) {
                set_flash_alert('danger', 'Your email address has not been verified. Please check your inbox for the verification link.');
                redirect('login'); 
                return;
            }

            if (!password_verify($password, $user->password)) {
                set_flash_alert('danger', 'Authentication failed. Invalid username/email or password.');
                redirect('login');
                return;
            }

            $full_user_details = $this->OrgModel->getMemberById($user->id);

            if (!isset($_SESSION)) {
                session_start(); 
            }
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;

            $role_name = 'General Member'; // Default/Fallback
            $role_id = $full_user_details['role_id'] ?? NULL;
            
            if ($role_id) {
                $roles = $this->OrgModel->getRoles();
                $found_role = array_filter($roles, fn($r) => (int)($r['id'] ?? 0) === (int)$role_id);
                $role_name = reset($found_role)['name'] ?? 'General Member';
            }
            
            $_SESSION['user_name'] = ($full_user_details['fname'] ?? '') . ' ' . ($full_user_details['lname'] ?? '');
            $_SESSION['user_role'] = $role_name; // Set the actual role name
            $_SESSION['is_logged_in'] = true;
            
            if (function_exists('session_write_close')) {
                session_write_close();
            }
            
            set_flash_alert('success', 'Welcome back, ' . htmlspecialchars($user->username) . '! Redirecting you to the dashboard.');

            redirect(BASE_URL . '/org/dashboard'); 
            exit; 
        }

        $this->call->view('login');
    }

    // ----------------------------------------------------------------------
    //  Verification and Logout Logic
    // ----------------------------------------------------------------------

    public function verify_email()
    {
        $token = $this->io->get('token');
        
        if (empty($token)) {
            set_flash_alert('danger', 'Verification link is incomplete. Invalid token provided.');
            redirect('login');
            return;
        }

        $found = $this->AuthModel->find_by_token($token);
        
        if (!is_object($found)) { 
            set_flash_alert('danger', 'The verification link is invalid or has expired. Please contact support.');
            redirect('login');
            return;
        }

        $ok = $this->AuthModel->verify_email($found->id); 
        
        if ($ok) {
            set_flash_alert('success', 'Email successfully verified! You can now log in to your account.');
        } else {
            set_flash_alert('danger', 'Email verification failed due to a system error. Please contact support.');
        }
        
        redirect('login');
        exit;
    }

    public function logout() {
        if (isset($_SESSION)) {
             session_destroy();
        }
        
        set_flash_alert('success', 'You have been successfully logged out.');
        redirect('login');
        return; 
    }

    
    public function test_email()
    {
        // Define test parameters
        $to = 'justinebolanos1018@gmail.com'; 
        $subject = 'Maestro PHPMailer Test';
        $body = '
            <h2>PHPMailer Test Successful!</h2>
            <p>This message confirms that your email configuration in <code>app/config/email.php</code> and the PHPMailer helper are working.</p>
            <p><b>Sent at:</b> ' . date('Y-m-d H:i:s') . '</p>
        ';

        $result = sendEmail($to, $subject, $body);

        if ($result === true) {
            echo '<h3 style="color:green;">✅ PHPMailer Test Email sent successfully to ' . htmlspecialchars($to) . '!</h3>';
        } else {
            echo '<h3 style="color:red;">❌ PHPMailer Test Email failed.</h3>';
            echo '<p>Check your <code>app/config/email.php</code> settings and PHP logs for details: ' . htmlspecialchars($result) . '</p>';
        }
    }
}