<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    private $db;
    private $conn;

    public function __construct($db) {
        $this->userModel = new User($db);
        $this->db = $db;
        $this->conn = $db;  // Inicializar la propiedad conn
        require_once __DIR__ . '/../config/SimpleCaptcha.php';
    }

    public function login() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Si hay un mensaje antiguo, asegurarnos de que se muestre
        if (isset($_SESSION['mensaje_temp'])) {
            $_SESSION['mensaje'] = $_SESSION['mensaje_temp'];
            $_SESSION['mensaje_tipo'] = $_SESSION['mensaje_tipo_temp'];
            unset($_SESSION['mensaje_temp']);
            unset($_SESSION['mensaje_tipo_temp']);
        }

        if (isset($_SESSION['dni'])) {
            header('Location: index.php?action=dashboard');
            exit;
        }

        // Verificar remember token
        if (!isset($_SESSION['dni']) && isset($_COOKIE['remember_token'])) {
            $datos = $this->userModel->verificarRememberToken($_COOKIE['remember_token']);
            if ($datos) {
                $_SESSION['dni'] = $datos['dni'];
                $_SESSION['nombre'] = $datos['nombre'];
                $_SESSION['usuario_id'] = $datos['id'];
                header('Location: index.php?action=dashboard');
                exit;
            } else {
                setcookie('remember_token', '', time() - 3600, '/');
            }
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // 1. Verificar CSRF Token
            if (!isset($_POST['csrf_token']) || !SimpleCaptcha::validateCSRFToken($_POST['csrf_token'])) {
                $_SESSION['mensaje'] = "Error de seguridad. Por favor, intente nuevamente.";
                $_SESSION['mensaje_tipo'] = "error";
                header('Location: index.php?action=login');
                exit;
            }

            // 2. Verificar intentos de login
            $ip = $_SERVER['REMOTE_ADDR'];
            if (SimpleCaptcha::checkBruteForce($ip, $this->db)) {
                $_SESSION['mensaje'] = "Demasiados intentos fallidos. Por favor, espere 30 minutos antes de intentar nuevamente.";
                $_SESSION['mensaje_tipo'] = "error";
                header('Location: index.php?action=login');
                exit;
            }

            // 3. Validar y sanitizar entradas
            $dni = SimpleCaptcha::sanitizeInput($_POST['dni']);
            if (!isset($dni) || empty($dni)) {
                $_SESSION['mensaje'] = "El DNI es requerido";
                $_SESSION['mensaje_tipo'] = "error";
                header('Location: index.php?action=login');
                exit;
            }

            if (!preg_match('/^\d{8}$/', $dni)) {
                $_SESSION['mensaje'] = "El DNI debe contener exactamente 8 números";
                $_SESSION['mensaje_tipo'] = "error";
                header('Location: index.php?action=login');
                exit;
            }

            $clave = $_POST['clave'];
            if (!isset($clave) || empty($clave)) {
                $_SESSION['mensaje'] = "La contraseña es requerida";
                $_SESSION['mensaje_tipo'] = "error";
                header('Location: index.php?action=login');
                exit;
            }

            // 4. Intentar login
            $resultado = $this->userModel->login($dni, $clave);

            switch ($resultado['status']) {
                case 'success':
                    SimpleCaptcha::resetLoginAttempts($ip, $this->db);
                    
                    $recordar = isset($_POST['remember']);
                    if ($recordar) {
                        $remember_token = $this->userModel->generarRememberToken($dni);
                        setcookie('remember_token', $remember_token, time() + 60 * 60 * 24 * 30, '/');
                    }

                    $_SESSION['dni'] = $resultado['usuario']['dni'];
                    $_SESSION['nombre'] = $resultado['usuario']['nombre'];
                    $_SESSION['celular'] = $resultado['usuario']['celular'];
                    $_SESSION['usuario_id'] = $resultado['usuario']['id'];

                    // Regenerar ID de sesión para prevenir session fixation
                    session_regenerate_id(true);

                    // Guardar mensaje de éxito para la siguiente página
                    $_SESSION['mensaje_temp'] = "¡Bienvenido " . $resultado['usuario']['nombre'] . "!";
                    $_SESSION['mensaje_tipo_temp'] = "exito";

                    header("Location: index.php?action=dashboard");
                    exit;

                case 'pendiente':
                    SimpleCaptcha::logLoginAttempt($ip, $this->db);
                    $_SESSION['mensaje'] = "Su cuenta está pendiente de activación. Por favor, espere a que un administrador active su cuenta.";
                    $_SESSION['mensaje_tipo'] = "warning";
                    break;

                case 'inactivo':
                    SimpleCaptcha::logLoginAttempt($ip, $this->db);
                    $_SESSION['mensaje'] = "Su cuenta está inactiva. Por favor, contacte al administrador.";
                    $_SESSION['mensaje_tipo'] = "error";
                    break;

                case 'clave_incorrecta':
                    SimpleCaptcha::logLoginAttempt($ip, $this->db);
                    $_SESSION['mensaje'] = "La contraseña ingresada es incorrecta.";
                    $_SESSION['mensaje_tipo'] = "error";
                    break;

                case 'no_existe':
                    SimpleCaptcha::logLoginAttempt($ip, $this->db);
                    $_SESSION['mensaje'] = "No existe una cuenta con el DNI ingresado.";
                    $_SESSION['mensaje_tipo'] = "error";
                    break;
            }
        }
        
        require __DIR__ . '/../views/login.php';
    }

    public function register() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre' => $_POST['nombre'],
                'apellidos' => $_POST['apellidos'],
                'dia' => $_POST['dia'],
                'mes' => $_POST['mes'],
                'anio' => $_POST['anio'],
                'dni' => $_POST['dni'],
                'celular' => $_POST['celular'],
                'clave' => $_POST['clave'],
                'confirmar_clave' => $_POST['confirmar_clave'],
                'preguntas' => isset($_POST['preguntas']) ? $_POST['preguntas'] : []
            ];

            $errores = $this->userModel->validarRegistro($datos);

            // Validar pregunta de seguridad
            if (empty($datos['preguntas']) || 
                !isset($datos['preguntas'][0]['pregunta']) || 
                !isset($datos['preguntas'][0]['respuesta']) ||
                empty($datos['preguntas'][0]['pregunta']) || 
                empty($datos['preguntas'][0]['respuesta'])) {
                $errores[] = "Debe responder la pregunta de seguridad";
            }

            if (empty($errores)) {
                try {
                    $this->conn->begin_transaction();

                    $fechaHoraActual = date('Y-m-d H:i:s');
                    $remember_token = '';
                    $email = '';

                    // Registrar usuario
                    if ($this->userModel->register(
                        $fechaHoraActual,
                        $datos['nombre'],
                        $datos['apellidos'],
                        $datos['dni'],
                        $datos['celular'],
                        $email,
                        $datos['clave'],
                        $remember_token,
                        $datos['dia'],
                        $datos['mes'],
                        $datos['anio']
                    )) {
                        // Obtener el ID del usuario recién registrado
                        $usuario_id = $this->conn->insert_id;
                        
                        // Guardar la pregunta de seguridad
                        if ($this->userModel->guardarPreguntasSeguridad($usuario_id, $datos['preguntas'])) {
                            $this->conn->commit();
                            $_SESSION['registro_exitoso'] = true;
                            $_SESSION['mensaje'] = "¡Registro exitoso! Su cuenta será activada después de la verificación. Por favor, espere el mensaje de confirmación antes de intentar iniciar sesión.";
                            $_SESSION['mensaje_tipo'] = "exito";
                            header('Location: index.php?action=register');
                            exit;
                        }
                    }

                    $this->conn->rollback();
                    throw new Exception("Error al registrar el usuario");
                } catch (Exception $e) {
                    $this->conn->rollback();
                    error_log("Error en registro: " . $e->getMessage());
                    $_SESSION['mensaje'] = "Error en el servidor. Por favor, intente más tarde.";
                    $_SESSION['mensaje_tipo'] = "error";
                    header('Location: index.php?action=register');
                    exit;
                }
            }
            
            if (!empty($errores)) {
                $_SESSION['errores'] = $errores;
                $_SESSION['datos_form'] = $datos;
                $_SESSION['mensaje'] = implode("<br>", $errores);
                $_SESSION['mensaje_tipo'] = "error";
                header('Location: index.php?action=register');
                exit;
            }
        }

        require_once __DIR__ . '/../views/register.php';
    }

    private function calcularEdad($fecha_nacimiento) {
        $fecha_nac = new DateTime($fecha_nacimiento);
        $hoy = new DateTime();
        return $hoy->diff($fecha_nac)->y;
    }

    public function dashboard() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // if (!isset($_SESSION['dni'])) {
        //     header("Location: index.php?action=login");
        //     exit();
        // }
        require __DIR__ . '/../views/dashboard.php';
    }

    public function perfil() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // if (!isset($_SESSION['dni'])) {
        //     header("Location: index.php?action=login");
        //     exit();
        // }
        require __DIR__ . '/../views/perfil.php';
    }

    public function emergencias() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        // if (!isset($_SESSION['dni'])) {
        //     header("Location: index.php?action=login");
        //     exit();
        // }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tipo_emergencia = $_POST['tipo_emergencia'];
            $latitud = $_POST['latitud'];
            $longitud = $_POST['longitud'];
            $nombre = $_SESSION['nombre'];
            $dni = $_SESSION['dni'];
            $celular = $_SESSION['celular'];
            $usuario_id = $_SESSION['usuario_id'];

            if ($this->userModel->registrarEmergencia($usuario_id, $tipo_emergencia, $latitud, $longitud, $nombre, $dni, $celular)) {
                echo "Emergencia registrada con éxito";
            } else {
                echo "Error al registrar la emergencia";
            }
        } else {
            require __DIR__ . '/../views/emergencias.php';
        }
    }

    public function reportes() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // if (!isset($_SESSION['dni'])) {
        //     header("Location: index.php?action=login");
        //     exit();
        // }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Verificar si se envió un archivo
            if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['reporte_status'] = 'error';
                header("Location: index.php?action=reportes");
                exit();
            }

            // Procesar la imagen
            $imagen = $_FILES['imagen'];
            $nombre_archivo = uniqid() . '_' . $imagen['name'];
            $ruta_destino = 'uploads/' . $nombre_archivo;

            if (move_uploaded_file($imagen['tmp_name'], $ruta_destino)) {
                // Registrar el reporte
                $resultado = $this->userModel->registrarReporte(
                    $_SESSION['usuario_id'],
                    $_POST['descripcion'],
                    $nombre_archivo
                );
            }

            if ($resultado) {
                $_SESSION['reporte_status'] = 'success';
            } else {
                $_SESSION['reporte_status'] = 'error';
            }
            
            header("Location: index.php?action=reportes");
            exit();
        }

        require __DIR__ . '/../views/reportes.php';
    }

    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['dni'])) {
            $dni = $_SESSION['dni']; // Obtener el DNI de la sesión
            $this->userModel->limpiarRememberToken($dni);
            setcookie('remember_token', '', time() - 3600, '/');
        }
        session_unset();
        session_destroy();
        header("Location: index.php?action=login");
        exit();
    }

    public function updatePerfil() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['dni'])) {
            header("Location: index.php?action=login");
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $celular = $_POST['celular'];
            $clave = $_POST['clave'];
            $confirmar_clave = $_POST['confirmar_clave'];

            if ($clave !== $confirmar_clave) {
                die("Las claves no coinciden");
            }

            $dni = $_SESSION['dni'];
            if ($this->userModel->updatePerfil($dni, $celular, $clave)) {
                $_SESSION['celular'] = $celular;
                echo "Perfil actualizado con éxito";
            } else {
                echo "Error al actualizar el perfil";
            }
        }
    }

    public function verificarSesion() {
        if (!isset($_SESSION['dni']) && isset($_COOKIE['remember_token'])) {
            $datos = $this->userModel->verificarRememberToken($_COOKIE['remember_token']);
            
            if ($datos) {
                $_SESSION['dni'] = $datos['dni'];
                return true;
            } else {
                setcookie('remember_token', '', time() - 3600, '/');
            }
        }
        return isset($_SESSION['dni']);
    }

    public function verificarDNI() {
        if (isset($_GET['dni'])) {
            error_log("Verificando DNI desde AJAX: " . $_GET['dni']);
            $dni = $_GET['dni'];
            $exists = $this->userModel->existeDNI($dni);
            error_log("Resultado de verificación: " . ($exists ? "DNI existe" : "DNI no existe"));
            header('Content-Type: application/json');
            echo json_encode(['exists' => $exists]);
            exit;
        } else {
            error_log("No se recibió DNI en la solicitud");
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No se proporcionó DNI']);
            exit;
        }
    }

    public function recuperarPassword() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['paso_recuperacion'])) {
            $_SESSION['paso_recuperacion'] = 1;
        }

        require __DIR__ . '/../views/recuperar_password.php';
    }

    public function verificarDNIRecuperacion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?action=recuperarPassword');
            exit;
        }

        $dni = trim($_POST['dni']);
        
        // Validar formato del DNI
        if (!preg_match('/^\d{8}$/', $dni)) {
            $_SESSION['mensaje'] = "El DNI debe contener exactamente 8 números";
            $_SESSION['mensaje_tipo'] = "error";
            header('Location: index.php?action=recuperarPassword');
            exit;
        }

        // Verificar si el usuario existe y obtener sus preguntas de seguridad
        $preguntas = $this->userModel->getPreguntasSeguridad($dni);
        
        if (empty($preguntas)) {
            $_SESSION['mensaje'] = "No se encontró un usuario con ese DNI o no tiene preguntas de seguridad configuradas";
            $_SESSION['mensaje_tipo'] = "error";
            header('Location: index.php?action=recuperarPassword');
            exit;
        }

        // Guardar DNI y preguntas en la sesión
        $_SESSION['dni_recuperacion'] = $dni;
        $_SESSION['preguntas_seguridad'] = $preguntas;
        $_SESSION['paso_recuperacion'] = 2;
        
        header('Location: index.php?action=recuperarPassword');
        exit;
    }

    public function verificarRespuestas() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['dni_recuperacion'])) {
            header('Location: index.php?action=recuperarPassword');
            exit;
        }

        $respuestas = $_POST['respuestas'] ?? [];
        $dni = $_SESSION['dni_recuperacion'];

        if ($this->userModel->verificarPreguntasSeguridad($dni, $respuestas)) {
            $_SESSION['paso_recuperacion'] = 3;
            header('Location: index.php?action=recuperarPassword');
            exit;
        } else {
            $_SESSION['mensaje'] = "Las respuestas proporcionadas son incorrectas";
            $_SESSION['mensaje_tipo'] = "error";
            header('Location: index.php?action=recuperarPassword');
            exit;
        }
    }

    public function actualizarPassword() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
            !isset($_SESSION['dni_recuperacion']) || 
            $_SESSION['paso_recuperacion'] != 3) {
            header('Location: index.php?action=recuperarPassword');
            exit;
        }

        $nueva_clave = $_POST['nueva_clave'];
        $confirmar_clave = $_POST['confirmar_clave'];
        $dni = $_SESSION['dni_recuperacion'];

        // Validar la nueva contraseña
        if ($nueva_clave !== $confirmar_clave) {
            $_SESSION['mensaje'] = "Las contraseñas no coinciden";
            $_SESSION['mensaje_tipo'] = "error";
            header('Location: index.php?action=recuperarPassword');
            exit;
        }

        // Validar requisitos de la contraseña
        if (strlen($nueva_clave) < 8 || strlen($nueva_clave) > 20 ||
            !preg_match("/[A-Z]/", $nueva_clave) ||
            !preg_match("/[0-9]/", $nueva_clave) ||
            !preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $nueva_clave)) {
            $_SESSION['mensaje'] = "La contraseña no cumple con los requisitos mínimos";
            $_SESSION['mensaje_tipo'] = "error";
            header('Location: index.php?action=recuperarPassword');
            exit;
        }

        if ($this->userModel->actualizarContraseña($dni, $nueva_clave)) {
            // Limpiar variables de sesión
            unset($_SESSION['paso_recuperacion']);
            unset($_SESSION['dni_recuperacion']);
            unset($_SESSION['preguntas_seguridad']);
            
            $_SESSION['mensaje'] = "¡Contraseña actualizada con éxito! Ya puedes iniciar sesión con tu nueva contraseña.";
            $_SESSION['mensaje_tipo'] = "exito";
            header('Location: index.php?action=login');
            exit;
        } else {
            $_SESSION['mensaje'] = "Error al actualizar la contraseña. Por favor, intente nuevamente.";
            $_SESSION['mensaje_tipo'] = "error";
            header('Location: index.php?action=recuperarPassword');
            exit;
        }
    }

}
?>