<?php
// controllers/AcademicoController.php
require_once 'config/conexion.php';
require_once 'models/AcademicoModel.php';

class AcademicoController {
    private $db;
    private $model;

    public function __construct() {
        $database = new Conexion();
        $this->db = $database->conectar();
        $this->model = new AcademicoModel($this->db);
    }

    // Cargar listas concurrentes para dar soporte dual al Administrador
    public function administrarNotas() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'docente' && $_SESSION['rol'] !== 'administrador')) {
            header("Location: index.php?action=login");
            exit();
        }

        $grado_filtro = isset($_GET['grado']) ? $_GET['grado'] : null;
        $seccion_filtro = isset($_GET['seccion']) ? $_GET['seccion'] : null;
        $termino = isset($_POST['buscar']) ? $_POST['buscar'] : '';
        
        // Ambos roles cargan siempre la gestión de alumnos
        $estudiantes = !empty($termino) ? $this->model->buscarEstudiantes($termino) : $this->model->obtenerTodosEstudiantes($grado_filtro, $seccion_filtro);
        $materias = $this->model->obtenerMaterias();

        // Si es administrador, cargamos ADEMÁS el listado de docentes para la pestaña de control técnico
        $docentes = [];
        if ($_SESSION['rol'] === 'administrador') {
            $docentes = $this->model->obtenerTodosDocentes();
        }

        require_once 'views/gestion_notas.php';
    }

    public function procesarNotas() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $estudiante_id = $_POST['estudiante_id'];
            $materia_id = $_POST['materia_id'];
            $act1 = floatval($_POST['act1']);
            $act2 = floatval($_POST['act2']);
            $examen = floatval($_POST['examen']);

            $this->model->guardarNotasMateria($estudiante_id, $materia_id, $act1, $act2, $examen);
            header("Location: index.php?action=notas");
        }
    }

    public function registrarEstudianteManual() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nie = trim($_POST['nie']);
            $nombre = trim($_POST['nombre']);
            $apellido = trim($_POST['apellido']);
            $correo = trim($_POST['correo']);
            $grado = $_POST['grado'];
            $seccion = $_POST['seccion'];

            try {
                $checkUser = $this->db->prepare("SELECT id, rol FROM usuarios WHERE username = :username");
                $checkUser->bindParam(':username', $nie);
                $checkUser->execute();
                $usuarioExistente = $checkUser->fetch(PDO::FETCH_ASSOC);

                if ($usuarioExistente) {
                    if ($usuarioExistente['rol'] !== 'estudiante') {
                        $updateUser = $this->db->prepare("UPDATE usuarios SET rol = 'estudiante', nombre = :nombre, apellido = :apellido WHERE username = :username");
                        $updateUser->bindParam(':username', $nie);
                        $updateUser->bindParam(':nombre', $nombre);
                        $updateUser->bindParam(':apellido', $apellido);
                        $updateUser->execute();
                    }
                } else {
                    $queryUser = "INSERT INTO usuarios (username, password, rol, nombre, apellido) VALUES (:username, :password, 'estudiante', :nombre, :apellido)";
                    $stmtUser = $this->db->prepare($queryUser);
                    $pass_hash = password_hash($nie, PASSWORD_BCRYPT);
                    $stmtUser->bindParam(':username', $nie);
                    $stmtUser->bindParam(':password', $pass_hash);
                    $stmtUser->bindParam(':nombre', $nombre);
                    $stmtUser->bindParam(':apellido', $apellido);
                    $stmtUser->execute();
                }

                $checkEst = $this->db->prepare("SELECT id FROM estudiantes WHERE nie = :nie");
                $checkEst->bindParam(':nie', $nie);
                $checkEst->execute();

                if ($checkEst->rowCount() > 0) {
                    $updateEst = $this->db->prepare("UPDATE estudiantes SET nombre = :nombre, apellido = :apellido, correo = :correo, grado = :grado, seccion = :seccion WHERE nie = :nie");
                    $updateEst->bindParam(':nie', $nie);
                    $updateEst->bindParam(':nombre', $nombre);
                    $updateEst->bindParam(':apellido', $apellido);
                    $updateEst->bindParam(':correo', $correo);
                    $updateEst->bindParam(':grado', $grado);
                    $updateEst->bindParam(':seccion', $seccion);
                    $updateEst->execute();

                    echo "<script>alert('¡Perfil vinculado con éxito!'); window.location.href='index.php?action=notas';</script>";
                    exit();
                } else {
                    if ($this->model->insertarEstudianteManual($nie, $nombre, $apellido, $correo, $grado, $seccion)) {
                        echo "<script>alert('¡Estudiante registrado con éxito!'); window.location.href='index.php?action=notas';</script>";
                        exit();
                    }
                }
            } catch (Exception $e) {
                echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='index.php?action=notas';</script>";
                exit();
            }
        }
    }

    public function modificarEstudiante() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id_estudiante'];
            $nie = trim($_POST['nie']);
            $nombre = trim($_POST['nombre']);
            $apellido = trim($_POST['apellido']);
            $correo = trim($_POST['correo']);
            $grado = $_POST['grado'];
            $seccion = $_POST['seccion'];

            if ($this->model->actualizarDatosEstudiante($id, $nie, $nombre, $apellido, $correo, $grado, $seccion)) {
                echo "<script>alert('¡Datos actualizados correctamente!'); window.location.href='index.php?action=notas';</script>";
            } else {
                echo "<script>alert('Error al actualizar.'); window.location.href='index.php?action=notas';</script>";
            }
            exit();
        }
    }

    public function borrarEstudiante() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        
        if (isset($_GET['docente_id'])) {
            $id_docente = $_GET['docente_id'];
            $this->model->eliminarDocenteCompleto($id_docente);
            header("Location: index.php?action=notas");
            exit();
        }

        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $nie = isset($_GET['nie']) ? $_GET['nie'] : null;
        
        if ($id && $nie) {
            $this->model->eliminarEstudianteCompleto($id, $nie);
        }
        header("Location: index.php?action=notas");
    }

    public function verBoletaNotas() {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $nie = $_SESSION['username']; 
        $registros = $this->model->obtenerBoletaCompleta($nie);
        require_once 'views/boleta_notas.php';
    }

    public function imprimirBoleta() {
        $nie = isset($_GET['nie']) ? $_GET['nie'] : '';
        $registros = $this->model->obtenerBoletaCompleta($nie);
        require_once 'views/imprimir_boleta.php';
    }
}
?>