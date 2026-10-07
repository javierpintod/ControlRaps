<?php
/**
 * Módulo de Auditoría de Lotes y Reversión Atómica (Rollback)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/data_helper.php';

$currentUser = requireAuth();
$repo = new SenaRepository();
$cargas = $repo->getCargas();

$canRollback = hasRole(['ADMIN', 'LIDER_FORMACION']);

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-[11px] font-bold text-[#0C2340] uppercase tracking-wider bg-slate-100 px-2 py-0.5 rounded">
                Tabla de Auditoría (cargas_archivo)
            </span>
            <h2 class="text-xl font-heading font-bold text-[#0C2340] mt-1">
                Auditoría de Lotes Ingestados y Reversión Atómica
            </h2>
            <p class="text-xs text-slate-500 mt-1 max-w-2xl">
                Trazabilidad histórica de todas las cargas de archivos procesadas en Supabase PostgreSQL. Los administradores y líderes pueden aplicar reversión atómica si se detectan inconsistencias.
            </p>
        </div>

        <div class="flex items-center space-x-2 text-xs text-slate-500 bg-slate-50 px-3 py-2 rounded-lg border border-slate-200">
            <i data-lucide="lock" class="w-3.5 h-3.5 text-[#0C2340]"></i>
            <span>Políticas RBAC Activas: <strong class="text-slate-800"><?= $currentUser['rol'] ?></strong></span>
        </div>
    </div>

    <!-- Batches Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-100 px-4 py-3 border-b border-slate-200 flex items-center justify-between text-xs">
            <span class="font-bold text-slate-700 uppercase tracking-wider">
                Lotes Registrados (<?= count($cargas) ?> transacciones históricas)
            </span>
            <span class="text-slate-500 font-mono text-[11px]">
                Target: juicios_evaluativos
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Batch ID (UUID)</th>
                        <th class="py-3 px-4">Archivo Origen</th>
                        <th class="py-3 px-4">Fecha de Corte</th>
                        <th class="py-3 px-4 text-center">Registros</th>
                        <th class="py-3 px-4">Responsable</th>
                        <th class="py-3 px-4">Estado</th>
                        <th class="py-3 px-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    <?php if (empty($cargas)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-400">
                                No hay lotes registrados en el historial.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($cargas as $c): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-600">
                                    <span class="font-bold text-[#0C2340]"><?= substr($c['batch_id'], 0, 8) ?>...</span>
                                    <span class="text-slate-400"><?= substr($c['batch_id'], -8) ?></span>
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-800 flex items-center gap-1.5">
                                    <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span><?= htmlspecialchars($c['nombre_archivo']) ?></span>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600 whitespace-nowrap">
                                    <?= $c['fecha_corte'] ?>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-800">
                                    <?= number_format($c['registros_exitosos'] ?? $c['total_registros']) ?>
                                    <?php if (!empty($c['inconsistencias'])): ?>
                                        <span class="text-rose-500 font-normal text-[10px] block">(<?= $c['inconsistencias'] ?> errores)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-700"><?= htmlspecialchars($c['usuario_nombre']) ?></div>
                                    <div class="text-[10px] text-slate-400 font-mono"><?= $c['usuario_rol'] ?></div>
                                </td>
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <?php if ($c['estado'] === 'EXITOSO'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Exitoso
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-rose-100 text-rose-800 border border-rose-300" title="<?= htmlspecialchars($c['motivo_rollback'] ?? '') ?>">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                            Revocado
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <?php if ($c['estado'] === 'EXITOSO'): ?>
                                        <?php if ($canRollback): ?>
                                            <button 
                                                onclick='openRollbackModal(<?= json_encode($c) ?>)' 
                                                class="px-2.5 py-1 bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 border border-rose-300 rounded font-semibold transition text-xs flex items-center space-x-1 ml-auto cursor-pointer"
                                            >
                                                <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                                <span>Revertir</span>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-[11px] italic">Solo Administrador</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-rose-600 font-mono text-[11px] block max-w-xs truncate" title="<?= htmlspecialchars($c['motivo_rollback'] ?? 'Sin motivo') ?>">
                                            <?= htmlspecialchars($c['motivo_rollback'] ?? 'Revertido') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ======================================================================== -->
<!-- MODAL DE CONFIRMACIÓN DE REVERSIÓN ATÓMICA (ROLLBACK) -->
<!-- ======================================================================== -->
<div id="rollbackModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-[#081628]/70 backdrop-blur-sm">
    <div class="bg-white rounded-xl border border-slate-300 shadow-2xl max-w-lg w-full p-6 space-y-4 text-xs">
        <div class="flex items-center space-x-3 text-rose-700 border-b border-slate-200 pb-3">
            <div class="w-9 h-9 rounded-full bg-rose-100 flex items-center justify-center">
                <i data-lucide="alert-octagon" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="font-bold text-sm text-[#0C2340]">Confirmar Reversión Atómica (Rollback)</h3>
                <p class="text-[11px] text-slate-500">Esta acción desestimará el lote en la base de datos Supabase.</p>
            </div>
        </div>

        <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 space-y-1">
            <p class="font-bold">Advertencia de Integridad Académica:</p>
            <p class="text-[11px]">
                Se revocarán las calificaciones del lote <strong id="modal-rollback-batch-id">--</strong> correspondientes al archivo <strong id="modal-rollback-file">--</strong>. Los indicadores de los aprendices serán recalculados.
            </p>
        </div>

        <form id="rollbackForm" onsubmit="submitRollback(event)" class="space-y-3">
            <input type="hidden" id="rollbackTargetBatchId" />

            <div>
                <label class="block font-semibold text-slate-700 mb-1">
                    Motivo de la Reversión o Dictamen de Auditoría:
                </label>
                <textarea 
                    id="rollbackReasonInput" 
                    required 
                    rows="3" 
                    placeholder="Describe el motivo (ej. Códigos de competencia con desfase o error de corte)..."
                    class="w-full bg-slate-50 border border-slate-300 rounded-lg p-2.5 text-xs text-slate-800 focus:outline-none focus:border-rose-600"
                ></textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">
                    Palabra clave de seguridad (Escribe <strong class="text-rose-600 font-mono">ROLLBACK</strong>):
                </label>
                <input 
                    type="text" 
                    id="securityWordInput" 
                    required 
                    placeholder="ROLLBACK"
                    class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-xs font-mono font-bold text-rose-700 focus:outline-none focus:border-rose-600 uppercase"
                />
            </div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-end space-x-2">
                <button type="button" onclick="closeRollbackModal()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-medium transition cursor-pointer">
                    Cancelar
                </button>
                <button type="submit" id="btnConfirmRollback" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg font-bold transition flex items-center space-x-1.5 cursor-pointer shadow">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Confirmar Rollback</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let activeRollbackCarga = null;

    function openRollbackModal(carga) {
        activeRollbackCarga = carga;
        document.getElementById('rollbackTargetBatchId').value = carga.batch_id;
        document.getElementById('modal-rollback-batch-id').textContent = carga.batch_id.substring(0, 18) + '...';
        document.getElementById('modal-rollback-file').textContent = carga.nombre_archivo;
        document.getElementById('rollbackReasonInput').value = '';
        document.getElementById('securityWordInput').value = '';
        document.getElementById('rollbackModal').classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeRollbackModal() {
        document.getElementById('rollbackModal').classList.add('hidden');
    }

    function submitRollback(e) {
        e.preventDefault();
        const batchId = document.getElementById('rollbackTargetBatchId').value;
        const motivo = document.getElementById('rollbackReasonInput').value.trim();
        const secWord = document.getElementById('securityWordInput').value.trim().toUpperCase();

        if (secWord !== 'ROLLBACK') {
            alert('Debes escribir la palabra "ROLLBACK" para confirmar la reversión de auditoría.');
            return;
        }

        const btn = document.getElementById('btnConfirmRollback');
        btn.disabled = true;
        btn.innerHTML = '<span class="animate-spin inline-block mr-1">⟳</span> Revirtiendo...';

        fetch('<?= BASE_URL ?>/api/rollback_batch.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ batch_id: batchId, motivo: motivo })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('✔ Reversión atómica completada para el lote seleccionado.');
                window.location.reload();
            } else {
                alert('Error al revertir: ' + (data.error || 'Ocurrió un error.'));
                btn.disabled = false;
                btn.innerHTML = '<span>Confirmar Rollback</span>';
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión al ejecutar el rollback.');
            btn.disabled = false;
            btn.innerHTML = '<span>Confirmar Rollback</span>';
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
