<?php
/**
 * Exportador de Reportes de Coordinación Académica en Formato CSV / Excel
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/data_helper.php';

$currentUser = requireAuth();

$centroId = $_GET['centro'] ?? getActiveCentroId();
$coordId = $_GET['coordinacion'] ?? getActiveCoordinacionId();

$repo = new SenaRepository();
$centro = $repo->getCentroById($centroId);
$coordinacion = $coordId !== 'TODAS' ? $repo->getCoordinacionById($coordId) : null;

$aprendices = $repo->getAprendices($centroId, $coordId);

$coordSlug = $coordinacion ? preg_replace('/[^A-Za-z0-9_-]/', '_', $coordinacion['nombre_coordinacion']) : 'CONSOLIDADO_CENTRO';
$filename = "REPORTE_SENA_{$centroId}_{$coordSlug}_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');
// UTF-8 BOM para Excel
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

// Metadatos institucionales
fputcsv($out, ['SERVICIO NACIONAL DE APRENDIZAJE - SENA']);
fputcsv($out, ['CENTRO DE FORMACION:', $centro['nombre_centro'] ?? $centroId]);
fputcsv($out, ['REGIONAL / CIUDAD:', ($centro['regional'] ?? '') . ' - ' . ($centro['ciudad'] ?? '')]);
fputcsv($out, ['COORDINACION ACADEMICA:', $coordinacion['nombre_coordinacion'] ?? 'Todas las Coordinaciones']);
fputcsv($out, ['COORDINADOR RESPONSABLE:', $coordinacion['coordinador_nombre'] ?? 'N/A']);
fputcsv($out, ['FECHA DE EMISION:', date('Y-m-d H:i:s')]);
fputcsv($out, ['GENERADO POR:', $currentUser['nombre'] . ' (' . $currentUser['rol'] . ')']);
fputcsv($out, []); // Línea vacía

// Encabezados de datos
fputcsv($out, [
    'Tipo Doc',
    'Numero Documento',
    'Nombre Completo',
    'Email Institucional',
    'Codigo Programa',
    'Nombre Programa',
    'Total RAPs',
    'RAPs Aprobados',
    'RAPs Pendientes',
    'RAPs No Aprobados',
    '% Avance',
    'Estado Academico'
]);

// Filas
foreach ($aprendices as $ap) {
    fputcsv($out, [
        $ap['tipo_identificacion'] ?? 'CC',
        $ap['numero_identificacion'] ?? '',
        $ap['nombre_completo'] ?? '',
        $ap['email'] ?? '',
        $ap['codigo_programa'] ?? '',
        $ap['nombre_programa'] ?? '',
        $ap['total_raps'] ?? 0,
        $ap['raps_aprobados'] ?? 0,
        $ap['raps_pendientes'] ?? 0,
        $ap['raps_no_aprobados'] ?? 0,
        ($ap['porcentaje_avance'] ?? 0) . '%',
        $ap['estado_academico'] ?? 'Al Día'
    ]);
}

fclose($out);
exit;
