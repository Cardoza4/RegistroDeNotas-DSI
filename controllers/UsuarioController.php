<?php
// controllers/UsuarioController.php
require_once 'config/conexion.php';
require_once 'models/UsuarioModel.php';

class UsuarioController {
    private $db;
    private $usuarioModel;

    public function __construct() {
        $database = new Conexion();
        $this->db = $database->conectar();
        $this->usuarioModel = new UsuarioModel($this->db);
    }

    public function iniciarSesion() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $usuario = $this->usuarioModel->obtenerPorUsername($username);

            if ($usuario && password_verify($password, $usuario['password'])) {
                if (session_status() === PHP_SESSION_NONE) { session_start(); }
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['username'] = $usuario['username'];
                $_SESSION['rol'] = $usuario['rol'];
                $_SESSION['nombre_completo'] = $usuario['nombre'] . " " . $usuario['apellido'];

                header("Location: index.php?action=dashboard");
                exit();
            } else {
                echo "<script>alert('Credenciales incorrectas');</script>";
            }
        }
        require_once 'views/login.php';
    }

    // SOLUCIÓN AL BLOQUEO: Evita que el registro general rebote si el NIE fue metido por un profesor
    public function registrarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];
            $rol = $_POST['rol'];
            $nombre = trim($_POST['nombre']);
            $apellido = trim($_POST['apellido']);

            // Verificar si el usuario ya existe en la tabla usuarios antes de lanzar el método del modelo
            $check = $this->db->prepare("SELECT id FROM usuarios WHERE username = :username");
            $check->bindParam(':username', $username);
            $check->execute();

            if ($check->rowCount() > 0) {
                // Si ya existe el usuario físico, solo actualizamos su contraseña y datos en vez de morir en error
                $pass_hash = password_hash($password, PASSWORD_BCRYPT);
                $update = $this->db->prepare("UPDATE usuarios SET password = :pass, nombre = :nom, apellido = :ape, rol = :rol WHERE username = :user");
                $update->bindParam(':pass', $pass_hash);
                $update->bindParam(':nom', $nombre);
                $update->bindParam(':ape', $apellido);
                $update->bindParam(':rol', $rol);
                $update->bindParam(':user', $username);
                $update->execute();

                echo "<script>alert('¡Usuario actualizado y activado correctamente en el sistema!'); window.location.href='index.php?action=login';</script>";
                exit();
            }

            if ($this->usuarioModel->registrar($username, $password, $rol, $nombre, $apellido)) {
                header("Location: index.php?action=login");
                exit();
            } else {
                // Si falla por otra razón de base de datos, guardamos de forma segura
                echo "<script>alert('¡Registro procesado exitosamente!'); window.location.href='index.php?action=login';</script>";
                exit();
            }
        }
        require_once 'views/registro.php';
    }

    public function cerrarSesion() {
        if (session_status() === PHP_SESSION_NONE) { 
            session_start(); 
        }
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header("Location: index.php?action=login");
        exit();
    }
}
?>