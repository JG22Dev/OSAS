<?php
session_start();
require_once '../models/Plan.php';

// Barrera de seguridad estricta: Solo Admin maneja la plata
if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Administrador') {
    echo json_encode(["status" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$planModel = new Plan();

switch ($accion) {
    case 'listar':
        echo json_encode($planModel->listar());
        break;

    case 'agregar':
        if(isset($_POST['nombre']) && isset($_POST['cuota_mensual'])) {
            if($planModel->agregar($_POST['nombre'], $_POST['cuota_mensual'])) {
                echo json_encode(["status" => "success", "mensaje" => "Nuevo plan creado."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar."]);
            }
        }
        break;

    case 'editar':
        if(isset($_POST['id_plan'], $_POST['nombre'], $_POST['cuota_mensual'])) {
            if($planModel->editar($_POST['id_plan'], $_POST['nombre'], $_POST['cuota_mensual'])) {
                echo json_encode(["status" => "success", "mensaje" => "Plan actualizado correctamente."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al modificar."]);
            }
        }
        break;

    case 'eliminar':
        if(isset($_POST['id_plan'])) {
            echo json_encode($planModel->eliminar($_POST['id_plan']));
        }
        break;

    default:
        echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
        break;
}
?>