<?php
require_once '../models/Especialidad.php';

// Verificamos qué acción se pide (ej: listar o registrar)
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

$especialidad = new Especialidad();

switch ($accion) {
    case 'listar':
        $resultado = $especialidad->listarTodas();
        echo json_encode($resultado);
        break;

    case 'registrar':
        // Asumimos que los datos llegan por POST
        if(isset($_POST['nombre']) && !empty($_POST['nombre'])) {
            $nombre = $_POST['nombre'];
            
            if($especialidad->registrar($nombre)) {
                echo json_encode(["status" => "success", "mensaje" => "Especialidad registrada con éxito."]);
            } else {
                echo json_encode(["status" => "error", "mensaje" => "No se pudo registrar la especialidad."]);
            }
        } else {
            echo json_encode(["status" => "error", "mensaje" => "El nombre es obligatorio."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "mensaje" => "Acción no válida."]);
        break;
}
?>