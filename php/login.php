<?php
/**
 * Pantalla de Inicio de Sesión Institucional SENA
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

$error = null;
$redirect = $_GET['redirect'] ?? (BASE_URL . '/index.php');

// Si ya tiene sesión activa, redirigir
if (isLoggedIn()) {
    header('Location: ' . $redirect);
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = loginUser($email, $password);
    if ($result['success']) {
        $_SESSION['flash_success'] = 'Bienvenido(a), ' . $result['user']['nombre'] . '. Sesión iniciada como ' . $result['user']['rol'] . '.';
        header('Location: ' . $redirect);
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="es" class="h-full bg-[#081628]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Institucional • <?= APP_NAME ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen bg-[#081628] flex flex-col justify-center items-center p-4 sm:p-6 text-slate-100 font-sans relative overflow-x-hidden">

    <!-- Glowing Background Accents -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-[#0D7A53] blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-[#1B365D] blur-3xl"></div>
    </div>

    <div class="max-w-md w-full relative z-10 space-y-6">
        <!-- Brand Header -->
        <div class="text-center space-y-3">
            <div class="inline-flex w-16 h-16 bg-white rounded-xl p-2.5 items-center justify-center shadow-2xl border border-slate-700 mx-auto">
                <svg viewBox="0 0 100 100" class="w-full h-full" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="48" fill="#0C2340" />
                    <path d="M50 18C42 18 36 24 36 32C36 39 42 44 49 45V55H32C26 55 22 59 22 65C22 71 27 75 33 75H49V84H53V75H69C75 75 80 71 80 65C80 59 76 55 70 55H53V45C60 44 66 39 66 32C66 24 60 18 50 18Z" fill="#0D7A53" />
                    <circle cx="50" cy="32" r="7" fill="#FFFFFF" />
                    <circle cx="34" cy="65" r="5" fill="#FFFFFF" />
                    <circle cx="68" cy="65" r="5" fill="#FFFFFF" />
                </svg>
            </div>

            <div>
                <span class="text-[11px] font-bold tracking-widest text-[#0D7A53] uppercase bg-emerald-500/10 border border-[#0D7A53]/30 px-3 py-1 rounded-full">
                    SENA • Sistema Integrado SofiaPlus
                </span>
                <h1 class="text-2xl font-bold font-['Plus_Jakarta_Sans'] text-white mt-2">
                    Plataforma Analítica de Juicios Evaluativos
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    Ingesta estructurada ETL, auditoría RBAC y analítica DirectQuery conectada a Supabase
                </p>
            </div>
        </div>

        <!-- Login Card -->
        <div class="bg-[#0C2340] border border-[#1B365D] rounded-xl p-6 shadow-2xl space-y-5">
            <?php if ($error): ?>
                <div class="p-3 bg-rose-500/15 border border-rose-500/30 rounded-lg text-xs text-rose-300 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-4 text-xs">
                <div>
                    <label class="block text-slate-300 font-semibold mb-1.5">
                        Correo Electrónico Institucional
                    </label>
                    <div class="relative">
                        <i data-lucide="mail" class="w-4 h-4 absolute left-3 top-3 text-slate-400"></i>
                        <input
                            type="email"
                            name="email"
                            id="login-email"
                            required
                            value="gestor@sena.edu.co"
                            placeholder="usuario@sena.edu.co"
                            class="w-full bg-[#081628] border border-slate-700 rounded-lg pl-9 pr-3 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-[#0D7A53] transition"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-slate-300 font-semibold mb-1.5">
                        Contraseña Institucional
                    </label>
                    <div class="relative">
                        <i data-lucide="lock" class="w-4 h-4 absolute left-3 top-3 text-slate-400"></i>
                        <input
                            type="password"
                            name="password"
                            id="login-password"
                            required
                            value="gestor123"
                            placeholder="••••••••"
                            class="w-full bg-[#081628] border border-slate-700 rounded-lg pl-9 pr-3 py-2.5 text-white placeholder-slate-500 focus:outline-none focus:border-[#0D7A53] transition"
                        />
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full bg-[#0D7A53] hover:bg-emerald-600 text-white font-semibold py-2.5 px-4 rounded-lg transition duration-200 flex items-center justify-center space-x-2 shadow-lg shadow-[#0D7A53]/25 cursor-pointer"
                >
                    <span>Ingresar a la Plataforma</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>

            <!-- Quick Profiles for Instant Testing -->
            <div class="pt-4 border-t border-slate-800">
                <p class="text-[11px] font-semibold text-slate-400 mb-2 uppercase tracking-wider text-center">
                    Perfiles de Prueba Rápida (Selecciona uno para ingresar):
                </p>

                    <!-- Subdirector de Centro -->
                    <button
                        type="button"
                        onclick="selectUser('subdirector@sena.edu.co', 'subdirector123')"
                        class="w-full text-left p-2.5 rounded-lg border border-amber-500/50 hover:border-amber-400 bg-amber-950/20 hover:bg-amber-950/40 transition flex items-center justify-between group cursor-pointer"
                    >
                        <div class="flex items-center space-x-2.5">
                            <div class="w-7 h-7 rounded-full bg-amber-500/20 text-amber-300 flex items-center justify-center font-bold text-[10px] border border-amber-500/40">
                                SD
                            </div>
                            <div>
                                <div class="text-white font-medium text-[11px] group-hover:text-amber-300 transition">
                                    Dr. Jorge Eduardo Londoño Ulloa
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    subdirector@sena.edu.co • <span class="text-amber-400 font-semibold">SUBDIRECTOR DE CENTRO</span>
                                </div>
                            </div>
                        </div>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-500 group-hover:text-amber-300 group-hover:translate-x-0.5 transition"></i>
                    </button>

                    <!-- Coordinadora de Software -->
                    <button
                        type="button"
                        onclick="selectUser('coord.software@sena.edu.co', 'coord123')"
                        class="w-full text-left p-2.5 rounded-lg border border-cyan-500/40 hover:border-cyan-400 bg-cyan-950/20 hover:bg-cyan-950/40 transition flex items-center justify-between group cursor-pointer"
                    >
                        <div class="flex items-center space-x-2.5">
                            <div class="w-7 h-7 rounded-full bg-cyan-500/20 text-cyan-300 flex items-center justify-center font-bold text-[10px] border border-cyan-500/40">
                                CS
                            </div>
                            <div>
                                <div class="text-white font-medium text-[11px] group-hover:text-cyan-300 transition">
                                    Ing. Claudia Patricia Duarte Gómez
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    coord.software@sena.edu.co • <span class="text-cyan-400 font-semibold">COORDINADOR SOFTWARE</span>
                                </div>
                            </div>
                        </div>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-500 group-hover:text-cyan-300 group-hover:translate-x-0.5 transition"></i>
                    </button>

                    <!-- Gestor / Lider -->
                    <button
                        type="button"
                        onclick="selectUser('gestor@sena.edu.co', 'gestor123')"
                        class="w-full text-left p-2.5 rounded-lg border border-slate-700/80 hover:border-emerald-400/60 bg-[#081628]/60 hover:bg-[#081628] transition flex items-center justify-between group cursor-pointer"
                    >
                        <div class="flex items-center space-x-2.5">
                            <div class="w-7 h-7 rounded-full bg-emerald-500/20 text-emerald-300 flex items-center justify-center font-bold text-[10px] border border-emerald-500/40">
                                LF
                            </div>
                            <div>
                                <div class="text-white font-medium text-[11px] group-hover:text-emerald-300 transition">
                                    Ing. Carlos Alberto Mendoza
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    gestor@sena.edu.co • <span class="text-emerald-400 font-semibold">LÍDER DE FORMACIÓN</span>
                                </div>
                            </div>
                        </div>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-500 group-hover:text-emerald-300 group-hover:translate-x-0.5 transition"></i>
                    </button>

                    <!-- Admin -->
                    <button
                        type="button"
                        onclick="selectUser('admin@sena.edu.co', 'admin123')"
                        class="w-full text-left p-2.5 rounded-lg border border-slate-700/80 hover:border-indigo-400/60 bg-[#081628]/60 hover:bg-[#081628] transition flex items-center justify-between group cursor-pointer"
                    >
                        <div class="flex items-center space-x-2.5">
                            <div class="w-7 h-7 rounded-full bg-indigo-500/20 text-indigo-300 flex items-center justify-center font-bold text-[10px] border border-indigo-500/40">
                                AD
                            </div>
                            <div>
                                <div class="text-white font-medium text-[11px] group-hover:text-indigo-300 transition">
                                    Dr. Fernando Arango Botero
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    admin@sena.edu.co • <span class="text-indigo-400 font-semibold">ADMIN NACIONAL</span>
                                </div>
                            </div>
                        </div>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-500 group-hover:text-indigo-300 group-hover:translate-x-0.5 transition"></i>
                    </button>

                    <!-- Instructor -->
                    <button
                        type="button"
                        onclick="selectUser('instructor@sena.edu.co', 'instructor123')"
                        class="w-full text-left p-2.5 rounded-lg border border-slate-700/80 hover:border-blue-400/60 bg-[#081628]/60 hover:bg-[#081628] transition flex items-center justify-between group cursor-pointer"
                    >
                        <div class="flex items-center space-x-2.5">
                            <div class="w-7 h-7 rounded-full bg-blue-500/20 text-blue-300 flex items-center justify-center font-bold text-[10px] border border-blue-500/40">
                                IN
                            </div>
                            <div>
                                <div class="text-white font-medium text-[11px] group-hover:text-blue-300 transition">
                                    Lic. Martha Gómez Restrepo
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    instructor@sena.edu.co • <span class="text-blue-400 font-semibold">INSTRUCTOR (Solo Lectura)</span>
                                </div>
                            </div>
                        </div>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-slate-500 group-hover:text-blue-300 group-hover:translate-x-0.5 transition"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="text-center text-[11px] text-slate-500 flex items-center justify-center space-x-1.5">
            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#0D7A53]"></i>
            <span>Sistema Protegido con Autenticación RBAC y Cifrado TLS 1.3</span>
        </div>
    </div>

    <script>
        if (window.lucide) lucide.createIcons();

        function selectUser(email, pass) {
            document.getElementById('login-email').value = email;
            document.getElementById('login-password').value = pass;
            document.querySelector('form').submit();
        }
    </script>
</body>
</html>
