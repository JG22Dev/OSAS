<?php
require_once '../config/Conexion.php';

class Turno {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    // Busca profesionales filtrados por especialidad
    public function obtenerProfesionalesPorEspecialidad($id_especialidad) {
        $query = "SELECT id_profesional, nombre, apellido FROM PROFESIONAL WHERE id_especialidad = :id_esp";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id_esp", $id_especialidad);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nueva función para obtener horarios disponibles
    public function obtenerHorariosDisponibles($id_profesional, $fecha) {
        // 1. Averiguamos qué día de la semana es la fecha (en español)
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $numeroDia = date('w', strtotime($fecha));
        $dia_semana = $dias[$numeroDia];

        // 2. Buscamos si el médico atiende ese día
        $queryHorario = "SELECT hora_inicio, hora_fin FROM HORARIO_ATENCION 
                         WHERE id_profesional = :id_prof AND dia_semana = :dia";
        $stmtH = $this->conn->prepare($queryHorario);
        $stmtH->bindParam(":id_prof", $id_profesional);
        $stmtH->bindParam(":dia", $dia_semana);
        $stmtH->execute();
        $horarioLaboral = $stmtH->fetch(PDO::FETCH_ASSOC);

        // Si no atiende ese día, devolvemos un array vacío
        if (!$horarioLaboral) {
            return [];
        }

        // 3. Buscamos los turnos que YA ESTÁN OCUPADOS ese día para ese médico
        $queryOcupados = "SELECT TIME(fecha_hora) as hora_ocupada FROM TURNO 
                          WHERE id_profesional = :id_prof AND DATE(fecha_hora) = :fecha 
                          AND id_estado IN (2, 3)"; // Reservado o Confirmado
        $stmtO = $this->conn->prepare($queryOcupados);
        $stmtO->bindParam(":id_prof", $id_profesional);
        $stmtO->bindParam(":fecha", $fecha);
        $stmtO->execute();
        $ocupados = $stmtO->fetchAll(PDO::FETCH_COLUMN); // Trae solo un array simple con las horas

        // 4. Armamos los bloques de 30 minutos
        $disponibles = [];
        $inicio = strtotime($horarioLaboral['hora_inicio']);
        $fin = strtotime($horarioLaboral['hora_fin']);

        while ($inicio < $fin) {
            $hora_str = date('H:i:s', $inicio);
            
            // Si la hora generada NO está en la lista de ocupados, la ofrecemos
            if (!in_array($hora_str, $ocupados)) {
                $disponibles[] = date('H:i', $inicio); // Formato lindo "09:30"
            }
            
            // Sumamos 30 minutos para la siguiente iteración
            $inicio = strtotime('+30 minutes', $inicio);
        }

        return $disponibles;
    }


    // Guarda el turno en la BD
    public function registrarReserva($id_usuario, $id_profesional, $fecha_hora, $motivo) {
        try {
            // 1. Necesitamos saber cuál es el ID de Afiliado de este Usuario
            $queryAfiliado = "SELECT id_afiliado FROM AFILIADO WHERE id_usuario = :id_user";
            $stmtAfi = $this->conn->prepare($queryAfiliado);
            $stmtAfi->bindParam(":id_user", $id_usuario);
            $stmtAfi->execute();
            $afiliado = $stmtAfi->fetch(PDO::FETCH_ASSOC);

            if(!$afiliado) return false; // Si no es un afiliado, cortamos acá.
            $id_afiliado = $afiliado['id_afiliado'];

            // 2. Insertamos el turno (Estado 2 = 'Reservado')
            $id_estado = 2; 
            $query = "INSERT INTO TURNO (fecha_hora, motivo_consulta, id_profesional, id_afiliado, id_estado) 
                      VALUES (:fecha, :motivo, :id_prof, :id_afi, :id_estado)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":fecha", $fecha_hora);
            $stmt->bindParam(":motivo", $motivo);
            $stmt->bindParam(":id_prof", $id_profesional);
            $stmt->bindParam(":id_afi", $id_afiliado);
            $stmt->bindParam(":id_estado", $id_estado);

            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>