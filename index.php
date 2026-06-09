<?php
// =========================================================================
// index.php - Enrutador Central (Front Controller) - INCA NOTES
// =========================================================================

// 1. Configuración de seguridad y manejo de sesiones
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Importación de los Controladores del Sistema
require_once 'controllers/UsuarioController.php';
require_once 'controllers/AcademicoController.php';

// 3. Instanciación de los objetos controladores
$usuarioController = new UsuarioController();
$academicoController = new AcademicoController();

// 4. Capturar la acción solicitada (por defecto carga el dashboard o login)
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';

// 5. Sistema de Enrutamiento (Matriz de Decisiones del MVP)
switch ($action) {
    
    // ==========================================
    // SECCIÓN 1: AUTENTICACIÓN Y ACCESOS
    // ==========================================
    case 'login':
        $usuarioController->iniciarSesion();
        break;

    case 'logout':
        $usuarioController->cerrarSesion();
        break;

    case 'registro':
        $usuarioController->registrarUsuario();
        break;

    case 'dashboard':
        if (!isset($_SESSION['rol'])) {
            header("Location: index.php?action=login");
            exit();
        }
        
        // Redirección inteligente según el rol del usuario logueado
        if ($_SESSION['rol'] === 'estudiante') {
            // El rol estudiante carga directamente su historial académico (boleta)
            $academicoController->verBoletaNotas();
        } else if ($_SESSION['rol'] === 'docente' || $_SESSION['rol'] === 'administrador') {
            // NUEVO NUEVO: Envía a Docentes y Administradores al Menú Principal de Tarjetas (views/dashboard.php)
            require_once 'views/dashboard.php';
        } else {
            header("Location: index.php?action=login");
        }
        break;

    // ==========================================
    // SECCIÓN 2: CONTROL ACADÉMICO Y NOTAS
    // ==========================================
    case 'notes':
    case 'notas':
        // Carga la interfaz de control de matrícula o personal docente según rol
        $academicoController->administrarNotas();
        break;

    case 'guardar_notes': 
    case 'guardar_notas':
        // Procesa las calificaciones introducidas por el docente (35%, 35%, 30%)
        $academicoController->procesarNotas();
        break;

    // ==========================================
    // SECCIÓN 3: GESTIÓN DE MATRÍCULA Y PERSONAL (CRUD)
    // ==========================================
    case 'registrar_estudiante':
        // Registro inteligente de alumnos (inmune a duplicados)
        $academicoController->registrarEstudianteManual();
        break;

    case 'modificar_estudiante':
        // Procesa la edición explícita desde el modal de lápiz amarillo
        $academicoController->modificarEstudiante();
        break;

    case 'borrar_estudiante':
        // Baja definitiva coordinada (Funciona para Alumnos o Docentes según parámetros)
        $academicoController->borrarEstudiante();
        break;

    // ==========================================
    // SECCIÓN 4: REPORTES Y BOLETAS
    // ==========================================
    case 'ver_boleta':
        $academicoController->verBoletaNotas();
        break;

    case 'imprimir_boleta':
        $academicoController->imprimirBoleta();
        break;

    // ==========================================
    // MANEJO DE EXCEPCIONES: RUTA POR DEFECTO
    // ==========================================
    default:
        header("Location: index.php?action=dashboard");
        break;
}
?>