</main>

<!-- Institutional Footer -->
<footer class="bg-white border-t border-[#E2E8F0] mt-auto py-4 text-xs text-slate-500 no-print">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-3">
        <div class="flex items-center space-x-2">
            <span class="font-bold text-[#0C2340]">SENA - SERVICIO NACIONAL DE APRENDIZAJE</span>
            <span>•</span>
            <span>Dirección de Formación Profesional • SofiaPlus DirectQuery</span>
        </div>
        <div class="flex items-center space-x-4 text-[11px]">
            <span>Base de Datos: <strong class="font-mono text-[#0D7A53]"><?= isSupabaseConfigured() ? 'Supabase Live' : 'XAMPP Demo Cache' ?></strong></span>
            <span>•</span>
            <span>Rol Activo: <strong class="text-slate-800"><?= htmlspecialchars(getCurrentUser()['rol'] ?? 'Invitado') ?></strong></span>
            <span>•</span>
            <span>Versión: <span class="font-mono"><?= APP_VERSION ?></span></span>
        </div>
    </div>
</footer>

<script>
    // Inicializar iconos de Lucide
    if (window.lucide) {
        lucide.createIcons();
    }

    // Auto-ocultar toasts
    setTimeout(function() {
        const s = document.getElementById('toast-success');
        if (s) s.style.display = 'none';
        const e = document.getElementById('toast-error');
        if (e) e.style.display = 'none';
    }, 5000);
</script>
</body>
</html>
