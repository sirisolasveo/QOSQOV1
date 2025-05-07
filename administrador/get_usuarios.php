<?php
require_once '../database.php';
require_once '../models/Admin.php';

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    if (!isset($_SESSION['cip'])) {
        throw new Exception('No autorizado');
    }

    $admin = new Admin($conn);
    $usuarios = $admin->getUsuarios();
    
    echo json_encode($usuarios);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}