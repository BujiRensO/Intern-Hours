<?php
require_once 'config.php';
session_start();

// Redirect logged-in users to their dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: views/feed.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OurTracker - Track Your Internship Time</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⏱️</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
  </head>

  <body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm fixed w-full z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <div class="flex items-center">
            <a href="index.php" class="text-2xl font-bold text-gray-900">OurTracker</a>
          </div>
          <div class="hidden md:flex space-x-8">
            <a href="#features" class="text-gray-600 hover:text-gray-900 transition">Features</a>
            <a href="#how-it-works" class="text-gray-600 hover:text-gray-900 transition">How It Works</a>
            <a href="#contact" class="text-gray-600 hover:text-gray-900 transition">Contact</a>
          </div>
          <div class="flex space-x-4">
            <a href="views/feed.php" class="px-4 py-2 text-gray-900 hover:text-black font-medium transition">Login</a>
            <a href="views/feed.php?page=register" class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-black font-medium transition">Sign Up</a>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg pt-32 pb-20 px-4">
      <div class="max-w-7xl mx-auto text-center">
        <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
          Track Your Internship Hours with Ease
        </h1>
        <p class="text-xl md:text-2xl text-gray-200 mb-8 max-w-3xl mx-auto">
          A simple, efficient way for interns to log hours and for supervisors to track progress. Stay organized and never miss a beat.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="views/feed.php?page=register" class="px-8 py-4 bg-white text-gray-900 rounded-lg font-semibold hover:bg-gray-100 transition shadow-lg inline-block">
            Get Started Free
          </a>
          <a href="#features" class="px-8 py-4 border-2 border-white text-white rounded-lg font-semibold hover:bg-white hover:text-gray-900 transition inline-block">
            Learn More
          </a>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-4">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Why Choose Intern Hours?</h2>
          <p class="text-gray-600 text-lg max-w-2xl mx-auto">Everything you need to manage internship time tracking efficiently</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
          <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300">
            <div class="w-14 h-14 bg-gray-200 rounded-lg flex items-center justify-center mb-6">
              <svg class="w-8 h-8 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Easy Time Logging</h3>
            <p class="text-gray-600">Log your hours in seconds with our intuitive interface. No complicated forms or processes.</p>
          </div>
          <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300">
            <div class="w-14 h-14 bg-gray-200 rounded-lg flex items-center justify-center mb-6">
              <svg class="w-8 h-8 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Detailed Reports</h3>
            <p class="text-gray-600">Generate comprehensive reports for supervisors and HR. Export data in multiple formats.</p>
          </div>
          <div class="feature-card bg-white p-8 rounded-xl shadow-md transition duration-300">
            <div class="w-14 h-14 bg-gray-200 rounded-lg flex items-center justify-center mb-6">
              <svg class="w-8 h-8 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Secure & Private</h3>
            <p class="text-gray-600">Your data is encrypted and secure. Only authorized personnel can access sensitive information.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 px-4 bg-white">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">How It Works</h2>
          <p class="text-gray-600 text-lg max-w-2xl mx-auto">Get started in three simple steps</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="w-16 h-16 bg-gray-900 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">1</div>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Create Account</h3>
            <p class="text-gray-600">Sign up with your email and set up your profile in minutes.</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-gray-900 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">2</div>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Log Hours</h3>
            <p class="text-gray-600">Start tracking your daily work hours with our easy-to-use interface.</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-gray-900 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">3</div>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Submit & Track</h3>
            <p class="text-gray-600">Submit your timesheets and track approval status in real-time.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="gradient-bg py-20 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">Ready to Start Tracking?</h2>
        <p class="text-xl text-gray-200 mb-8">Join thousands of interns and supervisors who trust OurTracker for time tracking.</p>
        <a href="views/feed.php?page=register" class="px-8 py-4 bg-white text-gray-900 rounded-lg font-semibold hover:bg-gray-100 transition shadow-lg inline-block">
          Create Free Account
        </a>
      </div>
    </section>

    <!-- Footer -->
    <footer id="contact" class="bg-gray-800 text-gray-300 py-12 px-4">
      <div class="max-w-7xl mx-auto">
        <div class="grid md:grid-cols-4 gap-8">
          <div>
            <span class="text-2xl font-bold text-white">OurTracker</span>
            <p class="mt-4 text-gray-400">Making internship time tracking simple and efficient for everyone.</p>
          </div>
          <div>
            <h4 class="text-white font-semibold mb-4">Product</h4>
            <ul class="space-y-2">
              <li><a href="#" class="hover:text-white transition">Features</a></li>
              <li><a href="#" class="hover:text-white transition">Pricing</a></li>
              <li><a href="#" class="hover:text-white transition">Security</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-semibold mb-4">Company</h4>
            <ul class="space-y-2">
              <li><a href="#" class="hover:text-white transition">About</a></li>
              <li><a href="#" class="hover:text-white transition">Blog</a></li>
              <li><a href="#" class="hover:text-white transition">Careers</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-semibold mb-4">Support</h4>
            <ul class="space-y-2">
              <li><a href="#" class="hover:text-white transition">Help Center</a></li>
              <li><a href="#" class="hover:text-white transition">Contact Us</a></li>
              <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-700 mt-12 pt-8 text-center text-gray-400">
          <p>&copy; 2026 OurTracker. All rights reserved.</p>
        </div>
      </div>
    </footer>

    <script>
      // Smooth scrolling for navigation links
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            target.scrollIntoView({
              behavior: 'smooth',
              block: 'start'
            });
          }
        });
      });

      // Add scroll effect to navigation
      window.addEventListener('scroll', function() {
        const nav = document.querySelector('nav');
        if (window.scrollY > 50) {
          nav.classList.add('shadow-md');
        } else {
          nav.classList.remove('shadow-md');
        }
      });
    </script>
  </body>
</html>
