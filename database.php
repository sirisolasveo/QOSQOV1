<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'qosqo';

global $conn;  // Declarar $conn como global

try {
    $conn = new mysqli($host, $user, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    error_log("Error de conexión: " . $e->getMessage());
    die("Error: No se pudo establecer la conexión a la base de datos");
}
?>