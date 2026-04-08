<?php
require_once '../config/Conexion.php';

class Agenda {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    // Traductor: Entra id_usuario, sale id_profesional
    public function obtenerIdProfesional($id_usuario) {
        $query = "SELECT id_profesional FROM PROFESIONAL WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_usuario", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['id_profesional'] : false;
    }

    // Buscar los turnos de un día específico
    public function listarTurnosDelDia($id_profesional, $fecha) {
        $query = "SELECT t.id_turno, TIME(t.fecha_hora) as hora, t.motivo_consulta, t.id_estado, 
                         e.nombre_estado, a.nombre, a.apellido, a.dni, p.nombre as plan_nombre
                  FROM TURNO t
                  INNER JOIN AFILIADO a ON t.id_afiliado = a.id_afiliado
                  INNER JOIN PLAN p ON a.id_plan = p.id_plan
                  INNER JOIN ESTADO_TURNO e ON t.id_estado = e.id_estado
                  WHERE t.id_profesional = :id_prof AND DATE(t.fecha_hora) = :fecha
                  ORDER BY t.fecha_hora ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_prof", $id_profesional);
        $stmt->bindParam(":fecha", $fecha);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cambiar el estado del turno (Ej: de 'Reservado' a 'Asistió')
    public function cambiarEstadoTurno($id_turno, $id_estado) {
        $query = "UPDATE TURNO SET id_estado = :estado WHERE id_turno = :id_turno";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $id_estado);
        $stmt->bindParam(":id_turno", $id_turno);
        return $stmt->execute();
    }
}
?>