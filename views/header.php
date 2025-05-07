
<?php

 require_once __DIR__ . '/../config/init.php';

 $auth->requireAuth();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>QOSQOPOL</title>
    <style>
        .header {
            background-color: #f1f1f1;
            padding: 20px;
            text-align: center;
        }
        .header a {
            margin: 0 15px;
            text-decoration: none;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>QOSQOPOL</h1>
        <nav>
            <a href="index.php?action=dashboard">Inicio</a>
            <a href="index.php?action=perfil">Perfil</a>
            <a href="index.php?action=emergencias">Emergencias</a>
            <a href="index.php?action=reportes">Reportes</a>
            <a href="index.php?action=logout">Cerrar sesión</a>
        </nav>
    </div>
</body>
</html>