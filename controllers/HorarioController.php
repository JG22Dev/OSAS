<?php
session_start();
require_once '../models/Horario.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Profesional Medico') {
    echo json_encode(["status" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$horarioModel = new Horario();
$id_profesional = $horarioModel->obtenerIdProfesional($_SESSION['id_usuario']);

if (!$id_profesional) {
    echo json_encode(["status" => "error", "mensaje" => "No se encontró el perfil profesional."]);
    exit;
}

switch ($accion) {
    case 'listar_sedes':
        echo json_encode($horarioModel->obtenerSedes());
        break;

    case 'listar':
        echo json_encode($horarioModel->listarHorarios($id_profesional));
        break;

    case 'registrar':
        if(isset($_POST['dia_semana'], $_POST['hora_inicio'], $_POST['hora_fin'], $_POST['id_sede'])) {
            $dia = $_POST['dia_semana'];
            $inicio = $_POST['hora_inicio'];
            $fin = $_POST['hora_fin'];
            $id_sede = $_POST['id_sede'];

            // Validación 1: Lógica temporal básica
            if(strtotime($inicio) >= strtotime($fin)) {
                echo json_encode(["status" => "error", "mensaje" => "La hora de fin debe ser posterior a la de inicio."]);
                exit;
            }

            // Validación 2: Algoritmo Anti-Teletransportación y superposición
            $validacion = $horarioModel->validarDisponibilidad($id_profesional, $dia, $inicio, $fin, $id_sede);
            
            if (!$validacion['valido']) {
                echo json_encode(["status" => "error", "mensaje" => $validacion['mensaje']]);
                exit;
            }

            // Si pasa todo, guardamos
            if($horarioModel->registrarHorario($id_profesional, $dia, $inicio, $fin, $id_sede)) {
                echo json_encode(["status" => "success", "mensaje" => "Bloque horario asignado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar en la base de datos."]);
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