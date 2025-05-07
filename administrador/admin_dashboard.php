<!-- filepath: /c:/xampp/htdocs/QOSQO/administrador/admin_dashboard.php -->
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cip'])) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
</head>
<body>
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
    <p>Has iniciado sesión como administrador.</p>
    <ul>
        <li><a href="admin_routes.php?action=usuarios">Administrar Usuarios</a></li>
        <li><a href="admin_routes.php?action=emergencias">Administrar Emergencias</a></li>
        <li><a href="admin_routes.php?action=reportes">Administrar Reportes</a></li>
    </ul>
    <a href="admin_routes.php?action=logout">Cerrar sesión</a>
</body>
</html>