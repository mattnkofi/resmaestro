<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; padding: 30px; border: 1px solid #e0e0e0; border-radius: 8px; }
        .header { font-size: 24px; font-weight: 600; color: #4f46e5; }
        .content { margin-top: 20px; }
        .content p { margin-bottom: 20px; }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #6366f1;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
        }
        .footer { margin-top: 30px; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Welcome to VCMS!</div>
        <div class="content">
            <p>Hi <?= html_escape($name); ?>,</p>
            <p>Thanks for registering. Please click the button below to verify your email address and activate your account.</p>
            <a href="<?= site_url('auth/verify/' . $token); ?>" class="button">Verify My Account</a>
            <p style="margin-top: 20px;">If the button doesn't work, copy and paste this link into your browser:</p>
            <p style="font-size: 12px; word-break: break-all;"><?= site_url('auth/verify/' . $token); ?></p>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y'); ?> VCMS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>