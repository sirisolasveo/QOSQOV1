<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cip'])) {
    die('No autorizado');
}

require_once '../lib/tcpdf/tcpdf.php';
require_once '../database.php';
require_once '../models/Admin.php';

class MYPDF extends TCPDF {
    public function Header() {
        $this->SetY(12);
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 15, 'POLICÍA NACIONAL DEL PERÚ (QOSQOPOL)', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(25);
    }
}

try {
    $admin = new Admin($conn);
    $descargo = $admin->getDescargoReporte($_GET['id']);
    $reporte = $admin->getReporteCompleto($_GET['id']);

    if (!$descargo || !$reporte) {
        die('Reporte o descargo no encontrado');
    }

    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('Sistema PNP');
    $pdf->SetAuthor('Policía Nacional del Perú');
    $pdf->SetTitle('Reporte de Incidente');

    $pdf->SetMargins(20, 20, 20);
    $pdf->AddPage();

    // Agregar imagen del escudo
    $pdf->Image('../assets/romario.jpg', ($pdf->GetPageWidth() - 40)/2, $pdf->GetY(), 40, 40, 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    $pdf->Ln(45);

    // Título del documento
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'REPORTE DE INCIDENTE', 0, 1, 'C');
    $pdf->Ln(5);

    // Información del reporte
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'DATOS DEL REPORTE:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);

    $pdf->Cell(40, 7, 'Fecha:', 0);
    $pdf->Cell(0, 7, date('d/m/Y H:i', strtotime($reporte['fecha'])), 0, 1);

    $pdf->Cell(40, 7, 'Estado:', 0);
    $pdf->Cell(0, 7, $reporte['estado'], 0, 1);

    $pdf->Cell(40, 7, 'Ciudadano:', 0);
    $pdf->Cell(0, 7, $reporte['nombre'] . ' ' . $reporte['apellidos'], 0, 1);

    $pdf->Cell(40, 7, 'DNI:', 0);
    $pdf->Cell(0, 7, $reporte['dni'], 0, 1);

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, 'Descripción del incidente:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(0, 7, $reporte['descripcion'], 0, 'L');

    $pdf->Ln(10);

    // Información del descargo
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'DATOS DEL DESCARGO:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);

    $pdf->Cell(40, 7, 'Fecha:', 0);
    $pdf->Cell(0, 7, date('d/m/Y H:i', strtotime($descargo['fecha_registro'])), 0, 1);

    $pdf->Cell(40, 7, 'Vehículo:', 0);
    $pdf->Cell(0, 7, $descargo['placa_vehiculo'], 0, 1);

    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, 'Medidas adoptadas:', 0, 1);
    $pdf->SetFont('helvetica', '', 11);
    $pdf->MultiCell(0, 7, $descargo['medidas_adoptadas'], 0, 'L');

    $pdf->Ln(20);

    // Imagen del reporte si existe
    if (!empty($reporte['imagen'])) {
        $imagePath = '../uploads/' . $reporte['imagen'];
        if (file_exists($imagePath)) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'EVIDENCIA FOTOGRÁFICA:', 0, 1);
            $pdf->Image($imagePath, 30, $pdf->GetY(), 150);
        }
    }

    $pdf->Output('Reporte_' . $_GET['id'] . '.pdf', 'I');

} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}