<?php
/**
 * API para Autocompletado Predictivo y Búsqueda de Centros del SENA
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/data_helper.php';

$query = $_GET['q'] ?? '';
$repo = new SenaRepository();
$centros = $repo->getCentros($query);

echo json_encode([
    'success' => true,
    'total' => count($centros),
    'centros' => $centros
], JSON_UNESCAPED_UNICODE);
