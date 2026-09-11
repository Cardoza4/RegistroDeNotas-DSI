<?php
// index.php

require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/AcademicoController.php';

$action = $_POST['action'] ?? $_GET['action'] ?? 'login';

$usuarioCtrl = new UsuarioController();
$academicoCtrl = new AcademicoController();

switch ($action) {
    case 'login':
        $usuarioCtrl->iniciarSesion();
        break;

    case 'registro':
    case 'registrar':
    case 'registrarUsuario':
        $usuarioCtrl->registrarUsuario();
        break;

    case 'dashboard':
        $usuarioCtrl->dashboard();
        break;

    case 'notas':
        $academicoCtrl->administrarNotas();
        break;

    case 'guardar_notas':
        $academicoCtrl->guardarNotas();
        break;

    case 'registrar_estudiante':
        $academicoCtrl->registrarEstudiante();
        break;

    case 'modificar_estudiante':
        $academicoCtrl->modificarEstudiante();
        break;

    case 'borrar_estudiante':
        $academicoCtrl->borrarEstudiante();
        break;

    case 'matricula':
    case 'alumnos':
        $academicoCtrl->matricula();
        break;

    case 'boleta':
    case 'imprimir_boleta':
        $academicoCtrl->verBoleta();
        break;

    case 'logout':
        $usuarioCtrl->cerrarSesion();
        break;

    default:
        $usuarioCtrl->iniciarSesion();
        break;

    case 'registrar_docente':
        $academicoCtrl->registrarDocente();
        break;
}
?>