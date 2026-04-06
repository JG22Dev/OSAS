<?php
require_once '../config/Conexion.php';

class Afiliado {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    // Traemos los planes para armar el <select>
    public function obtenerPlanes() {
        $query = "SELECT id_plan, nombre FROM PLAN ORDER BY cuota_mensual ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listamos los afiliados con su plan y email
    public function listarAfiliados() {
        $query = "SELECT a.id_afiliado, a.nombre, a.apellido, a.dni, a.numero_credencial, p.nombre as plan_nombre, u.email 
                  FROM AFILIADO a
                  INNER JOIN PLAN p ON a.id_plan = p.id_plan
                  INNER JOIN USUARIO u ON a.id_usuario = u.id_usuario
                  ORDER BY a.apellido ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Alta Doble con Transacción
    public function registrarAfiliado($nombre, $apellido, $dni, $fecha_nacimiento, $id_plan, $email, $password) {
        try {
            $this->conn->beginTransaction();

            // 1. Insertamos en USUARIO (id_rol = 2 es Afiliado)
            $id_rol_afiliado = 2;
            $estado = 'activo';
            $queryUsuario = "INSERT INTO USUARIO (email, password_hash, estado, id_rol) 
                             VALUES (:email, :password, :estado, :id_rol)";
            $stmtU = $this->conn->prepare($queryUsuario);
            $stmtU->bindParam(":email", $email);
            $stmtU->bindParam(":password", $password); 
            $stmtU->bindParam(":estado", $estado);
            $stmtU->bindParam(":id_rol", $id_rol_afiliado);
            $stmtU->execute();

            $id_usuario_nuevo = $this->conn->lastInsertId();

            // 2. Generamos un número de credencial automático (Ej: CRED-35123456)
            $numero_credencial = "CRED-" . $dni;

            // 3. Insertamos en AFILIADO
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
}
?>