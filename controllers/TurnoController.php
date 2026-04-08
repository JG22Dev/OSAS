<?php
// Modo "Chismoso" encendido para que los errores se vean y no rompan el JSON
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); 

try {
    session_start();
    require_once '../models/Turno.php';

    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';
    $turnoModel = new Turno();

    switch ($accion) {
        // --- RUTAS V2 (FILTROS EN CASCADA) ---
        case 'get_sedes':
            echo json_encode($turnoModel->obtenerSedes());
            break;

        case 'get_especialidades_sede':
            if(isset($_GET['id_sede'])) { 
                echo json_encode($turnoModel->obtenerEspecialidadesPorSede($_GET['id_sede'])); 
            } else {
                echo json_encode([]); 
            }
            break;

        case 'get_profesionales_v2':
            if(isset($_GET['id_especialidad']) && isset($_GET['id_sede'])) {
                echo json_encode($turnoModel->obtenerProfesionalesFiltrados($_GET['id_sede'], $_GET['id_especialidad']));
            } else {
                echo json_encode([]);
            }
            break;

        // --- RUTAS DE HORARIOS Y RESERVAS ---
        case 'get_dias_laborales':
            if(isset($_GET['id_profesional'])) { echo json_encode($turnoModel->obtenerDiasLaborales($_GET['id_profesional'])); }
            break;

        case 'get_horarios_libres':
            if(isset($_GET['id_profesional']) && isset($_GET['fecha'])) {
                echo json_encode($turnoModel->obtenerHorariosDisponibles($_GET['id_profesional'], $_GET['fecha']));
            }
            break;

        case 'reservar':
            if(!isset($_SESSION['id_usuario'])) { 
                echo json_encode(["status" => "error", "mensaje" => "Sesión expirada. Volvé a iniciar sesión."]); 
                exit; 
            }
            
            if(isset($_POST['id_profesional'], $_POST['fecha_hora'], $_POST['id_sede'])) {
                $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : '';
                
                if($turnoModel->registrarReserva($_SESSION['id_usuario'], $_POST['id_profesional'], $_POST['fecha_hora'], $motivo, $_POST['id_sede'])) {
                    echo json_encode(["status" => "success", "mensaje" => "¡Turno reservado exitosamente!"]);
                } else { 
                    echo json_encode(["status" => "error", "mensaje" => "No se pudo registrar el turno en la base de datos."]); 
                }
            } else { 
                echo json_encode(["status" => "error", "mensaje" => "Faltan datos obligatorios para la reserva."]); 
            }
            break;

        case 'listar_mis_turnos':
            if(isset($_SESSION['id_usuario'])) { 
                echo json_encode($turnoModel->listarMisTurnos($_SESSION['id_usuario'])); 
            }
            break;

        case 'cancelar_turno':
            if(isset($_SESSION['id_usuario']) && isset($_POST['id_turno'])) {
                echo json_encode($turnoModel->cancelarTurno($_POST['id_turno'], $_SESSION['id_usuario']));
            }
            break;
            
        default:
            echo json_encode(["status" => "error", "mensaje" => "Acción no válida solicitada al servidor."]);
            break;
    }

// ATRAPAMOS CUALQUIER FALLA FATAL PARA QUE EL JSON NO SE ROMPA
} catch (Exception $e) {
    echo json_encode(["status" => "error", "mensaje" => "Error del servidor: " . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(["status" => "error", "mensaje" => "Falla fatal del sistema: " . $e->getMessage()]);
}
?>