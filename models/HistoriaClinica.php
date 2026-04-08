<?php
require_once '../config/Conexion.php';

class HistoriaClinica {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    // 1. Traer la agenda DE HOY para el médico logueado
    public function obtenerAgendaHoy($id_usuario_medico) {
        // Primero buscamos qué profesional es este usuario
        $queryProf = "SELECT id_profesional FROM PROFESIONAL WHERE id_usuario = :id_user";
        $stmtP = $this->conn->prepare($queryProf);
        $stmtP->bindParam(":id_user", $id_usuario_medico);
        $stmtP->execute();
        $prof = $stmtP->fetch(PDO::FETCH_ASSOC);
        
        if(!$prof) return [];

        // Buscamos los turnos de hoy
        $query = "SELECT t.id_turno, TIME(t.fecha_hora) as hora, t.id_estado, 
                         a.id_afiliado, a.nombre as pac_nombre, a.apellido as pac_apellido, a.dni, a.numero_credencial,
                         s.nombre as sede_nombre
                  FROM TURNO t
                  INNER JOIN AFILIADO a ON t.id_afiliado = a.id_afiliado
                  INNER JOIN SEDE s ON t.id_sede = s.id_sede
                  WHERE t.id_profesional = :id_prof 
                  AND DATE(t.fecha_hora) = CURDATE()
                  ORDER BY t.fecha_hora ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_prof", $prof['id_profesional']);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Guardar la evolución y cerrar el turno
    public function guardarEvolucion($id_turno, $diagnostico, $tratamiento, $receta) {
        try {
            $this->conn->beginTransaction();

            // Insertar en Historia Clínica
            $queryInsert = "INSERT INTO HISTORIA_CLINICA (id_turno, diagnostico, tratamiento, receta) 
                            VALUES (:id_turno, :diagnostico, :tratamiento, :receta)";
            $stmtI = $this->conn->prepare($queryInsert);
            $stmtI->bindParam(":id_turno", $id_turno);
            $stmtI->bindParam(":diagnostico", $diagnostico);
            $stmtI->bindParam(":tratamiento", $tratamiento);
            $stmtI->bindParam(":receta", $receta);
            $stmtI->execute();

            // Cambiar estado del turno a 4 (Atendido)
            $id_estado_atendido = 4;
            $queryUpdate = "UPDATE TURNO SET id_estado = :estado WHERE id_turno = :id_turno";
            $stmtU = $this->conn->prepare($queryUpdate);
            $stmtU->bindParam(":estado", $id_estado_atendido);
            $stmtU->bindParam(":id_turno", $id_turno);
            $stmtU->execute();

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // 3. Ver todo el historial médico de un paciente específico
    public function obtenerHistorialPaciente($id_afiliado) {
        $query = "SELECT hc.fecha_registro, hc.diagnostico, hc.tratamiento, hc.receta, 
                         p.nombre as med_nombre, p.apellido as med_apellido, e.nombre as especialidad
                  FROM HISTORIA_CLINICA hc
                  INNER JOIN TURNO t ON hc.id_turno = t.id_turno
                  INNER JOIN PROFESIONAL p ON t.id_profesional = p.id_profesional
                  INNER JOIN ESPECIALIDAD e ON p.id_especialidad = e.id_especialidad
                  WHERE t.id_afiliado = :id_afi
                  ORDER BY hc.fecha_registro DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_afi", $id_afiliado);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Marcar paciente como Ausente (Estado 5)
    public function marcarAusente($id_turno) {
        $id_estado_ausente = 5;
        $query = "UPDATE TURNO SET id_estado = :estado WHERE id_turno = :id_turno";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $id_estado_ausente);
        $stmt->bindParam(":id_turno", $id_turno);
        return $stmt->execute();
    }
}
?>