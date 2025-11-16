<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

use SendGrid\Mail\Mail;

/**
 * ------------------------------------------------------------------
 * Mailer Library for LavaLust Framework (Fixed Version)
 * ------------------------------------------------------------------
 * - Handles SendGrid API email sending
 * - Added SSL verification bypass to fix cURL error 60 on localhost
 * - Keeps full compatibility with LavaLust config system
 * ------------------------------------------------------------------
 */
class Mailer
{
    /** @var Mail */
    protected $mail;

    /** @var string */
    protected $apiKey;

    public function __construct()
    {
        $this->autoloadComposer();

        $this->mail   = new Mail();
        $this->apiKey = config_item('sendgrid_api_key');

        $this->setDefaultSender();
    }

    protected function autoloadComposer(): void
    {
        $composer = config_item('composer_autoload');
        if ($composer === TRUE) {
            $path = ROOT_DIR . 'vendor/autoload.php';
        } elseif (is_string($composer)) {
            $path = $composer;
        } else {
            $path = ROOT_DIR . 'vendor/autoload.php';
        }

        if (file_exists($path)) {
            require_once $path;
        }
    }

    protected function setDefaultSender(): void
    {
        $from     = config_item('sendgrid_from');
        $fromName = config_item('sendgrid_from_name') ?: 'Maestro';

        if ($from) {
            $this->mail->setFrom($from, $fromName);
            $this->mail->setReplyTo($from, $fromName);
        }
    }

    protected function baseUrl(): string
    {
        return rtrim(config_item('base_url') ?: '', '/');
    }

    // ---------------- Fluent API ----------------
    public function from(string $email, string $name = ''): self
    {
        $this->mail->setFrom($email, $name ?: $email);
        return $this;
    }

    public function replyTo(string $email, string $name = ''): self
    {
        $this->mail->setReplyTo($email, $name ?: $email);
        return $this;
    }

    public function to(string $email, string $name = ''): self
    {
        $this->mail->addTo($email, $name ?: $email);
        return $this;
    }

    public function cc(string $email, string $name = ''): self
    {
        $this->mail->addCc($email, $name ?: $email);
        return $this;
    }

    public function bcc(string $email, string $name = ''): self
    {
        $this->mail->addBcc($email, $name ?: $email);
        return $this;
    }

    public function subject(string $subject): self
    {
        $this->mail->setSubject($subject);
        return $this;
    }

    public function html(string $html, ?string $altText = null): self
    {
        $this->mail->addContent('text/html', $html);
        $this->mail->addContent('text/plain', $altText ?: strip_tags($html));
        return $this;
    }

    public function text(string $text): self
    {
        $this->mail->addContent('text/plain', $text);
        return $this;
    }

    public function attach(string $path, ?string $name = null): self
    {
        if (is_file($path)) {
            $fileContent = base64_encode(file_get_contents($path));
            $this->mail->addAttachment(
                $fileContent,
                mime_content_type($path),
                $name ?: basename($path),
                'attachment'
            );
        }
        return $this;
    }

    // ---------------- Helper Methods ----------------

    public function sendVerification(string $email, string $username, string $token): bool|string
    {
        $verifyUrl = $this->baseUrl() . "/auth/verify/{$token}";
        $tplPath   = ROOT_DIR . PUBLIC_DIR . '/templates/reset_verify_email.html';

        $html = is_file($tplPath)
            ? file_get_contents($tplPath)
            : '<p>Hi {username}, verify your account here: <a href="{verify_url}">{verify_url}</a></p>';

        $body = strtr($html, [
            '{username}'   => htmlspecialchars($username, ENT_QUOTES, 'UTF-8'),
            '{verify_url}' => $verifyUrl,
        ]);

        return $this->to($email)
            ->subject('Verify your LavaLust account')
            ->html($body)
            ->send();
    }

    public function sendPasswordReset(string $email, string $token): bool|string
    {
        $resetUrl = $this->baseUrl() . "/auth/set-new-password/?token={$token}";
        $tplPath  = ROOT_DIR . PUBLIC_DIR . '/templates/reset_password_email.html';

        $html = is_file($tplPath)
            ? file_get_contents($tplPath)
            : '<p>Reset your password here: <a href="{reset_url}">{reset_url}</a></p>';

        $body = strtr($html, [
            '{reset_url}' => $resetUrl,
        ]);

        return $this->to($email)
            ->subject('Reset your password')
            ->html($body)
            ->send();
    }

    // ---------------- Send ----------------
    public function send(): bool|string
    {
        try {
            // ✅ Added fix: disable SSL verification for localhost
            $options = [
                'verify_ssl' => false,
            ];

            $sendgrid = new \SendGrid($this->apiKey, $options);
            $response = $sendgrid->send($this->mail);
            $status   = $response->statusCode();

            if ($status >= 200 && $status < 300) {
                return true;
            }

            return 'SendGrid Error: ' . $response->body();
        } catch (\Throwable $e) {
            return 'SendGrid Error: ' . $e->getMessage();
        }
    }
}
