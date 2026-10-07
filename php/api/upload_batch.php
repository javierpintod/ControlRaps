<?php
/**
 * Endpoint API para Ingestar Lote de Juicios Evaluativos
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/data_helper.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if (!hasRole(['ADMIN', 'LIDER_FORMACION'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Permisos insuficientes']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['filas'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cuerpo de solicitud inválido o sin filas']);
    exit;
}

$currentUser = getCurrentUser();
$repo = new SenaRepository();

$cargaData = [
    'nombre_archivo' => $input['nombre_archivo'] ?? 'CARGA_MANUAL.xlsx',
    'usuario_id' => $currentUser['id'],
    'usuario_nombre' => $currentUser['nombre'],
    'usuario_rol' => $currentUser['rol'],
    'fecha_corte' => $input['fecha_corte'] ?? date('Y-m-d'),
    'total_registros' => (int) ($input['total_registros'] ?? count($input['filas'])),
    'registros_exitosos' => (int) ($input['registros_exitosos'] ?? count($input['filas'])),
    'inconsistencias' => (int) ($input['inconsistencias'] ?? 0),
];

$result = $repo->commitBatch($cargaData, $input['filas']);

$_SESSION['flash_success'] = "✔ Ingesta finalizada: Se incorporaron exitosamente {$result['registros']} registros al corte {$cargaData['fecha_corte']}.";

echo json_encode($result);
