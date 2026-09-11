<?php
// controllers/UsuarioController.php

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

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
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $usuario = $this->usuarioModel->obtenerPorUsername($username);

            if ($usuario && password_verify($password, $usuario['password'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['usuario_id']      = $usuario['id'];
                $_SESSION['username']        = $usuario['username'];
                $_SESSION['rol']             = strtolower($usuario['rol']);
                $_SESSION['nombre_completo'] = $usuario['nombre'] . " " . $usuario['apellido'];

                header("Location: index.php?action=dashboard");
                exit();
            } else {
                echo "<script>alert('Credenciales incorrectas');</script>";
            }
        }
        require_once __DIR__ . '/../views/login.php';
    }

    public function registrarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre   = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $rol      = $_POST['rol'] ?? 'estudiante';

            $resultado = $this->usuarioModel->registrar($username, $password, $rol, $nombre, $apellido);

            if ($resultado) {
                echo "<script>alert('Usuario registrado exitosamente'); window.location.href = 'index.php?action=login';</script>";
                exit();
            } else {
                echo "<script>alert('Error al registrar usuario o el usuario ya existe');</script>";
            }
        }
        require_once __DIR__ . '/../views/registro.php';
    }

    public function registrar() {
        $this->registrarUsuario();
    }

    public function cerrarSesion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        session_destroy();
        header("Location: index.php?action=login");
        exit();
    }
    public function guardarNotas() {
        if (session_status() === PHP_SESSION_NONE) { 
            session_start(); 
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Verificar si los datos llegaron en el formato esperado
            if (!isset($_POST['notas']) || empty($_POST['notas'])) {
                echo "<script>
                    alert('Atención: No se recibieron notas para procesar. Verifique los campos.'); 
                    window.location.href = 'index.php?action=notas';
                </script>";
                exit();
            }

            $materia_id = $_POST['materia_id'] ?? null;
            $periodo    = $_POST['periodo'] ?? 1;

            try {
                foreach ($_POST['notas'] as $estudiante_id => $calificaciones) {
                    $act1   = floatval($calificaciones['act1'] ?? 0);
                    $act2   = floatval($calificaciones['act2'] ?? 0);
                    $examen = floatval($calificaciones['examen'] ?? 0);

                    $this->model->guardarCalificacion(
                        $estudiante_id,
                        $materia_id,
                        $periodo,
                        $act1,
                        $act2,
                        $examen
                    );
                }

                echo "<script>
                    alert('Notas guardadas exitosamente.'); 
                    window.location.href = 'index.php?action=notas&status=success';
                </script>";
                exit();
            } catch (Exception $e) {
                die("Error al registrar las notas en la base de datos: " . $e->getMessage());
            }
        }

        header("Location: index.php?action=notas");
        exit();
    }

    public function dashboard() {
        if (session_status() === PHP_SESSION_NONE) { 
            session_start(); 
        }

        // Permitir acceso a cualquier usuario autenticado (admin, docente o estudiante)
        if (!isset($_SESSION['rol']) && !isset($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }

        require_once __DIR__ . '/../views/dashboard.php';
    }
}
?>