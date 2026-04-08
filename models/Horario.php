<?php
require_once '../config/Conexion.php';

class Horario {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    public function obtenerIdProfesional($id_usuario) {
        $query = "SELECT id_profesional FROM PROFESIONAL WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_usuario", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado ? $resultado['id_profesional'] : false;
    }

    // Traer sedes activas para el formulario
    public function obtenerSedes() {
        $query = "SELECT id_sede, nombre FROM SEDE WHERE estado = 'activo' ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarHorarios($id_profesional) {
        $query = "SELECT h.id_horario, h.dia_semana, h.hora_inicio, h.hora_fin, s.nombre as sede_nombre 
                  FROM HORARIO_ATENCION h
                  INNER JOIN SEDE s ON h.id_sede = s.id_sede
                  WHERE h.id_profesional = :id_prof 
                  ORDER BY FIELD(h.dia_semana, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'), h.hora_inicio ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_prof", $id_profesional);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔥 ALGORITMO ANTI-TELETRANSPORTACIÓN 🔥
    public function validarDisponibilidad($id_profesional, $dia, $inicio, $fin, $id_sede_nueva) {
        $query = "SELECT hora_inicio, hora_fin, id_sede FROM HORARIO_ATENCION WHERE id_profesional = :id_prof AND dia_semana = :dia";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_prof", $id_profesional);
        $stmt->bindParam(":dia", $dia);
        $stmt->execute();
        $bloques_existentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $inicio_nuevo = strtotime($inicio);
        $fin_nuevo = strtotime($fin);

        foreach ($bloques_existentes as $b) {
            $inicio_exist = strtotime($b['hora_inicio']);
            $fin_exist = strtotime($b['hora_fin']);
            $sede_exist = $b['id_sede'];

            // 1. Regla de Superposición (Choque directo)
            if ($inicio_nuevo < $fin_exist && $fin_nuevo > $inicio_exist) {
                return ["valido" => false, "mensaje" => "El horario choca con otro bloque que ya tenés cargado de " . substr($b['hora_inicio'],0,5) . " a " . substr($b['hora_fin'],0,5) . " hs."];
            }

            // 2. Regla de Tiempo de Viaje (Si es en otra clínica)
            if ($sede_exist != $id_sede_nueva) {
                // Si el nuevo bloque va DESPUÉS del existente
                if ($inicio_nuevo >= $fin_exist) {
                    $minutos_diferencia = ($inicio_nuevo - $fin_exist) / 60;
                    if ($minutos_diferencia < 60) {
                        return ["valido" => false, "mensaje" => "Necesitás al menos 60 mins de viaje entre clínicas. Tu bloque anterior termina a las " . substr($b['hora_fin'],0,5) . " hs."];
                    }
                }
                // Si el nuevo bloque va ANTES del existente
                if ($fin_nuevo <= $inicio_exist) {
                    $minutos_diferencia = ($inicio_exist - $fin_nuevo) / 60;
                    if ($minutos_diferencia < 60) {
                        return ["valido" => false, "mensaje" => "Necesitás al menos 60 mins de viaje para llegar a tu siguiente turno a las " . substr($b['hora_inicio'],0,5) . " hs en la otra sede."];
                    }
                }
            }
        }
        return ["valido" => true]; // Pasó todos los controles
    }

    // Registrar horario (Ahora incluye id_sede)
    public function registrarHorario($id_profesional, $dia, $inicio, $fin, $id_sede) {
        $query = "INSERT INTO HORARIO_ATENCION (dia_semana, hora_inicio, hora_fin, id_profesional, id_sede) 
                  VALUES (:dia, :inicio, :fin, :id_prof, :id_sede)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":dia", $dia);
        $stmt->bindParam(":inicio", $inicio);
        $stmt->bindParam(":fin", $fin);
        $stmt->bindParam(":id_prof", $id_profesional);
        $stmt->bindParam(":id_sede", $id_sede);
        
        return $stmt->execute();
    }
}
?>