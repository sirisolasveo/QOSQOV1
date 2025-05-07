<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['cip'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

require_once '../database.php';
require_once '../models/Admin.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID de emergencia no proporcionado');
    }

    $admin = new Admin($conn);
    $descargo = $admin->getDescargo($_GET['id']);
    
    if (!$descargo) {
        throw new Exception('Descargo no encontrado');
    }

    echo json_encode($descargo);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}