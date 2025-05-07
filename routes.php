<?php
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/database.php';

$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$authController = new AuthController($conn);

switch ($action) {
    case 'login':
        $authController->login();
        break;
    case 'register':
        $authController->register();
        break;
    case 'verificarDNI':
        $authController->verificarDNI();
        break;
    case 'dashboard':
        $authController->dashboard();
        break;
    case 'perfil':
        $authController->perfil();
        break;
    case 'emergencias':
        $authController->emergencias();
        break;
    case 'reportes':
        $authController->reportes();
        break;
    case 'logout':
        $authController->logout();
        break;
    case 'updatePerfil':
        $authController->updatePerfil();
        break;
    case 'recuperarPassword':
        $authController->recuperarPassword();
        break;
    case 'verificarDNIRecuperacion':
        $authController->verificarDNIRecuperacion();
        break;
    case 'verificarRespuestas':
        $authController->verificarRespuestas();
        break;
    case 'actualizarPassword':
        $authController->actualizarPassword();
        break;
    default:
        $authController->login();
        break;
}
?>