<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maestro | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'maestro-green': '#00350cff',
                        'maestro-dark': '#0b0f0c',
                        'brand-glow': '#10b981', /* green-500 */
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            /* Animated Gradient Background */
            background: linear-gradient(135deg, #000, #005e19ff, #004314ff);
            background-size: 400% 400%;
            animation: gradient-animation 15s ease infinite;
            font-family: 'Poppins', sans-serif;
        }

        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-card {
            /* Frosted glass/dark card effect */
            background: rgba(11, 15, 12, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }
        
        .auth-btn {
            background-color: #005e19ff;
            transition: all 0.3s ease;
        }

        .auth-btn:hover {
            background-color: #00a72cff;
        }

        /* Enhanced logo glow effect */
        .logo-glow {
            filter: invert(1);
            text-shadow: 0 0 10px var(--tw-colors-brand-glow), 0 0 20px var(--tw-colors-brand-glow);
            transition: transform 0.3s ease;
        }

        /* Keyframe for element entrance */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-in {
            animation: fadeInDown 0.6s ease-out forwards;
        }
        
        /* FIX: Hide browser's native password reveal/clear icons */
        .password-input-field::-webkit-search-cancel-button,
        .password-input-field::-webkit-inner-spin-button,
        .password-input-field::-webkit-outer-spin-button,
        .password-input-field::-ms-reveal {
            -webkit-appearance: none;
            appearance: none;
            display: none;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center text-white p-4">

    <div class="grid grid-cols-1 lg:grid-cols-2 max-w-4xl w-full min-h-[500px] rounded-3xl overflow-hidden login-card">
        
        <div class="hidden lg:flex flex-col items-center justify-center p-8 bg-maestro-green/90 relative">
            <div class="absolute inset-0 bg-cover bg-center opacity-10" style="background-image: url('maestrologo.png');"></div>
            
            <div class="z-10 text-center">
                <img src="maestrologo.png" alt="Maestro Logo" class="w-36 h-36 mb-4 drop-shadow-lg logo-glow mx-auto" style="animation-delay: 0.1s;">
                <h1 class="text-5xl font-bold tracking-widest text-black mb-2 logo-glow" style="animation-delay: 0.2s;">MAESTRO</h1>
            </div>
        </div>

        <div class="p-8 md:p-12 flex flex-col justify-center">
            
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-green-400 animate-in" style="animation-delay: 0.1s;">Welcome Back</h2>
                <p class="text-gray-400 mt-1 animate-in" style="animation-delay: 0.2s;">Sign in to your account.</p>
            </div>

            <?php if (function_exists('flash_alert')) flash_alert(); ?> 

            <form action="<?= BASE_URL ?>/login" method="POST" class="space-y-6">
                
                <div class="animate-in" style="animation-delay: 0.3s;">
                    <label for="username" class="sr-only">Username or Email</label>
                    <input 
                        type="text" 
                        name="username" 
                        id="username"
                        placeholder="Username or Email"
                        required
                        class="w-full py-3 px-4 rounded-lg bg-white/5 border border-green-800 text-white placeholder-gray-500 input-style focus:ring-0 focus:border-green-500 transition"
                    >
                </div>

                <div x-data="{ passwordShown: false }" class="relative animate-in" style="animation-delay: 0.4s;">
                    <label for="password" class="sr-only">Password</label>
                    <input 
                        :type="passwordShown ? 'text' : 'password'" 
                        name="password" 
                        id="password"
                        placeholder="Password" 
                        required
                        class="password-input-field w-full py-3 px-4 pr-12 rounded-lg bg-white/5 border border-green-800 text-white placeholder-gray-500 input-style focus:ring-0 focus:border-green-500 transition">
                    
                    <button type="button" @click="passwordShown = !passwordShown" 
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/90 hover:text-white transition">
                        <i class="fa-solid" :class="passwordShown ? 'fa-eye-slash' : 'fa-eye'"></i>
                    </button>
                </div>
                
                <button 
                    type="submit" 
                    class="auth-btn w-full py-3 rounded-lg font-bold text-lg text-white shadow-lg shadow-maestro-green/50 hover:bg-green-700/80 transition-all duration-300 animate-in" style="animation-delay: 0.5s;">
                    <i class="fa-solid fa-sign-in-alt mr-2"></i> Log In
                </button>

            </form>

            <p class="text-center text-sm text-gray-500 mt-6 animate-in" style="animation-delay: 0.6s;">
                Don’t have an account? 
                <a href="<?= BASE_URL ?>/register" class="text-green-400 hover:text-green-300 font-semibold transition-colors duration-200">Register Here</a>
            </p>
        </div>

    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>
</body>
</html>