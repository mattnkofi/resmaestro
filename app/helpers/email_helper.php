<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once ROOTPATH . 'vendor/autoload.php';

/**
 * Helper: email_helper.php
 * 
 * Handles email sending via PHPMailer.
 */

function sendEmail($to, $subject, $message) {
    $config = [
        'host' => 'smtp.gmail.com',
        'username' => 'your_email@gmail.com',
        'password' => 'your_app_password',
        'port' => 587,
        'from_email' => 'your_email@gmail.com',
        'from_name' => 'Your System Name',
    ];

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = $config['port'];

        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
        return false;
    }
}


