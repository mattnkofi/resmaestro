<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; // Also import SMTP class if using constants like ENCRYPTION_STARTTLS

// CRITICAL FIX: Changed ROOTPATH to the globally defined ROOT_DIR
// This ensures PHPMailer's vendor files are loaded correctly.
require_once ROOT_DIR . 'vendor/autoload.php'; 

/**
 * Helper: email_helper.php
 * * Handles email sending via PHPMailer, loading settings from config/email.php.
 */

function sendEmail($to, $subject, $message) {
    
    // NOTE: We rely on autoload.php to load the 'email' config file.
    
    // Retrieve configuration settings from config/email.php
    $config = [
        // Using config_item() assumes the config is loaded via autoload.php
        'host'       => config_item('host'),
        'username'   => config_item('username'),
        'password'   => config_item('password'),
        'port'       => (int)config_item('port') ?: 587,
        'from_email' => config_item('from_email'),
        'from_name'  => config_item('from_name'),
    ];

    // FIX: Using the imported class name directly, thanks to the 'use' statements.
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        // Using imported class name for constant
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = $config['port'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        // CRITICAL: Add plain text alternative to lower spam score
        $mail->AltBody = strip_tags($message); 

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
        return $mail->ErrorInfo; // Return error string for debug output
    }
}