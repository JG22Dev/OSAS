<?php
session_start();
require_once '../models/Profesional.php';

// Barrera de seguridad Backend: Solo el Admin puede hacer esto
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
            
            $nombre = trim($_POST['nombre']);
            $apellido = trim($_POST['apellido']);
            $matricula = trim($_POST['matricula']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $id_especialidad = $_POST['id_especialidad'];

            // Validación básica
            if(empty($nombre) || empty($email) || empty($matricula)) {
                echo json_encode(["status" => "error", "mensaje" => "Faltan completar campos obligatorios."]);
                exit;
            }

            if($profesionalModel->registrarProfesional($nombre, $apellido, $matricula, $email, $password, $id_especialidad)) {
                echo json_encode(["status" => "success", "mensaje" => "¡Médico registrado y cuenta de acceso creada!"]);
            } else {
                // Si falla, suele ser porque el Email o la Matrícula ya existen (son campos UNIQUE en la base de datos)
                echo json_encode(["status" => "error", "mensaje" => "Error al guardar. Verificá que el email o la matrícula no estén repetidos."]);
            }
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Faltan datos obligatorios en el formulario."]);
        }
        break;
        
    default:
        echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
        break;
}
?>