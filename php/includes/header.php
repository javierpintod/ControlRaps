<?php
/**
 * Cabecera Institucional SENA Analytics Institutional
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/data_helper.php';

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');

$repo = new SenaRepository();
$cargasHeader = $repo->getCargas();
$selectedCutoff = $_GET['corte'] ?? ($cargasHeader[0]['fecha_corte'] ?? date('Y-m-d'));
$latency = $repo->getLatency();
$isSupabaseActive = isSupabaseConfigured();
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-[#F8FAFC]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> • <?= APP_SUBTITLE ?></title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sena: {
                            green: '#0D7A53',
                            'green-hover': '#0A6243',
                            navy: '#0C2340',
                            dark: '#081628',
                            blue: '#1B365D',
                            accent: '#39A900'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- SheetJS CDN (para lectura y generación de Excel/CSV en cliente) -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <style>
        @media print {
            body * { visibility: hidden; }
            #printable-modal, #printable-modal * { visibility: visible; }
            #printable-modal { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="h-full flex flex-col font-sans text-slate-800 antialiased selection:bg-[#0D7A53] selection:text-white">

<!-- Global Toast Notifications -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div id="toast-success" class="fixed top-4 right-4 z-50 flex items-center gap-3 bg-[#0D7A53] text-white px-4 py-3 rounded-lg shadow-2xl border border-emerald-600 animate-bounce text-sm">
        <i data-lucide="check-circle-2" class="w-5 h-5 flex-shrink-0"></i>
        <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
        <button onclick="this.parentElement.remove()" class="ml-2 text-white/80 hover:text-white">&times;</button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div id="toast-error" class="fixed top-4 right-4 z-50 flex items-center gap-3 bg-[#DC2626] text-white px-4 py-3 rounded-lg shadow-2xl border border-rose-700 text-sm">
        <i data-lucide="alert-octagon" class="w-5 h-5 flex-shrink-0"></i>
        <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        <button onclick="this.parentElement.remove()" class="ml-2 text-white/80 hover:text-white">&times;</button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Institutional Global Header -->
<header class="bg-[#0C2340] text-white border-b border-[#1B365D] sticky top-0 z-30 shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Top Institutional Brand Bar -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between py-3 gap-3 border-b border-[#1B365D]/60">
            <!-- Logo & Title -->
            <div class="flex items-center space-x-3.5">
                <a href="<?= BASE_URL ?>/index.php" class="w-10 h-10 bg-white rounded-md p-1.5 flex items-center justify-center shadow-inner flex-shrink-0 hover:scale-105 transition-transform">
                    <svg viewBox="0 0 100 100" class="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="50" cy="50" r="48" fill="#0C2340" />
                        <path d="M50 18C42 18 36 24 36 32C36 39 42 44 49 45V55H32C26 55 22 59 22 65C22 71 27 75 33 75H49V84H53V75H69C75 75 80 71 80 65C80 59 76 55 70 55H53V45C60 44 66 39 66 32C66 24 60 18 50 18Z" fill="#0D7A53" />
                        <circle cx="50" cy="32" r="7" fill="#FFFFFF" />
                        <circle cx="34" cy="65" r="5" fill="#FFFFFF" />
                        <circle cx="68" cy="65" r="5" fill="#FFFFFF" />
                    </svg>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#0D7A53] bg-[#E6F4EA] px-2 py-0.5 rounded">
                            SENA • SofiaPlus
                        </span>
                        <span class="text-[10px] font-mono text-emerald-300 bg-emerald-950/60 border border-emerald-500/30 px-2 py-0.5 rounded flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping inline-block"></span>
                            <?= $isSupabaseActive ? 'Supabase PostgreSQL' : 'Modo Demo / XAMPP' ?>
                        </span>
                    </div>
                    <h1 class="text-lg font-heading font-bold text-white tracking-tight leading-tight">
                        <?= APP_NAME ?>
                    </h1>
                </div>
            </div>

            <!-- Header Controls: Cutoff snapshot + DirectQuery Latency + User session -->
            <div class="flex items-center flex-wrap gap-3">
                <!-- Selector de Fecha de Corte / Snapshot -->
                <div class="flex items-center bg-[#1B365D]/60 border border-slate-700 rounded-md px-2.5 py-1 text-xs">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400 mr-2"></i>
                    <span class="text-slate-400 mr-2 hidden sm:inline">Corte:</span>
                    <select onchange="window.location.href='?corte=' + this.value" class="bg-transparent text-white font-medium focus:outline-none cursor-pointer">
                        <?php 
                        $cortesUnicos = array_unique(array_column($cargasHeader, 'fecha_corte'));
                        if (empty($cortesUnicos)) $cortesUnicos = ['2026-10-01', '2026-09-15', '2026-08-30'];
                        foreach ($cortesUnicos as $c): 
                        ?>
                            <option value="<?= $c ?>" class="bg-[#0C2340] text-white" <?= $c === $selectedCutoff ? 'selected' : '' ?>>
                                <?= $c ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Medidor de Latencia DirectQuery -->
                <div class="flex items-center bg-[#081628] border border-slate-700/80 rounded-md px-2.5 py-1 text-xs text-slate-300" title="Latencia en tiempo real hacia la base de datos">
                    <span class="w-2 h-2 rounded-full bg-[#0D7A53] mr-2 animate-pulse"></span>
                    <span class="hidden sm:inline mr-1 text-slate-400">DirectQuery:</span>
                    <span class="font-mono text-emerald-400 font-semibold" id="header-latency-display"><?= $latency ?> ms</span>
                </div>

                <!-- Botón de Refresco DirectQuery -->
                <button onclick="window.location.reload();" class="p-1.5 bg-[#1B365D]/80 hover:bg-[#1B365D] border border-slate-700 text-slate-200 rounded-md transition" title="Refrescar DirectQuery">
                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                </button>

                <!-- Menú de Usuario y Rol -->
                <?php if ($currentUser): ?>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 bg-[#1B365D] hover:bg-slate-700 border border-slate-600 rounded-md px-3 py-1 text-xs transition">
                            <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <div class="text-left hidden lg:block leading-tight">
                                <div class="font-bold text-white max-w-[130px] truncate"><?= htmlspecialchars($currentUser['nombre']) ?></div>
                                <div class="text-[10px] text-emerald-300 font-mono"><?= $currentUser['rol'] ?></div>
                            </div>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                        </button>

                        <div class="absolute right-0 mt-2 w-64 bg-[#0C2340] border border-slate-700 rounded-lg shadow-2xl py-2 z-50 hidden group-hover:block text-xs">
                            <div class="px-4 py-2 border-b border-slate-800">
                                <p class="text-white font-bold truncate"><?= htmlspecialchars($currentUser['nombre']) ?></p>
                                <p class="text-slate-400 text-[11px] truncate"><?= htmlspecialchars($currentUser['email']) ?></p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold tracking-wider 
                                    <?= $currentUser['rol'] === 'ADMIN' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : ($currentUser['rol'] === 'LIDER_FORMACION' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-500/20 text-slate-300 border border-slate-500/30') ?>">
                                    ROL: <?= $currentUser['rol'] ?>
                                </span>
                            </div>
                            <a href="<?= BASE_URL ?>/test_connection.php" class="flex items-center gap-2 px-4 py-2 text-slate-300 hover:text-white hover:bg-[#1B365D] transition">
                                <i data-lucide="database" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Diagnóstico Supabase</span>
                            </a>
                            <a href="<?= BASE_URL ?>/logout.php" class="flex items-center gap-2 px-4 py-2 text-rose-300 hover:text-rose-100 hover:bg-rose-950/40 transition border-t border-slate-800">
                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                <span>Cerrar Sesión</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="bg-[#0D7A53] hover:bg-emerald-600 px-3 py-1.5 rounded text-xs font-semibold text-white transition">
                        Iniciar Sesión
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navigation Bar -->
        <nav class="flex items-center space-x-1 sm:space-x-3 py-2 overflow-x-auto text-xs font-medium text-slate-300">
            <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-2 px-3 py-1.5 rounded-md transition <?= $currentPage === 'index.php' ? 'bg-[#0D7A53] text-white shadow-sm font-semibold' : 'hover:bg-[#1B365D]/60 hover:text-white' ?>">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                <span>Visor Power BI (DirectQuery)</span>
            </a>

            <?php if (hasRole(['ADMIN', 'LIDER_FORMACION'])): ?>
                <a href="<?= BASE_URL ?>/upload.php" class="flex items-center gap-2 px-3 py-1.5 rounded-md transition <?= $currentPage === 'upload.php' ? 'bg-[#0D7A53] text-white shadow-sm font-semibold' : 'hover:bg-[#1B365D]/60 hover:text-white' ?>">
                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    <span>Cargar Archivo Excel</span>
                </a>
            <?php else: ?>
                <span class="flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-500 cursor-not-allowed opacity-60" title="Requiere rol de Administrador o Líder">
                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                    <span>Cargar Archivo (Restringido)</span>
                </span>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/history.php" class="flex items-center gap-2 px-3 py-1.5 rounded-md transition <?= $currentPage === 'history.php' ? 'bg-[#0D7A53] text-white shadow-sm font-semibold' : 'hover:bg-[#1B365D]/60 hover:text-white' ?>">
                <i data-lucide="history" class="w-4 h-4"></i>
                <span>Auditoría de Lotes</span>
            </a>

            <a href="<?= BASE_URL ?>/test_connection.php" class="flex items-center gap-2 px-3 py-1.5 rounded-md transition <?= $currentPage === 'test_connection.php' ? 'bg-[#0D7A53] text-white shadow-sm font-semibold' : 'hover:bg-[#1B365D]/60 hover:text-white' ?>">
                <i data-lucide="database" class="w-4 h-4 text-emerald-400"></i>
                <span>Conexión Supabase</span>
            </a>
        </nav>
    </div>
</header>

<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
