<?php
require_once '../config/Conexion.php';

class Profesional {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    // Traemos las especialidades para armar el <select> en el formulario
    public function obtenerEspecialidades() {
        $query = "SELECT id_especialidad, nombre FROM ESPECIALIDAD ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listamos a los médicos para la tabla, haciendo un JOIN para traer el nombre de la especialidad y el email
    public function listarProfesionales() {
        $query = "SELECT p.id_profesional, p.nombre, p.apellido, p.matricula, e.nombre as especialidad, u.email 
                  FROM PROFESIONAL p
                  INNER JOIN ESPECIALIDAD e ON p.id_especialidad = e.id_especialidad
                  INNER JOIN USUARIO u ON p.id_usuario = u.id_usuario
                  ORDER BY p.apellido ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // El Alta Doble con Transacción
    public function registrarProfesional($nombre, $apellido, $matricula, $email, $password, $id_especialidad) {
        try {
            // INICIAMOS LA TRANSACCIÓN: Todo o nada
            $this->conn->beginTransaction();

            // 1. Insertamos en la tabla USUARIO (id_rol = 3 es el Médico)
            $id_rol_medico = 3;
            $estado = 'activo';
            $queryUsuario = "INSERT INTO USUARIO (email, password_hash, estado, id_rol) 
                             VALUES (:email, :password, :estado, :id_rol)";
            $stmtU = $this->conn->prepare($queryUsuario);
            $stmtU->bindParam(":email", $email);
            // Nota: Mantenemos el password en texto plano para las pruebas, en producción usar password_hash()
            $stmtU->bindParam(":password", $password); 
            $stmtU->bindParam(":estado", $estado);
            $stmtU->bindParam(":id_rol", $id_rol_medico);
            $stmtU->execute();

            // Capturamos el ID del usuario recién creado
            $id_usuario_nuevo = $this->conn->lastInsertId();

            // 2. Insertamos en la tabla PROFESIONAL
            $queryProf = "INSERT INTO PROFESIONAL (nombre, apellido, matricula, id_usuario, id_especialidad) 
                          VALUES (:nombre, :apellido, :matricula, :id_usuario, :id_especialidad)";
            $stmtP = $this->conn->prepare($queryProf);
            $stmtP->bindParam(":nombre", $nombre);
            $stmtP->bindParam(":apellido", $apellido);
            $stmtP->bindParam(":matricula", $matricula);
            $stmtP->bindParam(":id_usuario", $id_usuario_nuevo);
            $stmtP->bindParam(":id_especialidad", $id_especialidad);
            $stmtP->execute();

            // SI TODO SALIÓ BIEN, CONFIRMAMOS LOS CAMBIOS
            $this->conn->commit();
            return true;

        } catch(PDOException $e) {
            // SI ALGO FALLÓ, DESHACEMOS TODO PARA NO DEJAR DATOS ROTOS
            $this->conn->rollBack();
            // Retornamos falso (en un sistema real acá podríamos guardar $e->getMessage() en un log de errores)
            return false; 
        }
    }
}
?>