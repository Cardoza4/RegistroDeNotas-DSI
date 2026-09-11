<?php
// controllers/AcademicoController.php

// Detección dinámica del archivo de conexión del proyecto
$archivosConexion = [
    __DIR__ . '/../config/conexion.php',
    __DIR__ . '/../config/Conexion.php',
    __DIR__ . '/../config/db.php',
    __DIR__ . '/../config/database.php',
    __DIR__ . '/../config/Database.php',
    __DIR__ . '/../conexion.php',
    __DIR__ . '/../db.php'
];

foreach ($archivosConexion as $ruta) {
    if (file_exists($ruta)) {
        require_once $ruta;
        break;
    }
}

require_once __DIR__ . '/../models/AcademicoModel.php';

class AcademicoController {
    private $model;
    private $conn;

    public function __construct() {
        // 1. Intentar conectar mediante las clases estándar del sistema
        if (class_exists('Conexion')) {
            $conInstance = new Conexion();
            $this->conn = method_exists($conInstance, 'conectar') ? $conInstance->conectar() : (method_exists($conInstance, 'getConnection') ? $conInstance->getConnection() : null);
        } elseif (class_exists('Database')) {
            $dbInstance = new Database();
            $this->conn = method_exists($dbInstance, 'getConnection') ? $dbInstance->getConnection() : (method_exists($dbInstance, 'conectar') ? $dbInstance->conectar() : null);
        }

        // 2. Si no se detectó clase, buscar variable global existente
        if (!$this->conn) {
            global $conn, $conexion, $pdo, $db;
            $this->conn = $conn ?? $conexion ?? $pdo ?? $db ?? null;
        }

        // 3. Respaldo directo en caso de no encontrar los archivos de configuración
        if (!$this->conn) {
            try {
                $this->conn = new PDO("mysql:host=localhost;dbname=registro_academico;charset=utf8", "root", "");
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (Exception $e) {
                die("Error crítico de conexión: " . $e->getMessage());
            }
        }

        $this->model = new AcademicoModel($this->conn);
    }

    public function administrarNotas() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'docente' && $_SESSION['rol'] !== 'administrador')) {
            header("Location: index.php?action=login");
            exit();
        }

        $grado   = $_GET['grado'] ?? null;
        $seccion = $_GET['seccion'] ?? null;

        $estudiantes = $this->model->obtenerEstudiantesPorFiltro($grado, $seccion);
        $materias    = $this->model->obtenerMaterias();
        $docentes    = ($_SESSION['rol'] === 'administrador') ? $this->model->obtenerDocentes() : [];

        require_once __DIR__ . '/../views/gestion_notas.php';
    }

    public function matricula() {
        $this->administrarNotas();
    }

    public function guardarNotas() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $estudiante_id = $_POST['estudiante_id'] ?? null;
            $materia_id    = $_POST['materia_id'] ?? null;
            $periodo       = intval($_POST['periodo'] ?? 1);
            $act1          = floatval($_POST['act1'] ?? 0);
            $act2          = floatval($_POST['act2'] ?? 0);
            $examen        = floatval($_POST['examen'] ?? 0);

            if ($estudiante_id && $materia_id) {
                $resultado = $this->model->guardarCalificacion(
                    $estudiante_id,
                    $materia_id,
                    $periodo,
                    $act1,
                    $act2,
                    $examen
                );

                if ($resultado) {
                    echo "<script>alert('Calificaciones guardadas exitosamente'); window.location.href = 'index.php?action=notas';</script>";
                } else {
                    echo "<script>alert('Error al guardar las calificaciones'); window.location.href = 'index.php?action=notas';</script>";
                }
                exit();
            }
        }

        header("Location: index.php?action=notas");
        exit();
    }

    public function registrarEstudiante() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nie      = trim($_POST['nie'] ?? '');
            $nombre   = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $correo   = trim($_POST['correo'] ?? '');
            $grado    = trim($_POST['grado'] ?? '');
            $seccion  = trim($_POST['seccion'] ?? '');

            if (!empty($nie) && !empty($nombre) && !empty($apellido)) {
                $resultado = $this->model->registrarEstudianteCompleto($nie, $nombre, $apellido, $correo, $grado, $seccion);

                if ($resultado) {
                    echo "<script>alert('Estudiante matriculado con NIE: " . htmlspecialchars($nie) . "'); window.location.href = 'index.php?action=notas';</script>";
                } else {
                    echo "<script>alert('Error: El NIE ya está registrado en el sistema.'); window.location.href = 'index.php?action=notas';</script>";
                }
                exit();
            }
        }

        header("Location: index.php?action=notas");
        exit();
    }

    public function modificarEstudiante() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id       = $_POST['id_estudiante'] ?? null;
            $nombre   = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $correo   = trim($_POST['correo'] ?? '');
            $grado    = trim($_POST['grado'] ?? '');
            $seccion  = trim($_POST['seccion'] ?? '');

            if ($id) {
                $this->model->actualizarEstudiante($id, $nombre, $apellido, $correo, $grado, $seccion);
                echo "<script>alert('Expediente actualizado exitosamente'); window.location.href = 'index.php?action=notas';</script>";
                exit();
            }
        }
        header("Location: index.php?action=notas");
        exit();
    }

    public function borrarEstudiante() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        // Baja de docente (Exclusivo Administrador)
        if (isset($_GET['docente_id']) && (strtolower($_SESSION['rol'] ?? '') === 'administrador')) {
            $this->model->eliminarUsuario($_GET['docente_id']);
            echo "<script>alert('Cuenta docente eliminada exitosamente.'); window.location.href = 'index.php?action=notas&tab=docentes';</script>";
            exit();
        }

        // Baja de estudiante
        if (isset($_GET['id'])) {
            $nie = $_GET['nie'] ?? null;
            $this->model->eliminarEstudiante($_GET['id'], $nie);
            echo "<script>alert('Estudiante dado de baja exitosamente.'); window.location.href = 'index.php?action=notas';</script>";
            exit();
        }

        header("Location: index.php?action=notas");
        exit();
    }
public function registrarDocente() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'administrador') {
            header("Location: index.php?action=login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $nombre   = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!empty($username) && !empty($password) && !empty($nombre)) {
                $resultado = $this->model->registrarDocente($username, $nombre, $apellido, $password);
                if ($resultado) {
                    echo "<script>alert('Docente registrado correctamente con usuario: " . htmlspecialchars($username) . "'); window.location.href = 'index.php?action=notas&tab=docentes';</script>";
                } else {
                    echo "<script>alert('Error: El código de docente ya se encuentra registrado.'); window.location.href = 'index.php?action=notas&tab=docentes';</script>";
                }
                exit();
            }
        }

        header("Location: index.php?action=notas&tab=docentes");
        exit();
    }
    public function verBoleta() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if (!isset($_SESSION['rol'])) {
            header("Location: index.php?action=login");
            exit();
        }

        $rol = strtolower($_SESSION['rol']);
        $nie = ($rol === 'estudiante' || $rol === 'alumno') ? ($_SESSION['username'] ?? null) : ($_GET['nie'] ?? null);

        if (!$nie) {
            header("Location: index.php?action=notas");
            exit();
        }

        $boleta = $this->model->obtenerBoletaPorNIE($nie);

        // Carga directa de la vista oficial
        require __DIR__ . '/../views/boleta.php';
        exit();
    }
}
?>