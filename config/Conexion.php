<?php
class Conexion {
    private $host = sql202.infinityfree.com; 
    private $user = if0_42887369_XXX;       
    private $pass = incanotes215;           
    private $db   = if0_42887369_registroacademico;        
    private $port = 3306;
    private $conexion;

    public function conectar() {
        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db . ";charset=utf8mb4";
            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conexion = new PDO($dsn, $this->user, $this->pass, $opciones);
            return $this->conexion;
        } catch (PDOException $e) {
            die("Error de conexión remota PDO: " . $e->getMessage());
        }
    }
}
?>