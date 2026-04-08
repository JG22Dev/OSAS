<?php
session_start();
require_once '../models/Recepcion.php';

// Barrera: Solo Recepción (o el Admin) pueden ver esto
if (!isset($_SESSION['id_usuario']) || !in_array($_SESSION['nombre_rol'], ['Recepcion', 'Administrador'])) {
    echo json_encode(["status" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$recepcionModel = new Recepcion();

switch ($accion) {
    case 'listar_hoy':
        // Si no mandan fechas, por defecto busca desde hoy hasta hoy
        $inicio = isset($_GET['inicio']) && !empty($_GET['inicio']) ? $_GET['inicio'] : date('Y-m-d');
        $fin = isset($_GET['fin']) && !empty($_GET['fin']) ? $_GET['fin'] : date('Y-m-d');
        
        echo json_encode($recepcionModel->listarPacientesPorFecha($inicio, $fin));
        break;

    case 'confirmar_llegada':
        if(isset($_POST['id_turno'])) {
            $id_turno = $_POST['id_turno'];

            if($recepcionModel->confirmarLlegada($id_turno)) {
                echo json_encode(["status" => "success", "mensaje" => "Paciente anunciado. El médico ya lo ve en su agenda."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al actualizar el sistema."]);
            }
        }
        break;
        case 'buscar_paciente':
        if(isset($_GET['dni'])) {
            $paciente = $recepcionModel->buscarPacientePorDNI($_GET['dni']);
            if($paciente) {
                echo json_encode(["status" => "success", "datos" => $paciente]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "No se encontró ningún paciente con ese DNI."]);
            }
        }
        break;

        case 'reservar_turno':
            if(isset($_POST['id_afiliado'], $_POST['id_profesional'], $_POST['fecha_hora'])) {
                $id_afiliado = $_POST['id_afiliado'];
                $id_profesional = $_POST['id_profesional'];
                $fecha_hora = $_POST['fecha_hora'];
                $motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : 'Turno manual (Telefónico/Presencial)';

                if($recepcionModel->registrarTurnoManual($id_afiliado, $id_profesional, $fecha_hora, $motivo)) {
                    echo json_encode(["status" => "success", "mensaje" => "¡Turno asignado exitosamente!"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Error al guardar el turno."]);
                }
            }
            break;
        default:
        echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
        break;
}
?>
