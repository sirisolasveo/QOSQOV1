<?php
class Admin {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($cip, $clave) {
        $sql = "SELECT * FROM administradores WHERE cip = ? AND clave = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $cip, $clave);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getUsuarios() {
        $sql = "SELECT id, nombre, apellidos, edad, dni, celular,estado FROM usuarios ORDER BY id DESC";
        $result = $this->conn->query($sql);
        $usuarios = array();
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $usuarios[] = $row;
            }
        }
        
        return $usuarios;
    }

    public function eliminarUsuario($id) {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getUsuario($id) {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function actualizarUsuario($id, $nombre, $apellidos, $edad, $dni, $celular) {
        $sql = "UPDATE usuarios SET nombre = ?, apellidos = ?, edad = ?, dni = ?, celular = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssissi", $nombre, $apellidos, $edad, $dni, $celular, $id);
        return $stmt->execute();
    }

    public function actualizarEstado($id, $estado) {
        $sql = "UPDATE usuarios SET estado = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $estado, $id);
        return $stmt->execute();
    }

    public function getEmergencias() {
        $sql = "SELECT e.*, u.nombre as nombre_usuario 
                FROM emergencias e 
                LEFT JOIN usuarios u ON e.usuario_id = u.id 
                ORDER BY e.fecha DESC";
        $result = $this->conn->query($sql);
        $emergencias = array();
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $emergencias[] = $row;
            }
        }
        
        return $emergencias;
    }

    public function actualizarEstadoEmergencia($id, $estado) {
        $sql = "UPDATE emergencias SET estado = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $estado, $id);
        return $stmt->execute();
    }

    public function getReportes() {
        $sql = "SELECT r.*, u.nombre, u.apellidos, u.dni 
                FROM reportes r 
                LEFT JOIN usuarios u ON r.usuario_id = u.id 
                ORDER BY r.fecha DESC";
        $result = $this->conn->query($sql);
        $reportes = array();
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $reportes[] = $row;
            }
        }
        
        return $reportes;
    }

    public function actualizarEstadoReporte($id, $estado) {
        $sql = "UPDATE reportes SET estado = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $estado, $id);
        return $stmt->execute();
    }

    public function getReporte($id) {
        $query = "SELECT * FROM reportes WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function registrarDescargo($emergencia_id, $dni_usuario, $cip_administrador, $placa_vehiculo, $medidas_adoptadas) {
        $sql = "INSERT INTO descargo_emergencias (emergencia_id, dni_usuario, cip_administrador, placa_vehiculo, medidas_adoptadas) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issss", $emergencia_id, $dni_usuario, $cip_administrador, $placa_vehiculo, $medidas_adoptadas);
        return $stmt->execute();
    }

    public function getDescargo($emergencia_id) {
        $sql = "SELECT * FROM descargo_emergencias WHERE emergencia_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $emergencia_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function actualizarDescargo($emergencia_id, $cip_administrador, $placa_vehiculo, $medidas_adoptadas) {
        $sql = "UPDATE descargo_emergencias 
                SET placa_vehiculo = ?, medidas_adoptadas = ?, fecha_modificacion = CURRENT_TIMESTAMP 
                WHERE emergencia_id = ? AND cip_administrador = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssis", $placa_vehiculo, $medidas_adoptadas, $emergencia_id, $cip_administrador);
        return $stmt->execute();
    }

    public function getEmergencia($id) {
        $sql = "SELECT * FROM emergencias WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getDescargoReporte($id) {
        $query = "SELECT * FROM descargo_reportes WHERE reportes_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function registrarDescargoReporte($id_reporte, $cip_admin, $placa_vehiculo, $medidas_adoptadas) {
        try {
            $this->conn->begin_transaction();

            // Actualizar estado del reporte
            $stmt = $this->conn->prepare("UPDATE reportes SET estado = 'ATENDIDO' WHERE id = ?");
            $stmt->bind_param('i', $id_reporte);
            if (!$stmt->execute()) {
                throw new Exception('Error al actualizar el estado del reporte');
            }

            // Insertar descargo
            $stmt = $this->conn->prepare("INSERT INTO descargo_reportes 
                (emergencia_id, dni_usuario, cip_administrador, placa_vehiculo, medidas_adoptadas) 
                SELECT id, dni_usuario, ?, ?, ? 
                FROM reportes WHERE id = ?");
            
            $stmt->bind_param('sssi', $cip_admin, $placa_vehiculo, $medidas_adoptadas, $id_reporte);
            if (!$stmt->execute()) {
                throw new Exception('Error al registrar el descargo');
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function getReporteCompleto($id) {
        $query = "SELECT r.*, u.nombre, u.apellidos, u.dni,
                  a.nombre as nombre_admin, a.apellidos as apellido_admin
                  FROM reportes r
                  INNER JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN administradores a ON a.cip = ?
                  WHERE r.id = ?";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('si', $_SESSION['cip'], $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

}
?>