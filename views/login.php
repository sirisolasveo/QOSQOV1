<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QOSQO SEGURO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="assets/escudo-pnp.png" alt="Escudo PNP">
            <h2>QOSQO SEGURO</h2>
            <p>Ingresa a tu cuenta para reportar emergencias</p>
        </div>

        <?php if(isset($_SESSION['mensaje'])): ?>
            <div class="mensaje-<?php echo isset($_SESSION['mensaje_tipo']) ? $_SESSION['mensaje_tipo'] : 'exito'; ?>">
                <i class="fas <?php echo isset($_SESSION['mensaje_tipo']) && $_SESSION['mensaje_tipo'] == 'error' ? 'fa-exclamation-circle' : 'fa-check-circle'; ?>"></i>
                <?php 
                echo $_SESSION['mensaje'];
                unset($_SESSION['mensaje']);
                unset($_SESSION['mensaje_tipo']);
                ?>
            </div>
        <?php endif; ?>
        
        <form id="loginForm" action="index.php?action=login" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo SimpleCaptcha::generateCSRFToken(); ?>">
            
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
                       title="El DNI debe tener 8 números exactamente"
                       required>
            </div>

            <div class="form-group">
                <label for="clave">
                    <i class="fas fa-lock"></i>Contraseña
                </label>
                <div class="password-container">
                    <input type="password" id="clave" name="clave" 
                           placeholder="Ingresa tu contraseña"
                           required>
                    <button type="button" class="toggle-password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>  
            </div>

            <div class="remember-container">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Mantener sesión iniciada</label>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Ingresar
            </button>

            <div class="register-link">
                ¿No tienes una cuenta? 
                <a href="index.php?action=register">Regístrate aquí</a>
            </div>
            <div class="recover-link">
                ¿Olvidaste tu contraseña? 
                <a href="index.php?action=recuperarPassword">Recuperar contraseña</a>
            </div>
        </form>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>