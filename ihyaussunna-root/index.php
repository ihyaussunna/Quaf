<?php
/**
 * JAMIA IHYA-US-SUNNA - OFFICIAL PORTAL
 * Master Gateway & Coming Soon Platform
 * Domain: ihyaussunna.in
 *
 * Rules:
 * - Root domain (ihyaussunna.in) renders this prestigious Coming Soon portal.
 * - Food Counter is strictly routed and isolated to /foodcounter/ paths.
 */
declare(strict_types=1);

$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($rawUri, PHP_URL_PATH);
$trimmedUri = trim((string) $requestPath, '/');

// 1. Food Counter Routing (Only accessible on designated /foodcounter paths)
if ($trimmedUri === 'foodcounter') {
    header('Location: /foodcounter/', true, 301);
    exit;
}

if (str_starts_with($trimmedUri, 'foodcounter/')) {
    $subPath = substr($trimmedUri, strlen('foodcounter/'));

    // Admin Panel: /foodcounter/admin or /foodcounter/admin.php
    if ($subPath === 'admin' || $subPath === 'admin.php' || str_starts_with($subPath, 'admin/')) {
        if (file_exists(__DIR__.'/foodcounter/admin.php')) {
            require __DIR__.'/foodcounter/admin.php';
            exit;
        }
    }

    // API Engine: /foodcounter/api or /foodcounter/api.php
    if ($subPath === 'api' || $subPath === 'api.php') {
        if (file_exists(__DIR__.'/foodcounter/api.php')) {
            require __DIR__.'/foodcounter/api.php';
            exit;
        }
    }

    // Counter Scanner: /foodcounter/ or /foodcounter/index.php
    if ($subPath === '' || $subPath === 'index.php') {
        if (file_exists(__DIR__.'/foodcounter/index.php')) {
            require __DIR__.'/foodcounter/index.php';
            exit;
        }
    }
}

// 2. Official Institutional Coming Soon Portal for ihyaussunna.in
?>
<!DOCTYPE html>
<html lang="ml" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Jamia Ihya-us-sunna | Official Portal Coming Soon</title>
    
    <!-- Meta SEO & Social Sharing -->
    <meta name="description" content="Official Web Portal of Jamia Ihya-us-sunna, Markazu Saquafathi Sunniyya, Karanthur, Calicut, Kerala. Academic Excellence, Da'wa College, and Islamic Heritage.">
    <meta name="keywords" content="Jamia Ihyaussunna, Ihya-us-sunna, Markaz Karanthur, Calicut, Dawa College, Markazu Saquafathi Sunniyya, QUAF 2026">
    <meta name="author" content="Jamia Ihya-us-sunna, Karanthur">
    <meta property="og:title" content="Jamia Ihya-us-sunna | Official Portal Coming Soon">
    <meta property="og:description" content="Centre for Islamic Scholasticism & Academic Excellence. Markazu Saquafathi Sunniyya, Karanthur, Calicut.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://ihyaussunna.in">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="foodcounter/favicon.png">
    <link rel="apple-touch-icon" href="foodcounter/favicon.png">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            850: '#064e3b',
                            950: '#022c22',
                        },
                        gold: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    },
                    fontFamily: {
                        sora: ['Sora', 'sans-serif'],
                        anek: ['Anek Malayalam', 'sans-serif'],
                        amiri: ['Amiri', 'serif'],
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Anek+Malayalam:wght@300;400;500;600;700;800&family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Sora', sans-serif;
            background-color: #030a07;
            color: #f1f5f9;
        }
        .font-anek { font-family: 'Anek Malayalam', sans-serif; }
        .font-amiri { font-family: 'Amiri', serif; }

        /* Subtle Islamic Arabesque Ambient Glow */
        .ambient-mesh {
            background-image: 
                radial-gradient(circle at 50% 0%, rgba(5, 150, 105, 0.25) 0%, transparent 60%),
                radial-gradient(circle at 10% 40%, rgba(217, 119, 6, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 90% 70%, rgba(16, 185, 129, 0.18) 0%, transparent 55%);
        }

        .gold-border {
            border-image: linear-gradient(to right, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.8), rgba(245, 158, 11, 0.2)) 1;
        }

        @keyframes float-gentle {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .animate-float {
            animation: float-gentle 5s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-amber-500 selection:text-slate-950 ambient-mesh relative overflow-x-hidden">

    <!-- Top Navigation Bar -->
    <header class="w-full border-b border-emerald-900/40 bg-slate-950/60 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
            
            <!-- Logo & Title -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-emerald-950 border border-emerald-500/40 flex items-center justify-center shadow-lg shadow-emerald-950/50 group-hover:scale-105 transition">
                    <svg class="w-6 h-6 text-amber-300" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
                    </svg>
                </div>
                <div>
                    <span class="block text-sm sm:text-base font-extrabold tracking-tight text-white group-hover:text-amber-400 transition">
                        JAMIA IHYA-US-SUNNA
                    </span>
                    <span class="block text-[10px] text-emerald-400/80 font-anek uppercase tracking-wider">
                        Markazu Saquafathi Sunniyya • Karanthur
                    </span>
                </div>
            </a>

            <!-- Quick Portals Navigation -->
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="https://quaf.ihyaussunna.in" target="_blank" rel="noopener noreferrer" 
                   class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-gradient-to-r from-red-600 to-[#be1e2d] hover:from-red-500 hover:to-red-700 text-white text-[11px] sm:text-xs font-bold shadow-md shadow-red-950/40 flex items-center gap-1.5 transition">
                    <span class="w-2 h-2 rounded-full bg-white animate-ping"></span>
                    <span>QUAF '09 Fest</span>
                    <span class="text-[9px] opacity-80">↗</span>
                </a>

                <a href="/foodcounter/" 
                   class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-xl bg-emerald-900/60 hover:bg-emerald-800/80 border border-emerald-600/40 text-emerald-200 text-[11px] sm:text-xs font-semibold transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                    <span class="hidden sm:inline">Food Counter</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Hero Section -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-12 sm:py-20 text-center relative z-10 flex-1 flex flex-col justify-center items-center">
        
        <!-- Arabic Basmalah -->
        <div class="mb-4 text-emerald-400/90 font-amiri text-2xl sm:text-3xl tracking-wide select-none">
            بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
        </div>

        <!-- Status Pill -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-950/80 border border-emerald-500/30 text-emerald-300 text-xs font-bold mb-6 backdrop-blur-md shadow-inner">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="font-anek tracking-wide">ഔദ്യോഗിക വെബ്‌പോർട്ടൽ നിർമ്മാണത്തിലാണ്</span>
            <span class="text-emerald-500 font-mono">•</span>
            <span class="uppercase tracking-wider font-mono text-[10px]">COMING SOON</span>
        </div>

        <!-- Main Majestic Heading -->
        <div class="space-y-3 max-w-4xl">
            <h2 class="text-amber-400 font-amiri text-4xl sm:text-5xl md:text-6xl font-bold tracking-normal text-shadow-lg">
                جامعة إحياء السنة
            </h2>
            <h1 class="text-3xl sm:text-5xl md:text-6xl font-black text-white tracking-tight leading-tight">
                JAMIA IHYA-US-SUNNA
            </h1>
            <p class="text-sm sm:text-lg text-emerald-200/90 font-medium font-anek max-w-2xl mx-auto leading-relaxed pt-2">
                മാർകസുസ്സഖാഫത്തിസ്സുന്നിയ്യക്ക് കീഴിൽ ഉന്നത സമന്വയ വിദ്യാഭ്യാസവും ആത്മീയ നവോത്ഥാനവും നയിക്കുന്ന ഇഹ്‌യാഉസ്സുന്നയുടെ സമഗ്രമായ ഔദ്യോഗിക വെബ്‌പോർട്ടൽ ഉടൻ ലഭ്യമാകും.
            </p>
        </div>

        <!-- Core Academic Highlights (പ്രധാന വിവരങ്ങൾ) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 w-full mt-12 text-left">
            
            <!-- Card 1 -->
            <div class="p-5 rounded-2xl bg-slate-900/70 border border-emerald-800/40 hover:border-emerald-500/50 backdrop-blur-md transition group shadow-lg">
                <div class="w-10 h-10 rounded-xl bg-emerald-950 text-emerald-400 border border-emerald-700/50 flex items-center justify-center font-bold text-sm mb-3 group-hover:scale-110 transition">
                    01
                </div>
                <h3 class="text-sm font-bold text-white mb-1">Da'wa College</h3>
                <h4 class="text-xs text-amber-400 font-anek font-semibold mb-2">ദഅ്‌വാ കോളേജ്</h4>
                <p class="text-[11px] text-slate-400 font-anek leading-relaxed">
                    മത-ഭൗതിക സമന്വയ വിദ്യാഭ്യാസത്തിൽ മാതൃകാ പണ്ഡിതരെയും ചിന്തകരെയും വാർത്തെടുക്കുന്ന ഉന്നത കലാലയം.
                </p>
            </div>

            <!-- Card 2 -->
            <div class="p-5 rounded-2xl bg-slate-900/70 border border-emerald-800/40 hover:border-emerald-500/50 backdrop-blur-md transition group shadow-lg">
                <div class="w-10 h-10 rounded-xl bg-emerald-950 text-emerald-400 border border-emerald-700/50 flex items-center justify-center font-bold text-sm mb-3 group-hover:scale-110 transition">
                    02
                </div>
                <h3 class="text-sm font-bold text-white mb-1">Students Union (ISU)</h3>
                <h4 class="text-xs text-amber-400 font-anek font-semibold mb-2">വിദ്യാർത്ഥി യൂണിയൻ</h4>
                <p class="text-[11px] text-slate-400 font-anek leading-relaxed">
                    വിദ്യാർത്ഥികളുടെ സർഗ്ഗാത്മക, കലാ-സാഹിത്യ പാടവങ്ങളെ പരിപോഷിപ്പിക്കുന്ന ഇഹ്‌യാഉസ്സുന്ന സ്റ്റുഡന്റ്സ് യൂണിയൻ.
                </p>
            </div>

            <!-- Card 3 -->
            <div class="p-5 rounded-2xl bg-slate-900/70 border border-emerald-800/40 hover:border-emerald-500/50 backdrop-blur-md transition group shadow-lg">
                <div class="w-10 h-10 rounded-xl bg-emerald-950 text-emerald-400 border border-emerald-700/50 flex items-center justify-center font-bold text-sm mb-3 group-hover:scale-110 transition">
                    03
                </div>
                <h3 class="text-sm font-bold text-white mb-1">Academic & Research</h3>
                <h4 class="text-xs text-amber-400 font-anek font-semibold mb-2">വൈജ്ഞാനിക ഗവേഷണം</h4>
                <p class="text-[11px] text-slate-400 font-anek leading-relaxed">
                    ഖുർആൻ, ഹദീസ്, ഫിഖ്ഹ്, ഇസ്ലാമിക ചരിത്ര ഗവേഷണങ്ങൾ, ആനുകാലിക പ്രസിദ്ധീകരണങ്ങൾ എന്നിവ.
                </p>
            </div>

            <!-- Card 4 -->
            <div class="p-5 rounded-2xl bg-slate-900/70 border border-emerald-800/40 hover:border-emerald-500/50 backdrop-blur-md transition group shadow-lg">
                <div class="w-10 h-10 rounded-xl bg-emerald-950 text-emerald-400 border border-emerald-700/50 flex items-center justify-center font-bold text-sm mb-3 group-hover:scale-110 transition">
                    04
                </div>
                <h3 class="text-sm font-bold text-white mb-1">Community Services</h3>
                <h4 class="text-xs text-amber-400 font-anek font-semibold mb-2">സാമൂഹിക സേവനം</h4>
                <p class="text-[11px] text-slate-400 font-anek leading-relaxed">
                    ധാർമ്മിക മുന്നേറ്റം, ദഅ്‌വാ പ്രവർത്തനങ്ങൾ, കാരുണ്യ പദ്ധതികൾ എന്നിവയുടെ സമഗ്ര ആസൂത്രണം.
                </p>
            </div>

        </div>

        <!-- Active Gateways Section (നിലവിൽ പ്രവർത്തിക്കുന്ന സർവീസുകൾ) -->
        <div class="mt-12 w-full max-w-3xl bg-slate-900/90 border border-emerald-700/40 rounded-3xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl text-left">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-emerald-900/60 pb-5 mb-5">
                <div>
                    <h3 class="text-base sm:text-lg font-black text-white flex items-center gap-2">
                        <span>Active Platforms & Gateways</span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-mono text-[10px] font-bold">ONLINE</span>
                    </h3>
                    <p class="text-xs text-slate-400 font-anek mt-0.5">നിലവിൽ സന്ദർശിക്കാൻ സാധിക്കുന്ന അനുബന്ധ പോർട്ടലുകൾ</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Gateway 1: QUAF Fest Portal -->
                <a href="https://quaf.ihyaussunna.in" target="_blank" rel="noopener noreferrer" 
                   class="p-4 rounded-2xl bg-gradient-to-br from-red-950/60 to-slate-900 border border-red-800/40 hover:border-red-500/60 transition group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-red-400 mb-2">
                            <span>QUAF '09 ART FEST</span>
                            <span class="group-hover:translate-x-1 group-hover:-translate-y-1 transition text-sm">↗</span>
                        </div>
                        <h4 class="text-sm font-extrabold text-white mb-1">ക്വാഫ് 2026 ഫെസ്റ്റിവൽ പോർട്ടൽ</h4>
                        <p class="text-[11px] text-slate-400 font-anek leading-relaxed">
                            ലൈവ് റിസൾട്ടുകൾ, പോയിന്റ് ടേബിൾ, സ്റ്റേജ് ഷെഡ്യൂളുകൾ, ഡിജിറ്റൽ പോസ്റ്ററുകൾ കാണാം.
                        </p>
                    </div>
                    <div class="mt-3 text-[10px] text-red-300/80 font-mono">
                        quaf.ihyaussunna.in
                    </div>
                </a>

                <!-- Gateway 2: Food Counter Module -->
                <a href="/foodcounter/" 
                   class="p-4 rounded-2xl bg-gradient-to-br from-emerald-950/60 to-slate-900 border border-emerald-800/40 hover:border-emerald-500/60 transition group flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-xs font-bold text-emerald-400 mb-2">
                            <span>AUTHORIZED VOLUNTEERS</span>
                            <span class="group-hover:translate-x-1 group-hover:-translate-y-1 transition text-sm">↗</span>
                        </div>
                        <h4 class="text-sm font-extrabold text-white mb-1">ഫുഡ് കൗണ്ടർ മാനേജ്‌മെന്റ്</h4>
                        <p class="text-[11px] text-slate-400 font-anek leading-relaxed">
                            ഡൈനിംഗ് ഹാളിലെ ക്വാഫ് വിദ്യാർത്ഥി ചെസ്റ്റ് കാർഡ് QR സ്കാനിംഗും മീൽ മാനേജ്‌മെന്റും.
                        </p>
                    </div>
                    <div class="mt-3 text-[10px] text-emerald-300/80 font-mono">
                        ihyaussunna.in/foodcounter/
                    </div>
                </a>

            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-emerald-950 bg-slate-950/80 backdrop-blur-md py-6 px-4 text-center text-xs text-slate-500 relative z-10">
        <div class="max-w-4xl mx-auto space-y-2">
            <p class="font-anek text-slate-400">
                <strong class="text-slate-300">Jamia Ihya-us-sunna</strong> • Markazu Saquafathi Sunniyya, Karanthur, Kozhikode, Kerala, India - 673571
            </p>
            <p class="text-[11px] text-slate-600 font-mono">
                © <?= date('Y') ?> Jamia Ihya-us-sunna. All Rights Reserved. Designed for Islamic Excellence.
            </p>
        </div>
    </footer>

</body>
</html>
