<?php
require_once '../config/Conexion.php';

class Plan {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    public function listar() {
        $query = "SELECT * FROM PLAN ORDER BY cuota_mensual ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregar($nombre, $cuota) {
        try {
            $query = "INSERT INTO PLAN (nombre, cuota_mensual) VALUES (:nombre, :cuota)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":cuota", $cuota);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    public function editar($id_plan, $nombre, $cuota) {
        $query = "UPDATE PLAN SET nombre = :nombre, cuota_mensual = :cuota WHERE id_plan = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":cuota", $cuota);
        $stmt->bindParam(":id", $id_plan);
        return $stmt->execute();
    }

    public function eliminar($id_plan) {
        try {
            $query = "DELETE FROM PLAN WHERE id_plan = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id_plan);
            $stmt->execute();
            return ["status" => "success", "mensaje" => "Plan comercial eliminado."];
        } catch(PDOException $e) {
            // Error 1451 significa que hay afiliados usando este plan
            if($e->getCode() == '23000') {
                return ["status" => "error", "mensaje" => "No podés eliminar este plan porque hay pacientes (afiliados) usándolo. Editale el nombre o precio en su lugar."];
            }
            return ["status" => "error", "mensaje" => "Error de base de datos."];
        }
    }
}
?>