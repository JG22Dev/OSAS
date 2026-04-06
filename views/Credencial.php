<?php
require_once '../config/Conexion.php';

class Credencial {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    // Trae los datos del carnet
    public function obtenerDatosAfiliado($id_usuario) {
        $query = "SELECT a.nombre, a.apellido, a.dni, a.numero_credencial, p.id_plan, p.nombre as nombre_plan, p.nivel_prioridad 
                  FROM AFILIADO a
                  INNER JOIN PLAN p ON a.id_plan = p.id_plan
                  WHERE a.id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_usuario", $id_usuario);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Trae la lista de beneficios asociados a ese plan
    public function obtenerBeneficios($id_plan) {
        $query = "SELECT b.descripcion 
                  FROM BENEFICIO b
                  INNER JOIN PLAN_BENEFICIO pb ON b.id_beneficio = pb.id_beneficio
                  WHERE pb.id_plan = :id_plan";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_plan", $id_plan);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>