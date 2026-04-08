<?php
require_once '../models/Especialidad.php';

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$especialidadModel = new Especialidad();

switch ($accion) {
    case 'listar': echo json_encode($especialidadModel->listar()); break;

    case 'agregar':
        if(isset($_POST['nombre'])) {
            if($especialidadModel->agregar($_POST['nombre'])) {
                echo json_encode(["status" => "success", "mensaje" => "Especialidad agregada."]);
            } else { echo json_encode(["status" => "error", "mensaje" => "Error o duplicada."]); }
        }
        break;

    case 'editar':
        if(isset($_POST['id_especialidad']) && isset($_POST['nombre'])) {
            if($especialidadModel->editar($_POST['id_especialidad'], $_POST['nombre'])) {
                echo json_encode(["status" => "success", "mensaje" => "Actualizado."]);
            } else { echo json_encode(["status" => "error", "mensaje" => "Error."]); }
        }
        break;

    case 'eliminar':
        if(isset($_POST['id_especialidad'])) { echo json_encode($especialidadModel->eliminar($_POST['id_especialidad'])); }
        break;

    // 🔥 NUEVAS RUTAS PARA SEDES 🔥
    case 'get_configuracion_sedes':
        if(isset($_GET['id_especialidad'])) {
            $sedes_totales = $especialidadModel->obtenerSedesDisponibles();
            $sedes_activas = $especialidadModel->obtenerSedesPorEspecialidad($_GET['id_especialidad']);
            
            echo json_encode([
                "todas" => $sedes_totales,
                "seleccionadas" => $sedes_activas
            ]);
        }
        break;

    case 'guardar_sedes':
        if(isset($_POST['id_especialidad'])) {
            // Si el admin no tildó ninguna, $_POST['sedes'] no existe, mandamos array vacío
            $sedes = isset($_POST['sedes']) ? $_POST['sedes'] : []; 
            if($especialidadModel->asignarSedes($_POST['id_especialidad'], $sedes)) {
                echo json_encode(["status" => "success", "mensaje" => "Configuración guardada."]);
            } else { echo json_encode(["status" => "error", "mensaje" => "Error al guardar."]); }
        }
        break;

    default: echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]); break;
}
?>