<?php
/**
 * Cierre de Sesión Seguro
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

logoutUser();
session_start();
$_SESSION['flash_success'] = 'Sesión institucional cerrada de forma segura.';
header('Location: ' . BASE_URL . '/login.php');
exit;
