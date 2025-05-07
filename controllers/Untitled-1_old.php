   error_log("Iniciando proceso de login");

        // 1. Verificar si ya hay sesión activa
        if (isset($_SESSION['dni'])) {
            error_log("Sesión existente para DNI: " . $_SESSION['dni']);
            header('Location: index.php?action=dashboard');
        
        }

        // 2. Verificar remember token solo si no hay sesión
        if (!isset($_SESSION['dni']) && isset($_COOKIE['remember_token'])) {
            error_log("Verificando remember token: " . $_COOKIE['remember_token']);
            $datos = $this->userModel->verificarRememberToken($_COOKIE['remember_token']);
            
            if ($datos) {
                error_log("Token válido encontrado para DNI: " . $datos['dni']);
                $_SESSION['dni'] = $datos['dni'];
                $_SESSION['nombre'] = $datos['nombre'];
                $_SESSION['usuario_id'] = $datos['id'];
                header('Location: index.php?action=dashboard');
                
            } else {
                // Si el token no es válido, eliminarlo
                error_log("Token inválido, eliminando cookie");
                setcookie('remember_token', '', time() - 3600, '/');
            }
        }

        // 3. Procesar formulario de login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dni = $_POST['dni'];
            $clave = $_POST['clave'];
            $remember = isset($_POST['remember']);
            
            error_log("Intento de login para DNI: " . $dni);
            
            $datos = $this->userModel->login($dni, $clave);

            if ($datos) {
                $_SESSION['dni'] = $datos['dni'];
                $_SESSION['nombre'] = $datos['nombre'];
                $_SESSION['usuario_id'] = $datos['id'];
                
                if ($remember) {
                    $token = $this->userModel->generarRememberToken($dni);
                    if ($token) {
                        setcookie('remember_token', $token, time() + (2 * 365 * 24 * 60 * 60), '/');
                        error_log("Token generado y cookie establecida");
                    }
                }

                header('Location: index.php?action=dashboard');
                
            } else {
                $error = 'Credenciales inválidas';
            }
        }

        // 4. Mostrar formulario de login
        require __DIR__ . '/../views/login.php';