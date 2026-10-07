<?php
/**
 * Herramienta de Diagnóstico de Conexión a Supabase
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/Supabase.php';

$currentUser = requireAuth();
$sb = new SupabaseClient();
$diag = $sb->testConnection();

require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-[11px] font-bold text-[#0D7A53] uppercase tracking-wider bg-emerald-50 px-2 py-0.5 rounded">
                Diagnóstico de Infraestructura Cloud
            </span>
            <h2 class="text-xl font-heading font-bold text-[#0C2340] mt-1">
                Estado de Conexión con Supabase PostgreSQL
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Verificación de latencia, políticas PostgREST y sincronización con el servicio XAMPP.
            </p>
        </div>

        <button onclick="window.location.reload()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-semibold text-slate-700 transition flex items-center space-x-1.5 border border-slate-300">
            <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-[#0D7A53]"></i>
            <span>Volver a Probar</span>
        </button>
    </div>

    <!-- Diagnostic Result Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden p-6 space-y-6">
        
        <!-- Status Banner -->
        <div class="p-4 rounded-xl border <?= $diag['success'] ? 'bg-emerald-50 border-emerald-300 text-emerald-900' : 'bg-amber-50 border-amber-300 text-amber-900' ?> flex items-start space-x-3">
            <div class="w-8 h-8 rounded-full <?= $diag['success'] ? 'bg-[#0D7A53]' : 'bg-amber-500' ?> text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                <i data-lucide="<?= $diag['success'] ? 'check' : 'alert-triangle' ?>" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm">
                    <?= $diag['success'] ? 'CONEXIÓN ACTIVA A SUPABASE (PostgreSQL DirectQuery)' : 'MODO DEMO / SUPABASE PENDIENTE DE CONFIGURAR' ?>
                </h4>
                <p class="text-xs mt-1">
                    <?= htmlspecialchars($diag['message']) ?>
                </p>
            </div>
        </div>

        <!-- Connection Details Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs font-mono">
            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                <span class="text-slate-400 block text-[10px] font-sans font-semibold uppercase">SUPABASE PROJECT URL:</span>
                <span class="font-bold text-slate-700 truncate block mt-0.5" title="<?= SUPABASE_URL ?>">
                    <?= htmlspecialchars(SUPABASE_URL) ?>
                </span>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                <span class="text-slate-400 block text-[10px] font-sans font-semibold uppercase">API KEY STATUS:</span>
                <span class="font-bold text-slate-700 block mt-0.5">
                    <?= isSupabaseConfigured() ? '••••••••' . substr(SUPABASE_KEY, -6) : 'NO CONFIGURADA' ?>
                </span>
            </div>

            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200">
                <span class="text-slate-400 block text-[10px] font-sans font-semibold uppercase">LATENCIA MEDIDA:</span>
                <span class="font-bold text-emerald-600 block mt-0.5 text-sm">
                    <?= $diag['latency_ms'] ?> ms
                </span>
            </div>
        </div>

        <!-- Setup Instructions Box -->
        <div class="border-t border-slate-200 pt-6 space-y-4 text-xs">
            <h4 class="font-bold text-sm text-[#0C2340] flex items-center gap-2">
                <i data-lucide="book-open" class="w-4 h-4 text-[#0D7A53]"></i>
                Guía Rápida para Conectar tu Proyecto de Supabase
            </h4>

            <ol class="list-decimal list-inside space-y-2 text-slate-600 leading-relaxed">
                <li>
                    Crea un proyecto gratis en <a href="https://supabase.com" target="_blank" class="text-[#0D7A53] underline font-bold">supabase.com</a>.
                </li>
                <li>
                    Abre el <strong>SQL Editor</strong> en tu panel de Supabase y copia/pega el script incluido en:
                    <code class="bg-slate-100 px-2 py-0.5 rounded text-[#0C2340] font-mono font-bold">php/database/supabase_schema.sql</code>. Haz clic en <strong>Run</strong>.
                </li>
                <li>
                    Ve a <strong>Project Settings &gt; API</strong> y copia tu <strong>Project URL</strong> y tu <strong>anon public key</strong>.
                </li>
                <li>
                    Abre el archivo <code class="bg-slate-100 px-2 py-0.5 rounded text-[#0C2340] font-mono font-bold">config.php</code> en tu editor y actualiza las constantes:
                    <pre class="bg-slate-900 text-slate-100 p-3 rounded-lg mt-1 font-mono text-[11px] overflow-x-auto">
define('SUPABASE_URL', 'https://tu-proyecto.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...');
                    </pre>
                </li>
                <li>
                    ¡Listo! Regresa a esta pantalla o al Visor DirectQuery y el sistema guardará y consultará todos los lotes y aprendices directamente en tu base de datos de Supabase.
                </li>
            </ol>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
