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
        throw new Exception('ID del reporte no proporcionado');
    }

    $admin = new Admin($conn);
    
    // Consulta para obtener el descargo del reporte
    $query = "SELECT dr.* 
              FROM descargo_reportes dr 
              WHERE dr.reportes_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $_GET['id']);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $descargo = $resultado->fetch_assoc();

    if (!$descargo) {
        throw new Exception('Descargo de reporte no encontrado');
    }

    echo json_encode($descargo);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}