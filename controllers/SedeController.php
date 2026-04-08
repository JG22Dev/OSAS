<?php
session_start();
require_once '../models/Sede.php';

// Barrera de seguridad
if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Administrador') {
    echo json_encode(["status" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$sedeModel = new Sede();

switch ($accion) {
    case 'listar':
        echo json_encode($sedeModel->listarTodas());
        break;

    case 'listar_activas': // Para los selects del futuro
        echo json_encode($sedeModel->listarActivas());
        break;

    case 'agregar':
        if(isset($_POST['nombre']) && isset($_POST['direccion'])) {
            if($sedeModel->agregar($_POST['nombre'], $_POST['direccion'])) {
                echo json_encode(["status" => "success", "mensaje" => "Clínica agregada exitosamente."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar. Verificá los datos."]);
            }
        }
        break;

    case 'editar':
        if(isset($_POST['id_sede'], $_POST['nombre'], $_POST['direccion'])) {
            if($sedeModel->editar($_POST['id_sede'], $_POST['nombre'], $_POST['direccion'])) {
                echo json_encode(["status" => "success", "mensaje" => "Datos de la sede actualizados."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Hubo un error al actualizar."]);
            }
        }
        break;

    case 'cambiar_estado':
        if(isset($_POST['id_sede'], $_POST['estado'])) {
            if($sedeModel->cambiarEstado($_POST['id_sede'], $_POST['estado'])) {
                $mensaje = $_POST['estado'] === 'activo' ? "Sede habilitada." : "Sede inhabilitada (Baja Lógica).";
                echo json_encode(["status" => "success", "mensaje" => $mensaje]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "No se pudo cambiar el estado."]);
            }
        }
        break;
        
    default:
        echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
        break;
}
?>