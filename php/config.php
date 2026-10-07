<?php
/**
 * Configuración del Sistema SENA Analytics Institutional - ControlRaps
 * Compatible con XAMPP (Apache + PHP 8.x) y Supabase (PostgreSQL / REST PostgREST)
 */

// Zona horaria institucional SENA (Colombia)
date_default_timezone_set('America/Bogota');

// Inicializar sesión segura si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Constantes de la aplicación
define('APP_NAME', 'SENA Analytics Institutional');
define('APP_SUBTITLE', 'Control de RAPs y Juicios Evaluativos');
define('APP_VERSION', '2.5.0-XAMPP');
define('BASE_URL', '/controlraps'); // Ruta base en Apache htdocs

/**
 * ============================================================================
 * CARGA DE CREDENCIALES SUPABASE (config.local.php o Variables de Entorno)
 * ============================================================================
 */
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

if (!defined('SUPABASE_URL')) {
    define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'https://eoitdgvdpkbxpulkcnsq.supabase.co');
}

if (!defined('SUPABASE_KEY')) {
    define('SUPABASE_KEY', getenv('SUPABASE_KEY') ?: (getenv('SUPABASE_SECRET_KEY') ?: 'YOUR_SUPABASE_KEY'));
}

if (!defined('SUPABASE_PUBLISHABLE_KEY')) {
    define('SUPABASE_PUBLISHABLE_KEY', getenv('SUPABASE_PUBLISHABLE_KEY') ?: 'YOUR_PUBLISHABLE_KEY');
}

if (!defined('SUPABASE_SECRET_KEY')) {
    define('SUPABASE_SECRET_KEY', getenv('SUPABASE_SECRET_KEY') ?: 'YOUR_SECRET_KEY');
}

if (!defined('SUPABASE_JWKS_URL')) {
    define('SUPABASE_JWKS_URL', getenv('SUPABASE_JWKS_URL') ?: 'https://eoitdgvdpkbxpulkcnsq.supabase.co/auth/v1/.well-known/jwks.json');
}

// Conexión Directa opcional a PostgreSQL (Pooler o Direct de Supabase)
define('SUPABASE_DB_HOST', getenv('SUPABASE_DB_HOST') ?: 'aws-0-sa-east-1.pooler.supabase.com');
define('SUPABASE_DB_PORT', getenv('SUPABASE_DB_PORT') ?: '5432');
define('SUPABASE_DB_NAME', getenv('SUPABASE_DB_NAME') ?: 'postgres');
define('SUPABASE_DB_USER', getenv('SUPABASE_DB_USER') ?: 'postgres.eoitdgvdpkbxpulkcnsq');
define('SUPABASE_DB_PASS', getenv('SUPABASE_DB_PASS') ?: '');

/**
 * Verifica si las credenciales de Supabase están configuradas con valores reales
 */
function isSupabaseConfigured(): bool {
    return defined('SUPABASE_URL') 
        && defined('SUPABASE_KEY') 
        && !empty(SUPABASE_URL) 
        && !empty(SUPABASE_KEY) 
        && strpos(SUPABASE_URL, 'YOUR_PROJECT_ID') === false
        && strpos(SUPABASE_KEY, 'YOUR_SUPABASE') === false;
}
