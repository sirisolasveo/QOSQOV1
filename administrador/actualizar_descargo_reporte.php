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
    $descargo_actual = $admin->getDescargoReporte($_POST['id']);
    if (!$descargo_actual || $descargo_actual['cip_administrador'] !== $_SESSION['cip']) {
        throw new Exception('No tiene permiso para editar este descargo');
    }

    // Actualizar el descargo
    $query = "UPDATE descargo_reportes 
              SET placa_vehiculo = ?, 
                  medidas_adoptadas = ? 
              WHERE reportes_id = ? AND cip_administrador = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssis', 
        $_POST['placa_vehiculo'],
        $_POST['medidas_adoptadas'],
        $_POST['id'],
        $_SESSION['cip']
    );

    if (!$stmt->execute()) {
        throw new Exception('Error al actualizar el descargo');
    }

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}