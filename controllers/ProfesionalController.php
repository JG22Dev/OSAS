<?php
session_start();
require_once '../models/Profesional.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Administrador') {
    echo json_encode(["status" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$profesionalModel = new Profesional();

switch ($accion) {
    case 'listar_especialidades':
        echo json_encode($profesionalModel->obtenerEspecialidades());
        break;

    case 'listar_profesionales':
        echo json_encode($profesionalModel->listarProfesionales());
        break;

    case 'registrar':
        if(isset($_POST['nombre'], $_POST['apellido'], $_POST['matricula'], $_POST['email'], $_POST['password'], $_POST['id_especialidad'])) {
            if($profesionalModel->registrarProfesional($_POST['nombre'], $_POST['apellido'], $_POST['matricula'], $_POST['email'], $_POST['password'], $_POST['id_especialidad'])) {
                echo json_encode(["status" => "success", "mensaje" => "¡Médico registrado correctamente!"]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar. Verificá que el email o matrícula no estén repetidos."]);
            }
        }
        break;

    // NUEVO: Controlador para Editar
    case 'editar':
        if(isset($_POST['id_profesional'], $_POST['nombre'], $_POST['apellido'], $_POST['matricula'], $_POST['id_especialidad'])) {
            if($profesionalModel->editarProfesional($_POST['id_profesional'], $_POST['nombre'], $_POST['apellido'], $_POST['matricula'], $_POST['id_especialidad'])) {
                echo json_encode(["status" => "success", "mensaje" => "Datos actualizados correctamente."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Hubo un error al actualizar los datos."]);
            }
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Faltan datos para editar."]);
        }
        break;

    // NUEVO: Controlador para Baja Lógica
    case 'cambiar_estado':
        if(isset($_POST['id_usuario'], $_POST['estado'])) {
            if($profesionalModel->cambiarEstadoUsuario($_POST['id_usuario'], $_POST['estado'])) {
                $mensaje = $_POST['estado'] === 'activo' ? "Médico reactivado." : "Médico suspendido (Baja Lógica).";
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