<?php
session_start(); // Iniciamos la sesión de PHP
require_once '../models/Usuario.php';

$accion = isset($_GET['accion']) ? $_GET['accion'] : '';

switch ($accion) {
    case 'login':
        if(isset($_POST['email']) && isset($_POST['password'])) {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $usuarioModel = new Usuario();
            $datosUsuario = $usuarioModel->verificarLogin($email, $password);

            if($datosUsuario) {
                if($datosUsuario['estado'] === 'activo') {
                    // Guardamos los datos en la súper variable global de sesión
                    $_SESSION['id_usuario'] = $datosUsuario['id_usuario'];
                    $_SESSION['email'] = $datosUsuario['email'];
                    $_SESSION['id_rol'] = $datosUsuario['id_rol'];
                    $_SESSION['nombre_rol'] = $datosUsuario['nombre_rol'];

                    echo json_encode(["status" => "success", "mensaje" => "Acceso concedido"]);
                } else {
                    echo json_encode(["status" => "error", "mensaje" => "Usuario inactivo o suspendido"]);
                }
            } else {
                echo json_encode(["status" => "error", "mensaje" => "Credenciales incorrectas"]);
            }
        } else {
            echo json_encode(["status" => "error", "mensaje" => "Faltan datos obligatorios"]);
        }
        break;

    case 'logout':
        session_destroy(); // Destruye la sesión
        header("Location: ../index.php"); // Lo manda de vuelta al login
        break;
}
?>