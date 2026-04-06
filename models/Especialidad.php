<?php
require_once '../config/Conexion.php';

class Especialidad {
    private $conn;
    private $tabla = "ESPECIALIDAD";

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    // Método para LEER todas las especialidades
    public function listarTodas() {
        $query = "SELECT id_especialidad, nombre FROM " . $this->tabla . " ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para CREAR una nueva especialidad
    public function registrar($nombre) {
        $query = "INSERT INTO " . $this->tabla . " (nombre) VALUES (:nombre)";
        $stmt = $this->conn->prepare($query);

        // Limpiamos los datos por seguridad
        $nombre = htmlspecialchars(strip_tags($nombre));

        // Vinculamos el parámetro
        $stmt->bindParam(":nombre", $nombre);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>