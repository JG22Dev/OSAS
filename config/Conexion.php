<?php
class Conexion {
    // Cambiá estos valores según tu entorno local (XAMPP, WAMP, etc.)
    private $host = "localhost";
    private $db_name = "prepaga_medica"; // El nombre que le hayas puesto a tu BD
    private $username = "root";
    private $password = ""; 
    public $conn;

    public function conectar() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8", 
                $this->username, 
                $this->password
            );
            // Configuramos PDO para que lance excepciones si hay errores
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>