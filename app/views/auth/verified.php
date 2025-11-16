<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verified - VCMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --success-color: #10b981;
            --bg-card: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-tertiary: #f1f5f9;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --border-color: #e2e8f0;
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius-xl: 1rem;
            --radius-md: 0.5rem;
        }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-tertiary) 100%); display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .verified-container {
            width: 100%;
            max-width: 420px;
            padding: 3rem 2.5rem;
            background: var(--bg-card);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border-color);
            text-align: center;
        }
        .icon {
            /* You need an actual icon here (e.g., a green checkmark emoji or SVG/FontAwesome) */
            font-size: 4rem;
            color: var(--success-color);
            line-height: 1;
        }
        h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 1.5rem 0 0.5rem 0;
        }
        p {
            color: var(--text-secondary);
            margin: 0 0 1.5rem 0;
        }
        .btn-primary {
            display: inline-block;
            padding: 0.85rem 1.5rem;
            border: none;
            border-radius: var(--radius-md);
            background: var(--primary-color);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="verified-container">
        <div class="icon">✓</div> 
        <h1>Account Verified!</h1>
        <p>Your email has been successfully verified. You can now log in to your account.</p>
        <a href="<?= site_url('auth/login'); ?>" class="btn-primary">Go to Login</a>
    </div>
</body>
</html>