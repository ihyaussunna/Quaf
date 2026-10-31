<?php
/**
 * QUAF 2026 - Official Food Counter Management System
 * Optimized for Mobile & Tablet Touch Experience
 * ihyaussunna.in/foodcounter
 */
declare(strict_types=1);

// Clean routing for admin if accessed as /foodcounter/admin or ?admin
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if (is_string($requestUri) && (preg_match('#/foodcounter/admin/?$#i', $requestUri) || isset($_GET['admin']))) {
    require __DIR__.'/admin.php';
    exit;
}

require_once __DIR__.'/db.php';

$stmt = $pdo->query('SELECT * FROM meal_sessions WHERE is_active = 1 ORDER BY id DESC LIMIT 1');
$activeSession = $stmt->fetch();
if (! $activeSession) {
    $stmt = $pdo->query('SELECT * FROM meal_sessions ORDER BY id ASC LIMIT 1');
    $activeSession = $stmt->fetch();
}

$allSessions = $pdo->query('SELECT * FROM meal_sessions ORDER BY id ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>QUAF 2026 | Food Counter Management</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon.png">
    <link rel="apple-touch-icon" href="favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@400;600;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sora', sans-serif; -webkit-tap-highlight-color: transparent; }
        .font-anek { font-family: 'Anek Malayalam', sans-serif; }
        @keyframes pulse-fast {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.94; transform: scale(0.995); }
        }
        .animate-alert { animation: pulse-fast 1s infinite; }
        /* Prevent pull-to-refresh on mobile scanning */
        html, body { overscroll-behavior-y: contain; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col antialiased selection:bg-[#be1e2d] selection:text-white pb-16">

    <!-- Top Navigation Header -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40 px-3 sm:px-4 py-2.5 shadow-xs">
        <div class="max-w-5xl mx-auto flex items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <a href="index.php" class="flex items-center gap-2 sm:gap-3 group">
                    <img src="logo.svg" onerror="this.onerror=null; this.src='logo.png';" alt="QUAF Logo" class="h-8 sm:h-9 w-auto object-contain shrink-0">
                    <div class="border-l border-slate-200 pl-2 sm:pl-3">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs sm:text-sm font-extrabold text-slate-900 tracking-tight font-sora">Food Counter</span>
                            <span class="text-[9px] sm:text-[10px] px-1.5 py-0.2 rounded bg-red-100 text-[#be1e2d] font-bold font-mono">2026</span>
                        </div>
                        <p class="text-[10px] sm:text-[11px] text-slate-500 font-anek leading-tight">ഭക്ഷണ വിതരണ സിസ്റ്റം</p>
                    </div>
                </a>
            </div>

            <!-- Header Quick Controls -->
            <div class="flex items-center gap-1.5 sm:gap-2">
                <select id="counterSelect" onchange="saveCounter(this.value)" class="bg-slate-50 border border-slate-300 text-slate-800 text-xs font-bold rounded-lg px-2 sm:px-2.5 py-1.5 focus:outline-none focus:border-[#be1e2d] shadow-2xs">
                    <option value="Counter 1">Counter 1</option>
                    <option value="Counter 2">Counter 2</option>
                    <option value="Counter 3">Counter 3</option>
                    <option value="Counter 4">Counter 4</option>
                    <option value="VIP Counter">VIP Counter</option>
                </select>

                <button onclick="toggleSound()" id="soundBtn" title="Sound & Vibration" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 flex items-center justify-center transition">
                    <svg id="soundIconOn" class="w-4 h-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M11 5L6 9H2v6h4l5 4V5z" />
                    </svg>
                    <svg id="soundIconOff" class="w-4 h-4 text-slate-400 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Active Meal Session Banner (Admin-Controlled Single Active Session) -->
    <div id="sessionBanner" class="bg-emerald-50/90 border-b border-emerald-200/80 px-3 sm:px-4 py-2 text-emerald-950 transition">
        <div class="max-w-5xl mx-auto flex items-center justify-between gap-2 text-xs">
            <div class="flex items-center gap-2 truncate">
                <span id="sessionPulse" class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse shrink-0"></span>
                <span class="text-emerald-800 text-[11px] font-medium hidden sm:inline">Active:</span>
                <strong id="sessionTitle" class="text-emerald-950 font-black truncate text-xs sm:text-sm">
                    <?= htmlspecialchars($activeSession['title'] ?? 'No Active Session') ?>
                </strong>
                <span id="sessionTimeRange" class="hidden font-mono text-[11px] px-2 py-0.5 rounded-md bg-white border border-emerald-300 text-emerald-900 font-bold sm:inline-block shadow-2xs"></span>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                <span id="sessionStatusBadge" class="px-2.5 py-0.5 rounded-full bg-emerald-200 text-emerald-900 text-[10px] sm:text-xs font-extrabold uppercase tracking-wide font-sora">
                    Active
                </span>
            </div>
        </div>
    </div>

    <!-- Mobile & Tablet Segmented View Switcher (Visible on mobile/tablet screens) -->
    <div class="max-w-5xl mx-auto w-full px-3 sm:px-4 pt-3 lg:hidden">
        <div class="grid grid-cols-3 gap-1 p-1 bg-slate-200/80 rounded-xl text-xs font-bold text-slate-600">
            <button type="button" onclick="switchMobileTab('camera')" id="tabBtn-camera" class="py-2.5 rounded-lg transition text-center flex items-center justify-center gap-1.5 bg-white text-slate-900 shadow-xs">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Camera</span>
            </button>
            <button type="button" onclick="switchMobileTab('manual')" id="tabBtn-manual" class="py-2.5 rounded-lg transition text-center flex items-center justify-center gap-1.5 hover:text-slate-900">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <span>Manual</span>
            </button>
            <button type="button" onclick="switchMobileTab('stats')" id="tabBtn-stats" class="py-2.5 rounded-lg transition text-center flex items-center justify-center gap-1.5 hover:text-slate-900">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span id="tabStatsCount">Stats (0)</span>
            </button>
        </div>
    </div>

    <!-- Main Container -->
    <main class="max-w-5xl mx-auto w-full px-3 sm:px-4 pt-3 space-y-3 sm:space-y-4">

        <!-- Result / Feedback Card (Large, High-contrast, Instant Recognition) -->
        <div id="resultBox" class="hidden rounded-2xl p-4 sm:p-5 transition-all duration-200 border shadow-xl">
            <!-- Dynamic Content Injected Here via JavaScript -->
        </div>

        <!-- Desktop & Tablet Layout Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 sm:gap-4">

            <!-- 1. CAMERA SCANNER SECTION -->
            <div id="section-camera" class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl p-3.5 sm:p-5 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-red-100 text-[#be1e2d] flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-xs sm:text-sm font-bold text-slate-900">Live Camera Scanner</h2>
                                <p class="text-[10px] text-slate-500 font-anek hidden sm:block">ചെസ്റ്റ് കാർഡിലെ QR കോഡ് കാണിക്കുക</p>
                            </div>
                        </div>

                        <!-- Big Touch Button for Camera Toggle -->
                        <button id="cameraToggleBtn" onclick="toggleCamera()" class="py-2 px-3.5 sm:py-2.5 sm:px-4 rounded-xl bg-[#be1e2d] hover:bg-red-700 active:scale-95 text-white text-xs font-bold shadow-md shadow-red-900/20 transition flex items-center gap-1.5 cursor-pointer">
                            <span>Start Camera</span>
                        </button>
                    </div>

                    <!-- Camera Viewport (Optimized for Mobile/Tablet Aspect Ratio) -->
                    <div class="relative w-full aspect-square sm:aspect-video max-h-[360px] bg-slate-950 rounded-xl overflow-hidden border border-slate-300 flex items-center justify-center shadow-inner">
                        <div id="reader" class="w-full h-full"></div>
                        <div id="cameraPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 p-4 text-center bg-slate-900">
                            <div class="w-16 h-16 rounded-full bg-slate-800 text-slate-300 flex items-center justify-center mb-2.5 animate-pulse">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-slate-200">ക്യാമറ ഓണാക്കാൻ 'Start Camera' അമർത്തുക</span>
                            <span class="text-[11px] text-slate-400 mt-1 max-w-xs font-anek">കുട്ടികളുടെ ചെസ്റ്റ് കാർഡിലെ QR കോഡ് ക്യാമറക്ക് മുന്നിൽ കാണിക്കുക</span>
                        </div>
                    </div>
                </div>

                <!-- Camera Controls & Status -->
                <div class="flex items-center justify-between mt-2.5 text-xs text-slate-500 pt-2 border-t border-slate-100">
                    <span id="scanStatus" class="flex items-center gap-1.5 font-semibold text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        <span>Camera Standby</span>
                    </span>
                    <button onclick="toggleTorch()" id="torchBtn" class="hidden text-amber-600 hover:text-amber-700 font-bold text-xs py-1 px-2.5 rounded-lg bg-amber-50 border border-amber-200 items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Torch</span>
                    </button>
                </div>
            </div>

            <!-- 2. MANUAL ENTRY & QUICK STATS COLUMN -->
            <div id="section-manual" class="lg:col-span-5 space-y-3 sm:space-y-4">

                <!-- Manual Entry Card -->
                <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-xs sm:text-sm font-bold text-slate-900">Manual Chest Entry</h2>
                            <p class="text-[10px] text-slate-500 font-anek">ചെസ്റ്റ് നമ്പർ നേരിട്ട് ടൈപ്പ് ചെയ്യാം</p>
                        </div>
                    </div>

                    <form onsubmit="handleManualSubmit(event)" class="space-y-2.5 mt-2.5">
                        <div class="relative">
                            <input type="text" 
                                   id="manualChestInput" 
                                   inputmode="numeric" 
                                   autocomplete="off"
                                   autocapitalize="characters"
                                   placeholder="e.g. 1001 or QF1001" 
                                   class="w-full py-3.5 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-mono text-xl uppercase tracking-widest placeholder-slate-400 focus:bg-white focus:outline-none focus:border-[#be1e2d] focus:ring-2 focus:ring-[#be1e2d]/20 transition shadow-inner text-center font-extrabold">
                            <button type="button" onclick="document.getElementById('manualChestInput').value = ''; document.getElementById('manualChestInput').focus();" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-lg px-2">
                                ✕
                            </button>
                        </div>

                        <!-- Big Touch Action Button -->
                        <button type="submit" id="manualSubmitBtn" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:brightness-105 active:scale-98 text-white font-extrabold text-sm sm:text-base tracking-wide shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-2 cursor-pointer">
                            <span>Check & Issue Food</span>
                            <svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Live Progress Card -->
                <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Attendance Status</span>
                        <span id="statPercent" class="text-xs font-extrabold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">0%</span>
                    </div>

                    <div class="flex items-baseline justify-between mb-2.5">
                        <div class="flex items-baseline gap-1.5">
                            <span id="statServed" class="text-3xl font-black text-slate-900 font-sora">0</span>
                            <span class="text-slate-400 text-sm font-bold">/</span>
                            <span id="statTotal" class="text-slate-600 text-sm font-bold">1168</span>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500">Served Students</span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden mb-3 border border-slate-200">
                        <div id="statProgressBar" class="bg-gradient-to-r from-[#be1e2d] via-amber-500 to-emerald-500 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
                    </div>

                    <!-- Team-wise Breakdown -->
                    <div class="space-y-1.5 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-600">
                            <span>Team Distribution:</span>
                            <span class="text-[10px] text-slate-400 font-normal">Served / Total</span>
                        </div>
                        <div id="teamPills" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 gap-1.5 text-[11px]">
                            <!-- Injected dynamically -->
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- 3. RECENT SCANS SECTION -->
        <div id="section-stats" class="bg-white border border-slate-200 rounded-2xl p-3.5 sm:p-5 shadow-sm">
            <div class="flex items-center justify-between mb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Recent Scans (ഈ സെഷനിൽ നൽകിയവർ)</h3>
                </div>
                <button onclick="loadRecentScans()" class="text-xs font-bold text-slate-600 hover:text-slate-900 transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Refresh</span>
                </button>
            </div>

            <div class="overflow-x-auto -mx-3.5 sm:mx-0">
                <table class="w-full text-left text-xs min-w-[500px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-[11px] font-bold">
                            <th class="py-2.5 px-3">Chest</th>
                            <th class="py-2.5 px-3">Student Name</th>
                            <th class="py-2.5 px-3">Team</th>
                            <th class="py-2.5 px-3">Time</th>
                            <th class="py-2.5 px-3">Counter</th>
                            <th class="py-2.5 px-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="recentTableBody" class="divide-y divide-slate-100 text-slate-700">
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400 font-medium">ഈ സെഷനിൽ ഇതുവരെ ആരും ഭക്ഷണം വാങ്ങിയിട്ടില്ല</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Client Script -->
    <script>
        let html5QrCode = null;
        let isCameraRunning = false;
        let isScanningLocked = false;
        let soundEnabled = true;
        let currentMobileTab = 'camera';
        let lastWindowWidth = window.innerWidth;

        // Mobile Tab Switcher Logic
        function switchMobileTab(tab) {
            currentMobileTab = tab;
            const secCam = document.getElementById('section-camera');
            const secMan = document.getElementById('section-manual');
            const secStat = document.getElementById('section-stats');

            const btnCam = document.getElementById('tabBtn-camera');
            const btnMan = document.getElementById('tabBtn-manual');
            const btnStat = document.getElementById('tabBtn-stats');

            const activeBtnClass = "bg-white text-slate-900 shadow-xs";
            const inactiveBtnClass = "text-slate-600 hover:text-slate-900";

            if (btnCam && btnMan && btnStat) {
                [btnCam, btnMan, btnStat].forEach(b => {
                    b.className = "py-2.5 rounded-lg transition text-center flex items-center justify-center gap-1.5 " + inactiveBtnClass;
                });
            }

            if (window.innerWidth < 1024) {
                if (secCam) secCam.classList.add('hidden');
                if (secMan) secMan.classList.add('hidden');
                if (secStat) secStat.classList.add('hidden');

                if (tab === 'camera') {
                    if (secCam) secCam.classList.remove('hidden');
                    if (btnCam) btnCam.className = "py-2.5 rounded-lg transition text-center flex items-center justify-center gap-1.5 " + activeBtnClass;
                } else if (tab === 'manual') {
                    if (secMan) secMan.classList.remove('hidden');
                    if (btnMan) btnMan.className = "py-2.5 rounded-lg transition text-center flex items-center justify-center gap-1.5 " + activeBtnClass;
                    setTimeout(() => {
                        const inp = document.getElementById('manualChestInput');
                        if (inp) inp.focus();
                    }, 120);
                } else if (tab === 'stats') {
                    if (secStat) secStat.classList.remove('hidden');
                    if (btnStat) btnStat.className = "py-2.5 rounded-lg transition text-center flex items-center justify-center gap-1.5 " + activeBtnClass;
                    loadStats();
                    loadRecentScans();
                }
            } else {
                if (secCam) secCam.classList.remove('hidden');
                if (secMan) secMan.classList.remove('hidden');
                if (secStat) secStat.classList.remove('hidden');
            }
        }

        // Handle Responsive Resize (Ignore height-only changes caused by mobile virtual keyboard!)
        window.addEventListener('resize', () => {
            const newWidth = window.innerWidth;
            if (newWidth === lastWindowWidth) {
                return; // Keyboard opened/closed - keep the active tab as is!
            }
            lastWindowWidth = newWidth;

            if (newWidth >= 1024) {
                document.getElementById('section-camera')?.classList.remove('hidden');
                document.getElementById('section-manual')?.classList.remove('hidden');
                document.getElementById('section-stats')?.classList.remove('hidden');
            } else {
                switchMobileTab(currentMobileTab);
            }
        });

        // Audio & Haptic Feedback Synthesizer
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

        function playSuccessFeedback() {
            if (navigator.vibrate) navigator.vibrate(120); // 120ms gentle vibration
            if (!soundEnabled || !audioCtx) return;
            try {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const now = audioCtx.currentTime;

                const osc1 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(880, now);
                gain1.gain.setValueAtTime(0.3, now);
                gain1.gain.exponentialRampToValueAtTime(0.01, now + 0.2);
                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc1.start(now);
                osc1.stop(now + 0.2);

                const osc2 = audioCtx.createOscillator();
                const gain2 = audioCtx.createGain();
                osc2.type = 'triangle';
                osc2.frequency.setValueAtTime(1318.5, now + 0.1);
                gain2.gain.setValueAtTime(0.35, now + 0.1);
                gain2.gain.exponentialRampToValueAtTime(0.01, now + 0.45);
                osc2.connect(gain2);
                gain2.connect(audioCtx.destination);
                osc2.start(now + 0.1);
                osc2.stop(now + 0.45);
            } catch(e) {}
        }

        function playDuplicateFeedback() {
            if (navigator.vibrate) navigator.vibrate([150, 80, 150]); // Double strong vibration
            if (!soundEnabled || !audioCtx) return;
            try {
                if (audioCtx.state === 'suspended') audioCtx.resume();
                const now = audioCtx.currentTime;

                [0, 0.15].forEach(delay => {
                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(180, now + delay);
                    gain.gain.setValueAtTime(0.4, now + delay);
                    gain.gain.exponentialRampToValueAtTime(0.01, now + delay + 0.12);
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start(now + delay);
                    osc.stop(now + delay + 0.12);
                });
            } catch(e) {}
        }

        function toggleSound() {
            soundEnabled = !soundEnabled;
            const onIcon = document.getElementById('soundIconOn');
            const offIcon = document.getElementById('soundIconOff');
            if (soundEnabled) {
                if (onIcon) onIcon.classList.remove('hidden');
                if (offIcon) offIcon.classList.add('hidden');
            } else {
                if (onIcon) onIcon.classList.add('hidden');
                if (offIcon) offIcon.classList.remove('hidden');
            }
        }

        function saveCounter(val) {
            localStorage.setItem('quaf_food_counter_name', val);
        }

        function getCounterName() {
            return localStorage.getItem('quaf_food_counter_name') || 'Counter 1';
        }

        // Initialize On Load
        window.addEventListener('DOMContentLoaded', () => {
            const saved = getCounterName();
            const sel = document.getElementById('counterSelect');
            if (sel) sel.value = saved;
            
            if (window.innerWidth < 1024) {
                switchMobileTab('camera');
            } else {
                setTimeout(() => {
                    document.getElementById('manualChestInput')?.focus();
                }, 300);
            }

            loadStats();
            loadRecentScans();
        });

        // Global Barcode & QR Scanner Listener (For Handheld USB/Wireless Scanner Guns)
        let scannerBuffer = '';
        let lastScannerKeyTime = Date.now();

        window.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT' || e.target.tagName === 'TEXTAREA') {
                return;
            }

            const now = Date.now();
            if (now - lastScannerKeyTime > 120) {
                scannerBuffer = '';
            }
            lastScannerKeyTime = now;

            if (e.key === 'Enter') {
                if (scannerBuffer.trim().length >= 2) {
                    e.preventDefault();
                    const scannedVal = scannerBuffer.trim();
                    scannerBuffer = '';
                    processChestScan(scannedVal);
                }
            } else if (e.key.length === 1) {
                scannerBuffer += e.key;
            }
        });

        // Camera Management
        async function toggleCamera() {
            if (isCameraRunning) {
                stopCamera();
            } else {
                startCamera();
            }
        }

        async function startCamera() {
            try {
                document.getElementById('cameraPlaceholder').classList.add('hidden');
                html5QrCode = new Html5Qrcode("reader");

                const config = {
                    fps: 15,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: window.innerWidth < 640 ? 1.0 : 1.77,
                };

                await html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    onQrCodeScanned,
                    (errorMessage) => { /* ignore normal scanning frames */ }
                );

                isCameraRunning = true;
                const btn = document.getElementById('cameraToggleBtn');
                btn.textContent = "Stop Camera";
                btn.classList.replace('bg-[#be1e2d]', 'bg-slate-700');
                document.getElementById('scanStatus').innerHTML = '<span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span><span class="text-emerald-700 font-bold">Scanning Active</span>';
            } catch (err) {
                alert("ക്യാമറ തുറക്കാൻ സാധിച്ചില്ല: " + err);
                document.getElementById('cameraPlaceholder').classList.remove('hidden');
            }
        }

        async function stopCamera() {
            if (html5QrCode && isCameraRunning) {
                await html5QrCode.stop();
                html5QrCode.clear();
                isCameraRunning = false;
                document.getElementById('cameraPlaceholder').classList.remove('hidden');
                const btn = document.getElementById('cameraToggleBtn');
                btn.textContent = "Start Camera";
                btn.classList.replace('bg-slate-700', 'bg-[#be1e2d]');
                document.getElementById('scanStatus').innerHTML = '<span class="w-2 h-2 rounded-full bg-slate-400"></span><span>Camera Standby</span>';
            }
        }

        // Process Scan from Camera
        function onQrCodeScanned(decodedText) {
            if (isScanningLocked) return;
            isScanningLocked = true;

            processChestScan(decodedText).finally(() => {
                // 2.2 second debounce so student can move away
                setTimeout(() => {
                    isScanningLocked = false;
                }, 2200);
            });
        }

        // Process Scan from Manual Input
        function handleManualSubmit(e) {
            e.preventDefault();
            const input = document.getElementById('manualChestInput');
            const val = input.value.trim();
            if (!val) return;

            const btn = document.getElementById('manualSubmitBtn');
            btn.disabled = true;
            btn.innerHTML = 'Checking...';

            processChestScan(val).finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<span>Check & Issue Food</span><svg class="w-4 h-4 stroke-[2.5]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>';
                input.value = '';
                input.focus();
            });
        }

        // API Scan Request
        async function processChestScan(chestString) {
            const resultBox = document.getElementById('resultBox');
            const counter = getCounterName();

            try {
                const formData = new FormData();
                formData.append('action', 'scan');
                formData.append('chest', chestString);
                formData.append('counter', counter);

                const res = await fetch('api.php', { method: 'POST', body: formData });
                const data = await res.json();

                renderResult(data);
                loadStats();
                loadRecentScans();
            } catch (err) {
                resultBox.className = "rounded-2xl p-4 sm:p-5 bg-red-50 border-2 border-red-400 text-red-900 shadow-lg";
                resultBox.innerHTML = `<strong>കണക്ഷൻ എറർ:</strong> സെർവറുമായി ബന്ധപ്പെടാൻ സാധിച്ചില്ല (${err.message})`;
                resultBox.classList.remove('hidden');
            }
        }

        // Render Feedback Card
        function renderResult(data) {
            const box = document.getElementById('resultBox');
            box.classList.remove('hidden');

            if (data.status === 'issued') {
                // SUCCESS
                playSuccessFeedback();
                const st = data.student;
                box.className = "rounded-2xl p-4 sm:p-5 bg-emerald-50 border-2 border-emerald-500 shadow-xl shadow-emerald-500/10";
                box.innerHTML = `
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                                ✓
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-[11px] sm:text-xs px-2.5 py-0.5 rounded-full bg-emerald-200 text-emerald-900 font-extrabold uppercase tracking-wider font-anek">ഭക്ഷണം നൽകാം • SUCCESS</span>
                                    <span class="text-xs text-slate-500 font-mono font-bold">${data.time}</span>
                                </div>
                                <h3 class="text-lg sm:text-2xl font-black text-slate-950 font-sora leading-tight">${st.name}</h3>
                                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-1 text-xs">
                                    <span class="font-mono font-bold text-emerald-800 bg-white px-2 py-0.5 rounded border border-emerald-300 text-xs sm:text-sm shadow-2xs">${st.chest_number}</span>
                                    <span class="px-2 py-0.5 rounded font-bold text-white text-[10px] sm:text-[11px] shadow-2xs" style="background-color: ${st.group_color}">${st.group_name}</span>
                                    <span class="text-slate-600 font-semibold font-anek text-xs">${st.category}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-left sm:text-right sm:border-l sm:border-emerald-200 sm:pl-4 pt-2 sm:pt-0 border-t sm:border-t-0 border-emerald-200/60 flex sm:flex-col justify-between sm:justify-center">
                            <span class="text-[10px] sm:text-[11px] text-slate-500 font-medium">ലോഗ് ചെയ്തത്:</span>
                            <div class="text-xs font-bold text-slate-800">${getCounterName()}</div>
                        </div>
                    </div>
                `;
            } else if (data.status === 'already_taken') {
                // DUPLICATE ATTEMPT
                playDuplicateFeedback();
                const st = data.student;
                box.className = "rounded-2xl p-4 sm:p-5 bg-red-50 border-2 border-red-500 shadow-xl shadow-red-500/15 animate-alert";
                box.innerHTML = `
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                        <div class="flex items-center gap-3 sm:gap-4">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-red-600 text-white flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                                ✕
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="text-[11px] sm:text-xs px-2.5 py-0.5 rounded-full bg-red-600 text-white font-extrabold uppercase tracking-wider font-anek">ഭക്ഷണം ഇതിനകം നൽകിയതാണ്!</span>
                                </div>
                                <h3 class="text-lg sm:text-2xl font-black text-red-950 font-sora leading-tight">${st.name}</h3>
                                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-1 text-xs">
                                    <span class="font-mono font-bold text-red-800 bg-white px-2 py-0.5 rounded border border-red-300 text-xs sm:text-sm shadow-2xs">${st.chest_number}</span>
                                    <span class="px-2 py-0.5 rounded font-bold text-white text-[10px] sm:text-[11px] shadow-2xs" style="background-color: ${st.group_color}">${st.group_name}</span>
                                </div>
                                <div class="mt-2 text-xs font-anek text-red-900 bg-white p-2 rounded-xl border border-red-300 inline-block shadow-2xs">
                                    ഈ കുട്ടിക്ക് <strong>${data.served_at}</strong>-ന് (<strong>${data.counter}</strong> വഴി) ഇതിനകം ഭക്ഷണം നൽകിയിട്ടുണ്ട്.
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                // NOT FOUND
                playDuplicateFeedback();
                box.className = "rounded-2xl p-4 bg-amber-50 border-2 border-amber-500 text-amber-950 shadow-md";
                box.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-amber-900 text-xs sm:text-sm">${data.message || 'വിദ്യാർത്ഥിയെ കണ്ടെത്തിയില്ല'}</h4>
                            <p class="text-[11px] text-amber-800 mt-0.5 font-anek">ക്വാഫ് ഫെസ്റ്റിൽ രജിസ്റ്റർ ചെയ്ത ശരിയായ ചെസ്റ്റ് നമ്പർ നൽകുക.</p>
                        </div>
                    </div>
                `;
            }

            // Scroll feedback card smoothly into view
            box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Live Statistics Fetch
        async function loadStats() {
            try {
                const res = await fetch('api.php?action=stats');
                const d = await res.json();
                if (!d.success) return;

                document.getElementById('statServed').textContent = d.total_served;
                document.getElementById('statTotal').textContent = d.total_students;
                document.getElementById('statPercent').textContent = `${d.percentage}%`;
                document.getElementById('statProgressBar').style.width = `${d.percentage}%`;

                const tabCount = document.getElementById('tabStatsCount');
                if (tabCount) tabCount.textContent = `Stats (${d.total_served})`;

                if (d.active_session) {
                    document.getElementById('sessionTitle').textContent = d.active_session.title;
                }

                if (d.session_status) {
                    const st = d.session_status;
                    const banner = document.getElementById('sessionBanner');
                    const badge = document.getElementById('sessionStatusBadge');
                    const pulse = document.getElementById('sessionPulse');
                    const timeRange = document.getElementById('sessionTimeRange');

                    if (st.is_open) {
                        banner.className = "bg-emerald-50/90 border-b border-emerald-200/80 px-3 sm:px-4 py-2 text-emerald-950 transition";
                        badge.className = "px-2.5 py-0.5 rounded-full bg-emerald-200 text-emerald-900 text-[10px] sm:text-xs font-extrabold uppercase tracking-wide font-sora";
                        badge.textContent = st.badge_text || 'Active';
                        pulse.className = "w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse shrink-0";
                    } else {
                        banner.className = "bg-rose-50/95 border-b border-rose-300 px-3 sm:px-4 py-2 text-rose-950 transition";
                        badge.className = "px-2.5 py-0.5 rounded-full bg-rose-600 text-white text-[10px] sm:text-xs font-extrabold uppercase tracking-wide font-sora animate-pulse";
                        badge.textContent = st.badge_text || 'Closed';
                        pulse.className = "w-2.5 h-2.5 rounded-full bg-rose-600 shrink-0";
                    }

                    if (st.time_range) {
                        timeRange.textContent = st.time_range;
                        timeRange.classList.remove('hidden');
                    } else {
                        timeRange.classList.add('hidden');
                    }
                }

                // Render Team pills
                const container = document.getElementById('teamPills');
                container.innerHTML = d.teams.map(t => `
                    <div class="flex items-center justify-between p-1.5 sm:p-2 rounded-lg bg-slate-50 border border-slate-200">
                        <span class="font-bold truncate max-w-[85px] text-[10px] sm:text-xs" style="color: ${t.group_color}">${t.group_name}</span>
                        <span class="font-mono font-extrabold text-slate-800 text-[10px] sm:text-xs">${t.served_count}/${t.total_group_count}</span>
                    </div>
                `).join('');
            } catch (e) {}
        }

        // Load Recent Scans Feed
        async function loadRecentScans() {
            try {
                const res = await fetch('api.php?action=recent');
                const d = await res.json();
                if (!d.success || !d.recent) return;

                const tbody = document.getElementById('recentTableBody');
                if (d.recent.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="py-6 text-center text-slate-400 font-medium">ഈ സെഷനിൽ ഇതുവരെ ആരും ഭക്ഷണം വാങ്ങിയിട്ടില്ല</td></tr>';
                    return;
                }

                tbody.innerHTML = d.recent.map(r => `
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-2.5 px-3 font-mono font-bold text-emerald-700">
                            <span class="bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-xs">${r.student_chest}</span>
                        </td>
                        <td class="py-2.5 px-3 font-bold text-slate-900 text-xs">${r.name}</td>
                        <td class="py-2.5 px-3">
                            <span class="px-2 py-0.5 rounded text-[10px] text-white font-bold" style="background-color: ${r.group_color}">${r.group_name}</span>
                        </td>
                        <td class="py-2.5 px-3 text-slate-700 font-mono text-[11px] font-bold whitespace-nowrap">${r.formatted_time || r.scanned_at}</td>
                        <td class="py-2.5 px-3 text-slate-600 font-medium text-xs">${r.counter_name}</td>
                        <td class="py-2.5 px-3 text-right">
                            <button onclick="undoScan(${r.log_id})" class="text-[11px] font-bold text-rose-600 hover:text-rose-800 hover:underline cursor-pointer py-1 px-1.5">
                                Undo
                            </button>
                        </td>
                    </tr>
                `).join('');
            } catch(e) {}
        }

        async function undoScan(logId) {
            if (!confirm("ഈ എൻട്രി റദ്ദാക്കണോ (Undo food issue)?")) return;
            const fd = new FormData();
            fd.append('action', 'undo');
            fd.append('log_id', logId);
            await fetch('api.php', { method: 'POST', body: fd });
            loadStats();
            loadRecentScans();
            document.getElementById('resultBox').classList.add('hidden');
        }
    </script>
</body>
</html>
