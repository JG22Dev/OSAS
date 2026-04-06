<?php
session_start();
require_once '../models/Credencial.php';

// Barrera: Solo los afiliados tienen credencial
if (!isset($_SESSION['id_usuario']) || $_SESSION['nombre_rol'] !== 'Afiliado') {
    echo json_encode(["status" => "error", "mensaje" => "Acceso denegado"]);
    exit;
}

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$credencialModel = new Credencial();

switch ($accion) {
    case 'obtener_mi_credencial':
        $id_usuario = $_SESSION['id_usuario'];
        
        // 1. Buscamos los datos personales
        $datos = $credencialModel->obtenerDatosAfiliado($id_usuario);
        
        if ($datos) {
            // 2. Si existe, buscamos los beneficios de SU plan específico
            $beneficios = $credencialModel->obtenerBeneficios($datos['id_plan']);
            
            // Empaquetamos todo junto
            echo json_encode([
                "status" => "success",
                "datos" => $datos,
                "beneficios" => $beneficios
            ]);
        } else {
            echo json_encode(["status" => "error", "mensaje" => "No se encontró el perfil de afiliado."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "mensaje" => "Acción no válida"]);
        break;
}
?>