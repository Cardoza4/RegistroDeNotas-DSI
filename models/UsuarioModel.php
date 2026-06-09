<?php
class UsuarioModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrar($username, $password, $rol, $nombre, $apellido) {
        try {
            $this->conn->beginTransaction();

            $queryUser = "INSERT INTO usuarios (username, password, rol, nombre, apellido) VALUES (:username, :password, :rol, :nombre, :apellido)";
            $stmtUser = $this->conn->prepare($queryUser);
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);

            $stmtUser->bindParam(':username', $username);
            $stmtUser->bindParam(':password', $password_hashed);
            $stmtUser->bindParam(':rol', $rol);
            $stmtUser->bindParam(':nombre', $nombre);
            $stmtUser->bindParam(':apellido', $apellido);
            $stmtUser->execute();

            if ($rol === 'estudiante') {
                $queryEst = "INSERT INTO estudiantes (nie, nombre, apellido, correo) VALUES (:nie, :nombre, :apellido, :correo)";
                $stmtEst = $this->conn->prepare($queryEst);
                $correo_provisional = strtolower($username) . "@institucion.edu.sv";

                $stmtEst->bindParam(':nie', $username);
                $stmtEst->bindParam(':nombre', $nombre);
                $stmtEst->bindParam(':apellido', $apellido);
                $stmtEst->bindParam(':correo', $correo_provisional);
                $stmtEst->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function obtenerPorUsername($username) {
        $query = "SELECT * FROM usuarios WHERE username = :username LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>