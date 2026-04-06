<?php
session_start();
require_once '../models/Turno.php';

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$turnoModel = new Turno();

switch ($accion) {
    case 'get_horarios_libres':
        if(isset($_GET['id_profesional']) && isset($_GET['fecha'])) {
            $horarios = $turnoModel->obtenerHorariosDisponibles($_GET['id_profesional'], $_GET['fecha']);
            echo json_encode($horarios);
        }
        break;
        
    case 'get_profesionales':
        // Devuelve la lista de médicos para el select dependiente
        if(isset($_GET['id_especialidad'])) {
            $medicos = $turnoModel->obtenerProfesionalesPorEspecialidad($_GET['id_especialidad']);
            echo json_encode($medicos);
        }
        break;

    case 'reservar':
        // Verifica que esté logueado
        if(!isset($_SESSION['id_usuario'])) {
            echo json_encode(["status" => "error", "mensaje" => "Sesión expirada"]);
            exit;
        }

        // Recibe los datos del formulario
        if(isset($_POST['id_profesional']) && isset($_POST['fecha_hora'])) {
            $id_profesional = $_POST['id_profesional'];
            $fecha_hora = $_POST['fecha_hora'];
            $motivo = isset($_POST['motivo']) ? $_POST['motivo'] : '';
            $id_usuario = $_SESSION['id_usuario'];

            // Guarda la reserva
            if($turnoModel->registrarReserva($id_usuario, $id_profesional, $fecha_hora, $motivo)) {
                echo json_encode(["status" => "success", "mensaje" => "¡Turno reservado exitosamente!"]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "No se pudo registrar el turno."]);
            }
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Faltan datos obligatorios."]);
        }
        break;
        
    default:
        echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
        break;
}
?>