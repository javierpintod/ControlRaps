<?php
/**
 * Cabecera Institucional SENA Analytics Institutional
 * Con Soporte de Múltiples Centros (Autocompletado reactivo) y Múltiples Coordinaciones
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/data_helper.php';

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');

$repo = new SenaRepository();
$activeCentroId = getActiveCentroId();
$activeCoordId = getActiveCoordinacionId();

$activeCentro = $repo->getCentroById($activeCentroId);
$coordinacionesCentro = $repo->getCoordinacionesByCentro($activeCentroId);

$cargasHeader = $repo->getCargas($activeCentroId);
$selectedCutoff = $_GET['corte'] ?? ($cargasHeader[0]['fecha_corte'] ?? date('Y-m-d'));
$latency = $repo->getLatency();
$isSupabaseActive = isSupabaseConfigured();

// Permisos
$canAccessUpload = hasRole(['ADMIN', 'LIDER_FORMACION', 'COORDINADOR']);
$canAccessSubdirector = hasRole(['SUBDIRECTOR', 'ADMIN', 'LIDER_FORMACION', 'COORDINADOR']);
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-[#F8FAFC]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> • <?= htmlspecialchars($activeCentro['nombre_centro'] ?? 'SENA') ?></title>
    
    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
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

    <!-- SheetJS CDN (para lectura y generación de Excel/CSV) -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    <style>
        @media print {
            body * { visibility: hidden; }
            #printable-modal, #printable-modal *, #printable-subdirector-report, #printable-subdirector-report * { visibility: visible; }
            #printable-modal, #printable-subdirector-report { position: absolute; left: 0; top: 0; width: 100%; }
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
        <div class="flex flex-col md:flex-row md:items-center md:justify-between py-2.5 gap-3 border-b border-[#1B365D]/60">
            <!-- Logo & Title -->
            <div class="flex items-center space-x-3">
                <a href="<?= BASE_URL ?>/index.php" class="w-9 h-9 bg-white rounded-md p-1.5 flex items-center justify-center shadow-inner flex-shrink-0 hover:scale-105 transition-transform">
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
                        <span class="text-[9px] font-bold uppercase tracking-wider text-[#0D7A53] bg-[#E6F4EA] px-2 py-0.5 rounded">
                            SENA • SofiaPlus DirectQuery
                        </span>
                        <span class="text-[9px] font-mono text-emerald-300 bg-emerald-950/60 border border-emerald-500/30 px-2 py-0.5 rounded flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping inline-block"></span>
                            <?= $isSupabaseActive ? 'Supabase PostgreSQL Sync' : 'XAMPP Demo Cache' ?>
                        </span>
                    </div>
                    <h1 class="text-base font-heading font-bold text-white tracking-tight leading-tight">
                        <?= APP_NAME ?>
                    </h1>
                </div>
            </div>

            <!-- Header Controls: Cutoff + Latency + User menu -->
            <div class="flex items-center flex-wrap gap-2.5">
                <!-- Selector de Fecha de Corte / Snapshot -->
                <div class="flex items-center bg-[#1B365D]/60 border border-slate-700 rounded-md px-2.5 py-1 text-xs">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400 mr-1.5"></i>
                    <span class="text-slate-400 mr-1.5 hidden sm:inline">Corte:</span>
                    <select onchange="updateUrlParam('corte', this.value)" class="bg-transparent text-white font-medium focus:outline-none cursor-pointer text-xs">
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
                <div class="flex items-center bg-[#081628] border border-slate-700/80 rounded-md px-2.5 py-1 text-xs text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-[#0D7A53] mr-1.5 animate-pulse"></span>
                    <span class="hidden sm:inline mr-1 text-slate-400">DirectQuery:</span>
                    <span class="font-mono text-emerald-400 font-semibold"><?= $latency ?> ms</span>
                </div>

                <!-- Botón de Refresco -->
                <button onclick="window.location.reload();" class="p-1.5 bg-[#1B365D]/80 hover:bg-[#1B365D] border border-slate-700 text-slate-200 rounded-md transition" title="Refrescar DirectQuery">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                </button>

                <!-- Menú de Usuario y Rol -->
                <?php if ($currentUser): ?>
                    <div class="relative group">
                        <button class="flex items-center space-x-2 bg-[#1B365D] hover:bg-slate-700 border border-slate-600 rounded-md px-2.5 py-1 text-xs transition cursor-pointer">
                            <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                            <div class="text-left hidden lg:block leading-tight">
                                <div class="font-bold text-white max-w-[120px] truncate"><?= htmlspecialchars($currentUser['nombre']) ?></div>
                                <div class="text-[9px] text-emerald-300 font-mono"><?= $currentUser['rol'] ?></div>
                            </div>
                            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
                        </button>

                        <div class="absolute right-0 mt-2 w-64 bg-[#0C2340] border border-slate-700 rounded-lg shadow-2xl py-2 z-50 hidden group-hover:block text-xs">
                            <div class="px-4 py-2 border-b border-slate-800">
                                <p class="text-white font-bold truncate"><?= htmlspecialchars($currentUser['nombre']) ?></p>
                                <p class="text-slate-400 text-[11px] truncate"><?= htmlspecialchars($currentUser['email']) ?></p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold tracking-wider 
                                    <?= $currentUser['rol'] === 'SUBDIRECTOR' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : ($currentUser['rol'] === 'COORDINADOR' ? 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30' : ($currentUser['rol'] === 'ADMIN' ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30')) ?>">
                                    ROL: <?= $currentUser['rol'] ?>
                                </span>
                            </div>
                            <?php if ($canAccessSubdirector): ?>
                                <a href="<?= BASE_URL ?>/subdirector.php" class="flex items-center gap-2 px-4 py-2 text-slate-300 hover:text-white hover:bg-[#1B365D] transition">
                                    <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-amber-400"></i>
                                    <span>Dashboard Subdirector</span>
                                </a>
                            <?php endif; ?>
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

        <!-- SECOND TIER: SELECTORES MULTI-CENTRO CON AUTOCOMPLETADO Y MULTI-COORDINACIÓN -->
        <div class="py-2 border-b border-[#1B365D]/60 flex flex-wrap items-center justify-between gap-3 text-xs">
            <!-- SELECTOR AUTOCOMPLETABLE DE CENTRO DEL SENA -->
            <div class="flex items-center gap-2 flex-1 min-w-[280px] max-w-xl relative">
                <span class="text-slate-400 font-semibold flex items-center gap-1 flex-shrink-0 text-[11px]">
                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-[#0D7A53]"></i>
                    Centro SENA:
                </span>
                
                <!-- Input de Búsqueda Predictiva con Autocompletado -->
                <div class="relative flex-1">
                    <input 
                        type="text" 
                        id="centroSearchInput" 
                        value="<?= htmlspecialchars($activeCentro['nombre_centro'] ?? 'Seleccionar Centro...') ?>"
                        placeholder="Escribe para buscar centro (ej. CEET, Antioquia, Cundinamarca, Agro...)"
                        autocomplete="off"
                        onfocus="openCentrosDropdown()"
                        oninput="filterCentrosAutocomplete(this.value)"
                        class="w-full bg-[#1B365D]/80 hover:bg-[#1B365D] border border-slate-700 focus:border-[#0D7A53] rounded-md pl-8 pr-7 py-1 text-xs text-white placeholder-slate-400 focus:outline-none transition cursor-pointer truncate"
                    />
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1.5 pointer-events-none"></i>
                    <button type="button" onclick="toggleCentrosDropdown()" class="absolute right-2 top-1.5 text-slate-400 hover:text-white">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </button>

                    <!-- Desplegable Predictivo Flotante -->
                    <div id="centrosDropdownMenu" class="absolute left-0 right-0 mt-1 bg-[#0C2340] border border-slate-600 rounded-lg shadow-2xl z-50 max-h-72 overflow-y-auto hidden custom-scrollbar">
                        <div class="p-2 border-b border-slate-800 text-[10px] text-slate-400 font-semibold uppercase flex items-center justify-between">
                            <span>Todos los Centros de Formación</span>
                            <span id="centrosCountLabel">15 Centros</span>
                        </div>
                        <div id="centrosListContainer" class="py-1">
                            <!-- Inyectado por JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- SELECTOR DE COORDINACIÓN ACADÉMICA -->
            <div class="flex items-center gap-2">
                <span class="text-slate-400 font-semibold flex items-center gap-1 flex-shrink-0 text-[11px]">
                    <i data-lucide="network" class="w-3.5 h-3.5 text-amber-400"></i>
                    Coordinación:
                </span>
                <select 
                    id="coordinacionSelect" 
                    onchange="updateUrlParam('coordinacion', this.value)"
                    class="bg-[#1B365D]/80 border border-slate-700 rounded-md px-2.5 py-1 text-xs text-white font-medium focus:outline-none focus:border-[#0D7A53] cursor-pointer max-w-xs truncate"
                >
                    <option value="TODAS" <?= $activeCoordId === 'TODAS' ? 'selected' : '' ?>>Todas las Coordinaciones (Centro)</option>
                    <?php foreach ($coordinacionesCentro as $coord): ?>
                        <option value="<?= $coord['id'] ?>" <?= $activeCoordId === $coord['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($coord['nombre_coordinacion']) ?> (<?= htmlspecialchars($coord['coordinador_nombre']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Navigation Bar -->
        <nav class="flex items-center space-x-1 sm:space-x-2 py-2 overflow-x-auto text-xs font-medium text-slate-300">
            <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-md transition <?= $currentPage === 'index.php' ? 'bg-[#0D7A53] text-white shadow-sm font-semibold' : 'hover:bg-[#1B365D]/60 hover:text-white' ?>">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                <span>Visor Power BI</span>
            </a>

            <!-- Pestaña Especial Dashboard Subdirector -->
            <?php if ($canAccessSubdirector): ?>
                <a href="<?= BASE_URL ?>/subdirector.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-md transition <?= $currentPage === 'subdirector.php' ? 'bg-amber-600 text-white shadow-sm font-semibold' : 'bg-amber-950/40 text-amber-300 hover:bg-amber-900/60 border border-amber-600/30' ?>">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-amber-300"></i>
                    <span>Dashboard Subdirector</span>
                    <span class="bg-amber-500/20 text-amber-200 text-[9px] px-1.5 py-0.2 rounded font-bold uppercase">Gerencial</span>
                </a>
            <?php endif; ?>

            <?php if ($canAccessUpload): ?>
                <a href="<?= BASE_URL ?>/upload.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-md transition <?= $currentPage === 'upload.php' ? 'bg-[#0D7A53] text-white shadow-sm font-semibold' : 'hover:bg-[#1B365D]/60 hover:text-white' ?>">
                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    <span>Cargar Archivo Excel</span>
                </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/history.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-md transition <?= $currentPage === 'history.php' ? 'bg-[#0D7A53] text-white shadow-sm font-semibold' : 'hover:bg-[#1B365D]/60 hover:text-white' ?>">
                <i data-lucide="history" class="w-4 h-4"></i>
                <span>Auditoría de Lotes</span>
            </a>

            <a href="<?= BASE_URL ?>/test_connection.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-md transition <?= $currentPage === 'test_connection.php' ? 'bg-[#0D7A53] text-white shadow-sm font-semibold' : 'hover:bg-[#1B365D]/60 hover:text-white' ?>">
                <i data-lucide="database" class="w-4 h-4 text-emerald-400"></i>
                <span>Supabase Live</span>
            </a>
        </nav>
    </div>
</header>

<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

<script>
    // Catálogo en memoria para búsqueda ultra-rápida en cliente
    const allCentrosData = <?= json_encode(SENA_CENTROS_CATALOG, JSON_UNESCAPED_UNICODE) ?>;
    const currentActiveCentroId = <?= json_encode($activeCentroId) ?>;

    function renderCentrosList(centros) {
        const container = document.getElementById('centrosListContainer');
        const countLabel = document.getElementById('centrosCountLabel');
        if (!container) return;

        countLabel.textContent = `${centros.length} Centros encontrados`;
        container.innerHTML = '';

        if (centros.length === 0) {
            container.innerHTML = '<div class="p-3 text-center text-slate-400 text-xs">No se encontraron centros coincidentes.</div>';
            return;
        }

        centros.forEach(c => {
            const isSelected = c.id === currentActiveCentroId;
            const item = document.createElement('div');
            item.className = `p-2.5 hover:bg-[#1B365D] cursor-pointer border-b border-slate-800/60 transition flex items-start justify-between gap-2 ${isSelected ? 'bg-emerald-950/40 border-l-4 border-l-[#0D7A53]' : ''}`;
            item.onclick = function() { selectCentro(c.id); };

            item.innerHTML = `
                <div>
                    <div class="font-bold text-white text-xs ${isSelected ? 'text-emerald-300' : ''}">
                        ${c.nombre_centro}
                    </div>
                    <div class="text-[10px] text-slate-400 flex items-center gap-2 mt-0.5">
                        <span class="bg-slate-800 px-1.5 py-0.2 rounded font-mono text-slate-300">Cód: ${c.codigo_centro}</span>
                        <span>•</span>
                        <span>${c.regional} (${c.ciudad})</span>
                    </div>
                    <div class="text-[9px] text-slate-500 mt-0.5">
                        Subdirector: <span class="text-slate-300">${c.subdirector}</span>
                    </div>
                </div>
                ${isSelected ? '<span class="text-emerald-400 font-bold text-xs">✓ Activo</span>' : ''}
            `;
            container.appendChild(item);
        });
    }

    function openCentrosDropdown() {
        const dropdown = document.getElementById('centrosDropdownMenu');
        if (dropdown) {
            dropdown.classList.remove('hidden');
            renderCentrosList(allCentrosData);
        }
    }

    function toggleCentrosDropdown() {
        const dropdown = document.getElementById('centrosDropdownMenu');
        if (dropdown) {
            if (dropdown.classList.contains('hidden')) {
                openCentrosDropdown();
            } else {
                dropdown.classList.add('hidden');
            }
        }
    }

    function filterCentrosAutocomplete(text) {
        const q = text.toLowerCase().trim();
        const filtered = allCentrosData.filter(c => {
            return c.nombre_centro.toLowerCase().includes(q)
                || c.regional.toLowerCase().includes(q)
                || c.ciudad.toLowerCase().includes(q)
                || c.codigo_centro.toLowerCase().includes(q)
                || c.id.toLowerCase().includes(q);
        });
        renderCentrosList(filtered);
    }

    function selectCentro(centroId) {
        updateUrlParam('centro', centroId);
    }

    function updateUrlParam(key, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(key, value);
        // Al cambiar de centro, resetear coordinación si no se especificó
        if (key === 'centro') {
            url.searchParams.set('coordinacion', 'TODAS');
        }
        window.location.href = url.toString();
    }

    // Cerrar el dropdown al hacer clic fuera
    document.addEventListener('click', function(e) {
        const searchInput = document.getElementById('centroSearchInput');
        const dropdown = document.getElementById('centrosDropdownMenu');
        if (searchInput && dropdown && !searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
