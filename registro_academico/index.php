<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/Conexion.php';
require_once __DIR__ . '/controllers/AcademicoController.php';

$controller = new AcademicoController();

$action = $_GET['action'] ?? '';
if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
}

if (empty($action)) {
    $action = isset($_SESSION['usuario_id']) ? 'dashboard' : 'login';
}

switch ($action) {
    case 'login':
        $controller->login();
        break;

    case 'dashboard':
        $controller->dashboard();
        break;

    case 'gestion_notas':
        $controller->gestionNotas();
        break;

    case 'gestion_docentes':
    case 'gestion_personal_docente':
        $controller->gestionDocentes();
        break;

    case 'actualizar_docente_gestion':
        $controller->actualizarDocenteGestion();
        break;

    case 'matricula':
    case 'mostrar_matricula':
        $controller->mostrarMatricula();
        break;

    case 'guardar_matricula':
        $controller->guardarMatricula();
        break;

    case 'guardar_nota':
        $controller->guardarNota();
        break;

    case 'boleta_notas':
        $controller->boletaNotas();
        break;

    case 'editar_estudiante':
        $controller->editarEstudiante();
        break;

    case 'actualizar_estudiante':
        $controller->actualizarEstudiante();
        break;

    case 'usuarios_lista':
    case 'directorio_perfiles':
        $controller->usuariosLista();
        break;

    case 'editar_usuario':
    case 'editar_perfil':
        $controller->editarUsuarioDirectorio();
        break;

    case 'actualizar_usuario_directorio':
        $controller->actualizarUsuarioDirectorio();
        break;

    case 'eliminar_usuario':
    case 'eliminar_perfil':
        $controller->eliminarUsuario();
        break;

    case 'registro':
    case 'registro_usuarios':
        $controller->registroUsuarios();
        break;

    case 'datos_personales':
    case 'perfil':
        $controller->datosPersonales();
        break;

    case 'actualizar_password':
    case 'cambiar_password':
        $controller->actualizarPassword();
        break;

    case 'actualizar_datos_personales':
        $controller->actualizarDatosPersonales();
        break;

    case 'actualizar_foto':
    case 'subir_foto':
        $controller->actualizarFotoPerfil();
        break;

    case 'logout':
        session_destroy();
        header('Location: index.php?action=login');
        exit;

    default:
        $controller->login();
        break;
}