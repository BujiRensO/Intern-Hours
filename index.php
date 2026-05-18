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
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2250%22 cy=%2250%22 r=%2240%22 stroke=%22%236366f1%22 stroke-width=%228%22 fill=%22none%22/><path d=%22M50 20 v30 l20 10%22 stroke=%22%236366f1%22 stroke-width=%228%22 stroke-linecap=%22round%22/></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/index.css">
  </head>

  <body class="bg-gray-950 text-zinc-100 relative antialiased selection:bg-indigo-500 selection:text-white">
    <!-- Background Radial Ambient Glows -->
    <div class="absolute top-0 left-1/4 w-[500px] h-[500px] ambient-glow-1 animate-pulse-slow pointer-events-none z-0"></div>
    <div class="absolute top-1/3 right-10 w-[600px] h-[600px] ambient-glow-2 animate-pulse-slow pointer-events-none z-0"></div>
    <div class="absolute top-2/3 left-10 w-[450px] h-[450px] ambient-glow-3 animate-pulse-slow pointer-events-none z-0"></div>

    <!-- Navigation Header -->
    <header class="glass-nav fixed w-full z-50 transition-all duration-300">
      <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex justify-between h-20 items-center">
          <!-- Logo -->
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center shadow-[0_0_15px_rgba(99,102,241,0.4)] text-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <a href="index.php" class="text-2xl font-extrabold tracking-tight text-white hover:opacity-90 transition">OurTracker</a>
          </div>

          <!-- Desktop Navigation Links -->
          <nav class="hidden md:flex space-x-10">
            <a href="#how-it-works" class="text-sm font-medium text-zinc-400 hover:text-white transition duration-200">How It Works</a>
            <a href="#faqs" class="text-sm font-medium text-zinc-400 hover:text-white transition duration-200">FAQs</a>
          </nav>

          <!-- Auth CTA Actions -->
          <div class="hidden md:flex items-center space-x-5">
            <a href="views/feed.php" class="text-sm font-semibold text-zinc-300 hover:text-white transition duration-200">Log In</a>
            <a href="views/feed.php?page=register" class="px-5 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-semibold rounded-xl hover:shadow-[0_0_20px_rgba(99,102,241,0.4)] hover:scale-[1.02] active:scale-[0.98] transition-all duration-300">
              Sign Up Free
            </a>
          </div>

          <!-- Mobile Toggle Button -->
          <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg hover:bg-white/5 transition" aria-label="Toggle menu">
            <svg id="menu-icon" class="w-6 h-6 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
            <svg id="close-icon" class="w-6 h-6 text-zinc-300 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile Dropdown Menu -->
      <div id="mobile-menu" class="hidden md:hidden px-6 pb-6 pt-2 bg-gray-950/95 backdrop-blur-xl border-b border-white/5 space-y-4">
        <a href="#how-it-works" class="block text-base font-medium text-zinc-400 hover:text-white transition py-2 border-b border-white/5">How It Works</a>
        <a href="#faqs" class="block text-base font-medium text-zinc-400 hover:text-white transition py-2 border-b border-white/5">FAQs</a>
        <div class="pt-4 flex flex-col gap-3">
          <a href="views/feed.php" class="w-full text-center py-3 text-base font-semibold text-zinc-300 hover:text-white border border-white/10 rounded-xl transition">Log In</a>
          <a href="views/feed.php?page=register" class="w-full text-center py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-base font-semibold rounded-xl shadow-lg transition">Sign Up Free</a>
        </div>
      </div>
    </header>

    <!-- Main Container -->
    <main class="relative z-10 pt-20">
      
      <!-- Hero Section -->
      <section class="max-w-7xl mx-auto px-6 lg:px-8 pt-16 pb-24 text-center relative">

        <!-- Headline & Subheading -->
        <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-white mb-6 leading-tight max-w-4xl mx-auto">
          Track Your Internship Hours <br/>
          <span class="text-brand-gradient">With Absolute Ease</span>
        </h1>
        <p class="text-lg md:text-xl text-zinc-400 mb-10 max-w-2xl mx-auto leading-relaxed">
          The ultimate tracking workspace for interns to log logs and supervisors to review progress. Stay aligned, visual, and ahead of schedule.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-20">
          <a href="views/feed.php?page=register" class="w-full sm:w-auto px-8 py-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-bold shadow-[0_0_30px_rgba(99,102,241,0.3)] hover:shadow-[0_0_40px_rgba(99,102,241,0.5)] hover:scale-[1.03] active:scale-[0.98] transition-all duration-300 text-center">
            Start Tracking Free
          </a>
          <a href="#how-it-works" class="w-full sm:w-auto px-8 py-4 bg-white/5 hover:bg-white/10 border border-white/10 text-white rounded-xl font-bold transition-all text-center">
            Learn More
          </a>
        </div>

        <!-- Interactive Live Dashboard Mockup -->
                <div class="max-w-5xl mx-auto relative rounded-2xl glass-panel p-3 sm:p-5 shadow-[0_20px_50px_rgba(0,0,0,0.8)] border border-white/10 overflow-hidden group">
          <!-- Ambient card reflection -->
          <div class="absolute -top-40 -left-40 w-96 h-96 bg-purple-500/10 rounded-full filter blur-3xl pointer-events-none group-hover:bg-purple-500/15 transition-all"></div>
          
          <div class="rounded-xl overflow-hidden bg-zinc-950/80 border border-white/5 p-6 text-left font-sans text-xs">
            <!-- Welcome Card -->
            <div class="p-5 rounded-xl bg-zinc-900/60 border border-white/5 mb-6 flex justify-between items-center">
              <div>
                <h3 class="text-lg font-bold text-white font-heading">Welcome, John Doe</h3>
                <p class="text-xs text-zinc-500 font-medium">Software Department | Ast4rt3 Tech</p>
              </div>
              <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400 text-[10px] font-bold border border-indigo-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 pulse-ring-green animate-pulse"></span> Intern Session Active
              </div>
            </div>

            <!-- Workspace Layout (Calendar Grid & Stats Sidebar) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Calendar Column (Col-span-2) -->
              <div class="lg:col-span-2 p-5 rounded-xl bg-zinc-900/40 border border-white/5 flex flex-col space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-white/5">
                  <h4 class="text-sm font-bold text-white font-heading">December 2024</h4>
                  <div class="flex gap-2">
                    <button class="px-2.5 py-1 bg-white/5 hover:bg-white/10 rounded text-[10px] text-zinc-300 font-semibold border border-white/5 transition" disabled>← Prev</button>
                    <button class="px-2.5 py-1 bg-white/5 hover:bg-white/10 rounded text-[10px] text-zinc-300 font-semibold border border-white/5 transition" disabled>Next →</button>
                  </div>
                </div>
                
                <!-- Mock calendar grid -->
                <div class="grid grid-cols-7 gap-2 text-center text-[10px] font-medium">
                  <!-- Header Days -->
                  <div class="text-zinc-500 font-bold py-1">Sun</div>
                  <div class="text-zinc-500 font-bold py-1">Mon</div>
                  <div class="text-zinc-500 font-bold py-1">Tue</div>
                  <div class="text-zinc-500 font-bold py-1">Wed</div>
                  <div class="text-zinc-500 font-bold py-1">Thu</div>
                  <div class="text-zinc-500 font-bold py-1">Fri</div>
                  <div class="text-zinc-500 font-bold py-1">Sat</div>
                  
                  <!-- Month Days starting on Sunday December 1 -->
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-600">1</div>
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-600">2</div>
                  
                  <div class="p-2 rounded bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 relative group/day min-h-[40px] flex flex-col justify-between items-start transition hover:bg-indigo-500/15">
                    <span>3</span>
                    <span class="text-[8px] font-bold text-indigo-400 bg-indigo-500/20 px-1 rounded-sm">8.0h</span>
                  </div>
                  
                  <div class="p-2 rounded bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 relative min-h-[40px] flex flex-col justify-between items-start transition hover:bg-indigo-500/15">
                    <span>4</span>
                    <span class="text-[8px] font-bold text-indigo-400 bg-indigo-500/20 px-1 rounded-sm">8.0h</span>
                  </div>
                  
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-400 min-h-[40px] flex flex-col justify-between items-start transition hover:bg-white/5">
                    <span>5</span>
                    <span class="text-[8px] text-zinc-600">0.0h</span>
                  </div>
                  
                  <div class="p-2 rounded bg-rose-500/10 border border-rose-500/20 text-rose-300 relative min-h-[40px] flex flex-col justify-between items-start transition hover:bg-rose-500/15">
                    <span>6</span>
                    <span class="text-[7px] font-bold text-rose-400 bg-rose-500/20 px-1 rounded-sm">Absent</span>
                  </div>
                  
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-600">7</div>
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-600">8</div>
                  
                  <div class="p-2 rounded bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 relative min-h-[40px] flex flex-col justify-between items-start transition hover:bg-indigo-500/15">
                    <span>9</span>
                    <span class="text-[8px] font-bold text-indigo-400 bg-indigo-500/20 px-1 rounded-sm">7.5h</span>
                  </div>
                  
                  <div class="p-2 rounded bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 relative min-h-[40px] flex flex-col justify-between items-start transition hover:bg-indigo-500/15">
                    <span>10</span>
                    <span class="text-[8px] font-bold text-indigo-400 bg-indigo-500/20 px-1 rounded-sm">8.0h</span>
                  </div>
                  
                  <div class="p-2 rounded bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 relative min-h-[40px] flex flex-col justify-between items-start transition hover:bg-indigo-500/15">
                    <span>11</span>
                    <span class="text-[8px] font-bold text-indigo-400 bg-indigo-500/20 px-1 rounded-sm">8.0h</span>
                  </div>
                  
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-400 min-h-[40px] flex flex-col justify-between items-start transition hover:bg-white/5">
                    <span>12</span>
                    <span class="text-[8px] text-zinc-600">0.0h</span>
                  </div>
                  
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-400 min-h-[40px] flex flex-col justify-between items-start transition hover:bg-white/5">
                    <span>13</span>
                    <span class="text-[8px] text-zinc-600">0.0h</span>
                  </div>
                  
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-600">14</div>
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-600">15</div>
                  
                  <div class="p-2 rounded bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 relative min-h-[40px] flex flex-col justify-between items-start transition hover:bg-indigo-500/15">
                    <span>16</span>
                    <span class="text-[8px] font-bold text-indigo-400 bg-indigo-500/20 px-1 rounded-sm">8.5h</span>
                  </div>
                  
                  <div class="p-2 rounded bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 relative min-h-[40px] flex flex-col justify-between items-start transition hover:bg-indigo-500/15">
                    <span>17</span>
                    <span class="text-[8px] font-bold text-indigo-400 bg-indigo-500/20 px-1 rounded-sm">8.0h</span>
                  </div>
                  
                  <!-- Today's date showing live tracking -->
                  <div class="p-2 rounded bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 relative min-h-[40px] flex flex-col justify-between items-start shadow-[0_0_10px_rgba(16,185,129,0.1)]">
                    <span class="font-bold flex items-center gap-1">18 <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 pulse-ring-green inline-block"></span></span>
                    <span id="mockup-timer" class="text-[8px] font-extrabold text-emerald-400 bg-emerald-500/20 px-1 rounded-sm select-none">04:19:10</span>
                  </div>
                  
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-400 min-h-[40px] flex flex-col justify-between items-start transition hover:bg-white/5">
                    <span>19</span>
                    <span class="text-[8px] text-zinc-600">0.0h</span>
                  </div>
                  
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-400 min-h-[40px] flex flex-col justify-between items-start transition hover:bg-white/5">
                    <span>20</span>
                    <span class="text-[8px] text-zinc-600">0.0h</span>
                  </div>
                  
                  <div class="p-2 rounded bg-zinc-950/40 border border-white/5 text-zinc-600">21</div>
                </div>
                
                <div class="text-center text-[10px] text-zinc-500 italic pt-2">
                  Click a day to log or edit hours
                </div>
              </div>

              <!-- Sidebar Stats Column (Col-span-1) -->
              <div class="space-y-4">
                <!-- Stat Card 1 -->
                <div class="p-4 rounded-xl bg-zinc-900/50 border border-white/5 flex justify-between items-center transition hover:border-white/10">
                  <div>
                    <span class="text-[10px] text-zinc-500 uppercase font-semibold">Total Hours</span>
                    <h4 class="text-2xl font-extrabold text-white mt-1 font-heading">120.0 <span class="text-zinc-600 text-xs font-normal">hrs</span></h4>
                  </div>
                  <div class="w-10 h-10 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shadow-[0_0_10px_rgba(99,102,241,0.1)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>
                
                <!-- Stat Card 2 -->
                <div class="p-4 rounded-xl bg-zinc-900/50 border border-white/5 flex justify-between items-center transition hover:border-white/10">
                  <div>
                    <span class="text-[10px] text-zinc-500 uppercase font-semibold">Month Total</span>
                    <h4 class="text-2xl font-extrabold text-white mt-1 font-heading">32.0 <span class="text-zinc-600 text-xs font-normal">hrs</span></h4>
                  </div>
                  <div class="w-10 h-10 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 shadow-[0_0_10px_rgba(168,85,247,0.1)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                  </div>
                </div>
                
                <!-- Stat Card 3 -->
                <div class="p-4 rounded-xl bg-zinc-900/50 border border-white/5 flex justify-between items-center transition hover:border-white/10">
                  <div>
                    <span class="text-[10px] text-zinc-500 uppercase font-semibold">Today's Hours</span>
                    <h4 class="text-2xl font-extrabold text-white mt-1 font-heading">8.0 <span class="text-zinc-600 text-xs font-normal">hrs</span></h4>
                  </div>
                  <div class="w-10 h-10 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shadow-[0_0_10px_rgba(16,185,129,0.1)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>

                <!-- Stat Card 4 -->
                <div class="p-4 rounded-xl bg-zinc-900/50 border border-white/5 flex justify-between items-center transition hover:border-white/10">
                  <div>
                    <span class="text-[10px] text-zinc-500 uppercase font-semibold">Average/Day</span>
                    <h4 class="text-2xl font-extrabold text-white mt-1 font-heading">6.5 <span class="text-zinc-600 text-xs font-normal">hrs</span></h4>
                  </div>
                  <div class="w-10 h-10 rounded-lg bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-400 shadow-[0_0_10px_rgba(20,184,166,0.1)]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                </div>

                <!-- Date Filtering Panel -->
                <div class="p-4 rounded-xl bg-zinc-900/30 border border-white/5 space-y-2">
                  <span class="text-[10px] text-zinc-500 uppercase font-semibold block">Filter by Date</span>
                  <div class="grid grid-cols-2 gap-2 text-[9px]">
                    <div>
                      <label class="text-zinc-500 block mb-1">From Date</label>
                      <input type="text" value="2024-12-01" class="w-full bg-zinc-950/60 border border-white/5 rounded px-2 py-1 text-zinc-300" disabled>
                    </div>
                    <div>
                      <label class="text-zinc-500 block mb-1">To Date</label>
                      <input type="text" value="2024-12-31" class="w-full bg-zinc-950/60 border border-white/5 rounded px-2 py-1 text-zinc-300" disabled>
                    </div>
                  </div>
                  <div class="grid grid-cols-2 gap-2 pt-1">
                    <button class="w-full py-1 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-[9px] font-semibold rounded hover:bg-indigo-500/20 transition" disabled>Apply</button>
                    <button class="w-full py-1 bg-white/5 text-zinc-400 border border-white/5 text-[9px] font-semibold rounded hover:bg-white/10 transition" disabled>Reset</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Colleagues Card Panel Section -->
            <div class="p-5 rounded-xl bg-zinc-900/40 border border-white/5 mt-6">
              <h4 class="text-sm font-bold text-white mb-4 font-heading">Your Colleagues</h4>
              <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-center text-[10px]">
                <!-- Colleague 1 -->
                <div class="p-3 rounded-lg bg-zinc-950/40 border border-white/5 flex flex-col items-center transition hover:border-indigo-500/20 hover:bg-zinc-950/60">
                  <div class="w-8 h-8 rounded-full bg-zinc-800 flex items-center justify-center font-bold text-indigo-400 mb-1.5 border border-indigo-500/20">AS</div>
                  <span class="text-zinc-300 font-semibold block truncate w-full">Alice Smith</span>
                  <span class="text-zinc-500 text-[8px]">Software Intern</span>
                </div>
                <!-- Colleague 2 -->
                <div class="p-3 rounded-lg bg-zinc-950/40 border border-white/5 flex flex-col items-center transition hover:border-purple-500/20 hover:bg-zinc-950/60">
                  <div class="w-8 h-8 rounded-full bg-zinc-800 flex items-center justify-center font-bold text-purple-400 mb-1.5 border border-purple-500/20">BJ</div>
                  <span class="text-zinc-300 font-semibold block truncate w-full">Bob Johnson</span>
                  <span class="text-zinc-500 text-[8px]">Software Intern</span>
                </div>
                <!-- Colleague 3 -->
                <div class="p-3 rounded-lg bg-zinc-950/40 border border-white/5 flex flex-col items-center transition hover:border-teal-500/20 hover:bg-zinc-950/60">
                  <div class="w-8 h-8 rounded-full bg-zinc-800 flex items-center justify-center font-bold text-teal-400 mb-1.5 border border-teal-500/20">CW</div>
                  <span class="text-zinc-300 font-semibold block truncate w-full">Carol White</span>
                  <span class="text-zinc-500 text-[8px]">Design Intern</span>
                </div>
                <!-- Colleague 4 -->
                <div class="p-3 rounded-lg bg-zinc-950/40 border border-white/5 flex flex-col items-center transition hover:border-amber-500/20 hover:bg-zinc-950/60">
                  <div class="w-8 h-8 rounded-full bg-zinc-800 flex items-center justify-center font-bold text-amber-400 mb-1.5 border border-amber-500/20">DB</div>
                  <span class="text-zinc-300 font-semibold block truncate w-full">Dave Brown</span>
                  <span class="text-zinc-500 text-[8px]">Systems Intern</span>
                </div>
                <!-- Colleague 5 -->
                <div class="p-3 rounded-lg bg-zinc-950/40 border border-white/5 flex flex-col items-center transition hover:border-emerald-500/20 hover:bg-zinc-950/60">
                  <div class="w-8 h-8 rounded-full bg-zinc-800 flex items-center justify-center font-bold text-emerald-400 mb-1.5 border border-emerald-500/20">ME</div>
                  <span class="text-zinc-300 font-semibold block truncate w-full">Mary Evans</span>
                  <span class="text-zinc-500 text-[8px]">Quality Intern</span>
                </div>
                <!-- Colleague 6 -->
                <div class="p-3 rounded-lg bg-zinc-950/40 border border-white/5 flex flex-col items-center transition hover:border-rose-500/20 hover:bg-zinc-950/60">
                  <div class="w-8 h-8 rounded-full bg-zinc-800 flex items-center justify-center font-bold text-rose-400 mb-1.5 border border-rose-500/20">TL</div>
                  <span class="text-zinc-300 font-semibold block truncate w-full">Tom Lewis</span>
                  <span class="text-zinc-500 text-[8px]">DevOps Intern</span>
                </div>
              </div>
            </div>
          </div>
        </div>


      <!-- Stepper / Onboarding Timeline -->
      <section id="how-it-works" class="max-w-7xl mx-auto px-6 lg:px-8 py-20 relative">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-5xl font-extrabold text-white tracking-tight mb-4">
            How It Works
          </h2>
          <p class="text-zinc-400 text-base md:text-lg max-w-2xl mx-auto leading-relaxed">
            Get your internship hours tracking setup in three simple steps.
          </p>
        </div>

        <div class="relative">
          <!-- Desktop timeline bar -->
          <div class="hidden md:block absolute top-1/2 left-0 right-0 h-0.5 bg-gradient-to-r from-indigo-500 via-purple-500 to-teal-400 -translate-y-1/2 z-0 opacity-40"></div>
          
          <div class="grid md:grid-cols-3 gap-8 relative z-10">
            <!-- Step 1 -->
            <div class="rounded-2xl glass-panel p-8 text-center relative group">
              <div class="w-16 h-16 bg-zinc-950 border border-indigo-500/20 group-hover:border-indigo-500 text-indigo-400 rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6 shadow-lg transition-colors duration-300">1</div>
              <h3 class="text-xl font-bold text-white mb-3">Create Profile</h3>
              <p class="text-zinc-400 text-sm leading-relaxed">
                Sign up with your academic or work email, set your role as **Intern** or **Admin**, and link your profile to your office and organization.
              </p>
            </div>

            <!-- Step 2 -->
            <div class="rounded-2xl glass-panel p-8 text-center relative group">
              <div class="w-16 h-16 bg-zinc-950 border border-purple-500/20 group-hover:border-purple-500 text-purple-400 rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6 shadow-lg transition-colors duration-300">2</div>
              <h3 class="text-xl font-bold text-white mb-3">Log Daily Hours</h3>
              <p class="text-zinc-400 text-sm leading-relaxed">
                Access your dashboard, input your daily hours, write brief descriptions of your accomplishments, and track your metrics.
              </p>
            </div>

            <!-- Step 3 -->
            <div class="rounded-2xl glass-panel p-8 text-center relative group">
              <div class="w-16 h-16 bg-zinc-950 border border-teal-500/20 group-hover:border-teal-500 text-teal-400 rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6 shadow-lg transition-colors duration-300">3</div>
              <h3 class="text-xl font-bold text-white mb-3">Get Approvals</h3>
              <p class="text-zinc-400 text-sm leading-relaxed">
                Timesheets are synced with your supervisor instantly. View approval status updates in real-time and export reports anytime.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- FAQ Section -->
      <section id="faqs" class="max-w-4xl mx-auto px-6 lg:px-8 py-20">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-5xl font-extrabold text-white tracking-tight mb-4">
            Frequently Asked Questions
          </h2>
          <p class="text-zinc-400 text-sm md:text-base leading-relaxed">
            Everything you need to know about the OurTracker suite.
          </p>
        </div>

        <div class="space-y-4">
          <!-- FAQ 1 -->
          <details class="rounded-xl border border-white/5 p-5 glass-card bg-zinc-900/10 cursor-pointer group">
            <summary class="flex justify-between items-center font-bold text-white text-sm md:text-base select-none list-none">
              <span>Is OurTracker completely free to use?</span>
              <svg class="w-5 h-5 text-zinc-500 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </summary>
            <div class="mt-4 text-zinc-400 text-sm leading-relaxed">
              Yes! OurTracker is completely open and free for interns and academic supervisors. You can log unlimited hours, export PDF reports, and manage databases without any cost.
            </div>
          </details>

          <!-- FAQ 2 -->
          <details class="rounded-xl border border-white/5 p-5 glass-card bg-zinc-900/10 cursor-pointer group">
            <summary class="flex justify-between items-center font-bold text-white text-sm md:text-base select-none list-none">
              <span>How do supervisors review and approve hours?</span>
              <svg class="w-5 h-5 text-zinc-500 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </summary>
            <div class="mt-4 text-zinc-400 text-sm leading-relaxed">
              Supervisors log in with their **Admin** role credentials. They are presented with a supervisor-specific panel displaying list sheets, pending logs, and detailed profiles of interns. They can approve or reject items with single-click options.
            </div>
          </details>

          <!-- FAQ 3 -->
          <details class="rounded-xl border border-white/5 p-5 glass-card bg-zinc-900/10 cursor-pointer group">
            <summary class="flex justify-between items-center font-bold text-white text-sm md:text-base select-none list-none">
              <span>Can I export my timesheet data?</span>
              <svg class="w-5 h-5 text-zinc-500 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </summary>
            <div class="mt-4 text-zinc-400 text-sm leading-relaxed">
              Absolutely. Inside the intern dashboard, you can view your complete hours log history. There is a built-in export tool allowing you to download clean, professionally formatted reports as spreadsheet data or print-ready files.
            </div>
          </details>
        </div>
      </section>

      <!-- Pulse/Glow CTA Section -->
      <section class="max-w-5xl mx-auto px-6 lg:px-8 py-20 relative">
        <div class="rounded-3xl glass-panel p-10 sm:p-16 text-center relative overflow-hidden border border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.6)]">
          <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/10 via-purple-500/10 to-teal-400/5 pointer-events-none"></div>
          <div class="absolute -bottom-48 -right-48 w-96 h-96 bg-indigo-500/10 rounded-full filter blur-3xl pointer-events-none"></div>
          
          <h2 class="text-3xl md:text-5xl font-extrabold text-white mb-6 tracking-tight leading-tight">
            Ready to Streamline <br/>
            Your Time Tracking?
          </h2>
          <p class="text-zinc-400 text-base md:text-lg mb-10 max-w-xl mx-auto leading-relaxed">
            Join thousands of interns and coordinators who rely on OurTracker to maintain accurate and stress-free logging.
          </p>
          <a href="views/feed.php?page=register" class="inline-block px-10 py-5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-xl font-bold shadow-[0_0_30px_rgba(99,102,241,0.4)] hover:shadow-[0_0_40px_rgba(99,102,241,0.6)] hover:scale-[1.03] active:scale-[0.98] transition-all duration-300 text-center">
            Create Free Account
          </a>
        </div>
      </section>
      
    </main>

    <!-- Footer -->
    <footer class="bg-zinc-950/80 border-t border-white/5 text-zinc-400 py-16 px-6 lg:px-8 relative z-10">
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
          <div class="col-span-2 md:col-span-1 space-y-4">
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded bg-indigo-500 flex items-center justify-center text-white">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <span class="text-lg font-bold text-white">OurTracker</span>
            </div>
            <p class="text-zinc-500 text-sm leading-relaxed">
              Making internship logging, reports compilation, and supervisors approval clean and secure.
            </p>
          </div>
          <div>
            <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Product</h4>
            <ul class="space-y-2.5 text-sm">
              <li><a href="#how-it-works" class="hover:text-white transition">How It Works</a></li>
              <li><a href="views/feed.php" class="hover:text-white transition">Dashboard</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Security</h4>
            <ul class="space-y-2.5 text-sm">
              <li><a href="#" class="hover:text-white transition">Bcrypt Hashing</a></li>
              <li><a href="#" class="hover:text-white transition">Data Privacy</a></li>
              <li><a href="#" class="hover:text-white transition">SSL Protection</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Contact</h4>
            <ul class="space-y-2.5 text-sm">
              <li><a href="#" class="hover:text-white transition">Support Desk</a></li>
              <li><a href="#" class="hover:text-white transition">Documentation</a></li>
              <li><p class="text-zinc-600">v2.0 Stable Build</p></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-white/5 pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-zinc-600">
          <p>&copy; 2026 OurTracker. All rights reserved.</p>
          <div class="flex gap-4">
            <a href="#" class="hover:text-zinc-400 transition">Terms of Service</a>
            <a href="#" class="hover:text-zinc-400 transition">Privacy Policy</a>
          </div>
        </div>
      </div>
    </footer>

    <!-- Interactive Scripting -->
    <script>
      // 1. Mobile Menu Toggle
      const mobileBtn = document.getElementById('mobile-menu-btn');
      const mobileMenu = document.getElementById('mobile-menu');
      const menuIcon = document.getElementById('menu-icon');
      const closeIcon = document.getElementById('close-icon');

      if (mobileBtn && mobileMenu) {
        mobileBtn.addEventListener('click', () => {
          const isHidden = mobileMenu.classList.contains('hidden');
          if (isHidden) {
            mobileMenu.classList.remove('hidden');
            menuIcon.classList.add('hidden');
            closeIcon.classList.remove('hidden');
          } else {
            mobileMenu.classList.add('hidden');
            menuIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
          }
        });
      }

      // 2. Navigation Header Scroll Effect
      const header = document.querySelector('header');
      window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
          header.classList.add('shadow-[0_4px_30px_rgba(0,0,0,0.4)]');
          header.classList.remove('h-20');
          header.classList.add('h-16');
        } else {
          header.classList.remove('shadow-[0_4px_30px_rgba(0,0,0,0.4)]');
          header.classList.remove('h-16');
          header.classList.add('h-20');
        }
      });

      // 3. Live Ticking Timer in Dashboard Mockup
      let hours = 4;
      let minutes = 19;
      let seconds = 10;
      const timerEl = document.getElementById('mockup-timer');

      if (timerEl) {
        setInterval(() => {
          seconds++;
          if (seconds >= 60) {
            seconds = 0;
            minutes++;
            if (minutes >= 60) {
              minutes = 0;
              hours++;
            }
          }
          const hStr = String(hours).padStart(2, '0');
          const mStr = String(minutes).padStart(2, '0');
          const sStr = String(seconds).padStart(2, '0');
          timerEl.textContent = `${hStr}:${mStr}:${sStr}`;
        }, 1000);
      }
    </script>
  </body>
</html>
