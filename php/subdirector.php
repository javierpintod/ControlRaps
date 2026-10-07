<?php
/**
 * Dashboard Gerencial del Subdirector de Centro
 * Monitoreo Estratégico y Comparativo de Coordinaciones Académicas
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data_helper.php';

$currentUser = requireAuth();
requireRole(['SUBDIRECTOR', 'ADMIN', 'LIDER_FORMACION', 'COORDINADOR']);

$repo = new SenaRepository();
$activeCentroId = getActiveCentroId();
$centro = $repo->getCentroById($activeCentroId);

// Métricas consolidadas por coordinación para este centro
$coordinacionesMetricas = $repo->getMetricasPorCoordinacion($activeCentroId);

// Totales consolidados del centro
$totalAprendicesCentro = 0;
$totalRapsCentro = 0;
$rapsAprobadosCentro = 0;
$rapsPendientesCentro = 0;
$rapsNoAprobadosCentro = 0;
$enRiesgoCentro = 0;
$porCertificarCentro = 0;
$alDiaCentro = 0;

foreach ($coordinacionesMetricas as $cm) {
    $totalAprendicesCentro += $cm['total_aprendices'];
    $totalRapsCentro += $cm['total_raps'];
    $rapsAprobadosCentro += $cm['raps_aprobados'];
    $rapsPendientesCentro += $cm['raps_pendientes'];
    $rapsNoAprobadosCentro += $cm['raps_no_aprobados'];
    $enRiesgoCentro += $cm['en_riesgo'];
    $porCertificarCentro += $cm['por_certificar'];
    $alDiaCentro += $cm['al_dia'];
}

$tasaAprobacionCentro = $totalRapsCentro > 0 ? round(($rapsAprobadosCentro / $totalRapsCentro) * 100, 1) : 0;

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6" id="printable-subdirector-report">
    
    <!-- Header Banner Ejecutivo -->
    <div class="bg-gradient-to-r from-[#0C2340] via-[#1B365D] to-[#0D7A53] p-6 rounded-2xl shadow-xl text-white relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <svg viewBox="0 0 100 100" class="w-64 h-64" fill="currentColor">
                <circle cx="50" cy="50" r="48" />
            </svg>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-amber-300 bg-amber-500/20 border border-amber-500/40 px-2.5 py-0.5 rounded-full">
                        Despacho de Subdirección de Centro
                    </span>
                    <span class="text-[10px] text-slate-300 font-mono">
                        Cód. Centro: <?= $centro['codigo_centro'] ?>
                    </span>
                </div>

                <h2 class="text-2xl font-heading font-extrabold text-white mt-1.5">
                    <?= htmlspecialchars($centro['nombre_centro']) ?>
                </h2>

                <p class="text-xs text-slate-300 mt-1 flex items-center gap-2">
                    <span>Regional: <strong><?= $centro['regional'] ?> (<?= $centro['ciudad'] ?>)</strong></span>
                    <span>&bull;</span>
                    <span>Subdirector(a): <strong><?= $centro['subdirector'] ?></strong></span>
                </p>
            </div>

            <div class="flex items-center space-x-2 no-print">
                <a href="<?= BASE_URL ?>/api/export_coordinacion_report.php?centro=<?= urlencode($activeCentroId) ?>&coordinacion=TODAS" class="px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white border border-white/20 rounded-lg text-xs font-semibold transition flex items-center space-x-2">
                    <i data-lucide="download" class="w-4 h-4 text-emerald-300"></i>
                    <span>Exportar Informe Consolidado</span>
                </a>
                <button onclick="window.print()" class="px-3.5 py-2 bg-[#0D7A53] hover:bg-emerald-600 text-white rounded-lg text-xs font-bold transition flex items-center space-x-1.5 shadow cursor-pointer">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Imprimir Resumen</span>
                </button>
            </div>
        </div>
    </div>

    <!-- KPI Cards Grid Ejecutivo -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Aprendices Centro -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Población Activa</span>
                <div class="text-2xl font-bold font-heading text-[#0C2340] mt-1"><?= $totalAprendicesCentro ?></div>
                <span class="text-[10px] text-slate-400">Aprendices en el Centro</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#1B365D] flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Total Coordinaciones -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Coordinaciones</span>
                <div class="text-2xl font-bold font-heading text-slate-800 mt-1"><?= count($coordinacionesMetricas) ?></div>
                <span class="text-[10px] text-slate-400">Áreas Académicas Activas</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <i data-lucide="network" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Tasa Global Centro -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Aprobación del Centro</span>
                <div class="text-2xl font-bold font-heading text-[#0D7A53] mt-1"><?= $tasaAprobacionCentro ?>%</div>
                <span class="text-[10px] text-slate-400"><?= $rapsAprobadosCentro ?> de <?= $totalRapsCentro ?> RAPs evaluados</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-[#0D7A53] flex items-center justify-center">
                <i data-lucide="trending-up" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Aprendices en Riesgo Crítico -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600">En Riesgo Académico</span>
                <div class="text-2xl font-bold font-heading text-rose-600 mt-1"><?= $enRiesgoCentro ?></div>
                <span class="text-[10px] text-slate-400">Requieren plan de mejoramiento</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <i data-lucide="alert-octagon" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- Gráficos Comparativos por Coordinación con Chart.js -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Gráfico 1: Tasa de Aprobación por Coordinación -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-[#0C2340]">Comparativa de Rendimiento y Aprobación por Coordinación</h3>
                    <p class="text-xs text-slate-500">Métricas analíticas consolidadas de resultados de aprendizaje.</p>
                </div>
                <i data-lucide="bar-chart-3" class="w-5 h-5 text-slate-400"></i>
            </div>
            <div class="relative h-64">
                <canvas id="chartCoordinacionesTasa"></canvas>
            </div>
        </div>

        <!-- Gráfico 2: Distribución de Población -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-[#0C2340]">Población de Aprendices</h3>
                    <p class="text-xs text-slate-500">Distribución por coordinación.</p>
                </div>
                <i data-lucide="pie-chart" class="w-5 h-5 text-slate-400"></i>
            </div>
            <div class="relative h-64 flex items-center justify-center">
                <canvas id="chartCoordinacionesPoblacion"></canvas>
            </div>
        </div>

    </div>

    <!-- Cuadrícula Semáforo de Coordinaciones -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-heading font-bold text-[#0C2340]">
                    Estado de Cada Coordinación Académica
                </h3>
                <p class="text-xs text-slate-500">
                    Semáforo de cumplimiento: <span class="text-emerald-600 font-bold">Verde (&ge;85%)</span>, <span class="text-amber-600 font-bold">Amarillo (70%-84%)</span>, <span class="text-rose-600 font-bold">Rojo (&lt;70% o con aprendices en riesgo)</span>.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($coordinacionesMetricas as $item): 
                $coord = $item['coordinacion'];
                $semaforoColor = $item['semaforo'] === 'VERDE' ? 'emerald' : ($item['semaforo'] === 'AMARILLO' ? 'amber' : 'rose');
            ?>
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition space-y-4">
                    <!-- Top Coord Info -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-<?= $semaforoColor ?>-500 inline-block animate-pulse"></span>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                    <?= $coord['id'] ?>
                                </span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-<?= $semaforoColor ?>-50 text-<?= $semaforoColor ?>-700 border border-<?= $semaforoColor ?>-300">
                                    Semáforo: <?= $item['semaforo'] ?>
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-[#0C2340]">
                                <?= htmlspecialchars($coord['nombre_coordinacion']) ?>
                            </h4>
                            <p class="text-[11px] text-slate-500">
                                Responsable: <strong class="text-slate-700"><?= htmlspecialchars($coord['coordinador_nombre']) ?></strong>
                            </p>
                        </div>

                        <div class="text-right">
                            <span class="text-2xl font-bold font-heading text-<?= $semaforoColor ?>-600">
                                <?= $item['tasa_aprobacion'] ?>%
                            </span>
                            <span class="block text-[10px] text-slate-400">Meta: <?= $coord['meta_tasa_aprobacion'] ?>%</span>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-<?= $semaforoColor ?>-500 h-2 rounded-full" style="width: <?= min(100, $item['tasa_aprobacion']) ?>%"></div>
                        </div>
                    </div>

                    <!-- Mini Metrics Grid -->
                    <div class="grid grid-cols-4 gap-2 text-center text-xs bg-slate-50 p-2.5 rounded-lg border border-slate-200/80">
                        <div>
                            <span class="text-[10px] text-slate-400 block font-semibold">APRENDICES</span>
                            <span class="font-bold text-slate-800"><?= $item['total_aprendices'] ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-emerald-600 block font-semibold">APROBADOS</span>
                            <span class="font-bold text-emerald-700"><?= $item['raps_aprobados'] ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-amber-600 block font-semibold">PENDIENTES</span>
                            <span class="font-bold text-amber-700"><?= $item['raps_pendientes'] ?></span>
                        </div>
                        <div>
                            <span class="text-[10px] text-rose-600 block font-semibold">EN RIESGO</span>
                            <span class="font-bold text-rose-600"><?= $item['en_riesgo'] ?></span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between no-print">
                        <span class="text-[10px] text-slate-400 font-mono">
                            <?= $item['programas_count'] ?> programas adscritos
                        </span>

                        <div class="flex items-center space-x-2">
                            <a href="<?= BASE_URL ?>/index.php?centro=<?= urlencode($activeCentroId) ?>&coordinacion=<?= urlencode($coord['id']) ?>" class="px-2.5 py-1.5 bg-slate-100 hover:bg-[#0C2340] hover:text-white rounded text-xs font-semibold text-slate-700 transition flex items-center space-x-1">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Ver Aprendices</span>
                            </a>
                            <a href="<?= BASE_URL ?>/api/export_coordinacion_report.php?centro=<?= urlencode($activeCentroId) ?>&coordinacion=<?= urlencode($coord['id']) ?>" class="px-2.5 py-1.5 bg-[#0D7A53] hover:bg-emerald-600 rounded text-xs font-semibold text-white transition flex items-center space-x-1">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Descargar Reporte</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Matriz Comparativa Tabular -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-100 px-5 py-3 border-b border-slate-200 flex items-center justify-between text-xs font-bold text-slate-700 uppercase">
            <span>Matriz de Control Gerencial de Coordinaciones</span>
            <span class="font-mono text-slate-500">Auditoría Subdirección</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Coordinación</th>
                        <th class="py-3 px-4">Coordinador Responsable</th>
                        <th class="py-3 px-4 text-center">Población</th>
                        <th class="py-3 px-4 text-center">RAPs Aprobados</th>
                        <th class="py-3 px-4 text-center">Tasa Real</th>
                        <th class="py-3 px-4 text-center">Meta</th>
                        <th class="py-3 px-4 text-center">En Riesgo</th>
                        <th class="py-3 px-4 text-center">Estado</th>
                        <th class="py-3 px-4 text-right no-print">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php foreach ($coordinacionesMetricas as $item): 
                        $c = $item['coordinacion'];
                    ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-bold text-[#0C2340]">
                                <?= htmlspecialchars($c['nombre_coordinacion']) ?>
                            </td>
                            <td class="py-3 px-4 text-slate-700">
                                <div><?= htmlspecialchars($c['coordinador_nombre']) ?></div>
                                <div class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($c['coordinador_email']) ?></div>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">
                                <?= $item['total_aprendices'] ?>
                            </td>
                            <td class="py-3 px-4 text-center text-emerald-700 font-bold">
                                <?= $item['raps_aprobados'] ?> / <?= $item['total_raps'] ?>
                            </td>
                            <td class="py-3 px-4 text-center font-bold text-slate-800">
                                <?= $item['tasa_aprobacion'] ?>%
                            </td>
                            <td class="py-3 px-4 text-center font-mono text-slate-500">
                                <?= $c['meta_tasa_aprobacion'] ?>%
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($item['en_riesgo'] > 0): ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-300">
                                        <?= $item['en_riesgo'] ?> críticos
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400 font-mono">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?= $item['semaforo'] === 'VERDE' ? 'bg-emerald-100 text-emerald-800' : ($item['semaforo'] === 'AMARILLO' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') ?>">
                                    <?= $item['semaforo'] ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right no-print">
                                <a href="<?= BASE_URL ?>/index.php?centro=<?= urlencode($activeCentroId) ?>&coordinacion=<?= urlencode($c['id']) ?>" class="text-[#0D7A53] hover:underline font-bold text-xs">
                                    Detalle &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const coordLabels = <?= json_encode(array_map(fn($m) => substr($m['coordinacion']['nombre_coordinacion'], 0, 22) . '...', $coordinacionesMetricas), JSON_UNESCAPED_UNICODE) ?>;
        const coordTasas = <?= json_encode(array_column($coordinacionesMetricas, 'tasa_aprobacion')) ?>;
        const coordPoblacion = <?= json_encode(array_column($coordinacionesMetricas, 'total_aprendices')) ?>;

        // Gráfico 1: Tasa de Aprobación por Coordinación
        const ctxTasa = document.getElementById('chartCoordinacionesTasa')?.getContext('2d');
        if (ctxTasa) {
            new Chart(ctxTasa, {
                type: 'bar',
                data: {
                    labels: coordLabels,
                    datasets: [{
                        label: 'Tasa Real (%)',
                        data: coordTasas,
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

        // Gráfico 2: Población de Aprendices
        const ctxPob = document.getElementById('chartCoordinacionesPoblacion')?.getContext('2d');
        if (ctxPob) {
            new Chart(ctxPob, {
                type: 'doughnut',
                data: {
                    labels: coordLabels,
                    datasets: [{
                        data: coordPoblacion,
                        backgroundColor: ['#0C2340', '#0D7A53', '#2563EB', '#D97706', '#9333EA'],
                        borderWidth: 2,
                        borderColor: '#FFFFFF'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 9 } } }
                    },
                    cutout: '60%'
                }
            });
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
