<?php
session_start();
require_once '../models/Afiliado.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Administrador') {
    echo json_encode(["status" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$afiliadoModel = new Afiliado();

switch ($accion) {
    case 'listar_planes':
        echo json_encode($afiliadoModel->obtenerPlanes());
        break;

    case 'listar_afiliados':
        echo json_encode($afiliadoModel->listarAfiliados());
        break;

    case 'registrar':
        if(isset($_POST['nombre'], $_POST['apellido'], $_POST['dni'], $_POST['fecha_nacimiento'], $_POST['id_plan'], $_POST['email'], $_POST['password'])) {
            if($afiliadoModel->registrarAfiliado($_POST['nombre'], $_POST['apellido'], $_POST['dni'], $_POST['fecha_nacimiento'], $_POST['id_plan'], $_POST['email'], $_POST['password'])) {
                echo json_encode(["status" => "success", "mensaje" => "¡Afiliado registrado y credencial generada!"]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar. Verificá que el DNI o el Email no estén repetidos."]);
            }
        }
        break;

    case 'editar':
        if(isset($_POST['id_afiliado'], $_POST['nombre'], $_POST['apellido'], $_POST['dni'], $_POST['fecha_nacimiento'], $_POST['id_plan'])) {
            if($afiliadoModel->editarAfiliado($_POST['id_afiliado'], $_POST['nombre'], $_POST['apellido'], $_POST['dni'], $_POST['fecha_nacimiento'], $_POST['id_plan'])) {
                echo json_encode(["status" => "success", "mensaje" => "Datos del afiliado actualizados."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al actualizar. ¿El DNI ya existe?"]);
            }
        }
        break;

    case 'cambiar_estado':
        if(isset($_POST['id_usuario'], $_POST['estado'])) {
            if($afiliadoModel->cambiarEstadoUsuario($_POST['id_usuario'], $_POST['estado'])) {
                $mensaje = $_POST['estado'] === 'activo' ? "Afiliado reactivado." : "Cuenta de afiliado suspendida.";
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