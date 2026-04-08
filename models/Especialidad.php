<?php
require_once '../config/Conexion.php';

class Especialidad {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    public function listar() {
        $query = "SELECT * FROM ESPECIALIDAD ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregar($nombre) {
        try {
            $query = "INSERT INTO ESPECIALIDAD (nombre) VALUES (:nombre)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nombre", $nombre);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    public function editar($id_especialidad, $nombre) {
        $query = "UPDATE ESPECIALIDAD SET nombre = :nombre WHERE id_especialidad = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":id", $id_especialidad);
        return $stmt->execute();
    }

    public function eliminar($id_especialidad) {
        try {
            $query = "DELETE FROM ESPECIALIDAD WHERE id_especialidad = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id_especialidad);
            $stmt->execute();
            return ["status" => "success", "mensaje" => "Especialidad eliminada."];
        } catch(PDOException $e) {
            if($e->getCode() == '23000') {
                return ["status" => "error", "mensaje" => "No podés eliminar esta especialidad porque hay médicos asociados a ella."];
            }
            return ["status" => "error", "mensaje" => "Error al eliminar."];
        }
    }

    // 🔥 NUEVAS FUNCIONES PARA SEDES 🔥

    // Traer todas las sedes activas para mostrar en el modal
    public function obtenerSedesDisponibles() {
        $query = "SELECT id_sede, nombre FROM SEDE WHERE estado = 'activo' ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Traer las sedes donde YA ESTÁ ASIGNADA esta especialidad
    public function obtenerSedesPorEspecialidad($id_especialidad) {
        $query = "SELECT id_sede FROM SEDE_ESPECIALIDAD WHERE id_especialidad = :id_esp";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_esp", $id_especialidad);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // Devuelve array simple ej: [1, 3]
    }

    // Guardar la nueva configuración de sedes
    public function asignarSedes($id_especialidad, $sedes_seleccionadas) {
        try {
            $this->conn->beginTransaction();

            // 1. Borramos la configuración vieja de esta especialidad
            $queryDelete = "DELETE FROM SEDE_ESPECIALIDAD WHERE id_especialidad = :id_esp";
            $stmtD = $this->conn->prepare($queryDelete);
            $stmtD->bindParam(":id_esp", $id_especialidad);
            $stmtD->execute();

            // 2. Insertamos las nuevas sedes tildadas
            if (!empty($sedes_seleccionadas)) {
                $queryInsert = "INSERT INTO SEDE_ESPECIALIDAD (id_sede, id_especialidad) VALUES (:id_sede, :id_esp)";
                $stmtI = $this->conn->prepare($queryInsert);
                
                foreach ($sedes_seleccionadas as $id_sede) {
                    $stmtI->bindParam(":id_sede", $id_sede);
                    $stmtI->bindParam(":id_esp", $id_especialidad);
                    $stmtI->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>