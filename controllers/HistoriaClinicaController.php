<?php
session_start();
require_once '../models/HistoriaClinica.php';

// Barrera de seguridad estricta
if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Profesional Medico') {
    echo json_encode(["status" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$hcModel = new HistoriaClinica();

switch ($accion) {
    case 'listar_agenda_hoy':
        echo json_encode($hcModel->obtenerAgendaHoy($_SESSION['id_usuario']));
        break;

    case 'ver_historial':
        if(isset($_GET['id_afiliado'])) {
            echo json_encode($hcModel->obtenerHistorialPaciente($_GET['id_afiliado']));
        }
        break;

    case 'guardar_evolucion':
        if(isset($_POST['id_turno'], $_POST['diagnostico'])) {
            $tratamiento = isset($_POST['tratamiento']) ? $_POST['tratamiento'] : '';
            $receta = isset($_POST['receta']) ? $_POST['receta'] : '';
            
            if($hcModel->guardarEvolucion($_POST['id_turno'], $_POST['diagnostico'], $tratamiento, $receta)) {
                echo json_encode(["status" => "success", "mensaje" => "Evolución guardada. Turno cerrado."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar la historia clínica."]);
            }
        } else {
            echo json_encode(["status" => "error", "mensaje" => "El diagnóstico es obligatorio."]);
        }
        break;

    case 'marcar_ausente':
        if(isset($_POST['id_turno'])) {
            if($hcModel->marcarAusente($_POST['id_turno'])) {
                echo json_encode(["status" => "success", "mensaje" => "Paciente marcado como ausente."]);
            }
        }
        break;

    default:
        echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
        break;
}
?>
