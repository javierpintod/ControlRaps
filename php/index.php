<?php
/**
 * Visor Principal - Dashboard Analítico Institucional Power BI (DirectQuery)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data_helper.php';

$currentUser = requireAuth();
$repo = new SenaRepository();

$activeCentroId = getActiveCentroId();
$activeCoordId = getActiveCoordinacionId();

$activeCentro = $repo->getCentroById($activeCentroId);
$activeCoord = $activeCoordId !== 'TODAS' ? $repo->getCoordinacionById($activeCoordId) : null;

$programas = $repo->getProgramas($activeCentroId);
$cargas = $repo->getCargas($activeCentroId);
$aprendices = $repo->getAprendices($activeCentroId, $activeCoordId);

// Filtros
$selectedPrograma = $_GET['programa'] ?? 'Todos';
$onlyPending = isset($_GET['pendientes']) && $_GET['pendientes'] === '1';
$searchTerm = strtolower(trim($_GET['q'] ?? ''));

// Filtrar aprendices
$filteredAprendices = array_filter($aprendices, function($ap) use ($selectedPrograma, $onlyPending, $searchTerm) {
    if ($selectedPrograma !== 'Todos' && $ap['codigo_programa'] !== $selectedPrograma) {
        return false;
    }
    if ($onlyPending && $ap['raps_pendientes'] === 0 && $ap['raps_no_aprobados'] === 0) {
        return false;
    }
    if (!empty($searchTerm)) {
        $fullName = strtolower($ap['nombre_completo']);
        $doc = strtolower($ap['numero_identificacion']);
        if (strpos($fullName, $searchTerm) === false && strpos($doc, $searchTerm) === false) {
            return false;
        }
    }
    return true;
});

// Métricas agregadas
$totalAprendices = count($filteredAprendices);
$totalEvaluaciones = 0;
$evaluacionesAprobadas = 0;
$evaluacionesPendientes = 0;
$evaluacionesNoAprobadas = 0;
$enRiesgoCount = 0;
$porCertificarCount = 0;
$alDiaCount = 0;

foreach ($filteredAprendices as $ap) {
    $totalEvaluaciones += $ap['total_raps'];
    $evaluacionesAprobadas += $ap['raps_aprobados'];
    $evaluacionesPendientes += $ap['raps_pendientes'];
    $evaluacionesNoAprobadas += $ap['raps_no_aprobados'];
    if ($ap['estado_academico'] === 'En Riesgo') $enRiesgoCount++;
    elseif ($ap['estado_academico'] === 'Por Certificar') $porCertificarCount++;
    else $alDiaCount++;
}

$tasaAprobacion = $totalEvaluaciones > 0 ? round(($evaluacionesAprobadas / $totalEvaluaciones) * 100, 1) : 0;
$canUpload = hasRole(['ADMIN', 'LIDER_FORMACION', 'COORDINADOR']);

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Banner de Contexto de Coordinación / Centro -->
    <div class="bg-white px-4 py-3 rounded-xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3 text-xs">
        <div class="flex items-center gap-2">
            <span class="p-1.5 rounded-lg bg-emerald-50 text-[#0D7A53]">
                <i data-lucide="building-2" class="w-4 h-4"></i>
            </span>
            <div>
                <span class="text-slate-400 text-[10px] uppercase font-bold block">Centro Activo:</span>
                <span class="font-bold text-[#0C2340]"><?= htmlspecialchars($activeCentro['nombre_centro']) ?></span>
            </div>
            <span class="text-slate-300 mx-2 hidden sm:inline">&bull;</span>
            <div class="hidden sm:block">
                <span class="text-slate-400 text-[10px] uppercase font-bold block">Coordinación Académica:</span>
                <span class="font-bold text-amber-700"><?= htmlspecialchars($activeCoord['nombre_coordinacion'] ?? 'Todas las Coordinaciones del Centro') ?></span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?= BASE_URL ?>/api/export_coordinacion_report.php?centro=<?= urlencode($activeCentroId) ?>&coordinacion=<?= urlencode($activeCoordId) ?>" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-[#0D7A53] border border-emerald-300 rounded-md font-semibold transition flex items-center gap-1.5">
                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                <span>Descargar Reporte (.CSV)</span>
            </a>
            <?php if (hasRole(['SUBDIRECTOR', 'ADMIN', 'LIDER_FORMACION', 'COORDINADOR'])): ?>
                <a href="<?= BASE_URL ?>/subdirector.php?centro=<?= urlencode($activeCentroId) ?>" class="px-3 py-1.5 bg-[#0C2340] hover:bg-[#1B365D] text-white rounded-md font-semibold transition flex items-center gap-1.5">
                    <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>Ver Dashboard Subdirector</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Power BI Embedded Container Wrapper -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Power BI Header Bar -->
        <div class="bg-[#0C2340] px-4 py-3 flex flex-wrap items-center justify-between gap-3 text-white">
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#0D7A53] inline-block animate-pulse"></span>
                    <span class="font-heading font-semibold text-sm tracking-wide">
                        Power BI Embedded • App Owns Data (DirectQuery)
                    </span>
                </div>
                <span class="text-xs text-slate-300 hidden md:inline border-l border-slate-700 pl-3">
                    <?= isSupabaseConfigured() ? 'Supabase PostgreSQL Sync' : 'XAMPP Mock Sync' ?>
                </span>
            </div>

            <div class="flex items-center space-x-2">
                <button onclick="toggleDaxInspector()" class="px-3 py-1 bg-[#1B365D] hover:bg-slate-700 text-xs font-semibold rounded-md border border-slate-600 transition flex items-center space-x-1.5 cursor-pointer">
                    <i data-lucide="code-2" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>Inspector DAX / SQL</span>
                </button>
                <?php if ($canUpload): ?>
                    <a href="<?= BASE_URL ?>/upload.php" class="px-3 py-1 bg-[#0D7A53] hover:bg-emerald-600 text-xs font-semibold rounded-md transition flex items-center space-x-1.5 shadow-sm">
                        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                        <span>Cargar Lote</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filter & Control Ribbon -->
        <div class="bg-[#F8FAFC] px-4 py-3 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 text-xs">
            <form method="GET" action="" id="filterForm" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                <input type="hidden" name="centro" value="<?= htmlspecialchars($activeCentroId) ?>" />
                <input type="hidden" name="coordinacion" value="<?= htmlspecialchars($activeCoordId) ?>" />
                
                <!-- Filtro Programa -->
                <div class="flex items-center space-x-2">
                    <span class="font-semibold text-slate-700 flex items-center gap-1">
                        <i data-lucide="filter" class="w-3.5 h-3.5 text-[#0D7A53]"></i>
                        Programa:
                    </span>
                    <select name="programa" onchange="document.getElementById('filterForm').submit()" class="bg-white border border-slate-300 rounded-md px-3 py-1.5 font-medium text-slate-700 focus:outline-none focus:border-[#0D7A53]">
                        <option value="Todos" <?= $selectedPrograma === 'Todos' ? 'selected' : '' ?>>Todos los Programas</option>
                        <?php foreach ($programas as $p): ?>
                            <option value="<?= $p['codigo_programa'] ?>" <?= $selectedPrograma === $p['codigo_programa'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['codigo_programa'] . ' - ' . $p['nombre_programa']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro Pendientes / En Riesgo -->
                <label class="flex items-center space-x-2 cursor-pointer bg-white border border-slate-300 px-3 py-1.5 rounded-md hover:bg-slate-50 transition">
                    <input type="checkbox" name="pendientes" value="1" <?= $onlyPending ? 'checked' : '' ?> onchange="document.getElementById('filterForm').submit()" class="rounded text-[#0D7A53] focus:ring-[#0D7A53]">
                    <span class="font-medium text-slate-700">Solo Pendientes o En Riesgo</span>
                </label>

                <!-- Búsqueda rápida de texto -->
                <div class="relative flex-1 sm:w-64">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                    <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Buscar por nombre o documento..." class="w-full bg-white border border-slate-300 rounded-md pl-8 pr-3 py-1.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#0D7A53]">
                </div>
            </form>

            <div class="text-slate-500 font-mono text-[11px] flex items-center gap-2">
                <span>Registros filtrados: <strong class="text-slate-800"><?= $totalAprendices ?></strong></span>
            </div>
        </div>

        <!-- KPI Metric Cards Grid -->
        <div class="p-6 bg-slate-50 border-b border-slate-200">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- KPI 1: Total Aprendices -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Aprendices</span>
                        <div class="text-2xl font-bold font-heading text-[#0C2340] mt-1"><?= $totalAprendices ?></div>
                        <span class="text-[10px] text-slate-400">Población activa en corte</span>
                    </div>
                    <div class="w-11 h-11 rounded-lg bg-blue-50 text-[#1B365D] flex items-center justify-center">
                        <i data-lucide="users" class="w-5 h-5"></i>
                    </div>
                </div>

                <!-- KPI 2: Evaluaciones RAPs -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Juicios Evaluativos</span>
                        <div class="text-2xl font-bold font-heading text-slate-800 mt-1"><?= $totalEvaluaciones ?></div>
                        <span class="text-[10px] text-slate-400"><?= $evaluacionesAprobadas ?> Aprobados • <?= $evaluacionesPendientes ?> Pend.</span>
                    </div>
                    <div class="w-11 h-11 rounded-lg bg-emerald-50 text-[#0D7A53] flex items-center justify-center">
                        <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                    </div>
                </div>

                <!-- KPI 3: Tasa de Aprobación -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tasa de Aprobación</span>
                        <div class="text-2xl font-bold font-heading text-[#0D7A53] mt-1"><?= $tasaAprobacion ?>%</div>
                        <div class="w-24 bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
                            <div class="bg-[#0D7A53] h-1.5 rounded-full" style="width: <?= min(100, $tasaAprobacion) ?>%"></div>
                        </div>
                    </div>
                    <div class="w-11 h-11 rounded-lg bg-emerald-50 text-[#0D7A53] flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-5 h-5"></i>
                    </div>
                </div>

                <!-- KPI 4: Aprendices en Riesgo -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">Aprendices en Riesgo</span>
                        <div class="text-2xl font-bold font-heading text-rose-600 mt-1"><?= $enRiesgoCount ?></div>
                        <span class="text-[10px] text-slate-400">Requieren plan de mejoramiento</span>
                    </div>
                    <div class="w-11 h-11 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                    </div>
                </div>

            </div>
        </div>

        <!-- Interactive Charts Grid (Chart.js) -->
        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 bg-white border-b border-slate-200">
            <!-- Gráfico 1: Estado Académico (Doughnut) -->
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wide">Distribución de Estados Académicos</h3>
                    <i data-lucide="pie-chart" class="w-4 h-4 text-slate-400"></i>
                </div>
                <div class="relative h-56 flex items-center justify-center">
                    <canvas id="chartEstados"></canvas>
                </div>
            </div>

            <!-- Gráfico 2: Desempeño por Programa (Bar Chart) -->
            <div class="bg-slate-50/70 p-4 rounded-xl border border-slate-200 lg:col-span-2">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wide">Tasa de Aprobación por Programa de Formación</h3>
                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-slate-400"></i>
                </div>
                <div class="relative h-56">
                    <canvas id="chartProgramas"></canvas>
                </div>
            </div>
        </div>

        <!-- Learners Master Table -->
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-[#0C2340]">Matriz Académica Consolidada de Aprendices</h3>
                    <p class="text-xs text-slate-500">Listado interactivo conectado por DirectQuery a Supabase. Clic en "Ver Expediente" para consultar RAPs y generar Paz y Salvo.</p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="w-full text-xs text-left">
                    <thead class="bg-[#0C2340] text-white uppercase text-[11px] font-semibold">
                        <tr>
                            <th class="py-3 px-4">Documento</th>
                            <th class="py-3 px-4">Aprendiz</th>
                            <th class="py-3 px-4">Programa de Formación</th>
                            <th class="py-3 px-4 text-center">RAPs</th>
                            <th class="py-3 px-4 text-center">Juicios (A / P / D)</th>
                            <th class="py-3 px-4 text-center">Avance</th>
                            <th class="py-3 px-4 text-center">Estado Académico</th>
                            <th class="py-3 px-4 text-right">Expediente</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <?php if (empty($filteredAprendices)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                    No se encontraron aprendices con los filtros seleccionados.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($filteredAprendices as $ap): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-4 font-mono font-medium text-slate-700 whitespace-nowrap">
                                        <span class="text-[10px] text-slate-400 font-bold"><?= $ap['tipo_identificacion'] ?></span>
                                        <?= htmlspecialchars($ap['numero_identificacion']) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-[#0C2340]"><?= htmlspecialchars($ap['nombre_completo']) ?></div>
                                        <div class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($ap['email']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600 max-w-xs truncate" title="<?= htmlspecialchars($ap['nombre_programa']) ?>">
                                        <span class="font-bold text-slate-700">[<?= $ap['codigo_programa'] ?>]</span>
                                        <?= htmlspecialchars($ap['nombre_programa']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-800">
                                        <?= $ap['total_raps'] ?>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 font-mono font-semibold">
                                            <span class="text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded" title="Aprobados"><?= $ap['raps_aprobados'] ?> A</span>
                                            <span class="text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded" title="Pendientes"><?= $ap['raps_pendientes'] ?> P</span>
                                            <span class="text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded" title="No Aprobados"><?= $ap['raps_no_aprobados'] ?> D</span>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-16 bg-slate-200 rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full <?= $ap['porcentaje_avance'] === 100 ? 'bg-[#0D7A53]' : ($ap['porcentaje_avance'] >= 70 ? 'bg-blue-600' : 'bg-amber-500') ?>" style="width: <?= $ap['porcentaje_avance'] ?>%"></div>
                                            </div>
                                            <span class="font-mono font-bold text-[11px] text-slate-700"><?= $ap['porcentaje_avance'] ?>%</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center whitespace-nowrap">
                                        <?php if ($ap['estado_academico'] === 'Por Certificar'): ?>
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-[#0D7A53] border border-emerald-300">
                                                Por Certificar
                                            </span>
                                        <?php elseif ($ap['estado_academico'] === 'En Riesgo'): ?>
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-100 text-rose-700 border border-rose-300">
                                                En Riesgo
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-700 border border-blue-300">
                                                Al Día
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <button 
                                            onclick='openLearnerModal(<?= json_encode($ap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            class="inline-flex items-center space-x-1 px-2.5 py-1 bg-slate-100 hover:bg-[#0D7A53] hover:text-white rounded border border-slate-300 hover:border-emerald-600 text-slate-700 font-medium transition cursor-pointer text-xs"
                                        >
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            <span>Detalle</span>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================== -->
<!-- MODAL: EXPEDIENTE DEL APRENDIZ Y PAZ Y SALVO ACADÉMICO -->
<!-- ======================================================================== -->
<div id="learnerModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-[#081628]/70 backdrop-blur-sm overflow-y-auto">
    <div id="printable-modal" class="bg-white rounded-xl border border-slate-300 shadow-2xl max-w-3xl w-full overflow-hidden max-h-[92vh] flex flex-col">
        
        <!-- Modal Header -->
        <div class="bg-[#0C2340] text-white p-4 sm:p-5 flex items-center justify-between no-print">
            <div class="flex items-center space-x-3">
                <div id="modal-avatar" class="w-10 h-10 rounded-full bg-white text-[#0C2340] flex items-center justify-center font-bold text-sm">
                    --
                </div>
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#0D7A53] bg-[#E6F4EA] px-2 py-0.5 rounded">
                        Expediente SofiaPlus • Juicios Evaluativos
                    </span>
                    <h3 id="modal-nombre" class="text-base font-heading font-bold mt-1 text-white">
                        Nombre del Aprendiz
                    </h3>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <button onclick="window.print()" class="p-2 text-slate-300 hover:text-white hover:bg-slate-700 rounded-md transition" title="Imprimir Paz y Salvo">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                </button>
                <button onclick="closeLearnerModal()" class="p-2 text-slate-300 hover:text-white hover:bg-slate-700 rounded-md transition text-lg font-bold">
                    &times;
                </button>
            </div>
        </div>

        <!-- Institutional Header Visible ONLY in Print -->
        <div class="hidden print:block p-6 border-b border-slate-300 text-center">
            <h2 class="text-xl font-bold text-[#0C2340]">SERVICIO NACIONAL DE APRENDIZAJE - SENA</h2>
            <p class="text-sm text-slate-600 font-semibold">CERTIFICADO Y PAZ Y SALVO ACADÉMICO DE EVALUACIONES</p>
            <p class="text-xs text-slate-500 mt-1">Expedido a través de la Plataforma Analítica Institucional • DirectQuery</p>
        </div>

        <!-- Modal Body Content -->
        <div class="p-5 overflow-y-auto space-y-5 text-xs custom-scrollbar">
            <!-- Top Learner Info Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                <div>
                    <span class="text-slate-400 block text-[10px] font-semibold">DOCUMENTO:</span>
                    <span id="modal-documento" class="font-mono font-bold text-slate-800 text-xs">--</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] font-semibold">PROGRAMA:</span>
                    <span id="modal-programa" class="font-bold text-slate-800 text-xs truncate block">--</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] font-semibold">CORREO INSTITUCIONAL:</span>
                    <span id="modal-email" class="font-mono text-slate-700 text-xs truncate block">--</span>
                </div>
            </div>

            <!-- Paz y Salvo Banner (se muestra si 100% aprobado) -->
            <div id="modal-pazysalvo-banner" class="hidden p-4 rounded-xl border border-emerald-500/40 bg-emerald-50 text-emerald-900 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-[#0D7A53] text-white flex items-center justify-center flex-shrink-0">
                        <i data-lucide="award" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-[#0D7A53]">PAZ Y SALVO ACADÉMICO PARA CERTIFICACIÓN</h4>
                        <p class="text-[11px] text-slate-600">El aprendiz ha aprobado satisfactoriamente la totalidad de Resultados de Aprendizaje (RAPs) del programa.</p>
                    </div>
                </div>
                <span class="text-xs font-mono font-bold text-[#0D7A53] border border-[#0D7A53] px-2.5 py-1 rounded bg-white">100% CUMPLIDO</span>
            </div>

            <!-- Learning Outcomes Evaluated Table -->
            <div class="space-y-2">
                <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="file-check-2" class="w-4 h-4 text-[#0D7A53]"></i>
                    Juicios Evaluativos y Resultados de Aprendizaje Registrados
                </h4>

                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 text-slate-600 uppercase text-[10px] font-bold">
                            <tr>
                                <th class="py-2.5 px-3">Competencia Formativa</th>
                                <th class="py-2.5 px-3">Resultado de Aprendizaje (RAP)</th>
                                <th class="py-2.5 px-3 text-center">Juicio</th>
                                <th class="py-2.5 px-3 text-right">Evaluación</th>
                            </tr>
                        </thead>
                        <tbody id="modal-evaluaciones-list" class="divide-y divide-slate-200 bg-white">
                            <!-- Inyectado por JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-slate-50 px-5 py-3 border-t border-slate-200 flex items-center justify-between text-xs no-print">
            <span class="text-slate-500 font-mono text-[11px]">Sistema SofiaPlus • Auditoría SENA</span>
            <div class="flex items-center space-x-2">
                <button onclick="window.print()" class="px-3 py-1.5 bg-[#0D7A53] hover:bg-emerald-600 text-white rounded font-medium transition flex items-center space-x-1.5">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    <span>Imprimir Paz y Salvo</span>
                </button>
                <button onclick="closeLearnerModal()" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded font-medium transition">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================== -->
<!-- MODAL: INSPECTOR DAX / SQL DIRECTQUERY -->
<!-- ======================================================================== -->
<div id="daxModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-[#081628]/70 backdrop-blur-sm">
    <div class="bg-[#0C2340] border border-slate-700 text-white rounded-xl shadow-2xl max-w-2xl w-full p-5 space-y-4 text-xs font-mono">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <div class="flex items-center space-x-2">
                <i data-lucide="database" class="w-4 h-4 text-emerald-400"></i>
                <h3 class="font-bold text-sm text-white">Inspector de Consultas DirectQuery & DAX</h3>
            </div>
            <button onclick="toggleDaxInspector()" class="text-slate-400 hover:text-white text-base font-bold">&times;</button>
        </div>

        <div class="space-y-3">
            <div>
                <span class="text-slate-400 block text-[10px] mb-1">CONSULTA SQL POSTGRESQL (Enviada a Supabase):</span>
                <pre class="bg-[#081628] p-3 rounded border border-slate-800 text-emerald-300 text-[11px] overflow-x-auto">
SELECT 
    ap.numero_identificacion,
    ap.nombre_completo,
    ap.codigo_programa,
    COUNT(je.id) AS total_raps,
    SUM(CASE WHEN je.juicio = 'APROBADO' THEN 1 ELSE 0 END) AS raps_aprobados
FROM aprendices ap
LEFT JOIN juicios_evaluativos je ON ap.numero_identificacion = je.numero_identificacion
GROUP BY ap.numero_identificacion, ap.nombre_completo, ap.codigo_programa;
                </pre>
            </div>

            <div>
                <span class="text-slate-400 block text-[10px] mb-1">EXPRESIÓN DAX POWER BI TABULAR:</span>
                <pre class="bg-[#081628] p-3 rounded border border-slate-800 text-blue-300 text-[11px] overflow-x-auto">
Tasa_Aprobacion_Global := 
DIVIDE(
    CALCULATE(COUNTROWS('JuiciosEvaluativos'), 'JuiciosEvaluativos'[Juicio] = "APROBADO"),
    COUNTROWS('JuiciosEvaluativos'),
    0
) * 100
                </pre>
            </div>

            <div class="flex items-center justify-between pt-2 text-[11px] text-slate-400 border-t border-slate-800">
                <span>Latencia actual: <strong class="text-emerald-400 font-bold"><?= $latency ?> ms</strong></span>
                <span>Motor: Supabase PostgREST v12</span>
            </div>
        </div>

        <div class="text-right">
            <button onclick="toggleDaxInspector()" class="px-3 py-1.5 bg-[#1B365D] hover:bg-slate-700 text-white rounded transition text-xs">Cerrar Inspector</button>
        </div>
    </div>
</div>

<script>
    // Inicializar Gráficos con Chart.js
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico 1: Doughnut Estados
        const ctxEstados = document.getElementById('chartEstados')?.getContext('2d');
        if (ctxEstados) {
            new Chart(ctxEstados, {
                type: 'doughnut',
                data: {
                    labels: ['Por Certificar', 'Al Día', 'En Riesgo'],
                    datasets: [{
                        data: [<?= $porCertificarCount ?>, <?= $alDiaCount ?>, <?= $enRiesgoCount ?>],
                        backgroundColor: ['#0D7A53', '#2563EB', '#E11D48'],
                        borderWidth: 2,
                        borderColor: '#FFFFFF'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
                    },
                    cutout: '65%'
                }
            });
        }

        // Gráfico 2: Programas Aprobación
        const ctxProg = document.getElementById('chartProgramas')?.getContext('2d');
        if (ctxProg) {
            const progLabels = <?= json_encode(array_map(fn($p) => substr($p['nombre_programa'], 0, 18) . '...', $programas)) ?>;
            const progTasas = <?= json_encode(array_column($programas, 'tasa_aprobacion')) ?>;

            new Chart(ctxProg, {
                type: 'bar',
                data: {
                    labels: progLabels,
                    datasets: [{
                        label: 'Tasa Aprobación (%)',
                        data: progTasas,
                        backgroundColor: '#0D7A53',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    });

    // Control Modal Aprendiz
    function openLearnerModal(ap) {
        document.getElementById('modal-nombre').textContent = ap.nombre_completo;
        document.getElementById('modal-documento').textContent = ap.tipo_identificacion + ' ' + ap.numero_identificacion;
        document.getElementById('modal-programa').textContent = '[' + ap.codigo_programa + '] ' + ap.nombre_programa;
        document.getElementById('modal-email').textContent = ap.email;

        const initials = ap.nombre_completo.split(' ').map(n => n[0]).slice(0, 2).join('');
        document.getElementById('modal-avatar').textContent = initials;

        const isPazYSalvo = ap.raps_aprobados === ap.total_raps && ap.total_raps > 0;
        const banner = document.getElementById('modal-pazysalvo-banner');
        if (isPazYSalvo) {
            banner.classList.remove('hidden');
        } else {
            banner.classList.add('hidden');
        }

        const tbody = document.getElementById('modal-evaluaciones-list');
        tbody.innerHTML = '';

        if (!ap.evaluaciones || ap.evaluaciones.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-slate-400">Sin evaluaciones registradas para este aprendiz.</td></tr>';
        } else {
            ap.evaluaciones.forEach(ev => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition';

                let badge = '';
                if (ev.juicio === 'APROBADO') {
                    badge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-[#0D7A53]">APROBADO</span>';
                } else if (ev.juicio === 'NO_APROBADO') {
                    badge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">NO APROBADO</span>';
                } else {
                    badge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">POR EVALUAR</span>';
                }

                tr.innerHTML = `
                    <td class="py-2.5 px-3 font-medium text-slate-700">${ev.competencia || 'N/A'}</td>
                    <td class="py-2.5 px-3 text-slate-600">${ev.resultado_aprendizaje || 'N/A'}</td>
                    <td class="py-2.5 px-3 text-center whitespace-nowrap">${badge}</td>
                    <td class="py-2.5 px-3 text-right font-mono text-[11px] text-slate-500 whitespace-nowrap">${ev.fecha_evaluacion || 'Pendiente'}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        document.getElementById('learnerModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeLearnerModal() {
        document.getElementById('learnerModal').classList.add('hidden');
    }

    function toggleDaxInspector() {
        const m = document.getElementById('daxModal');
        m.classList.toggle('hidden');
        if (window.lucide) lucide.createIcons();
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
