<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('ROOT_PATH', dirname(__DIR__));

global $conn;  // Declarar $conn como global antes de incluir database.php
require_once ROOT_PATH . '/database.php';

// Debug - verificar que $conn existe después de incluir database.php
error_log("Verificando conexión en init.php");

if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Error: La conexión no es válida");
}

require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/middleware/AuthMiddleware.php';

$auth = new AuthMiddleware($conn);