<?php
require_once '../config/Conexion.php';

class Sede {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    // Trae todas las sedes para el panel de Admin
    public function listarTodas() {
        $query = "SELECT * FROM SEDE ORDER BY estado ASC, nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Trae solo las sedes activas (Esta la usaremos más adelante para el paciente)
    public function listarActivas() {
        $query = "SELECT * FROM SEDE WHERE estado = 'activo' ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar una nueva sucursal
    public function agregar($nombre, $direccion) {
        try {
            $query = "INSERT INTO SEDE (nombre, direccion) VALUES (:nombre, :direccion)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":direccion", $direccion);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    // Editar datos del edificio
    public function editar($id_sede, $nombre, $direccion) {
        try {
            $query = "UPDATE SEDE SET nombre = :nombre, direccion = :direccion WHERE id_sede = :id_sede";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":direccion", $direccion);
            $stmt->bindParam(":id_sede", $id_sede);
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    // Baja Lógica (Suspender clínica temporalmente por reformas, etc)
    public function cambiarEstado($id_sede, $nuevo_estado) {
        $query = "UPDATE SEDE SET estado = :estado WHERE id_sede = :id_sede";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $nuevo_estado);
        $stmt->bindParam(":id_sede", $id_sede);
        return $stmt->execute();
    }
}
?>