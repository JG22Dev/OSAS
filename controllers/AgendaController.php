<?php
session_start();
require_once '../models/Agenda.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Profesional Medico') {
    echo json_encode(["status" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$agendaModel = new Agenda();
$id_profesional = $agendaModel->obtenerIdProfesional($_SESSION['id_usuario']);

if (!$id_profesional) {
    echo json_encode(["status" => "error", "mensaje" => "Perfil profesional no encontrado."]);
    exit;
}

switch ($accion) {
    case 'listar':
        if(isset($_GET['fecha'])) {
            $turnos = $agendaModel->listarTurnosDelDia($id_profesional, $_GET['fecha']);
            echo json_encode($turnos);
        }
        break;

    case 'marcar_asistencia':
        // id_estado 4 = Asistió | id_estado 5 = Ausente
        if(isset($_POST['id_turno'], $_POST['id_estado'])) {
            $id_turno = $_POST['id_turno'];
            $id_estado = $_POST['id_estado'];

            if($agendaModel->cambiarEstadoTurno($id_turno, $id_estado)) {
                echo json_encode(["status" => "success", "mensaje" => "Estado del turno actualizado."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "No se pudo actualizar el turno."]);
            }
        }
        break;

    default:
        echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
        break;
}
?>
