<?php
require_once __DIR__ . '/../models/Admin.php';

class AdminController {
    private $adminModel;

    public function __construct($db) {
        $this->adminModel = new Admin($db);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cip = $_POST['cip'];
            $clave = $_POST['clave'];
            $admin = $this->adminModel->login($cip, $clave);
            if ($admin) {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['cip'] = $admin['cip'];
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['nombre'] = $admin['nombre'];
                header("Location: admin_dashboard.php?action=dashboard");
                exit();
            } else {
                echo "CIP o clave incorrectos";
            }
        } else {
            require __DIR__ . '/../administrador/admin_login.php';
        }
    }

    public function dashboard() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cip'])) {
            header("Location: admin_login.php");
            exit();
        }
        require __DIR__ . '/../administrador/admin_dashboard.php';
    }

    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        header("Location: admin_login.php");
        exit();
    }
    
    public function showUsuarios() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar si el administrador está logueado
        if (!isset($_SESSION['cip'])) {
            header("Location: admin_login.php");
            exit();
        }

        // Obtener la lista de usuarios
        $usuarios = $this->adminModel->getUsuarios();
        
        // Incluir la vista con los datos de usuarios
        require __DIR__ . '/../administrador/admin_usuarios.php';
    }

    public function eliminarUsuario() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['cip'])) {
            header("Location: admin_login.php");
            exit();
        }

        if (isset($_GET['id'])) {
            if ($this->adminModel->eliminarUsuario($_GET['id'])) {
                header("Location: admin_dashboard.php?action=usuarios&mensaje=eliminado");
            } else {
                header("Location: admin_dashboard.php?action=usuarios&error=1");
            }
        }
        exit();
    }

    public function editarUsuario() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['cip'])) {
            header("Location: admin_login.php");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $resultado = $this->adminModel->actualizarUsuario(
                $_POST['id'],
                $_POST['nombre'],
                $_POST['apellidos'],
                $_POST['edad'],
                $_POST['dni'],
                $_POST['celular']
            );
            
            if ($resultado) {
                header("Location: admin_dashboard.php?action=usuarios&mensaje=actualizado");
            } else {
                header("Location: admin_dashboard.php?action=usuarios&error=2");
            }
            exit();
        }

        $usuario = null;
        if (isset($_GET['id'])) {
            $usuario = $this->adminModel->getUsuario($_GET['id']);
        }
        
        require __DIR__ . '/../administrador/crud/editar_usuario.php';
    }

    public function showEmergencias() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar si el administrador está logueado
        if (!isset($_SESSION['cip'])) {
            header("Location: admin_login.php");
            exit();
        }

        // Obtener la lista de emergencias
        $emergencias = $this->adminModel->getEmergencias();
        
        // Incluir la vista con los datos de emergencias
        require __DIR__ . '/../administrador/admin_emergencias.php';
    }

    public function showReportes() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar si el administrador está logueado
        if (!isset($_SESSION['cip'])) {
            header("Location: admin_login.php");
            exit();
        }

        // Obtener la lista de reportes
        $reportes = $this->adminModel->getReportes();
        
        // Incluir la vista con los datos de reportes
        require __DIR__ . '/../administrador/admin_reportes.php';
    }

    public function actualizarEstadoReporte() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        header('Content-Type: application/json');
        
        if (!isset($_SESSION['cip'])) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit();
        }

        if (!isset($_POST['id']) || !isset($_POST['estado'])) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit();
        }

        $resultado = $this->adminModel->actualizarEstadoReporte($_POST['id'], $_POST['estado']);
        echo json_encode(['success' => $resultado]);
        exit();
    }
}
?>