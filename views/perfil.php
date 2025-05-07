<?php
require 'header.php';  // Como está en el mismo directorio
?>

<!-- filepath: /c:/xampp/htdocs/QOSQO/views/perfil.php -->
<?php

// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }
// if (!isset($_SESSION['dni'])) {
//     header("Location: index.php?action=login");
//     exit();
// }

// Obtener la información del usuario desde la base de datos
require_once __DIR__ . '/../database.php';
global $conn; // Asegurarse de que la conexión a la base de datos esté disponible
$dni = $_SESSION['dni'];
$sql = "SELECT * FROM usuarios WHERE dni = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $dni);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil</title>
    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("clave");
            var confirmPasswordField = document.getElementById("confirmar_clave");
            if (passwordField.type === "password") {
                passwordField.type = "text";
                confirmPasswordField.type = "text";
            } else {
                passwordField.type = "password";
                confirmPasswordField.type = "password";
            }
        }
    </script>
</head>
<body>
    <h2>Perfil</h2>
    <form action="index.php?action=updatePerfil" method="post">
        <p>Nombre: <?php echo htmlspecialchars($user['nombre']); ?></p>
        <p>Apellidos: <?php echo htmlspecialchars($user['apellidos']); ?></p>
        <p>Edad: <?php echo htmlspecialchars($user['edad']); ?></p>
        <p>DNI: <?php echo htmlspecialchars($user['dni']); ?></p>
        <p>Celular: <input type="text" name="celular" value="<?php echo htmlspecialchars($user['celular']); ?>" required></p>
        <p>Clave: <input type="password" id="clave" name="clave" value="<?php echo htmlspecialchars($user['clave']); ?>" required></p>
        <p>Confirmar Clave: <input type="password" id="confirmar_clave" name="confirmar_clave" value="<?php echo htmlspecialchars($user['clave']); ?>" required></p>
        <input type="checkbox" onclick="togglePasswordVisibility()"> Mostrar Clave
        <br><br>
        <input type="submit" value="Actualizar">
    </form>
</body>
</html>