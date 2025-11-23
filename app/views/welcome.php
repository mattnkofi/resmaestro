<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maestro | Welcome</title>
  <!-- Google Fonts: Poppins -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <style>
    body {
      background: linear-gradient(135deg, #000, #005e19ff, #004314ff);
      background-size: 200% 200%;
      font-family: 'Poppins', sans-serif;
    }

    .btn-primary {
      background-color: #005e19ff;
      color: white;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #00a72cff;
      transform: scale(1.05);
    }

    .btn-secondary {
      background-color: white;
      color: #04210f;
      transition: all 0.3s ease;
    }

    .btn-secondary:hover {
      background-color: #f3f4f6;
      transform: scale(1.05);
    }

    .card {
      background-color: #005e19ff;
      border-radius: 12px;
      padding: 2rem;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 25px rgba(0,0,0,0.4);
    }

    .counter {
      font-size: 2.5rem;
      font-weight: 700;
      color: #00c936ff;
    }
  </style>
</head>
<body class="min-h-screen flex flex-col">

  <!-- Nav -->
  <nav class="flex justify-between items-center p-6 max-w-7xl mx-auto w-full">
    <div class="flex items-center space-x-3">
      <img src="maestrologo.png" alt="Maestro Logo" class="w-10 h-10">
      <span class="text-white font-bold text-lg">Maestro</span>
    </div>
    <div class="space-x-4">
      <a href="<?= BASE_URL ?>/login" class="text-white font-bold hover:text-green-400 transition-colors">Log in</a>
      <a href="<?= BASE_URL ?>/register" class="btn-primary px-4 py-2 rounded-lg font-semibold">Sign up</a>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="flex-1 flex flex-col items-center justify-center text-center px-4 py-16 relative">
    <img src="maestrologo.png" alt="Maestro Logo" class="w-28 h-28 mb-6 animate-bounce">
    <h1 class="text-white text-5xl md:text-6xl font-bold mb-4 animate-fadeIn">Maestro</h1>
    <p class="text-white italic text-lg md:text-l mb-8 max-w-2xl animate-fadeIn delay-100">
      The ultimate control suite for student organizations. Simplify membership, events, and financial management—all in one place.
    </p>

    <div class="flex flex-col md:flex-row gap-4 mb-12">
      <a href="<?= BASE_URL ?>/register" class="btn-primary px-6 py-3 rounded-lg font-semibold">
        <span class="font-bold">Sign up for free</span>
      </a>
      <a href="https://mail.google.com/mail/?view=cm&fs=1&to=maestrositestomper@gmail.com" target="_blank" class="btn-secondary px-6 py-3 rounded-lg font-semibold">
        <span class="font-bold">Get in touch</span>
      </a>
    </div>
    </div>

    <!-- Dynamic Stats -->
    <div class="flex flex-wrap justify-center gap-10 mb-12">
      <div class="text-center">
        <div class="counter" x-data="{count: 0}" x-init="setInterval(() => { if(count<1500) count++ }, 1)"><span x-text="count"></span>+</div>
        <p class="text-white">Active Members</p>
      </div>
      <div class="text-center">
        <div class="counter" x-data="{count: 0}" x-init="setInterval(() => { if(count<350) count++ }, 5)"><span x-text="count"></span>+</div>
        <p class="text-white">Events Hosted</p>
      </div>
      <div class="text-center">
        <div class="counter" x-data="{count: 0}" x-init="setInterval(() => { if(count<120) count++ }, 10)"><span x-text="count"></span>+</div>
        <p class="text-white">Organizations Managed</p>
      </div>
    </div>

    <!-- Interactive Feature Cards -->
    <div class="grid md:grid-cols-3 gap-6 max-w-6xl w-full">
      <div class="card text-center">
        <h3 class="text-white text-xl font-semibold mb-2">Document Repository</h3>
        <p class="text-white italic">Centralized documents repository for approvals and revisions.</p>
      </div>
      <div class="card text-center">
        <h3 class="text-white text-xl font-semibold mb-2">Member Control</h3>
        <p class="text-white italic">Organization registrations and member managements.</p>
      </div>
      <div class="card text-center">
        <h3 class="text-white text-xl font-semibold mb-2">Monitor Organization</h3>
        <p class="text-white italic">Monitor documents, manage members, and generate summaries—all in seconds.</p>
      </div>
    </div>
  </section>

  <!-- Middle CTA Section -->
  <section class="py-16 px-4 bg-black bg-opacity-40 text-center">
    <h2 class="text-white text-4xl font-bold mb-6">Ready to simplify your organization?</h2>
    <p class="text-white italic mb-8 max-w-l mx-auto">
      Join educators and administrators already using Maestro to streamline their operations and stay organized.
    </p>
    <div class="flex justify-center gap-4">
      <a href="<?= BASE_URL ?>/register" class="btn-primary px-6 py-3 rounded-lg font-semibold">Sign up now</a>
      <a href="https://mail.google.com/mail/?view=cm&fs=1&to=maestrositestomper@gmail.com" target="_blank" class="btn-secondary px-6 py-3 rounded-lg font-semibold">Contact us</a>
    </div>
  </section>

  <script>
    // Simple fade-in animation
    document.querySelectorAll('.animate-fadeIn').forEach(el => {
      el.style.opacity = 0;
      setTimeout(() => { el.style.transition = 'opacity 1.2s ease'; el.style.opacity = 1 }, 200);
    });
  </script>

</body>
</html>
