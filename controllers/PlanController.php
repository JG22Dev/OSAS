<?php
session_start();
require_once '../models/Plan.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Administrador') {
    echo json_encode(["status" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$planModel = new Plan();

switch ($accion) {
    case 'listar_todo':
        // Buscamos los 5 planes
        $planes = $planModel->obtenerPlanes();
        
        // A cada plan le inyectamos su array de beneficios
        foreach ($planes as $key => $plan) {
            $beneficios = $planModel->obtenerBeneficiosPorPlan($plan['id_plan']);
            $planes[$key]['beneficios'] = $beneficios;
        }
        
        echo json_encode($planes);
        break;

    case 'agregar_beneficio':
        if(isset($_POST['id_plan'], $_POST['descripcion']) && !empty($_POST['descripcion'])) {
            $id_plan = $_POST['id_plan'];
            $descripcion = trim($_POST['descripcion']);

            if($planModel->agregarBeneficioAPlan($id_plan, $descripcion)) {
                echo json_encode(["status" => "success", "mensaje" => "Beneficio agregado exitosamente al plan."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Hubo un error al guardar el beneficio."]);
            }
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Faltan datos."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
        break;
}
?>