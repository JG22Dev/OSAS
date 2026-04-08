<?php
require_once '../config/Conexion.php';

class Turno {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    // 1. ACÁ ESTÁ LA FUNCIÓN DE SEDES
    public function obtenerSedes() {
        try {
            $query = "SELECT id_sede, nombre, direccion FROM SEDE WHERE estado = 'activo' ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return []; 
        }
    }

    // 2. ACÁ ESTÁ LA FUNCIÓN QUE TE MARCABA COMO NO ENCONTRADA (Línea 22 del error)
    public function obtenerEspecialidadesPorSede($id_sede) {
        try {
            $query = "SELECT e.id_especialidad, e.nombre 
                      FROM ESPECIALIDAD e
                      INNER JOIN SEDE_ESPECIALIDAD se ON e.id_especialidad = se.id_especialidad
                      WHERE se.id_sede = :id_sede
                      ORDER BY e.nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_sede", $id_sede);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // 3. ACÁ ESTÁ LA OTRA FUNCIÓN QUE TE MARCABA COMO NO ENCONTRADA (Línea 30 del error)
    public function obtenerProfesionalesFiltrados($id_sede, $id_especialidad) {
        try {
            $query = "SELECT DISTINCT p.id_profesional, p.nombre, p.apellido 
                      FROM PROFESIONAL p
                      INNER JOIN USUARIO u ON p.id_usuario = u.id_usuario
                      INNER JOIN HORARIO_ATENCION h ON p.id_profesional = h.id_profesional
                      WHERE p.id_especialidad = :id_esp 
                      AND h.id_sede = :id_sede
                      AND u.estado = 'activo'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_esp", $id_especialidad);
            $stmt->bindParam(":id_sede", $id_sede);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // --- FUNCIONES DE HORARIOS ---
    public function obtenerDiasLaborales($id_profesional) {
        try {
            $query = "SELECT dia_semana FROM HORARIO_ATENCION WHERE id_profesional = :id_prof";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_prof", $id_profesional);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN); 
        } catch (PDOException $e) { return []; }
    }

    public function obtenerHorariosDisponibles($id_profesional, $fecha) {
        try {
            $dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
            $numeroDia = date('w', strtotime($fecha));
            $dia_semana = $dias[$numeroDia];

            $queryHorario = "SELECT hora_inicio, hora_fin FROM HORARIO_ATENCION WHERE id_profesional = :id_prof AND dia_semana = :dia";
            $stmtH = $this->conn->prepare($queryHorario);
            $stmtH->bindParam(":id_prof", $id_profesional);
            $stmtH->bindParam(":dia", $dia_semana);
            $stmtH->execute();
            $horarioLaboral = $stmtH->fetch(PDO::FETCH_ASSOC);

            if (!$horarioLaboral) return [];

            $queryOcupados = "SELECT TIME(fecha_hora) as hora_ocupada FROM TURNO WHERE id_profesional = :id_prof AND DATE(fecha_hora) = :fecha AND id_estado IN (2, 3)";
            $stmtO = $this->conn->prepare($queryOcupados);
            $stmtO->bindParam(":id_prof", $id_profesional);
            $stmtO->bindParam(":fecha", $fecha);
            $stmtO->execute();
            $ocupados = $stmtO->fetchAll(PDO::FETCH_COLUMN);

            $disponibles = [];
            $inicio = strtotime($horarioLaboral['hora_inicio']);
            $fin = strtotime($horarioLaboral['hora_fin']);

            while ($inicio < $fin) {
                $hora_str = date('H:i:s', $inicio);
                if (!in_array($hora_str, $ocupados)) {
                    $disponibles[] = date('H:i', $inicio);
                }
                $inicio = strtotime('+30 minutes', $inicio);
            }
            return $disponibles;
        } catch (PDOException $e) { return []; }
    }

    // --- FUNCIONES DE REGISTRO Y GESTIÓN ---
    public function registrarReserva($id_usuario, $id_profesional, $fecha_hora, $motivo, $id_sede) {
        try {
            $queryAfiliado = "SELECT id_afiliado FROM AFILIADO WHERE id_usuario = :id_user";
            $stmtAfi = $this->conn->prepare($queryAfiliado);
            $stmtAfi->bindParam(":id_user", $id_usuario);
            $stmtAfi->execute();
            $afiliado = $stmtAfi->fetch(PDO::FETCH_ASSOC);

            if(!$afiliado) return false;
            $id_afiliado = $afiliado['id_afiliado'];

            $id_estado = 2; // Reservado
            $query = "INSERT INTO TURNO (fecha_hora, motivo_consulta, id_profesional, id_afiliado, id_estado, id_sede) 
                      VALUES (:fecha, :motivo, :id_prof, :id_afi, :id_estado, :id_sede)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":fecha", $fecha_hora);
            $stmt->bindParam(":motivo", $motivo);
            $stmt->bindParam(":id_prof", $id_profesional);
            $stmt->bindParam(":id_afi", $id_afiliado);
            $stmt->bindParam(":id_estado", $id_estado);
            $stmt->bindParam(":id_sede", $id_sede);
            
            return $stmt->execute();
        } catch(PDOException $e) { return false; }
    }

    public function listarMisTurnos($id_usuario) {
        try {
            $query = "SELECT t.id_turno, t.fecha_hora, p.nombre as med_nombre, p.apellido as med_apellido, 
                             e.nombre as especialidad, et.nombre_estado, t.id_estado, 
                             COALESCE(s.nombre, 'Sede No Asignada') as sede_nombre
                      FROM TURNO t
                      INNER JOIN PROFESIONAL p ON t.id_profesional = p.id_profesional
                      INNER JOIN ESPECIALIDAD e ON p.id_especialidad = e.id_especialidad
                      INNER JOIN ESTADO_TURNO et ON t.id_estado = et.id_estado
                      INNER JOIN AFILIADO a ON t.id_afiliado = a.id_afiliado
                      LEFT JOIN SEDE s ON t.id_sede = s.id_sede 
                      WHERE a.id_usuario = :id_user
                      ORDER BY t.fecha_hora DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_user", $id_usuario);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) { return []; }
    }

    public function cancelarTurno($id_turno, $id_usuario) {
        try {
            $query = "SELECT t.fecha_hora FROM TURNO t INNER JOIN AFILIADO a ON t.id_afiliado = a.id_afiliado 
                      WHERE t.id_turno = :id_turno AND a.id_usuario = :id_user";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id_turno", $id_turno);
            $stmt->bindParam(":id_user", $id_usuario);
            $stmt->execute();
            $turno = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$turno) return ["status" => "error", "mensaje" => "Turno no encontrado."];

            $ahora = time();
            $fecha_turno_timestamp = strtotime($turno['fecha_hora']);
            $horas_restantes = ($fecha_turno_timestamp - $ahora) / 3600;

            if ($horas_restantes < 24) {
                return ["status" => "error", "mensaje" => "Políticas de la empresa: Solo podés cancelar un turno con más de 24 horas de anticipación."];
            }

            $id_estado_cancelado = 6;
            $update = "UPDATE TURNO SET id_estado = :estado WHERE id_turno = :id_turno";
            $stmtU = $this->conn->prepare($update);
            $stmtU->bindParam(":estado", $id_estado_cancelado);
            $stmtU->bindParam(":id_turno", $id_turno);
            
            return $stmtU->execute() ? ["status" => "success", "mensaje" => "Turno cancelado exitosamente."] : ["status" => "error", "mensaje" => "Error al cancelar."];
        } catch (PDOException $e) { return ["status" => "error", "mensaje" => "Error de BD."]; }
    }
}
?>