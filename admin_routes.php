<!-- filepath: /c:/xampp/htdocs/QOSQO/admin_routes.php -->
<?php
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/database.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$adminController = new AdminController($conn);

switch ($action) {
    case 'login':
        $adminController->login();
        break;
    case 'dashboard':
        $adminController->dashboard();
        break;
    case 'usuarios':
        $adminController->showUsuarios(); // Cambiado para usar el nuevo método
        break;
    case 'editarUsuario':
        $adminController->editarUsuario();
        break;
    case 'eliminarUsuario':
        $adminController->eliminarUsuario();
        break;
    case 'emergencias':
        $adminController->showEmergencias();
        break;
    case 'reportes':
        require __DIR__ . '/administrador/admin_reportes.php';
        break;
    case 'logout':
        $adminController->logout();
        break;
    default:
        $adminController->login();
        break;
}
?>