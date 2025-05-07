<?php
require_once '../database.php';
require_once '../models/Admin.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['cip'])) {
        throw new Exception('No autorizado');
    }

    $admin = new Admin($conn);
    $emergencias = $admin->getEmergencias();
    
    echo json_encode($emergencias);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}