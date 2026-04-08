<?php
require_once '../config/Conexion.php';

class Recepcion {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    // Trae los turnos en un rango de fechas
    public function listarPacientesPorFecha($fecha_inicio, $fecha_fin) {
        $query = "SELECT t.id_turno, DATE(t.fecha_hora) as fecha, TIME(t.fecha_hora) as hora, t.id_estado, 
                         a.nombre as paciente_nombre, a.apellido as paciente_apellido, a.dni, a.numero_credencial,
                         p.nombre as medico_nombre, p.apellido as medico_apellido, e.nombre as especialidad
                  FROM TURNO t
                  INNER JOIN AFILIADO a ON t.id_afiliado = a.id_afiliado
                  INNER JOIN PROFESIONAL p ON t.id_profesional = p.id_profesional
                  INNER JOIN ESPECIALIDAD e ON p.id_especialidad = e.id_especialidad
                  WHERE DATE(t.fecha_hora) BETWEEN :inicio AND :fin
                  ORDER BY t.fecha_hora ASC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":inicio", $fecha_inicio);
        $stmt->bindParam(":fin", $fecha_fin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cambia el estado de "Reservado" (2) a "Confirmado / En Sala" (3)
    public function confirmarLlegada($id_turno) {
        $id_estado_confirmado = 3; 
        $query = "UPDATE TURNO SET id_estado = :estado WHERE id_turno = :id_turno";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $id_estado_confirmado);
        $stmt->bindParam(":id_turno", $id_turno);
        return $stmt->execute();
    }
    // Buscar paciente por DNI
    public function buscarPacientePorDNI($dni) {
        $query = "SELECT a.id_afiliado, a.nombre, a.apellido, p.nombre as plan_nombre
                  FROM AFILIADO a
                  INNER JOIN PLAN p ON a.id_plan = p.id_plan
                  WHERE a.dni = :dni";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":dni", $dni);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Guardar el turno manualmente
    public function registrarTurnoManual($id_afiliado, $id_profesional, $fecha_hora, $motivo) {
        $id_estado = 2; // Estado 2 = 'Reservado'
        $query = "INSERT INTO TURNO (fecha_hora, motivo_consulta, id_profesional, id_afiliado, id_estado) 
                  VALUES (:fecha, :motivo, :id_prof, :id_afi, :id_estado)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":fecha", $fecha_hora);
        $stmt->bindParam(":motivo", $motivo);
        $stmt->bindParam(":id_prof", $id_profesional);
        $stmt->bindParam(":id_afi", $id_afiliado);
        $stmt->bindParam(":id_estado", $id_estado);
        return $stmt->execute();
    }
}
?>