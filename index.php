<?php
<<<<<<< Updated upstream
// index.php

require_once __DIR__ . '/controllers/UsuarioController.php';
require_once __DIR__ . '/controllers/AcademicoController.php';
=======
// Configuración de reporte de errores
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================================================================
// PBI-54 (CRITERIO 2): VALIDACIÓN ESTRICTA Y DESTRUCCIÓN DE SESIÓN EN BACKEND
// =========================================================================
$tiempoLimiteInactividad = 300; // 5 minutos (300 segundos)
$action = $_GET['action'] ?? $_POST['action'] ?? 'login';

if (isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempoTranscurrido = time() - $_SESSION['ultimo_acceso'];
        if ($tiempoTranscurrido > $tiempoLimiteInactividad) {
            // Destrucción integral de la sesión e invalidación de cookie
            session_unset();
            session_destroy();
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            header('Location: index.php?action=login&timeout=1');
            exit;
        }
    }
    // Actualizar marca temporal en cada solicitud activa válida
    $_SESSION['ultimo_acceso'] = time();
}

// Carga del controlador principal
$controllerPath = __DIR__ . '/controllers/AcademicoController.php';
if (!file_exists($controllerPath)) {
    die("Error crítico: No se encontró el controlador en controllers/AcademicoController.php");
}
require_once $controllerPath;
>>>>>>> Stashed changes

$action = $_POST['action'] ?? $_GET['action'] ?? 'login';

<<<<<<< Updated upstream
$usuarioCtrl = new UsuarioController();
$academicoCtrl = new AcademicoController();
=======
// =========================================================================
// PBI-54 (CRITERIO 1): MONITOREO INTERACTIVO GLOBAL EN CLIENTE (FRONTEND)
// Se registra al finalizar la carga de cualquier vista autenticada
// =========================================================================
if (isset($_SESSION['usuario_id']) && !in_array($action, ['login', 'logout'])) {
    register_shutdown_function(function() {
        // Solo inyectar en respuestas de vistas HTML (no en redirecciones puras)
        if (!headers_sent() && http_response_code() >= 300 && http_response_code() < 400) {
            return;
        }
        ?>
        <script>
        (function() {
            const TIEMPO_LIMITE_MS = 300000; // 5 minutos (300,000 ms)
            let temporizadorInactividad;

            function expulsarPorInactividad() {
                window.location.href = 'index.php?action=logout&timeout=1';
            }

            function reiniciarTemporizador() {
                clearTimeout(temporizadorInactividad);
                temporizadorInactividad = setTimeout(expulsarPorInactividad, TIEMPO_LIMITE_MS);
            }

            // Eventos físicos que reinician el contador a cero
            const eventosActividad = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
            eventosActividad.forEach(function(evento) {
                document.addEventListener(evento, reiniciarTemporizador, { passive: true });
            });

            // Iniciar monitoreo al cargar la vista
            reiniciarTemporizador();
        })();
        </script>
        <?php
    });
}
>>>>>>> Stashed changes

// Enrutador del sistema
switch ($action) {

    // --- AUTENTICACIÓN Y DASHBOARD ---
    case 'login':
<<<<<<< Updated upstream
        $usuarioCtrl->iniciarSesion();
=======
        $controller->login();
        // Si acaba de iniciar sesión con éxito, inicializar marca de tiempo
        if (isset($_SESSION['usuario_id'])) {
            $_SESSION['ultimo_acceso'] = time();
        }
        break;

    case 'logout':
        $esTimeout = isset($_GET['timeout']) && $_GET['timeout'] == '1';
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
        }
        if ($esTimeout) {
            header('Location: index.php?action=login&timeout=1');
        } else {
            header('Location: index.php?action=login');
        }
        exit;

    case 'dashboard':
        $controller->dashboard();
        break;

    // --- GESTIÓN DE CALIFICACIONES (PBI-12, PBI-15, PBI-39, PBI-44) ---
    case 'gestion_notas':
    case 'control_notas':
        $controller->gestionNotas();
        break;

    case 'guardar_nota':
        $controller->guardarNota();
        break;

    // PBI-39: Ruta explícita para registrar y sustituir notas de recuperación
    case 'guardar_recuperacion':
        $controller->guardarRecuperacion();
        break;

    // PBI-15: Ruta directa para anulación segura de registros con auditoría
    case 'anular_nota':
        $controller->anularNota();
        break;

    // PBI-46: Cuadro de Honor y Ranking Automático por Sección
    case 'cuadro_honor':
        $controller->cuadroHonor();
        break;

    case 'boleta_notas':
        $controller->boletaNotas();
        break;

    // --- GESTIÓN DE PERSONAL DOCENTE (ADMINISTRADOR) ---
    case 'gestion_docentes':
        $controller->gestionDocentes();
        break;

    case 'actualizar_docente_gestion':
        $controller->actualizarDocenteGestion();
        break;

    // --- MATRÍCULA Y EXPEDIENTES ESTUDIANTILES ---
    case 'matricula':
    case 'mostrar_matricula':
        $controller->mostrarMatricula();
        break;

    case 'guardar_matricula':
        $controller->guardarMatricula();
        break;

    case 'editar_estudiante':
        $controller->editarEstudiante();
        break;

    case 'actualizar_estudiante':
        $controller->actualizarEstudiante();
        break;

    // --- DIRECTORIO DE USUARIOS ---
    case 'usuarios_lista':
    case 'directorio_usuarios':
        $controller->usuariosLista();
        break;

    case 'actualizar_usuario_directorio':
        $controller->actualizarUsuarioDirectorio();
        break;

    case 'eliminar_usuario':
        $controller->eliminarUsuario();
>>>>>>> Stashed changes
        break;

    case 'registro':
    case 'registrar':
    case 'registrarUsuario':
        $usuarioCtrl->registrarUsuario();
        break;

<<<<<<< Updated upstream
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
=======
    // --- PERFIL Y CONFIGURACIÓN PERSONAL ---
    case 'datos_personales':
    case 'perfil':
        $controller->datosPersonales();
        break;

    case 'actualizar_datos_personales':
        $controller->actualizarDatosPersonales();
        break;

    case 'actualizar_foto':
    case 'actualizar_foto_perfil':
        $controller->actualizarFotoPerfil();
        break;

    case 'actualizar_password':
        $controller->actualizarPassword();
        break;

    // --- RUTA POR DEFECTO ---
    default:
        if (isset($_SESSION['usuario_id'])) {
            header('Location: index.php?action=dashboard');
        } else {
            header('Location: index.php?action=login');
        }
        exit;
}
>>>>>>> Stashed changes
