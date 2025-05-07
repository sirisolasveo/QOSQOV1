<?php
if (!isset($usuario)) {
    header("Location: /QOSQO/admin_dashboard.php?action=usuarios");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Editar Usuario</h2>
        <form method="POST" action="/QOSQO/admin_dashboard.php?action=editarUsuario">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario['id']); ?>">
            
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Apellidos</label>
                <input type="text" class="form-control" name="apellidos" value="<?php echo htmlspecialchars($usuario['apellidos']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Edad</label>
                <input type="number" class="form-control" name="edad" value="<?php echo htmlspecialchars($usuario['edad']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>DNI</label>
                <input type="text" class="form-control" name="dni" value="<?php echo htmlspecialchars($usuario['dni']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Celular</label>
                <input type="text" class="form-control" name="celular" value="<?php echo htmlspecialchars($usuario['celular']); ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            <a href="/QOSQO/admin_dashboard.php?action=usuarios" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</body>
</html>