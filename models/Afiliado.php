<?php
require_once '../config/Conexion.php';

class Afiliado {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    public function obtenerPlanes() {
        $query = "SELECT id_plan, nombre FROM PLAN ORDER BY cuota_mensual ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarAfiliados() {
        $query = "SELECT a.id_afiliado, a.nombre, a.apellido, a.dni, a.fecha_nacimiento, a.numero_credencial, p.id_plan, p.nombre as plan_nombre, u.id_usuario, u.email, u.estado 
                  FROM AFILIADO a
                  INNER JOIN PLAN p ON a.id_plan = p.id_plan
                  INNER JOIN USUARIO u ON a.id_usuario = u.id_usuario
                  ORDER BY u.estado ASC, a.apellido ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarAfiliado($nombre, $apellido, $dni, $fecha_nacimiento, $id_plan, $email, $password) {
        try {
            $this->conn->beginTransaction();

            $id_rol_afiliado = 2;
            $estado = 'activo';
            $queryUsuario = "INSERT INTO USUARIO (email, password_hash, estado, id_rol) VALUES (:email, :password, :estado, :id_rol)";
            $stmtU = $this->conn->prepare($queryUsuario);
            $stmtU->bindParam(":email", $email);
            $stmtU->bindParam(":password", $password); 
            $stmtU->bindParam(":estado", $estado);
            $stmtU->bindParam(":id_rol", $id_rol_afiliado);
            $stmtU->execute();

            $id_usuario_nuevo = $this->conn->lastInsertId();
            $numero_credencial = "CRED-" . $dni;

            $queryAfi = "INSERT INTO AFILIADO (nombre, apellido, dni, fecha_nacimiento, numero_credencial, id_usuario, id_plan) 
                         VALUES (:nombre, :apellido, :dni, :fecha_nac, :credencial, :id_usuario, :id_plan)";
            $stmtA = $this->conn->prepare($queryAfi);
            $stmtA->bindParam(":nombre", $nombre);
            $stmtA->bindParam(":apellido", $apellido);
            $stmtA->bindParam(":dni", $dni);
            $stmtA->bindParam(":fecha_nac", $fecha_nacimiento);
            $stmtA->bindParam(":credencial", $numero_credencial);
            $stmtA->bindParam(":id_usuario", $id_usuario_nuevo);
            $stmtA->bindParam(":id_plan", $id_plan);
            $stmtA->execute();

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false; 
        }
    }

    // NUEVO: Editar
    public function editarAfiliado($id_afiliado, $nombre, $apellido, $dni, $fecha_nacimiento, $id_plan) {
        try {
            $query = "UPDATE AFILIADO SET nombre = :nombre, apellido = :apellido, dni = :dni, fecha_nacimiento = :fecha_nac, id_plan = :id_plan 
                      WHERE id_afiliado = :id_afiliado";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":apellido", $apellido);
            $stmt->bindParam(":dni", $dni);
            $stmt->bindParam(":fecha_nac", $fecha_nacimiento);
            $stmt->bindParam(":id_plan", $id_plan);
            $stmt->bindParam(":id_afiliado", $id_afiliado);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    // NUEVO: Baja Lógica
    public function cambiarEstadoUsuario($id_usuario, $nuevo_estado) {
        $query = "UPDATE USUARIO SET estado = :estado WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $nuevo_estado);
        $stmt->bindParam(":id_usuario", $id_usuario);
        return $stmt->execute();
    }
}
?>