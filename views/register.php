<!-- filepath: /c:/xampp/htdocs/QOSQO/views/register.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - QOSQO SEGURO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
    <div class="form-container">
        <h2>Registro de Usuario</h2>

        <?php
        if (isset($_SESSION['errores']) && !empty($_SESSION['errores'])) {
            echo '<div class="error-message"><i class="fas fa-exclamation-circle"></i> ';
            foreach ($_SESSION['errores'] as $error) {
                echo htmlspecialchars($error) . '<br>';
            }
            echo '</div>';
            unset($_SESSION['errores']);
        }

        if (isset($_SESSION['mensaje'])) {
            $mensaje_tipo = isset($_SESSION['mensaje_tipo']) ? $_SESSION['mensaje_tipo'] : 'info';
            $icon_class = $mensaje_tipo == 'error' ? 'fa-exclamation-circle' : 
                         ($mensaje_tipo == 'exito' ? 'fa-check-circle' : 'fa-info-circle');
            $message_class = $mensaje_tipo == 'error' ? 'error-message' : 
                           ($mensaje_tipo == 'exito' ? 'success-message' : 'info-message');
            
            echo '<div class="' . $message_class . '"><i class="fas ' . $icon_class . '"></i> ' . 
                 htmlspecialchars($_SESSION['mensaje']) . '</div>';
            unset($_SESSION['mensaje']);
            unset($_SESSION['mensaje_tipo']);
        }

        $datos = isset($_SESSION['datos_form']) ? $_SESSION['datos_form'] : [];
        unset($_SESSION['datos_form']);
        ?>

        <form action="index.php?action=register" method="post" id="registroForm">
            <div class="form-group">
                <label for="nombre"><i class="fas fa-user"></i> Nombre:</label>
                <input type="text" id="nombre" name="nombre" 
                    value="<?php echo isset($datos['nombre']) ? htmlspecialchars($datos['nombre']) : ''; ?>"
                    pattern="[A-Za-zÑñÁáÉéÍíÓóÚú\s]{2,50}" 
                    placeholder="Ingrese su nombre"
                    title="Solo letras, mínimo 2 caracteres, máximo 50" required>
            </div>
            
            <div class="form-group">
                <label for="apellidos"><i class="fas fa-user"></i> Apellidos:</label>
                <input type="text" id="apellidos" name="apellidos" 
                    value="<?php echo isset($datos['apellidos']) ? htmlspecialchars($datos['apellidos']) : ''; ?>"
                    pattern="[A-Za-zÑñÁáÉéÍíÓóÚú\s]{2,50}" 
                    placeholder="Ingrese sus apellidos"
                    title="Solo letras, mínimo 2 caracteres, máximo 50" required>
            </div>
            
            <div class="form-group">
                <label for="fecha_nacimiento"><i class="fas fa-calendar"></i> Fecha de Nacimiento:</label>
                <div class="date-selects">
                    <select id="dia" name="dia" required>
                        <option value="">Día</option>
                        <?php for($i=1; $i<=31; $i++): ?>
                            <option value="<?php echo sprintf('%02d', $i); ?>" 
                                <?php echo (isset($datos['dia']) && $datos['dia'] == sprintf('%02d', $i)) ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select id="mes" name="mes" required>
                        <option value="">Mes</option>
                        <?php 
                        $meses = array(
                            "01"=>"Enero", "02"=>"Febrero", "03"=>"Marzo", 
                            "04"=>"Abril", "05"=>"Mayo", "06"=>"Junio",
                            "07"=>"Julio", "08"=>"Agosto", "09"=>"Septiembre", 
                            "10"=>"Octubre", "11"=>"Noviembre", "12"=>"Diciembre"
                        );
                        foreach($meses as $num => $nombre): ?>
                            <option value="<?php echo $num; ?>" 
                                <?php echo (isset($datos['mes']) && $datos['mes'] == $num) ? 'selected' : ''; ?>>
                                <?php echo $nombre; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="anio" name="anio" required>
                        <option value="">Año</option>
                        <?php for($i=date('Y'); $i>=1900; $i--): ?>
                            <option value="<?php echo $i; ?>"
                                <?php echo (isset($datos['anio']) && $datos['anio'] == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <small class="input-help">Debes ser mayor de 14 años</small>
                <div id="edad-calculada" class="mt-2"></div>
            </div>
            
            <div class="form-group">
                <label for="dni"><i class="fas fa-id-card"></i> DNI:</label>
                <div class="input-container">
                    <input type="text" id="dni" name="dni" 
                        value="<?php echo isset($datos['dni']) ? htmlspecialchars($datos['dni']) : ''; ?>"
                        pattern="[0-9]{8}" 
                        placeholder="Ingrese su DNI"
                        maxlength="8"
                        title="Debe contener exactamente 8 números" 
                        required>
                    <small class="input-help">Debe contener exactamente 8 números</small>
                    <div class="validation-message"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="celular"><i class="fas fa-mobile-alt"></i> Celular:</label>
                <div class="input-container">
                    <input type="text" id="celular" name="celular" 
                        value="<?php echo isset($datos['celular']) ? htmlspecialchars($datos['celular']) : ''; ?>"
                        pattern="9[0-9]{8}" 
                        placeholder="Ingrese su número de celular"
                        maxlength="9"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                        required>
                    <small class="input-help">Debe empezar con 9 y contener exactamente 9 números</small>
                    <div class="validation-message"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="clave"><i class="fas fa-lock"></i> Clave:</label>
                <div class="password-container">
                    <input type="password" id="clave" name="clave" 
                        pattern=".{8,20}" 
                        maxlength="20"
                        placeholder="Ingrese su clave"
                        title="La clave debe tener entre 8 y 20 caracteres" required>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('clave')">
                        <i class="fa fa-eye"></i>
                    </button>
                    <div class="password-strength">
                        <div id="seguridadClave" class="strength-text">Seguridad: débil</div>
                        <div class="requirements">
                            <ul>
                                <li class="requirement" id="req-length">8-20 caracteres</li>
                                <li class="requirement" id="req-uppercase">Una mayúscula</li>
                                <li class="requirement" id="req-number">Un número</li>
                                <li class="requirement" id="req-special">Un carácter especial</li>
                            </ul>
                        </div>
                    </div>
                    <small class="input-help">La clave debe cumplir todos los requisitos</small>
                </div>
            </div>

            <input type="hidden" name="fecha_nacimiento" id="fecha_nacimiento">
            
            <div class="form-group">
                <label for="confirmar_clave"><i class="fas fa-lock"></i> Confirmar Clave:</label>
                <div class="password-container">
                    <input type="password" id="confirmar_clave" name="confirmar_clave" 
                        pattern=".{8,20}" 
                        maxlength="20"
                        placeholder="Confirme su clave"
                        title="La clave debe tener entre 8 y 20 caracteres" required>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirmar_clave')">
                        <i class="fa fa-eye"></i>
                    </button>
                    <small class="input-help">Repita su clave</small>
                </div>
            </div>

            <div class="security-questions-section">
                <h3><i class="fas fa-shield-alt"></i> Pregunta de Seguridad</h3>
                <p class="help-text">Esta pregunta te ayudará a recuperar tu cuenta si olvidas tu contraseña.</p>
                
                <div class="form-group">
                    <label for="pregunta1">Pregunta:</label>
                    <select id="pregunta1" name="preguntas[0][pregunta]" required>
                        <option value="">Seleccione una pregunta</option>
                        <option value="¿Cuál es el nombre de tu primera mascota?">¿Cuál es el nombre de tu primera mascota?</option>
                        <option value="¿En qué ciudad naciste?">¿En qué ciudad naciste?</option>
                        <option value="¿Cuál es el nombre de tu mejor amigo de la infancia?">¿Cuál es el nombre de tu mejor amigo de la infancia?</option>
                        <option value="¿Cuál es el apellido de soltera de tu madre?">¿Cuál es el apellido de soltera de tu madre?</option>
                    </select>
                    <input type="text" name="preguntas[0][respuesta]" placeholder="Tu respuesta" required>
                </div>
            </div>
            
            <input type="submit" value="Registrarse">
        </form>
        <a href="index.php?action=login" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al Login
        </a>
    </div>

    <div id="successModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-check-circle"></i> ¡Registro Exitoso!</h3>
            <p><?php echo isset($_SESSION['mensaje']) ? $_SESSION['mensaje'] : ''; ?></p>
            <a href="index.php?action=login" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Ir al Login
            </a>
        </div>
    </div>

    <?php if (isset($datos['fecha_nacimiento'])): ?>
    <script>
        var fechaGuardada = '<?php echo $datos['fecha_nacimiento']; ?>';
    </script>
    <?php endif; ?>

    <?php if (isset($_SESSION['registro_exitoso']) && $_SESSION['registro_exitoso']): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById('successModal');
            modal.style.display = "block";
        });
    </script>
    <?php 
        unset($_SESSION['registro_exitoso']);
    endif; ?>
    
    <script>
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
    </script>
    <script src="assets/js/register.js"></script>
</body>
</html>