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
            
            $nombre = trim($_POST['nombre']);
            $apellido = trim($_POST['apellido']);
            $dni = trim($_POST['dni']);
            $fecha_nacimiento = $_POST['fecha_nacimiento'];
            $id_plan = $_POST['id_plan'];
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            if(empty($nombre) || empty($dni) || empty($email)) {
                echo json_encode(["status" => "error", "mensaje" => "Faltan completar campos obligatorios."]);
                exit;
            }

            if($afiliadoModel->registrarAfiliado($nombre, $apellido, $dni, $fecha_nacimiento, $id_plan, $email, $password)) {
                echo json_encode(["status" => "success", "mensaje" => "¡Afiliado registrado y credencial generada!"]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar. Verificá que el DNI o el Email no estén repetidos."]);
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