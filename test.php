<?php
session_start();
$_SESSION['prueba'] = "sesion_activa";
echo "Sesión iniciada: " . session_id();
?>