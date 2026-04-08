<?php
require_once '../config/Conexion.php';

class Profesional {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    public function obtenerEspecialidades() {
        $query = "SELECT id_especialidad, nombre FROM ESPECIALIDAD ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarProfesionales() {
        $query = "SELECT p.id_profesional, p.nombre, p.apellido, p.matricula, e.nombre as especialidad, p.id_especialidad, u.id_usuario, u.email, u.estado 
                  FROM PROFESIONAL p
                  INNER JOIN ESPECIALIDAD e ON p.id_especialidad = e.id_especialidad
                  INNER JOIN USUARIO u ON p.id_usuario = u.id_usuario
                  ORDER BY u.estado ASC, p.apellido ASC"; 
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Para la Recepción: Solo médicos que no estén suspendidos
    public function listarProfesionalesActivos() {
        $query = "SELECT p.id_profesional, p.nombre, p.apellido 
                  FROM PROFESIONAL p
                  INNER JOIN USUARIO u ON p.id_usuario = u.id_usuario
                  WHERE u.estado = 'activo'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarProfesional($nombre, $apellido, $matricula, $email, $password, $id_especialidad) {
        try {
            $this->conn->beginTransaction();
            $id_rol_medico = 3;
            $estado = 'activo';
            
            $queryUsuario = "INSERT INTO USUARIO (email, password_hash, estado, id_rol) VALUES (:email, :password, :estado, :id_rol)";
            $stmtU = $this->conn->prepare($queryUsuario);
            $stmtU->bindParam(":email", $email);
            $stmtU->bindParam(":password", $password); 
            $stmtU->bindParam(":estado", $estado);
            $stmtU->bindParam(":id_rol", $id_rol_medico);
            $stmtU->execute();

            $id_usuario_nuevo = $this->conn->lastInsertId();

            $queryProf = "INSERT INTO PROFESIONAL (nombre, apellido, matricula, id_usuario, id_especialidad) VALUES (:nombre, :apellido, :matricula, :id_usuario, :id_especialidad)";
            $stmtP = $this->conn->prepare($queryProf);
            $stmtP->bindParam(":nombre", $nombre);
            $stmtP->bindParam(":apellido", $apellido);
            $stmtP->bindParam(":matricula", $matricula);
            $stmtP->bindParam(":id_usuario", $id_usuario_nuevo);
            $stmtP->bindParam(":id_especialidad", $id_especialidad);
            $stmtP->execute();

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false; 
        }
    }

    public function editarProfesional($id_profesional, $nombre, $apellido, $matricula, $id_especialidad) {
        try {
            $query = "UPDATE PROFESIONAL SET nombre = :nombre, apellido = :apellido, matricula = :matricula, id_especialidad = :id_especialidad 
                      WHERE id_profesional = :id_profesional";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":apellido", $apellido);
            $stmt->bindParam(":matricula", $matricula);
            $stmt->bindParam(":id_especialidad", $id_especialidad);
            $stmt->bindParam(":id_profesional", $id_profesional);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    public function cambiarEstadoUsuario($id_usuario, $nuevo_estado) {
        $query = "UPDATE USUARIO SET estado = :estado WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $nuevo_estado);
        $stmt->bindParam(":id_usuario", $id_usuario);
        return $stmt->execute();
    }
}
?>