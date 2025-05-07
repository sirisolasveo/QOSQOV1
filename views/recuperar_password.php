<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - QOSQO SEGURO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="assets/escudo-pnp.png" alt="Escudo PNP">
            <h2>Recuperar Contraseña</h2>
        </div>

        <?php if(isset($_SESSION['mensaje'])): ?>
            <div class="mensaje-<?php echo isset($_SESSION['mensaje_tipo']) ? $_SESSION['mensaje_tipo'] : 'info'; ?>">
                <i class="fas <?php echo isset($_SESSION['mensaje_tipo']) && $_SESSION['mensaje_tipo'] == 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'; ?>"></i>
                <?php 
                echo $_SESSION['mensaje'];
                unset($_SESSION['mensaje']);
                unset($_SESSION['mensaje_tipo']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['paso_recuperacion']) || $_SESSION['paso_recuperacion'] == 1): ?>
        <!-- Paso 1: Ingresar DNI -->
        <form id="formDNI" action="index.php?action=verificarDNIRecuperacion" method="post">
            <div class="form-group">
                <label for="dni">
                    <i class="fas fa-id-card"></i>DNI
                </label>
                <input type="text" id="dni" name="dni" 
                       pattern="\d{8}" 
                       inputmode="numeric"
                       minlength="8"
                       maxlength="8" 
                       placeholder="Ingresa tu DNI" 
                       required>
            </div>
            <button type="submit" class="btn-login">
                <i class="fas fa-search"></i> Verificar DNI
            </button>
        </form>

        <?php elseif ($_SESSION['paso_recuperacion'] == 2): ?>
        <!-- Paso 2: Responder pregunta de seguridad -->
        <form id="formPreguntas" action="index.php?action=verificarRespuestas" method="post">
            <?php 
            $preguntas = isset($_SESSION['preguntas_seguridad']) ? $_SESSION['preguntas_seguridad'] : [];
            if (!empty($preguntas)): 
            ?>
            <div class="form-group">
                <label for="respuesta0">
                    <i class="fas fa-question-circle"></i><?php echo htmlspecialchars($preguntas[0]); ?>
                </label>
                <input type="text" 
                       id="respuesta0" 
                       name="respuestas[]" 
                       required 
                       placeholder="Tu respuesta">
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-check"></i> Verificar Respuesta
            </button>
        </form>

        <?php elseif ($_SESSION['paso_recuperacion'] == 3): ?>
        <!-- Paso 3: Nueva contraseña -->
        <form id="formNuevaClave" action="index.php?action=actualizarPassword" method="post">
            <div class="form-group">
                <label for="nueva_clave">
                    <i class="fas fa-lock"></i>Nueva Contraseña
                </label>
                <div class="password-container">
                    <input type="password" 
                           id="nueva_clave" 
                           name="nueva_clave" 
                           pattern=".{8,20}"
                           required
                           placeholder="Ingresa tu nueva contraseña">
                    <button type="button" class="toggle-password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirmar_clave">
                    <i class="fas fa-lock"></i>Confirmar Contraseña
                </label>
                <div class="password-container">
                    <input type="password" 
                           id="confirmar_clave" 
                           name="confirmar_clave" 
                           pattern=".{8,20}"
                           required
                           placeholder="Confirma tu nueva contraseña">
                    <button type="button" class="toggle-password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-save"></i> Guardar Nueva Contraseña
            </button>
        </form>
        <?php endif; ?>

        <div class="back-links">
            <a href="index.php?action=login" class="back-link">
                <i class="fas fa-arrow-left"></i> Volver al Login
            </a>
        </div>
    </div>

    <script src="assets/js/recuperar_password.js"></script>
</body>
</html>