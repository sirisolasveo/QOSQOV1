<?php
ob_start(); // Inicia el buffer de salida
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cip'])) {
    die('No autorizado');
}

// Limpia cualquier salida previa
ob_clean();

require_once '../lib/tcpdf/tcpdf.php';
require_once '../database.php';
require_once '../models/Admin.php';

class MYPDF extends TCPDF {
    public function Header() {
        // Aumentamos el espacio desde la parte superior
        $this->SetY(12); // Cambiamos de posición Y predeterminada
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 15, 'POLICÍA NACIONAL DEL PERÚ (QOSQOPOL)', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(25); // Aumentamos el espacio después del título
    }
}

try {
    $admin = new Admin($conn);
    $descargo = $admin->getDescargo($_GET['id']);
    $emergencia = $admin->getEmergencia($_GET['id']);

    if (!$descargo || !$emergencia) {
        die('Descargo no encontrado');
    }

    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('Sistema PNP');
    $pdf->SetAuthor('Policía Nacional del Perú');
    $pdf->SetTitle('Descargo de Emergencia');

    $pdf->SetMargins(20, 20, 20);
    $pdf->AddPage();

    // Agregar imagen del escudo
    $pdf->Image('../assets/romario.jpg', ($pdf->GetPageWidth() - 40)/2, $pdf->GetY(), 40, 40, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->Ln(45); // Espacio después de la imagen


    // Título - Ajustamos el espacio y el tamaño
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Ln(5); // Agregamos espacio adicional desde el header
    $pdf->Cell(0, 10, 'DESCARGO DE ATENCIÓN', 0, 1, 'C');
    $pdf->Cell(0, 10, 'DE EMERGENCIA', 0, 1, 'C');
    $pdf->Ln(5);


    // Información de la emergencia
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'DATOS DE LA EMERGENCIA:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    
    $pdf->Cell(40, 7, 'Tipo:', 0);
    $pdf->Cell(0, 7, $emergencia['tipo_emergencia'], 0, 1);
    
    $pdf->Cell(40, 7, 'Fecha:', 0);
    $pdf->Cell(0, 7, date('d/m/Y H:i', strtotime($emergencia['fecha'])), 0, 1);
    
    $pdf->Cell(40, 7, 'Ciudadano:', 0);
    $pdf->Cell(0, 7, $emergencia['nombre'], 0, 1);
    
    $pdf->Cell(40, 7, 'DNI:', 0);
    $pdf->Cell(0, 7, $emergencia['dni'], 0, 1);

    $pdf->Ln(10);

    // Información del descargo
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'DATOS DEL DESCARGO:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    
    $pdf->Cell(40, 7, 'Vehículo:', 0);
    $pdf->Cell(0, 7, $descargo['placa_vehiculo'], 0, 1);
    
    $pdf->Cell(40, 7, 'Fecha Registro:', 0);
    $pdf->Cell(0, 7, date('d/m/Y H:i', strtotime($descargo['fecha_registro'])), 0, 1);

    // Agregar fecha de modificación si existe
    if (!empty($descargo['fecha_modificacion'])) {
        $pdf->Cell(40, 7, 'Modificado:', 0);
        $pdf->Cell(0, 7, date('d/m/Y H:i', strtotime($descargo['fecha_modificacion'])), 0, 1);
    }

    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, 'Medidas Adoptadas:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(0, 7, $descargo['medidas_adoptadas'], 0, 'L');

    $pdf->Ln(20);

    // Firma y datos del oficial
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, 'Oficial a Cargo:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 7, 'CIP: ' . $descargo['cip_administrador'], 0, 1);

    // Ubicación
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, 'Ubicación de la Emergencia:', 0, 1);
    $pdf->SetTextColor(0, 0, 255);
    $pdf->SetFont('helvetica', 'U', 11);
    $maps_url = "https://www.google.com/maps?q={$emergencia['latitud']},{$emergencia['longitud']}";
    $pdf->Cell(0, 7, $maps_url, 0, 1, 'L', false, $maps_url);

    ob_end_clean(); // Limpia el buffer final
    $pdf->Output('Descargo_Emergencia_' . $_GET['id'] . '.pdf', 'I');

} catch (Exception $e) {
    ob_end_clean(); // Limpia el buffer en caso de error
    die('Error: ' . $e->getMessage());
}