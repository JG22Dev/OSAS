<?php
require_once '../config/Conexion.php';

class Usuario {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->conectar();
    }

    public function verificarLogin($email, $password) {
        // Buscamos al usuario y traemos su ROL de paso
        $query = "SELECT u.id_usuario, u.email, u.estado, u.id_rol, r.nombre_rol 
                  FROM USUARIO u 
                  INNER JOIN ROL r ON u.id_rol = r.id_rol 
                  WHERE u.email = :email AND u.password_hash = :password";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $password); // Nota: En producción usaríamos password_verify()
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC); // Devuelve los datos del usuario
        }
        return false; // Credenciales inválidas
    }
}
?>