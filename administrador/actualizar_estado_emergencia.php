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
    if (!isset($_POST['id']) || !isset($_POST['estado'])) {
        throw new Exception('Datos incompletos');
    }

    $admin = new Admin($conn);
    $id = $_POST['id'];
    $estado = $_POST['estado'];
    
    // Actualizar estado
    $resultado = $admin->actualizarEstadoEmergencia($id, $estado);

    // Si es ATENDIDO, registrar el descargo
    if ($estado === 'ATENDIDO' && isset($_POST['placa_vehiculo']) && isset($_POST['medidas_adoptadas'])) {
        $emergencia = $admin->getEmergencia($id);
        if (!$emergencia) {
            throw new Exception('Emergencia no encontrada');
        }

        $descargo = $admin->registrarDescargo(
            $id,
            $emergencia['dni'],
            $_SESSION['cip'],
            $_POST['placa_vehiculo'],
            $_POST['medidas_adoptadas']
        );

        if (!$descargo) {
            throw new Exception('Error al registrar el descargo');
        }
    }
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}