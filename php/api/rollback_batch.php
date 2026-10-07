<?php
/**
 * Endpoint API para Reversión Atómica de Lote (Rollback)
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
$batchId = $input['batch_id'] ?? null;
$motivo = trim($input['motivo'] ?? '');

if (!$batchId || !$motivo) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Batch ID y motivo son obligatorios']);
    exit;
}

$repo = new SenaRepository();
$result = $repo->rollbackBatch($batchId, $motivo);

$_SESSION['flash_success'] = "✔ Reversión atómica completada para el lote " . substr($batchId, 0, 18) . "...";

echo json_encode($result);
