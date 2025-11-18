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

        // Load necessary libraries and models
        $this->call->library('Mailer');
        $this->call->library('lauth');          // Authentication library
        $this->call->helper('email_helper');    // For sending emails
        $this->call->model('AuthModel');        // Your user model
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
                // ENHANCED ERROR MESSAGE 4: Email exists
                if ($this->AuthModel->exists(['email' => $email])) {
                    $errors[] = 'This email address is already registered. Try logging in or recovering your password.';
                }
                // ENHANCED ERROR MESSAGE 5: Username exists
                if ($this->AuthModel->exists(['username' => $username])) {
                    $errors[] = 'The username is already taken. Please choose a different one.';
                }
            }

            if (!empty($errors)) {
                set_flash_alert('danger', implode(' ', $errors));
                redirect('register');
                return;
            }

            // Register the user (lauth assumed)
            $user_id = $this->lauth->register($username, $email, $password, $email_token);
            
            if ($user_id) {
                // Update user details
                $this->AuthModel->filter(['id' => $user_id])->update([
                    'fname' => $fName,
                    'lname' => $lName,
                    'email_verification_token' => $email_token 
                ]);

                // Prepare verification email content
                $verify_link = BASE_URL . '/verify-email?token=' . $email_token;
                $subject = 'Please verify your email address for Maestro';
                $message = '
                    <p>Hi ' . htmlspecialchars($fName) . ',</p>
                    <p>Thank you for registering. Please click the button below to verify your email address:</p>
                    <p style="text-align:center;">
                        <a href="' . $verify_link . '" style="background-color: #00a72c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">Verify Email Address</a>
                    </p>
                    <p>If the button does not work, copy and paste this URL into your browser:</p>
                    <p><small>' . $verify_link . '</small></p>
                    <p>If you did not sign up, please ignore this email.</p>
                ';

                // Send email
                if (function_exists('sendEmail')) {
                    sendEmail($email, $subject, $message);
                } else {
                    $this->Mailer
                        ->to($email)
                        ->subject($subject)
                        ->html($message)
                        ->send();
                }

                // ENHANCED SUCCESS MESSAGE 1
                set_flash_alert('success', 'Registration successful! An email verification link has been sent to ' . htmlspecialchars($email) . '.');
                redirect('login');
                return;
            } else {
                // ENHANCED ERROR MESSAGE 6: General failure
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

            // ENHANCED ERROR MESSAGE 7: Invalid credentials (general fail)
            if (!is_object($user)) {
                set_flash_alert('danger', 'Authentication failed. Invalid username/email or password.');
                redirect('login');
                return;
            }

            // ENHANCED ERROR MESSAGE 8: Account not verified
            if (isset($user->email_verified) && $user->email_verified == 0) {
                set_flash_alert('danger', 'Your account is not verified. Please check your inbox for the verification link.');
                redirect('login');
                return;
            }

            // ENHANCED ERROR MESSAGE 9: Invalid password
            if (!password_verify($password, $user->password)) {
                set_flash_alert('danger', 'Authentication failed. Invalid username/email or password.');
                redirect('login');
                return;
            }

            // 4. Successful login - Start session
            if (!isset($_SESSION)) {
                session_start(); 
            }
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            // --- MODIFICATION START ---
            // Assuming the $user object fetched from the AuthModel contains 'fname', 'lname', and 'role' fields.
            $_SESSION['user_name'] = $user->fname . ' ' . $user->lname;
            $_SESSION['user_role'] = $user->role ?? 'Organization Member'; // Default to Member if role field is missing
            // --- MODIFICATION END ---
            $_SESSION['is_logged_in'] = true;
            
            // --- CRITICAL FIX: Ensures session data is saved before redirect ---
            if (function_exists('session_write_close')) {
                session_write_close();
            }
            
            // ENHANCED SUCCESS MESSAGE 2
            set_flash_alert('success', 'Welcome back, ' . htmlspecialchars($user->username) . '! Redirecting you to the dashboard.');

            // Redirect to dashboard
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
        
        // ENHANCED ERROR MESSAGE 10: Missing token
        if (empty($token)) {
            set_flash_alert('danger', 'Verification link is incomplete. Invalid token provided.');
            redirect('login');
            return;
        }

        $found = $this->AuthModel->find_by_token($token);
        
        // ENHANCED ERROR MESSAGE 11: Invalid/expired token
        if (!is_object($found)) { 
            set_flash_alert('danger', 'The verification link is invalid or has expired. Please contact support.');
            redirect('login');
            return;
        }

        $ok = $this->AuthModel->verify_email($found->id); 
        
        if ($ok) {
            // ENHANCED SUCCESS MESSAGE 3
            set_flash_alert('success', 'Email successfully verified! You can now log in to your account.');
        } else {
            // ENHANCED ERROR MESSAGE 12: DB verification failed
            set_flash_alert('danger', 'Email verification failed due to a system error. Please contact support.');
        }
        
        redirect('login');
        exit;
    }

    public function logout() {
        // FIX: Replaced $this->lauth->logout() with direct session destruction
        if (isset($_SESSION)) {
             session_destroy();
        }
        
        // ENHANCED SUCCESS MESSAGE 4
        set_flash_alert('success', 'You have been successfully logged out.');
        redirect('login');
    }

    // Utility function to test email configuration
    public function test_email()
    {
        // Change this to your own address for testing
        $to = 'test@example.com'; 
        $subject = 'Maestro Email Test';
        $body = '
            <h2>Email Test Successful!</h2>
            <p>This message confirms that your email configuration and library are working.</p>
            <p><b>Sent at:</b> ' . date('Y-m-d H:i:s') . '</p>
        ';

        // Use your existing Emailer library
        $result = $this->Mailer
            ->to($to)
            ->subject($subject)
            ->html($body)
            ->send();

        // Output simple result in browser
        if ($result === true) {
            echo '<h3 style="color:green;">✅ Email sent successfully to ' . htmlspecialchars($to) . '!</h3>';
        } else {
            echo '<h3 style="color:red;">❌ Failed to send email.</h3>';
            echo '<pre>' . htmlspecialchars($result) . '</pre>';    
        }
    }
}