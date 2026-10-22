<?php
class Conexion {
    private $host     = 'sql202.infinityfree.com';
    private $dbname   = 'if0_42887369_registroacademico';
    private $username = 'if0_42887369';
    private $password = 'incanotes215';
    private $port     = 3306;
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

 public function conectar() {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
                $this->pdo = new PDO($dsn, $this->user, $this->pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"
                ]);
            } catch (PDOException $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }
        return $this->pdo;
    }

    public static function getConnection() {
        $instancia = new self();
        return $instancia->conectar();
    }
}

// Compatibilidad por si algún script usa global $pdo
try {
    $conexionObj = new Conexion();
    $pdo = $conexionObj->conectar();
} catch (Exception $e) {
    die("Error inicializando PDO global: " . $e->getMessage());
}