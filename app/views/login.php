<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maestro | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        /* Background gradient animation */
    body {
        background: linear-gradient(135deg, #000, #005e19ff, #004314ff);
        background-size: 200% 200%;
        font-family: 'Poppins', sans-serif;

    }

        /* Glow and transition effects */
        .card {
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 1rem;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.25);
        }

        .glow-text {
            /* Fixed broken CSS definition and set consistent style */
            text-shadow: 0 0 10px #000000ff, 0 0 20px #000000ff;
        }

        .input-style {
            transition: all 0.3s ease;
        }

        .input-style:focus {
            background-color: rgba(16, 185, 129, 0.2);
            box-shadow: 0 0 10px #10b981;
        }

        .auth-btn {
            background-color: #005e19ff;
            transition: all 0.3s ease;
        }

        .auth-btn:hover {
            background-color: #00a72cff;
            box-shadow: 0 0 15px #000000ff;
            transform: scale(1.02);
        }
        
        /* --- NEW CSS FOR HIGHLY VISIBLE CREDENTIAL ERROR NOTIFICATION UI --- */
        .credential-error-notification {
            /* Positioning relative to the card */
            position: relative; 
            
            /* High visibility styling */
            padding: 1rem 1.5rem; 
            margin-bottom: 1.5rem; 
            border-radius: 0.75rem; 
            
            /* Background & Border - High Contrast Red */
            background-color: rgba(185, 28, 28, 0.95); /* Tailwind: bg-red-700, slightly transparent */
            color: #fee2e2; /* Tailwind: text-red-100 */
            
            /* Elevation/Glow for prominence */
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.6); 
            
            font-weight: 700; /* Tailwind: font-bold */
            text-align: center;
            display: flex; 
            align-items: center;
            justify-content: center;
            gap: 0.75rem; 
            
            /* Subtle animation to draw attention */
        }

        .credential-error-icon {
            color: #fee2e2; 
            width: 1.5rem;
            height: 1.5rem;
            flex-shrink: 0;
        }

        @keyframes pulse-border {
            from {
                box-shadow: 0 5px 25px rgba(239, 68, 68, 0.4);
            }
            to {
                box-shadow: 0 5px 30px rgba(239, 68, 68, 0.8);
            }
        }
        /* ------------------------------------------------------------------ */
    </style>
</head>
<body class="min-h-screen flex items-center justify-center text-gray-100 p-4">

    <div class="card w-full max-w-md p-8 shadow-2xl">
        <div class="flex flex-col items-center">
            <!-- Replaced missing image with inline SVG Icon (Placeholder) -->
            <img src="maestrologo.png" alt="Maestro Logo" class="w-16 h-16 mb-3 drop-shadow-lg hover:scale-110 transition-transform duration-300">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span class="text-3xl font-bold tracking-widest text-green-400 glow-text">MAESTRO</span>
        </div>

        <p class="text-center text-gray-400 italic mb-6">Log in to continue</p>
        
        <!-- Display Flash Alert Messages Here -->
        <!-- This calls the modified flash_alert() helper function -->
        <?php if (function_exists('flash_alert')) flash_alert(); ?> 

        <form action="<?= BASE_URL ?>/login" method="POST" class="space-y-5">
            <div>
                <input 
                    type="text" 
                    name="username" 
                    placeholder="Enter your username or email" 
                    required
                    class="input-style w-full px-4 py-2 rounded-lg bg-white/10 text-white italic placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400"
                >
            </div>

            <div>
                <input 
                    type="password" 
                    name="password" 
                    placeholder="Enter your password" 
                    required
                    class="input-style w-full px-4 py-2 rounded-lg bg-white/10 text-white italic placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>

            <button 
                type="submit" 
                class="auth-btn w-full py-2 mt-2 rounded-lg font-bold text-white transition-all duration-300">
                Login
            </button>

            <p class="text-center text-sm text-gray-400 mt-4">
                Don’t have an account? 
                <a href="<?= BASE_URL ?>/register" class="text-white hover:text-green-600 font-bold transition-colors duration-200">Register</a>
            </p>
        </form>
    </div>

</body>
</html>