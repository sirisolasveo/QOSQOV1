<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['cip'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

require_once '../database.php';
require_once '../models/Admin.php';

try {
    if (!isset($_POST['id']) || !isset($_POST['placa_vehiculo']) || !isset($_POST['medidas_adoptadas'])) {
        throw new Exception('Datos incompletos');
    }

    $admin = new Admin($conn);
    
    // Verificar si el administrador actual es quien creó el descargo
    $descargo_actual = $admin->getDescargo($_POST['id']);
    if (!$descargo_actual || $descargo_actual['cip_administrador'] !== $_SESSION['cip']) {
        throw new Exception('No tiene permiso para editar este descargo');
    }

    $resultado = $admin->actualizarDescargo(
        $_POST['id'],
        $_SESSION['cip'],
        $_POST['placa_vehiculo'],
        $_POST['medidas_adoptadas']
    );
    
    if (!$resultado) {
        throw new Exception('Error al actualizar el descargo');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}