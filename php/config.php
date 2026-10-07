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
 * CONFIGURACIÓN DE CONEXIÓN A SUPABASE
 * ============================================================================
 * Para conectar con tu proyecto en Supabase:
 * 1. Crea un proyecto gratuito en https://supabase.com
 * 2. Ve a Project Settings -> API
 * 3. Copia tu Project URL en SUPABASE_URL
 * 4. Copia tu anon public key (o service_role key) en SUPABASE_KEY
 * 
 * Si aún no tienes un proyecto configurado, el sistema operará automáticamente
 * en MODO DEMO / SIMULACIÓN con los datos institucionales del SENA.
 */
define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'https://YOUR_PROJECT_ID.supabase.co');
define('SUPABASE_KEY', getenv('SUPABASE_KEY') ?: 'YOUR_SUPABASE_ANON_KEY');

// Conexión Directa opcional a PostgreSQL (Pooler o Direct de Supabase)
define('SUPABASE_DB_HOST', getenv('SUPABASE_DB_HOST') ?: 'aws-0-sa-east-1.pooler.supabase.com');
define('SUPABASE_DB_PORT', getenv('SUPABASE_DB_PORT') ?: '5432');
define('SUPABASE_DB_NAME', getenv('SUPABASE_DB_NAME') ?: 'postgres');
define('SUPABASE_DB_USER', getenv('SUPABASE_DB_USER') ?: 'postgres.YOUR_PROJECT_ID');
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
