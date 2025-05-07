<!-- filepath: /c:/xampp/htdocs/QOSQO/administrador/admin_login.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Administrador</title>
</head>
<body>
    <h2>Login Administrador</h2>
    <form action="admin_login.php?action=login" method="post">
        <p>CIP: <input type="text" name="cip" required></p>
        <p>Clave: <input type="password" name="clave" required></p>
        <input type="submit" value="Login">
    </form>
</body>
</html>