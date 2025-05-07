<?php
class User {
    private $conn;
    private $dni;

    public function __construct($db) {
        $this->conn = $db;
    }

    private function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    private function verificarIntentos($ip) {
        $sql = "SELECT COUNT(*) as intentos FROM registro_attempts 
                WHERE ip_address = ? AND timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ip);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['intentos'] >= 5;
    }

    private function registrarIntento($ip) {
        $sql = "INSERT INTO registro_attempts (ip_address) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $ip);
        return $stmt->execute();
    }

    private function calcularEdad($dia, $mes, $anio) {
        $fecha_nac = new DateTime("$anio-$mes-$dia");
        $hoy = new DateTime();
        return $hoy->diff($fecha_nac)->y;
    }

    public function register($fechaHoraActual, $nombre, $apellidos, $dni, $celular, $email, $clave, $remember_token, $dia, $mes, $anio) {
        // Verificar intentos de registro
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($this->verificarIntentos($ip)) {
            throw new Exception("Demasiados intentos de registro. Por favor, espere una hora.");
        }

        try {
            $this->conn->begin_transaction();

            // Calcular edad
            $edad = $this->calcularEdad($dia, $mes, $anio);
            
            // Verificar edad mínima
            if ($edad < 14) {
                throw new Exception("Debes ser mayor de 14 años para registrarte");
            }

            // Hash de la contraseña con bcrypt
            $hashedPassword = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Sanitizar todos los datos
            $nombre = htmlspecialchars(strip_tags(trim($nombre)));
            $apellidos = htmlspecialchars(strip_tags(trim($apellidos)));
            $dni = htmlspecialchars(strip_tags(trim($dni)));
            $celular = htmlspecialchars(strip_tags(trim($celular)));
            $email = htmlspecialchars(strip_tags(trim($email)));
            
            $sql = "INSERT INTO usuarios (
                fecha_hora, 
                nombre, 
                apellidos, 
                dni, 
                celular, 
                email, 
                clave, 
                remember_token, 
                edad,
                estado,
                fecha_registro,
                ultima_sesion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PENDIENTE', NOW(), NULL)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ssssssssi", 
                $fechaHoraActual, 
                $nombre, 
                $apellidos, 
                $dni, 
                $celular, 
                $email, 
                $hashedPassword, 
                $remember_token, 
                $edad
            );

            $result = $stmt->execute();
            
            if ($result) {
                // Registrar el intento exitoso
                $this->registrarIntento($ip);
                $this->conn->commit();
                return true;
            } else {
                $this->conn->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Error en registro: " . $e->getMessage());
            throw $e;
        }
    }

    public function checkUserStatus($dni) {
        $sql = "SELECT * FROM usuarios WHERE dni = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function login($dni, $clave) {
        // Primero verificamos si el usuario existe y obtenemos su estado
        $usuario = $this->checkUserStatus($dni);
        
        if (!$usuario) {
            return ['status' => 'no_existe'];
        }

        // Verificar si el usuario está pendiente
        if ($usuario['estado'] === 'PENDIENTE') {
            return ['status' => 'pendiente'];
        }

        // Verificar si el usuario está activo
        if ($usuario['estado'] !== 'ACTIVO') {
            return ['status' => 'inactivo'];
        }

        // Verificar la contraseña con password_verify
        if (!password_verify($clave, $usuario['clave'])) {
            return ['status' => 'clave_incorrecta'];
        }

        // Si todo está bien, retornamos el usuario con estado success
        return ['status' => 'success', 'usuario' => $usuario];
    }

    public function registrarEmergencia($usuario_id, $tipo_emergencia, $latitud, $longitud, $nombre, $dni, $celular) {
        $sql = "INSERT INTO emergencias (usuario_id, tipo_emergencia, latitud, longitud, nombre, dni, celular) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issssss", $usuario_id, $tipo_emergencia, $latitud, $longitud, $nombre, $dni, $celular);
        return $stmt->execute();
    }

    public function updatePerfil($dni, $celular, $clave) {
        $sql = "UPDATE usuarios SET celular = ?, clave = ? WHERE dni = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $celular, $clave, $dni);
        return $stmt->execute();
    }

    public function registrarReporte($usuario_id, $descripcion, $imagen) {
        $sql = "INSERT INTO reportes (usuario_id, descripcion, imagen) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $usuario_id, $descripcion, $imagen);
        return $stmt->execute();
    }

    public function generarRememberToken($dni) {
        $token = bin2hex(random_bytes(32));
        $sql = "UPDATE usuarios SET remember_token = ? WHERE dni = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $token, $dni);
        
        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }

    public function verificarRememberToken($token) {
        if (empty($token)) return false;
        
        $sql = "SELECT * FROM usuarios WHERE remember_token = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $token);
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando verificación de token");
            return false;
        }
        
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function limpiarRememberToken($dni) {
        $sql = "UPDATE usuarios SET remember_token = NULL WHERE dni = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $dni);
        return $stmt->execute();
    }

    public function validarRegistro($datos) {
        $errores = [];
        
        // Validar nombre
        $nombre = trim($datos['nombre']);
        if (!preg_match("/^[A-Za-zÑñÁáÉéÍíÓóÚú\s]{2,50}$/", $nombre)) {
            $errores[] = "El nombre solo debe contener letras y tener entre 2 y 50 caracteres";
        }
        
        // Validar apellidos
        $apellidos = trim($datos['apellidos']);
        if (!preg_match("/^[A-Za-zÑñÁáÉéÍíÓóÚú\s]{2,50}$/", $apellidos)) {
            $errores[] = "Los apellidos solo deben contener letras y tener entre 2 y 50 caracteres";
        }
        
        // Validar fecha de nacimiento y edad
        try {
            $edad = $this->calcularEdad($datos['dia'], $datos['mes'], $datos['anio']);
            if ($edad < 14) {
                $errores[] = "Debes ser mayor de 14 años para registrarte";
            }
        } catch (Exception $e) {
            $errores[] = "Fecha de nacimiento inválida";
        }
        
        // Validar DNI
        $dni = trim($datos['dni']);
        if (!preg_match("/^[0-9]{8}$/", $dni)) {
            $errores[] = "El DNI debe contener exactamente 8 números";
        } elseif ($this->existeDNI($dni)) {
            $errores[] = "Este DNI ya está registrado en el sistema";
        }
        
        // Validar celular
        $celular = trim($datos['celular']);
        if (!preg_match("/^9[0-9]{8}$/", $celular)) {
            $errores[] = "El celular debe empezar con 9 y contener exactamente 9 números";
        }
        
        // Validación robusta de contraseña
        $clave = $datos['clave'];
        if (strlen($clave) < 8) {
            $errores[] = "La contraseña debe tener al menos 8 caracteres";
        }
        if (strlen($clave) > 20) {
            $errores[] = "La contraseña no puede tener más de 20 caracteres";
        }
        if (!preg_match("/[A-Z]/", $clave)) {
            $errores[] = "La contraseña debe contener al menos una letra mayúscula";
        }
        if (!preg_match("/[0-9]/", $clave)) {
            $errores[] = "La contraseña debe contener al menos un número";
        }
        if (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $clave)) {
            $errores[] = "La contraseña debe contener al menos un carácter especial (!@#$%^&*(),.?\":{}|<>)";
        }
        
        // Validar que las contraseñas coincidan
        if ($clave !== $datos['confirmar_clave']) {
            $errores[] = "Las contraseñas no coinciden";
        }

        // Prevenir inyección SQL y XSS en todos los campos
        foreach ($datos as $campo => $valor) {
            if (is_string($valor)) {
                $datos[$campo] = htmlspecialchars(strip_tags(trim($valor)));
            }
        }

        return $errores;
    }

    public function existeDNI($dni) {
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE dni = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $dni);
        if (!$stmt->execute()) {
            error_log("Error al verificar DNI: " . $stmt->error);
            return false;
        }
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        error_log("Verificando DNI: " . $dni . " - Existe: " . ($row['count'] > 0 ? 'Sí' : 'No'));
        return $row['count'] > 0;
    }

    public function guardarPreguntasSeguridad($usuario_id, $preguntas) {
        if (empty($usuario_id) || empty($preguntas)) {
            error_log("Error: ID de usuario o preguntas vacías");
            return false;
        }

        try {
            // Verificar que el usuario existe
            $sql_check = "SELECT id FROM usuarios WHERE id = ?";
            $stmt = $this->conn->prepare($sql_check);
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                error_log("Error: Usuario no encontrado con ID: " . $usuario_id);
                return false;
            }

            // Iniciar transacción
            $this->conn->begin_transaction();
            
            // Eliminar preguntas existentes si las hay
            $sql_delete = "DELETE FROM preguntas_seguridad WHERE usuario_id = ?";
            $stmt = $this->conn->prepare($sql_delete);
            $stmt->bind_param("i", $usuario_id);
            if (!$stmt->execute()) {
                throw new Exception("Error al eliminar preguntas existentes: " . $stmt->error);
            }
            
            // Insertar nuevas preguntas
            $sql_insert = "INSERT INTO preguntas_seguridad (usuario_id, pregunta, respuesta) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($sql_insert);
            
            foreach ($preguntas as $pregunta) {
                if (!isset($pregunta['pregunta']) || !isset($pregunta['respuesta'])) {
                    throw new Exception("Datos de pregunta incompletos");
                }

                $respuesta_hash = password_hash($pregunta['respuesta'], PASSWORD_BCRYPT);
                $stmt->bind_param("iss", 
                    $usuario_id, 
                    $pregunta['pregunta'],
                    $respuesta_hash
                );

                if (!$stmt->execute()) {
                    throw new Exception("Error al insertar pregunta: " . $stmt->error);
                }
            }
            
            // Confirmar transacción
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Revertir cambios si hay error
            $this->conn->rollback();
            error_log("Error en guardarPreguntasSeguridad: " . $e->getMessage());
            return false;
        }
    }

    public function verificarPreguntasSeguridad($dni, $respuestas) {
        try {
            // Obtener el ID del usuario
            $sql_user = "SELECT id FROM usuarios WHERE dni = ?";
            $stmt = $this->conn->prepare($sql_user);
            $stmt->bind_param("s", $dni);
            $stmt->execute();
            $usuario = $stmt->get_result()->fetch_assoc();
            
            if (!$usuario) {
                return false;
            }

            // Obtener las preguntas del usuario
            $sql_preguntas = "SELECT pregunta, respuesta FROM preguntas_seguridad WHERE usuario_id = ?";
            $stmt = $this->conn->prepare($sql_preguntas);
            $stmt->bind_param("i", $usuario['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $preguntas_db = [];
            while ($row = $result->fetch_assoc()) {
                $preguntas_db[] = $row;
            }

            // Verificar cada respuesta
            foreach ($preguntas_db as $index => $pregunta) {
                if (!isset($respuestas[$index]) || 
                    !password_verify($respuestas[$index], $pregunta['respuesta'])) {
                    return false;
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error en verificarPreguntasSeguridad: " . $e->getMessage());
            return false;
        }
    }

    public function getPreguntasSeguridad($dni) {
        try {
            $sql = "SELECT ps.pregunta 
                    FROM preguntas_seguridad ps 
                    JOIN usuarios u ON ps.usuario_id = u.id 
                    WHERE u.dni = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $dni);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $preguntas = [];
            while ($row = $result->fetch_assoc()) {
                $preguntas[] = $row['pregunta'];
            }
            
            return $preguntas;
        } catch (Exception $e) {
            error_log("Error en getPreguntasSeguridad: " . $e->getMessage());
            return [];
        }
    }

    public function actualizarContraseña($dni, $nueva_clave) {
        try {
            $clave_hash = password_hash($nueva_clave, PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios SET clave = ? WHERE dni = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ss", $clave_hash, $dni);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en actualizarContraseña: " . $e->getMessage());
            return false;
        }
    }
}
?>