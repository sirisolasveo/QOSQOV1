<?php
class AuthMiddleware {
    private $conn;
    private $user;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->user = new User($conn);
    }

    public function verificarAutenticacion() {

        error_log("=== Verificando autenticación ===");
        error_log("Contenido actual de la sesión: " . print_r($_SESSION, true));
        
        // Verificar sesión activa
        if (isset($_SESSION['dni'])) {
            error_log("Sesión activa para DNI: " . $_SESSION['dni']);
            return true;
        } else {
            error_log("No hay sesión activa en _SESSION");
        }
        
        // Verificar remember token
        if (isset($_COOKIE['remember_token'])) {
            error_log("Verificando remember token: " . $_COOKIE['remember_token']);
            $datos = $this->user->verificarRememberToken($_COOKIE['remember_token']);
            
            if ($datos) {
                error_log("Token correcto: " . $datos['dni']);
                $_SESSION['dni'] = $datos['dni'];
                return true;
            }
            
            error_log("Token inválido");
            setcookie('remember_token', '', time() - 3600, '/');
        } else {
            error_log("No hay cookie remember_token presente");
        }
    
        error_log("Autenticación fallida, redirigiendo al login");
        return false;


    }
    

    public function requireAuth() {
        
        if(!$this->verificarAutenticacion()) {
            header('Location: /QOSQO/index.php?action=login');
            exit;
        }
        
    }
}