<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maestro | Register</title>
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

    .auth-btn {
      background-color: #005e19ff;
      transition: all 0.3s ease;
    }

    .auth-btn:hover {
      background-color: #00a72cff;
      box-shadow: 0 0 15px #000000ff;
      transform: scale(1.02);
    }

    .glow-text {
      text-shadow: 0 0 10px #000000ff, 0 0 20px #000000ff;
    }

    .input-style {
      transition: all 0.3s ease;
    }

    .input-style:focus {
      background-color: rgba(16, 185, 129, 0.2);
      box-shadow: 0 0 10px #10b981;
    }

    .card {
      transition: all 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 0 25px rgba(16, 185, 129, 0.2);
    }
    
    /* --- NEW CSS FOR HIGHLY VISIBLE CREDENTIAL ERROR NOTIFICATION UI (Used for all DANGER alerts here) --- */
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
      
      font-weight: 300; /* Tailwind: font-bold */
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

    /* Fallback/Standard Alert Styling for Success Messages */
    .alert-success {
      background-color: rgba(16, 185, 129, 0.95); /* Darker Green */
      color: #e6ffed; /* Light Green Text */
      border: 1px solid #10b981;
      padding: 1rem 1.5rem; 
      border-radius: 0.75rem;
      margin-bottom: 1.5rem;
      font-weight: 600;
      text-align: center;
    }

  </style>
</head>
<body class="min-h-screen flex items-center justify-center text-gray-100 p-4">

  <div class="card bg-black/40 backdrop-blur-lg border border-green-800/40 rounded-2xl shadow-2xl w-full max-w-md p-8">
    <div class="flex flex-col items-center mb-6">
      <div class="relative">
        <img src="maestrologo.png" alt="Maestro Logo" class="w-16 h-16 mb-2 drop-shadow-lg hover:scale-110 transition-transform duration-300">
      </div>
      <span class="text-3xl font-bold tracking-widest text-green-400 glow-text">MAESTRO</span>
    </div>

    <!-- Display Flash Alert Messages Here -->
    <?php if (function_exists('flash_alert')) flash_alert(); ?>

    <form action="<?=BASE_URL?>/register" method="post" class="space-y-4">
      <div>
        <!--<label class="block text-sm font-medium text-green-300 mb-1">First Name</label>-->
        <input type="text" name="fName" placeholder="First name"
          class="input-style w-full px-4 py-2 rounded-lg bg-white/10 text-white italic placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400" required>
      </div>

      <div>
        <!--<label class="block text-sm font-medium text-green-300 mb-1">Last Name</label>-->
        <input type="text" name="lName" placeholder="Last name"
          class="input-style w-full px-4 py-2 rounded-lg bg-white/10 text-white italic placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400" required>
      </div>

      <div>
        <!--<label class="block text-sm font-medium text-green-300 mb-1">Email</label>-->
        <input type="email" name="email" placeholder="Email address"
          class="input-style w-full px-4 py-2 rounded-lg bg-white/10 text-white italic placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400" required>
      </div>

      <div>
        <!--<label class="block text-sm font-medium text-green-300 mb-1">Username</label>-->
        <input type="text" name="username" placeholder="Choose a username"
          class="input-style w-full px-4 py-2 rounded-lg bg-white/10 text-white italic placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400" required>
      </div>

      <div>
        <!--<label class="block text-sm font-medium text-green-300 mb-1">Password</label>-->
        <input type="password" name="password" placeholder="Create a password"
          class="input-style w-full px-4 py-2 rounded-lg bg-white/10 text-white italic placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400" required>
      </div>

      <div>
        <!--<label class="block text-sm font-medium text-green-300 mb-1">Confirm Password</label>-->
        <input type="password" name="password_confirmation" placeholder="Confirm password"
          class="input-style w-full px-4 py-2 rounded-lg bg-white/10 text-white italic placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-400" required>
      </div>

      <button type="submit"
        class="auth-btn w-full py-2 mt-4 rounded-lg font-bold text-white transition-all duration-300 hover:shadow-[0_0_15px_#10b981]">
        Register
      </button>

      <p class="text-center text-sm text-gray-400 mt-4">
        Already have an account?
        <a href="<?=BASE_URL?>/login" class="text-white hover:text-green-600 font-bold transition-colors duration-200">Login</a>
      </p>
    </form>
  </div>

</body>
</html>