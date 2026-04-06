<?php
require_once '../config/Conexion.php';

class Plan {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    // Trae los 5 planes ordenados por nivel
    public function obtenerPlanes() {
        $query = "SELECT id_plan, nombre, cuota_mensual, nivel_prioridad FROM PLAN ORDER BY nivel_prioridad ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Trae los beneficios exclusivos de UN plan en particular
    public function obtenerBeneficiosPorPlan($id_plan) {
        $query = "SELECT b.descripcion 
                  FROM BENEFICIO b
                  INNER JOIN PLAN_BENEFICIO pb ON b.id_beneficio = pb.id_beneficio
                  WHERE pb.id_plan = :id_plan";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_plan", $id_plan);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crea un beneficio nuevo y se lo asigna a un plan automáticamente
    public function agregarBeneficioAPlan($id_plan, $descripcion) {
        try {
            $this->conn->beginTransaction();

            // 1. Creamos el beneficio en el catálogo
            $queryB = "INSERT INTO BENEFICIO (descripcion) VALUES (:desc)";
            $stmtB = $this->conn->prepare($queryB);
            $stmtB->bindParam(":desc", $descripcion);
            $stmtB->execute();
            
            $id_beneficio = $this->conn->lastInsertId();

            // 2. Lo vinculamos al Plan en la tabla intermedia
            $queryPB = "INSERT INTO PLAN_BENEFICIO (id_plan, id_beneficio) VALUES (:id_plan, :id_beneficio)";
            $stmtPB = $this->conn->prepare($queryPB);
            $stmtPB->bindParam(":id_plan", $id_plan);
            $stmtPB->bindParam(":id_beneficio", $id_beneficio);
            $stmtPB->execute();

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>