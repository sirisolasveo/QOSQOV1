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
    if (!isset($_POST['id']) || !isset($_POST['estado']) || 
        !isset($_POST['placa_vehiculo']) || !isset($_POST['medidas_adoptadas'])) {
        throw new Exception('Datos incompletos');
    }

    $admin = new Admin($conn);
    
    // Iniciamos transacción
    $conn->begin_transaction();

    try {
        // 1. Actualizamos el estado del reporte
        $stmt = $conn->prepare("UPDATE reportes SET estado = ? WHERE id = ?");
        $stmt->bind_param('si', $_POST['estado'], $_POST['id']);
        if (!$stmt->execute()) {
            throw new Exception('Error al actualizar el estado del reporte');
        }

        // 2. Obtenemos el DNI del usuario a través del JOIN con usuarios
        $stmt = $conn->prepare("SELECT u.dni 
                              FROM reportes r 
                              INNER JOIN usuarios u ON r.usuario_id = u.id 
                              WHERE r.id = ?");
        $stmt->bind_param('i', $_POST['id']);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $reporte = $resultado->fetch_assoc();

        if (!$reporte) {
            throw new Exception('Reporte no encontrado');
        }

        // 3. Insertamos el descargo con el DNI obtenido
        $stmt = $conn->prepare("INSERT INTO descargo_reportes 
            (reportes_id, dni_usuario, cip_administrador, placa_vehiculo, medidas_adoptadas) 
            VALUES (?, ?, ?, ?, ?)");
        
        $stmt->bind_param('issss', 
            $_POST['id'],
            $reporte['dni'],
            $_SESSION['cip'],
            $_POST['placa_vehiculo'],
            $_POST['medidas_adoptadas']
        );

        if (!$stmt->execute()) {
            throw new Exception('Error al registrar el descargo');
        }

        // Si todo sale bien, confirmamos la transacción
        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // Si algo sale mal, revertimos los cambios
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}