<?php
require_once '../../database.php';
require_once '../../models/Admin.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['cip'])) {
        throw new Exception('No autorizado');
    }

    if (!isset($_POST['id']) || !isset($_POST['estado'])) {
        throw new Exception('Datos incompletos');
    }

    $admin = new Admin($conn);
    $resultado = $admin->actualizarEstado($_POST['id'], $_POST['estado']);
    
    if ($resultado) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Error al actualizar');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}