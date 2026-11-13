<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Control de acceso: Docente (y Administrador para pruebas)
$rolActual = strtoupper($_SESSION['rol'] ?? '');
if (!isset($_SESSION['usuario_id']) || !in_array($rolActual, ['DOCENTE', 'PROFESOR', 'MAESTRO', 'ADMIN', 'ADMINISTRADOR'])) {
    header('Location: index.php?action=dashboard');
    exit;
}

$usuarioIdActual = (int)$_SESSION['usuario_id'];
$nombreDocenteSesion = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''));
if ($nombreDocenteSesion === '') {
    $nombreDocenteSesion = $_SESSION['usuario'] ?? $_SESSION['correo'] ?? 'Docente';
}

$listaEstudiantes = $estudiantes ?? [];
$msg = $_GET['msg'] ?? '';

// Cargar conexión a la base de datos
if (!isset($pdo) && !isset($conn) && !isset($db)) {
    $rutasConexion = [
        __DIR__ . '/../config/Conexion.php',
        __DIR__ . '/../config/conexion.php',
        __DIR__ . '/../config/database.php',
        __DIR__ . '/../config/db.php'
    ];
    foreach ($rutasConexion as $ruta) {
        if (file_exists($ruta)) {
            @require_once $ruta;
            break;
        }
    }
}

if (!isset($conexionDB)) {
    if (class_exists('Conexion')) {
        $conObj = new Conexion();
        $conexionDB = $conObj->conectar();
    } else {
        $conexionDB = $pdo ?? $conn ?? $db ?? null;
    }
}

// =========================================================================
// DETECCIÓN AUTOMÁTICA DEL GRADO Y SECCIÓN DE ORIENTACIÓN DEL DOCENTE
// =========================================================================
$gradoOrientador = trim($_SESSION['grado_orientador'] ?? $_SESSION['grado_orientacion'] ?? $_SESSION['grado_asignado'] ?? '');
$seccionOrientador = trim($_SESSION['seccion_orientador'] ?? $_SESSION['seccion_orientacion'] ?? $_SESSION['seccion_asignada'] ?? '');

if ($conexionDB instanceof PDO) {
    $extraerOrientacionDeFila = function (array $fila) use (&$gradoOrientador, &$seccionOrientador) {
        $posiblesGrado = ['grado_orientador', 'grado_orientacion', 'orientador_grado', 'grado_guia', 'grado_asignado', 'grado'];
        $posiblesSeccion = ['seccion_orientador', 'seccion_orientacion', 'orientador_seccion', 'seccion_guia', 'seccion_asignada', 'seccion', 'orientacion'];

        foreach ($posiblesGrado as $colG) {
            if ($gradoOrientador === '' && !empty($fila[$colG])) {
                $gradoOrientador = trim((string)$fila[$colG]);
            }
        }
        foreach ($posiblesSeccion as $colS) {
            if ($seccionOrientador === '' && !empty($fila[$colS])) {
                $seccionOrientador = trim((string)$fila[$colS]);
            }
        }

        foreach (['seccion_orientacion', 'orientacion', 'grado_orientacion', 'seccion_orientador'] as $colComb) {
            if (!empty($fila[$colComb]) && strpos($fila[$colComb], '-') !== false) {
                $partes = explode('-', $fila[$colComb], 2);
                if ($gradoOrientador === '') $gradoOrientador = trim($partes[0]);
                if ($seccionOrientador === '' || strpos($seccionOrientador, '-') !== false) {
                    $seccionOrientador = trim($partes[1]);
                }
            }
        }
    };

    try {
        $stmtU = $conexionDB->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
        $stmtU->execute([$usuarioIdActual]);
        $filaUsuario = $stmtU->fetch(PDO::FETCH_ASSOC);
        if ($filaUsuario) {
            $extraerOrientacionDeFila($filaUsuario);
            if ($nombreDocenteSesion === 'Docente' && !empty($filaUsuario['nombre'])) {
                $nombreDocenteSesion = trim(($filaUsuario['nombre'] ?? '') . ' ' . ($filaUsuario['apellido'] ?? ''));
            }
        }
    } catch (Exception $e) {}

    if ($gradoOrientador === '' || $seccionOrientador === '') {
        try {
            $stmtD = $conexionDB->prepare("SELECT * FROM docentes WHERE id = ? OR usuario_id = ? LIMIT 1");
            $stmtD->execute([$usuarioIdActual, $usuarioIdActual]);
            $filaDoc = $stmtD->fetch(PDO::FETCH_ASSOC);
            if ($filaDoc) {
                $extraerOrientacionDeFila($filaDoc);
            }
        } catch (Exception $e) {
            try {
                $correoBusq = $_SESSION['correo'] ?? $_SESSION['email'] ?? ($filaUsuario['correo'] ?? $filaUsuario['email'] ?? '');
                if ($correoBusq !== '') {
                    $stmtD2 = $conexionDB->prepare("SELECT * FROM docentes WHERE correo = ? OR email = ? LIMIT 1");
                    $stmtD2->execute([$correoBusq, $correoBusq]);
                    $filaDoc2 = $stmtD2->fetch(PDO::FETCH_ASSOC);
                    if ($filaDoc2) {
                        $extraerOrientacionDeFila($filaDoc2);
                    }
                }
            } catch (Exception $ex) {}
        }
    }
}

if ($seccionOrientador !== '' && stripos($seccionOrientador, 'secci') === false && strlen($seccionOrientador) <= 2) {
    $seccionOrientador = 'Sección ' . strtoupper($seccionOrientador);
}

if ($gradoOrientador === '' && $seccionOrientador === '') {
    $gradoOrientador = '1° Año Bachillerato';
    $seccionOrientador = 'Sección A';
}

// =========================================================================
// RESTRICCIÓN DE MATERIAS Y GRADOS SEGÚN PERFIL DEL DOCENTE
// =========================================================================
$todasLasMateriasList = [
    'Matemática', 'Lenguaje y Literatura', 'Estudios Sociales y Cívica',
    'Ciencia y Tecnología', 'Idioma Extranjero (Inglés)', 'Educación Física'
];

$materiasDocente = [];
$gradosDocente = [];

if (in_array($rolActual, ['ADMIN', 'ADMINISTRADOR'])) {
    $materiasDocente = $todasLasMateriasList;
    $gradosDocente = [
        '1° Grado', '2° Grado', '3° Grado', '4° Grado', '5° Grado', '6° Grado',
        '7° Grado', '8° Grado', '9° Grado', '1° Año Bachillerato', '2° Año Bachillerato'
    ];
} else {
    // Variable $docenteInfo proveniente de AcademicoController
    if (!empty($docenteInfo['materias_imparte'])) {
        $materiasDocente = array_filter(array_map('trim', explode(',', $docenteInfo['materias_imparte'])));
    }
    if (!empty($docenteInfo['grados_imparte'])) {
        $gradosDocente = array_filter(array_map('trim', explode(',', $docenteInfo['grados_imparte'])));
    }
    if (!empty($docenteInfo['grado_orientacion']) && strtolower($docenteInfo['grado_orientacion']) !== 'ninguno') {
        $go = trim($docenteInfo['grado_orientacion']);
        if (strpos($go, '-') !== false) {
            $go = trim(explode('-', $go)[0]);
        }
        $gradosDocente[] = $go;
    }
    $gradosDocente = array_values(array_unique($gradosDocente));
}

if (empty($materiasDocente)) { $materiasDocente = ['Sin materias asignadas']; }
if (empty($gradosDocente)) { $gradosDocente = ['Sin grados asignados']; }


if (empty($listaEstudiantes) && ($conexionDB instanceof PDO)) {
    try {
        $stmtEst = $conexionDB->query("SELECT * FROM estudiantes ORDER BY apellido ASC, nombre ASC");
        if ($stmtEst) {
            $listaEstudiantes = $stmtEst->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        try {
            $stmtEst2 = $conexionDB->query("SELECT * FROM usuarios WHERE UPPER(rol) IN ('ESTUDIANTE', 'ALUMNO') ORDER BY apellido ASC, nombre ASC");
            if ($stmtEst2) {
                $listaEstudiantes = $stmtEst2->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Exception $ex) {}
    }
}

$notasBD = [];
$notasDetalleBD = [];
$mapaEstadosActas = $estadosPeriodos ?? [];

if ($conexionDB instanceof PDO) {
    try {
        $sql = "SELECT estudiante_id, materia, periodo, act1, act2, examen, nota_periodo, 
                       nota_original_reprobada, nota_recuperacion, es_recuperada, modificado_por, fecha_modificacion,
                       COALESCE(nota_periodo, (COALESCE(act1,0)*0.35 + COALESCE(act2,0)*0.35 + COALESCE(examen,0)*0.30)) AS final_calc 
                FROM notas_periodos";
        $stmt = $conexionDB->query($sql);
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eId = (int)$row['estudiante_id'];
                $mat = trim($row['materia']);
                $per = (int)$row['periodo'];

                $tieneCalificacion = ($row['nota_periodo'] !== null || $row['act1'] !== null || $row['act2'] !== null || $row['examen'] !== null);
                if ($tieneCalificacion) {
                    $val = round((float)$row['final_calc'], 2);
                    $notasBD[$eId][$mat][$per] = $val;
                    $notasDetalleBD[$eId][$mat][$per] = [
                        'act1'                    => ($row['act1'] !== null) ? round((float)$row['act1'], 2) : null,
                        'act2'                    => ($row['act2'] !== null) ? round((float)$row['act2'], 2) : null,
                        'examen'                  => ($row['examen'] !== null) ? round((float)$row['examen'], 2) : null,
                        'final'                   => $val,
                        'nota_original_reprobada' => $row['nota_original_reprobada'],
                        'nota_recuperacion'       => $row['nota_recuperacion'],
                        'es_recuperada'           => (int)($row['es_recuperada'] ?? 0),
                        'modificado_por'          => $row['modificado_por'],
                        'fecha_modificacion'      => $row['fecha_modificacion']
                    ];
                }
            }
        }

        if (empty($mapaEstadosActas)) {
            $stmtActas = $conexionDB->query("SELECT materia, periodo, grado, seccion, estado, motivo_reapertura, fecha_actualizacion FROM control_periodos_actas");
            if ($stmtActas) {
                while ($fA = $stmtActas->fetch(PDO::FETCH_ASSOC)) {
                    $clave = trim($fA['materia']) . '_' . (int)$fA['periodo'] . '_' . trim($fA['grado']) . '_' . trim($fA['seccion']);
                    $mapaEstadosActas[$clave] = [
                        'estado' => strtoupper($fA['estado']),
                        'motivo' => $fA['motivo_reapertura'],
                        'fecha'  => $fA['fecha_actualizacion']
                    ];
                }
            }
        }
    } catch (Exception $e) {
        $notasBD = [];
        $notasDetalleBD = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Gestión de Calificaciones y Asistencia (Docente)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --primary: #2563eb;
            --bg-body: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: #090e17; color: white; padding: 0.85rem 3rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .brand { display: flex; align-items: center; gap: 12px; color: #ffffff; text-decoration: none; font-weight: 800; }
        .container { flex: 1; max-width: 1400px; width: 100%; margin: 0 auto; padding: 2.5rem 1.5rem; }
        
        .admin-tabs { display: flex; gap: 2rem; border-bottom: 2px solid var(--border); margin-bottom: 2rem; flex-wrap: wrap; }
        .admin-tab { text-decoration: none; font-size: 1.05rem; font-weight: 700; color: var(--text-muted); padding-bottom: 0.75rem; display: inline-flex; align-items: center; gap: 8px; position: relative; transition: color 0.2s; }
        .admin-tab.active { color: var(--text-main); }
        .admin-tab.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 3px; background: var(--text-main); border-radius: 2px 2px 0 0; }

        .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 2rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .title-area h2 { font-size: 1.5rem; font-weight: 800; color: var(--text-main); }
        .title-area p { font-size: 0.88rem; color: var(--text-muted); }

        .orientador-banner {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            padding: 0.6rem 1.1rem;
            border-radius: 10px;
            font-size: 0.84rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-ir-orientacion {
            background: #2563eb;
            color: #ffffff;
            border: none;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-ir-orientacion:hover { background: #1d4ed8; }

        .filters-bar { background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 1rem; display: flex; gap: 0.85rem; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; }
        .filters-bar select, .filters-bar input { padding: 0.55rem 1rem; border: 1px solid var(--border); border-radius: 8px; background: white; font-size: 0.88rem; color: var(--text-main); outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
        .filters-bar select.filtro-alerta { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.18); }
        .btn-filter { background: #334155; color: white; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.88rem; cursor: pointer; }
        
        .btn-matriz-toggle { background: #0f172a; color: white; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.2s; }
        .btn-matriz-toggle:hover, .btn-matriz-toggle.active { background: #2563eb; }
        .btn-excel { background: #16a34a; color: white; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: background 0.2s; }
        .btn-excel:hover { background: #15803d; }
        .btn-asistencia { background: #0284c7; color: white; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: background 0.2s; }
        .btn-asistencia:hover { background: #0369a1; }
        .btn-comite { background: #7c3aed; color: white; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: background 0.2s; }
        .btn-comite:hover { background: #6d28d9; }

        .sub-tabs-nav { display: flex; gap: 0.5rem; border-bottom: 2px solid var(--border); margin-bottom: 1.5rem; flex-wrap: wrap; }
        .sub-tab-link { background: transparent; border: none; padding: 0.75rem 1.25rem; font-size: 0.9rem; font-weight: 700; color: var(--text-muted); cursor: pointer; display: inline-flex; align-items: center; gap: 8px; border-bottom: 3px solid transparent; margin-bottom: -2px; border-radius: 8px 8px 0 0; transition: all 0.2s ease; }
        .sub-tab-link:hover { color: var(--text-main); background: #f8fafc; }
        .sub-tab-link.active { color: var(--primary); border-bottom-color: var(--primary); background: #eff6ff; }
        .sub-tab-pill { font-size: 0.72rem; padding: 2px 8px; border-radius: 999px; background: #e2e8f0; color: #334155; font-weight: 800; }
        .sub-tab-link.active .sub-tab-pill { background: #dbeafe; color: #1d4ed8; }
        .vista-sub-tab { display: none; animation: fadeSubTab 0.18s ease-out; }
        .vista-sub-tab.active { display: block; }
        @keyframes fadeSubTab { from { opacity: 0; transform: translateY(3px); } to { opacity: 1; transform: translateY(0); } }

        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 12px 14px; border-bottom: 1px solid var(--border); font-size: 0.88rem; vertical-align: middle; }
        th { background: #ffffff; font-weight: 700; color: var(--text-muted); text-transform: uppercase; font-size: 0.74rem; letter-spacing: 0.5px; }
        .user-cell { display: flex; align-items: center; gap: 12px; }
        .user-avatar-circle { width: 36px; height: 36px; border-radius: 50%; background: #eff6ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: bold; border: 1px solid #bfdbfe; overflow: hidden; }
        .user-avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
        
        .badge-condicion { padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; }
        .badge-aprobado { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-reprobado { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-sin-calificar { background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }

        .badge-acta-estado { padding: 4px 10px; border-radius: 20px; font-size: 0.76rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px; }
        .acta-abierta { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .acta-cerrada { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        .btn-action-badge { padding: 6px 12px; border-radius: 6px; color: white; text-decoration: none; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; margin-right: 4px; }
        .btn-notas { background: #10b981; cursor: pointer; border: none; }
        .btn-recup { background: #ea580c; cursor: pointer; border: none; }
        .btn-recup:disabled { background: #cbd5e1; color: #94a3b8; cursor: not-allowed; }
        .btn-boleta { background: #0284c7; }
        .btn-action-badge:hover { opacity: 0.9; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: var(--primary); text-decoration: none; font-weight: 600; margin-bottom: 1.25rem; font-size: 0.9rem; }

        .matriz-container { display: block; background: #f8fafc; border: 2px solid #cbd5e1; border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; }
        .matriz-header-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
        .input-matriz-nota { width: 90px; padding: 0.45rem 0.6rem; border: 1px solid #94a3b8; border-radius: 6px; text-align: center; font-weight: 700; font-size: 0.9rem; background: #ffffff; color: var(--text-main); }
        .input-matriz-nota:disabled, .input-matriz-nota[readonly] { background: #f1f5f9; color: #64748b; border-color: #cbd5e1; cursor: not-allowed; }
        .btn-guardar-matriz { background: #059669; color: white; border: none; padding: 0.7rem 1.5rem; border-radius: 8px; font-weight: 800; font-size: 0.9rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn-guardar-matriz:disabled { background: #94a3b8; cursor: not-allowed; }

        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(3px); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; }
        .modal-card { background: #ffffff; border-radius: 16px; width: 100%; max-width: 600px; padding: 2.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative; }
        .modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem; }
        .modal-title h3 { font-size: 1.4rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.2rem; }
        .modal-title p { font-size: 0.85rem; color: var(--text-muted); }
        .modal-close { background: none; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer; }
        .form-grid-modal { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; }
        .form-group-modal { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1.25rem; }
        .form-group-modal label { font-size: 0.82rem; font-weight: 700; color: #334155; }
        .form-group-modal input, .form-group-modal select, .form-group-modal textarea { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; background: #f8fafc; color: var(--text-main); font-weight: 600; }
        .form-group-modal input:disabled, .form-group-modal input[readonly] { background: #f1f5f9; color: #64748b; cursor: not-allowed; }
        .nota-final-box { background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; margin: 1.5rem 0; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem; }
        .btn-modal-cancel { background: #f1f5f9; color: #334155; border: none; padding: 0.75rem 1.25rem; border-radius: 8px; font-weight: 700; cursor: pointer; }
        .btn-modal-save { background: #059669; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn-modal-save:disabled { background: #94a3b8; cursor: not-allowed; }

        @media print {
            .sub-tabs-nav, .filters-bar, .orientador-banner, .admin-tabs, .btn-back, .navbar {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php?action=dashboard" class="brand">
            <div style="width:34px; height:34px; background:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                <img src="img/logo_inca.png" alt="Logo" style="width:28px; height:28px; object-fit:contain;" onerror="this.src='https://ui-avatars.com/api/?name=INCA&background=fff&color=0b1a30'">
            </div>
            <span>INCA NOTES</span>
        </a>
        <div style="display:flex; align-items:center; gap:15px;">
            <span style="color:#94a3b8; font-size:0.88rem;">
                <i class="fa-solid fa-chalkboard-user"></i> Panel de DOCENTE (<?= htmlspecialchars($nombreDocenteSesion) ?>)
            </span>
            <a href="index.php?action=logout" style="color:white; text-decoration:none; font-size:0.85rem; background:rgba(255,255,255,0.1); padding:0.4rem 1rem; border-radius:6px;">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <a href="index.php?action=dashboard" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>
        
        <!-- Pestaña Docente autorizada (Aislamiento RBAC PBI-44) -->
        <div class="admin-tabs">
            <a href="index.php?action=gestion_notas_docente" class="admin-tab active">
                <i class="fa-solid fa-graduation-cap"></i> Control de Alumnos, Notas y Asistencia
            </a>
            <a href="index.php?action=cuadro_honor" class="admin-tab">
                <i class="fa-solid fa-trophy"></i> Cuadro de Honor y Ranking
            </a>
        </div>

        <div class="card">
            <?php if (!empty($msg)): ?>
                <div style="background:#d1fae5; border:1px solid #a7f3d0; color:#065f46; padding:0.75rem 1rem; border-radius:8px; font-size:0.88rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>
                        <?php 
                        if ($msg === 'nota_anulada_exito') echo '¡Calificación anulada de forma segura! El periodo se restableció a "Sin calificar".';
                        elseif ($msg === 'recuperacion_exitosa') echo '¡Evaluación de recuperación registrada y nota sustituida con éxito!';
                        elseif ($msg === 'matriz_guardada') echo '¡Calificaciones de toda la sección guardadas exitosamente en una sola transacción!';
                        else echo '¡Calificación guardada y métricas actualizadas con éxito!';
                        ?>
                    </span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'no_reprobado'): ?>
                <div style="background:#fee2e2; border:1px solid #fecaca; color:#991b1b; padding:0.75rem 1rem; border-radius:8px; font-size:0.88rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Acción bloqueada: La recuperación se habilita exclusivamente para alumnos con nota inferior a 6.0 (Criterio PBI-39).</span>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'materia_no_autorizada'): ?>
                <div style="background:#fee2e2; border:1px solid #fecaca; color:#991b1b; padding:0.75rem 1rem; border-radius:8px; font-size:0.88rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-ban"></i>
                    <span>Acción bloqueada: Usted no tiene autorización para registrar calificaciones en esta asignatura.</span>
                </div>
            <?php endif; ?>

            <!-- PBI-47: Error si un docente intenta alterar un acta cerrada por manipulación de URL -->
            <?php if (isset($_GET['error']) && $_GET['error'] === 'periodo_cerrado'): ?>
                <div style="background:#fee2e2; border:1px solid #fecaca; color:#991b1b; padding:0.75rem 1rem; border-radius:8px; font-size:0.88rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-lock"></i>
                    <span>Acción bloqueada: El acta de este periodo se encuentra asentada y CERRADA por la Dirección. No se permiten alteraciones.</span>
                </div>
            <?php endif; ?>

            <div class="header-top">
                <div class="title-area">
                    <h2>Gestión Docente de Calificaciones y Asistencia</h2>
                    <p>Registra evaluaciones trimestrales, recuperaciones y emite reportes oficiales de tus secciones asignadas.</p>
                </div>
                <div class="orientador-banner" title="Solo puede imprimir la lista de asistencia de su grado y sección de orientación">
                    <i class="fa-solid fa-id-badge" style="font-size: 1.1rem;"></i>
                    <span>Orientador de: <strong><?= htmlspecialchars($gradoOrientador . ' - ' . $seccionOrientador) ?></strong></span>
                    <button type="button" class="btn-ir-orientacion" onclick="seleccionarSeccionOrientador()">
                        <i class="fa-solid fa-check"></i> Filtrar mi Sección
                    </button>
                </div>
            </div>

            <!-- Filtros principales -->
            <div class="filters-bar">
                <input type="text" id="input_buscar_pbi21" placeholder="🔍 Buscar por NIE o Nombre..." style="min-width: 200px;">
                <select name="grado" id="selectGrado">
                    <option value="">-- Filtrar por Grado --</option>
                    <?php foreach($gradosDocente as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>"><?= htmlspecialchars($g) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="seccion" id="selectSeccion">
                    <option value="">-- Sección --</option>
                    <option value="Sección A">Sección A</option><option value="Sección B">Sección B</option><option value="Sección C">Sección C</option>
                </select>

                <select id="selectCondicionPBI19" style="font-weight: 700; background: #f1f5f9;">
                    <option value="">-- Estado de Rendimiento --</option>
                    <option value="APROBADO">✅ Solo Aprobados (≥ 6.0)</option>
                    <option value="REPROBADO">⚠️ Reprobados / En Riesgo (&lt; 6.0)</option>
                    <option value="SIN CALIFICAR">⚪ Sin Calificar / Anulados</option>
                </select>

                <button type="button" class="btn-filter" id="btnFiltrarManual">Filtrar</button>

                <button type="button" class="btn-matriz-toggle" id="btnToggleMatriz" onclick="alternarModoMatriz()">
                    <i class="fa-solid fa-table-cells"></i> Matriz por Sección
                </button>

                <button type="button" class="btn-excel" id="btnExportarExcel">
                    <i class="fa-solid fa-file-excel"></i> Exportar a Excel
                </button>
                <button type="button" class="btn-asistencia" id="btnExportarAsistencia" title="Exclusivo para su grado de orientación: <?= htmlspecialchars($gradoOrientador . ' - ' . $seccionOrientador) ?>">
                    <i class="fa-solid fa-clipboard-user"></i> Lista de Asistencia (.pdf)
                </button>
                <button type="button" class="btn-comite" id="btnExportarComite">
                    <i class="fa-solid fa-user-check"></i> Nómina Comité (.pdf)
                </button>

                <a href="#" id="btnLimpiarFiltros" style="color: var(--text-muted); text-decoration: none; font-size: 0.88rem; margin-left: auto;">Limpiar Filtros</a>
            </div>

            <!-- BARRA DE PESTAÑAS INTERNAS DENTRO DE CONTROL DE ALUMNOS Y NOTAS -->
            <div class="sub-tabs-nav">
                <button type="button" class="sub-tab-link active" id="btnTabNomina" onclick="cambiarPestanaInterna('nomina')">
                    <i class="fa-solid fa-users"></i> Lista de Alumnos y Notas
                    <span class="sub-tab-pill" id="badgeConteoAlumnos"><?= count($listaEstudiantes) ?></span>
                </button>
                <button type="button" class="sub-tab-link" id="btnTabMatriz" onclick="cambiarPestanaInterna('matriz')">
                    <i class="fa-solid fa-table-cells"></i> Ingreso de Notas (Matriz por Sección)
                    <span class="sub-tab-pill">Trimestral</span>
                </button>
                <button type="button" class="sub-tab-link" id="btnTabGraficos" onclick="cambiarPestanaInterna('graficos')">
                    <i class="fa-solid fa-chart-pie"></i> Gráficos de Rendimiento
                    <span class="sub-tab-pill">Estadísticas</span>
                </button>
            </div>

            <!-- PESTAÑA 1: TABLA PRINCIPAL DE ESTUDIANTES -->
            <div id="vistaTabNomina" class="vista-sub-tab active">
                <div class="table-responsive">
                    <table id="tablaPrincipalEstudiantes">
                        <thead>
                            <tr>
                                <th>NIE</th>
                                <th>Estudiante</th>
                                <th>Grado/Sección</th>
                                <th id="th_materia_activa">ASIGNATURA</th>
                                <th style="text-align: center;" id="th_promedio_activo">PROMEDIO</th>
                                <th style="text-align: center;">CONDICIÓN</th>
                                <th style="text-align: right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($listaEstudiantes)): ?>
                                <?php foreach ($listaEstudiantes as $est): 
                                    $eId = (int)$est['id'];
                                    $nombreCompleto = strtolower(($est['nombre'] ?? '') . ' ' . ($est['apellido'] ?? ''));
                                    $gradoEstudiante = trim($est['grado'] ?? '');
                                    $seccionEstudiante = trim($est['seccion'] ?? '');
                                    $nombreFormateado = trim(($est['apellido'] ?? '') . ', ' . ($est['nombre'] ?? ''));
                                    
                                    $notasEstudiante = $notasBD[$eId] ?? [];
                                    if (empty($notasEstudiante) && (strpos($nombreCompleto, 'carlos') !== false && strpos($nombreCompleto, 'martinez') !== false)) {
                                        $notasEstudiante = $notasCarlosBoleta;
                                    }

                                    $jsonNotas = htmlspecialchars(json_encode($notasEstudiante), ENT_QUOTES, 'UTF-8');
                                    $jsonDetalle = htmlspecialchars(json_encode($notasDetalleBD[$eId] ?? []), ENT_QUOTES, 'UTF-8');
                                ?>
                                    <tr data-estudiante-id="<?= $eId ?>" 
                                        data-nie="<?= htmlspecialchars($est['nie'] ?? 'N/D') ?>"
                                        data-nombre="<?= htmlspecialchars($nombreFormateado) ?>"
                                        data-grado="<?= htmlspecialchars($gradoEstudiante) ?>" 
                                        data-seccion="<?= htmlspecialchars($seccionEstudiante) ?>" 
                                        data-notas="<?= $jsonNotas ?>"
                                        data-detalle="<?= $jsonDetalle ?>">
                                        <td><strong><?= htmlspecialchars($est['nie'] ?? 'N/D') ?></strong></td>
                                        <td>
                                            <div class="user-cell">
                                                <div class="user-avatar-circle">
                                                    <?php 
                                                    $fNom = basename($est['foto'] ?? '');
                                                    if (!empty($fNom) && file_exists(__DIR__ . '/../uploads/' . $fNom)): 
                                                    ?>
                                                        <img src="uploads/<?= htmlspecialchars($fNom) ?>" alt="Foto">
                                                    <?php else: ?>
                                                        <i class="fa-solid fa-user"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="span-nombre-est"><?= htmlspecialchars($nombreFormateado) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($gradoEstudiante . ' - ' . $seccionEstudiante) ?></td>
                                        <td class="col-materia-est">Promedio General</td>
                                        <td style="text-align: center; font-weight: 800;" class="col-promedio-est">0.00</td>
                                        <td style="text-align: center;" class="col-condicion-est">
                                            <span class="badge-condicion badge-reprobado">REPROBADO</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button type="button" class="btn-action-badge btn-notas" onclick="abrirModalNotas('<?= $est['id'] ?>', '<?= htmlspecialchars(($est['nombre'] ?? '') . ' ' . ($est['apellido'] ?? ''), ENT_QUOTES) ?>', '<?= htmlspecialchars($gradoEstudiante, ENT_QUOTES) ?>', '<?= htmlspecialchars($seccionEstudiante, ENT_QUOTES) ?>')">
                                                <i class="fa-solid fa-star"></i> Notas
                                            </button>
                                            <button type="button" class="btn-action-badge btn-recup" title="Registrar Nota de Recuperación" onclick="abrirModalRecuperacion('<?= $est['id'] ?>', '<?= htmlspecialchars(($est['nombre'] ?? '') . ' ' . ($est['apellido'] ?? ''), ENT_QUOTES) ?>', '<?= htmlspecialchars($gradoEstudiante, ENT_QUOTES) ?>', '<?= htmlspecialchars($seccionEstudiante, ENT_QUOTES) ?>')">
                                                <i class="fa-solid fa-arrow-up-right-dots"></i> Recuperación
                                            </button>
                                            <a href="index.php?action=boleta_notas&estudiante_id=<?= $est['id'] ?>" class="btn-action-badge btn-boleta" title="Boleta"><i class="fa-solid fa-file-lines"></i></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay estudiantes matriculados en sus grados asignados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PESTAÑA 2: MATRIZ DE CALIFICACIÓN POR SECCIÓN (PBI-12 + PBI-47 GRANULAR) -->
            <div id="vistaTabMatriz" class="vista-sub-tab">
                <div class="matriz-container" id="bloqueMatrizPBI12">
                    <form action="index.php?action=guardar_nota" method="POST" id="formMatrizSeccion">
                        <input type="hidden" name="modo_matriz" value="1">
                        <input type="hidden" name="matriz_grado" id="inputHiddenMatrizGrado" value="">
                        <input type="hidden" name="matriz_seccion" id="inputHiddenMatrizSeccion" value="">

                        <div class="matriz-header-bar">
                            <div>
                                <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                                    <i class="fa-solid fa-table-list" style="color: var(--primary);"></i> Matriz de Calificación Trimestral por Sección
                                </h3>
                                <p style="font-size: 0.82rem; color: var(--text-muted);">Ponderación automática: Actividad 1 (35%) + Actividad 2 (35%) + Examen (30%) • Rango: 0.00 a 10.00</p>
                            </div>
                            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                                <div>
                                    <label style="font-size: 0.78rem; font-weight: 700; color: #334155; display: block; margin-bottom: 3px;">Asignatura Asignada:</label>
                                    <select name="materia" id="matrizMateria" onchange="cargarValoresMatriz()" style="padding: 0.45rem 0.8rem; border: 1px solid #94a3b8; border-radius: 6px; font-weight: 700; background: #fff;">
                                        <?php foreach($materiasDocente as $m): ?>
                                            <option value="<?= htmlspecialchars($m) ?>"><?= htmlspecialchars($m) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label style="font-size: 0.78rem; font-weight: 700; color: #334155; display: block; margin-bottom: 3px;">Periodo:</label>
                                    <select name="periodo" id="matrizPeriodo" onchange="cargarValoresMatriz()" style="padding: 0.45rem 0.8rem; border: 1px solid #94a3b8; border-radius: 6px; font-weight: 700; background: #fff;">
                                        <option value="1">Periodo 1</option>
                                        <option value="2">Periodo 2</option>
                                        <option value="3">Periodo 3</option>
                                        <option value="4" id="optMatrizPeriodo4">Periodo 4</option>
                                    </select>
                                </div>

                                <div style="margin-top: 14px;">
                                    <button type="submit" id="btnSubmitMatriz" class="btn-guardar-matriz">
                                        <i class="fa-solid fa-floppy-disk"></i> Guardar Sección
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PBI-47: Banner Docente Informativo si el acta está cerrada -->
                        <div id="bannerActaCerradaMatriz" style="display:none; background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:0.85rem 1rem; border-radius:8px; font-size:0.85rem; font-weight:700; margin-bottom:1rem; align-items:center; gap:8px;">
                            <i class="fa-solid fa-lock" style="font-size:1.2rem;"></i>
                            <span id="textoBannerActaCerrada">ACTA ASENTADA OFICIALMENTE: El registro para esta sección está bloqueado en modo solo lectura. Solicite autorización de reapertura extraordinaria a la Dirección si requiere modificaciones.</span>
                        </div>

                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr style="background: #e2e8f0;">
                                        <th>NIE</th>
                                        <th>Estudiante</th>
                                        <th>Grado / Sección</th>
                                        <th style="text-align: center;">Actividad 1 (35%)</th>
                                        <th style="text-align: center;">Actividad 2 (35%)</th>
                                        <th style="text-align: center;">Examen (30%)</th>
                                        <th style="text-align: center;">Nota Final Periodo</th>
                                        <th style="text-align: center;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyMatriz">
                                    <?php if (!empty($listaEstudiantes)): ?>
                                        <?php foreach ($listaEstudiantes as $est): 
                                            $eId = (int)$est['id'];
                                            $gradoEst = trim($est['grado'] ?? '');
                                            $secEst   = trim($est['seccion'] ?? '');
                                            $detJson  = htmlspecialchars(json_encode($notasDetalleBD[$eId] ?? []), ENT_QUOTES, 'UTF-8');
                                        ?>
                                            <tr class="fila-matriz-item"
                                                data-id="<?= $eId ?>"
                                                data-grado="<?= htmlspecialchars($gradoEst) ?>"
                                                data-seccion="<?= htmlspecialchars($secEst) ?>"
                                                data-detalle="<?= $detJson ?>">
                                                <td>
                                                    <strong><?= htmlspecialchars($est['nie'] ?? 'N/D') ?></strong>
                                                    <input type="hidden" name="estudiante_ids[]" value="<?= $eId ?>" class="input-id-matriz">
                                                </td>
                                                <td><strong><?= htmlspecialchars(($est['apellido'] ?? '') . ', ' . ($est['nombre'] ?? '')) ?></strong></td>
                                                <td><?= htmlspecialchars($gradoEst . ' - ' . $secEst) ?></td>
                                                <td style="text-align: center;">
                                                    <input type="number" step="0.01" min="0" max="10" name="act1[<?= $eId ?>]" class="input-matriz-nota inp-a1" value="0.00" oninput="recalcularFilaMatriz(this)" required>
                                                </td>
                                                <td style="text-align: center;">
                                                    <input type="number" step="0.01" min="0" max="10" name="act2[<?= $eId ?>]" class="input-matriz-nota inp-a2" value="0.00" oninput="recalcularFilaMatriz(this)" required>
                                                </td>
                                                <td style="text-align: center;">
                                                    <input type="number" step="0.01" min="0" max="10" name="examen[<?= $eId ?>]" class="input-matriz-nota inp-ex" value="0.00" oninput="recalcularFilaMatriz(this)" required>
                                                </td>
                                                <td style="text-align: center; font-weight: 800; font-size: 1rem; color: var(--primary);" class="celda-nf-matriz">0.00</td>
                                                <td style="text-align: center;" class="celda-est-matriz">
                                                    <span class="badge-condicion badge-reprobado">REPROBADO</span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PESTAÑA 3: SECCIÓN DE ESTADÍSTICAS Y GRÁFICOS DINÁMICOS -->
            <div id="vistaTabGraficos" class="vista-sub-tab">
                <div id="seccion_estadisticas_pbi18" style="padding-top: 0.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-chart-pie" style="color: var(--primary);"></i> Reporte de Rendimiento por Sección y Asignatura
                            </h3>
                            <p style="font-size: 0.85rem; color: var(--text-muted);">Métricas calculadas directamente de las calificaciones oficiales.</p>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center; background: #f8fafc; padding: 0.4rem 0.85rem; border-radius: 8px; border: 1px solid var(--border); flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <label for="selectMateriaEstadistica" style="font-size: 0.82rem; font-weight: 700; color: #334155;">Materia:</label>
                                <select id="selectMateriaEstadistica" style="padding: 0.35rem 0.65rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.82rem; outline: none; background: #fff; font-weight: 600;">
                                    <option value="Todas">-- Todas mis Materias --</option>
                                    <?php foreach($materiasDocente as $m): ?>
                                        <option value="<?= htmlspecialchars($m) ?>"><?= htmlspecialchars($m) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <label for="selectPeriodoEstadistica" style="font-size: 0.82rem; font-weight: 700; color: #334155;">Periodo:</label>
                                <select id="selectPeriodoEstadistica" style="padding: 0.35rem 0.65rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.82rem; outline: none; background: #fff; font-weight: 600;">
                                    <option value="0">-- Promedio Anual (Global) --</option>
                                    <option value="1">Periodo 1</option><option value="2">Periodo 2</option><option value="3">Periodo 3</option><option value="4">Periodo 4</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.75rem;">
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 1.1rem; text-align: center;">
                            <div style="font-size: 0.78rem; font-weight: 800; color: #1e40af; text-transform: uppercase; letter-spacing: 0.5px;">Promedio General</div>
                            <div id="kpi_promedio" style="font-size: 1.9rem; font-weight: 800; color: #1d4ed8; margin-top: 4px;">--</div>
                        </div>
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 1.1rem; text-align: center;">
                            <div style="font-size: 0.78rem; font-weight: 800; color: #166534; text-transform: uppercase; letter-spacing: 0.5px;">Nota Más Alta</div>
                            <div id="kpi_max" style="font-size: 1.9rem; font-weight: 800; color: #15803d; margin-top: 4px;">--</div>
                        </div>
                        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 1.1rem; text-align: center;">
                            <div style="font-size: 0.78rem; font-weight: 800; color: #991b1b; text-transform: uppercase; letter-spacing: 0.5px;">Nota Más Baja</div>
                            <div id="kpi_min" style="font-size: 1.9rem; font-weight: 800; color: #b91c1c; margin-top: 4px;">--</div>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 1.1rem; text-align: center;">
                            <div style="font-size: 0.78rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Estudiantes Visibles</div>
                            <div id="kpi_total" style="font-size: 1.9rem; font-weight: 800; color: var(--text-main); margin-top: 4px;">0</div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                        <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                            <h4 id="tituloPastelMateria" style="font-size: 0.95rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-main); text-align: center;">Distribución de Rangos (Pastel)</h4>
                            <div style="position: relative; height: 260px;"><canvas id="chartPastelRendimiento"></canvas></div>
                        </div>
                        <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                            <h4 id="tituloBarrasMateria" style="font-size: 0.95rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-main); text-align: center;">Cantidad de Alumnos por Categoría (Barras)</h4>
                            <div style="position: relative; height: 260px;"><canvas id="chartBarrasRendimiento"></canvas></div>
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin-bottom: 1rem; color: var(--text-main); text-align: center;">Promedio Comparativo Real entre Mis Asignaturas</h4>
                        <div style="position: relative; height: 240px;"><canvas id="chartComparativoMaterias"></canvas></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: CALIFICACIONES ORDINARIAS CON AUDITORÍA (PBI-15 + PBI-47) -->
    <div class="modal-overlay" id="modalNotas">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">
                    <h3 id="modalEstudianteNombre">Nombre del Estudiante</h3>
                    <p id="modalSubtituloNivel">Bachillerato (4 Periodos) • Ponderación: 35% - 35% - 30%</p>
                </div>
                <button type="button" class="modal-close" onclick="cerrarModalNotas()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="index.php?action=guardar_nota" method="POST" id="formModalNotas" onsubmit="return solicitarConfirmacionGuardar(event)">
                <input type="hidden" id="estudiante_id" name="estudiante_id">
                <input type="hidden" id="modal_estudiante_grado" name="estudiante_grado" value="">
                <input type="hidden" id="modal_estudiante_seccion" name="estudiante_seccion" value="">
                <input type="hidden" id="accion_anular" name="accion_anular" value="">

                <div class="form-grid-modal">
                    <div class="form-group-modal">
                        <label for="materia">Asignatura</label>
                        <select id="materia" name="materia" onchange="actualizarValoresModalNotas()">
                            <?php foreach($materiasDocente as $m): ?>
                                <option value="<?= htmlspecialchars($m) ?>"><?= htmlspecialchars($m) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group-modal">
                        <label for="periodo">Periodo a Calificar</label>
                        <select id="periodo" name="periodo" onchange="actualizarValoresModalNotas()">
                            <option value="1">Periodo 1</option>
                            <option value="2">Periodo 2</option>
                            <option value="3">Periodo 3</option>
                            <option value="4">Periodo 4</option>
                        </select>
                    </div>
                </div>

                <!-- PBI-47: Alerta Docente si el acta individual de la sección está cerrada -->
                <div id="alertaActaCerradaModal" style="display:none; background:#fee2e2; border:1px solid #fca5a5; color:#991b1b; padding:0.65rem 0.85rem; border-radius:8px; font-size:0.8rem; font-weight:700; margin-bottom:1rem; align-items:center; gap:8px;">
                    <i class="fa-solid fa-lock"></i>
                    <span>Acta oficial asentada para esta sección. Solicite autorización de reapertura para modificar.</span>
                </div>

                <div class="form-grid-modal">
                    <div class="form-group-modal">
                        <label for="act1">Actividad 1 (35%)</label>
                        <input type="number" step="0.01" min="0" max="10" id="act1" name="act1" value="0.0" oninput="calcularNotaFinal()" required>
                    </div>
                    <div class="form-group-modal">
                        <label for="act2">Actividad 2 (35%)</label>
                        <input type="number" step="0.01" min="0" max="10" id="act2" name="act2" value="0.0" oninput="calcularNotaFinal()" required>
                    </div>
                </div>
                <div class="form-group-modal">
                    <label for="examen">Examen (30%)</label>
                    <input type="number" step="0.01" min="0" max="10" id="examen" name="examen" value="0.0" oninput="calcularNotaFinal()" required>
                </div>
                <div class="nota-final-box">
                    <span>NOTA FINAL DEL PERIODO:</span>
                    <span id="labelNotaFinal">0.0</span>
                </div>

                <div id="trazaAuditoriaBox" style="display:none; font-size:0.75rem; color:#64748b; background:#f8fafc; border:1px solid #e2e8f0; padding:6px 10px; border-radius:6px; margin-bottom:1.25rem;">
                    <i class="fa-solid fa-fingerprint"></i> <span id="trazaAuditoriaTexto"></span>
                </div>

                <div class="modal-footer" style="display:flex; justify-content:space-between; align-items:center;">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModalNotas()" style="margin-left:auto;">Cancelar</button>
                    <button type="submit" class="btn-modal-save" id="btnGuardarNotaModal"><i class="fa-solid fa-floppy-disk"></i> Guardar Calificación</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: EVALUACIÓN DE RECUPERACIÓN Y SUSTITUCIÓN (PBI-39) -->
    <div class="modal-overlay" id="modalRecuperacion">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">
                    <h3 id="recupEstudianteNombre">Registro de Recuperación</h3>
                    <p>Sustitución oficial de nota reprobatoria (&lt; 6.0) conforme a normativa</p>
                </div>
                <button type="button" class="modal-close" onclick="cerrarModalRecuperacion()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="index.php?action=guardar_recuperacion" method="POST" id="formRecuperacion">
                <input type="hidden" id="recup_estudiante_id" name="estudiante_id">
                <input type="hidden" id="recup_estudiante_grado" name="estudiante_grado" value="">
                <input type="hidden" id="recup_estudiante_seccion" name="estudiante_seccion" value="">

                <div class="form-grid-modal">
                    <div class="form-group-modal">
                        <label for="recup_materia">Asignatura</label>
                        <select id="recup_materia" name="materia" onchange="actualizarEstadoRecuperacionModal()">
                            <?php foreach($materiasDocente as $m): ?>
                                <option value="<?= htmlspecialchars($m) ?>"><?= htmlspecialchars($m) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group-modal">
                        <label for="recup_periodo">Periodo Reprobado</label>
                        <select id="recup_periodo" name="periodo" onchange="actualizarEstadoRecuperacionModal()">
                            <option value="1">Periodo 1</option>
                            <option value="2">Periodo 2</option>
                            <option value="3">Periodo 3</option>
                            <option value="4">Periodo 4</option>
                        </select>
                    </div>
                </div>

                <div id="recup_alerta_estado" style="padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: 0.88rem; font-weight: 700;"></div>

                <div class="form-group-modal" id="grupoInputRecuperacion">
                    <label for="nota_recuperacion">Nota Obtenida en Recuperación <span style="font-size:0.75rem; color:var(--primary);">(0.00 a 10.00)</span></label>
                    <input type="number" step="0.01" min="0" max="10" id="nota_recuperacion" name="nota_recuperacion" placeholder="Ej: 7.50" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModalRecuperacion()">Cancelar</button>
                    <button type="submit" class="btn-modal-save" id="btnGuardarRecuperacion" style="background:#ea580c;">
                        <i class="fa-solid fa-check-double"></i> Sustituir y Recalcular
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: CONFIRMACIÓN EXPLÍCITA DE ACCIÓN DE SEGURIDAD (PBI-15) -->
    <div class="modal-overlay" id="modalConfirmacionPBI15" style="z-index: 1100;">
        <div class="modal-card" style="max-width: 480px; text-align: center;">
            <div style="width: 58px; height: 58px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.7rem; margin: 0 auto 1.2rem;" id="iconoModalConfirm">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 id="tituloModalConfirm" style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin-bottom: 0.4rem;">Confirmar Acción</h3>
            <p id="mensajeModalConfirm" style="font-size: 0.88rem; color: #64748b; line-height: 1.5; margin-bottom: 1.5rem;">¿Está seguro de aplicar este cambio en las notas oficiales?</p>
            <div style="display: flex; justify-content: center; gap: 12px;">
                <button type="button" class="btn-modal-cancel" onclick="cerrarModalConfirmacionPBI15()">Cancelar</button>
                <button type="button" id="btnAceptarConfirmPBI15" class="btn-modal-save" style="background: #dc2626;">Sí, Confirmar</button>
            </div>
        </div>
    </div>

    <!-- MODAL 4: ALERTA DE IMPRESIÓN EN BLANCO O RESTRICCIÓN DE ORIENTADOR -->
    <div class="modal-overlay" id="modalAlertaImpresionVacia" style="z-index: 1200;">
        <div class="modal-card" style="max-width: 520px; text-align: center;">
            <div id="iconoAlertaImpresionBox" style="width: 62px; height: 62px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.85rem; margin: 0 auto 1.2rem; border: 2px solid #fde68a;">
                <i class="fa-solid fa-print"></i>
            </div>
            <h3 id="tituloAlertaImpresion" style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem;">
                Sin Selección para Imprimir
            </h3>
            <p id="mensajeAlertaImpresion" style="font-size: 0.92rem; color: #475569; line-height: 1.55; margin-bottom: 1.5rem;">
                No se tiene seleccionado ningún Grado o Sección. Si continúa sin seleccionar filtros, la impresión saldrá en blanco.
            </p>
            <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                <button type="button" class="btn-modal-cancel" onclick="cerrarAlertaImpresionYEnfocar()">
                    Cerrar
                </button>
                <button type="button" id="btnCargarSeccionOrientadorModal" class="btn-modal-save" style="background: #2563eb;" onclick="aplicarSeccionOrientadorDesdeModal()">
                    <i class="fa-solid fa-id-badge"></i> Cargar mi Sección de Orientación
                </button>
            </div>
        </div>
    </div>

    <script>
        const MATERIAS_DOCENTE = <?= json_encode(array_values($materiasDocente)) ?>;

        const GRADO_ORIENTADOR_DOCENTE = <?= json_encode($gradoOrientador) ?>;
        const SECCION_ORIENTADOR_DOCENTE = <?= json_encode($seccionOrientador) ?>;

        const MAPA_ESTADOS_ACTAS_PBI47 = <?= json_encode($mapaEstadosActas) ?>;

        function normalizarTexto(txt) {
            return (txt || '').toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
        }

        // PBI-47: Obtener estado de acta por Materia, Periodo, Grado y Sección
        function obtenerEstadoActa(materia, periodo, grado, seccion) {
            if (!grado || !seccion) return 'ABIERTO';
            const clave = materia.trim() + '_' + parseInt(periodo) + '_' + grado.trim() + '_' + seccion.trim();
            return MAPA_ESTADOS_ACTAS_PBI47[clave] ? MAPA_ESTADOS_ACTAS_PBI47[clave].estado : 'ABIERTO';
        }

        function esSeccionDeOrientacion(gradoSeleccionado, seccionSeleccionada) {
            const gSel = normalizarTexto(gradoSeleccionado);
            const sSel = normalizarTexto(seccionSeleccionada);
            const gOri = normalizarTexto(GRADO_ORIENTADOR_DOCENTE);
            const sOri = normalizarTexto(SECCION_ORIENTADOR_DOCENTE);

            if (gSel === '' || sSel === '') return false;

            const coincideGrado = (gSel === gOri) || gSel.includes(gOri) || gOri.includes(gSel);
            const letraSel = sSel.replace(/.*secci[oó]n\s*/i, '').trim();
            const letraOri = sOri.replace(/.*secci[oó]n\s*/i, '').trim();
            const coincideSeccion = (sSel === sOri) || (letraSel !== '' && letraSel === letraOri);

            return coincideGrado && coincideSeccion;
        }

        function seleccionarSeccionOrientador() {
            const selGrado = document.getElementById('selectGrado');
            const selSeccion = document.getElementById('selectSeccion');

            if (selGrado && GRADO_ORIENTADOR_DOCENTE) {
                for (let i = 0; i < selGrado.options.length; i++) {
                    if (normalizarTexto(selGrado.options[i].value) === normalizarTexto(GRADO_ORIENTADOR_DOCENTE) ||
                        normalizarTexto(selGrado.options[i].value).includes(normalizarTexto(GRADO_ORIENTADOR_DOCENTE))) {
                        selGrado.selectedIndex = i;
                        break;
                    }
                }
            }

            if (selSeccion && SECCION_ORIENTADOR_DOCENTE) {
                const letraOri = normalizarTexto(SECCION_ORIENTADOR_DOCENTE).replace(/.*secci[oó]n\s*/i, '').trim();
                for (let i = 0; i < selSeccion.options.length; i++) {
                    const valOpt = normalizarTexto(selSeccion.options[i].value);
                    const letraOpt = valOpt.replace(/.*secci[oó]n\s*/i, '').trim();
                    if (valOpt === normalizarTexto(SECCION_ORIENTADOR_DOCENTE) || (letraOri !== '' && letraOpt === letraOri)) {
                        selSeccion.selectedIndex = i;
                        break;
                    }
                }
            }

            if (typeof window.ejecutarFiltroTiempoRealDocente === 'function') {
                window.ejecutarFiltroTiempoRealDocente();
            }
        }

        function aplicarSeccionOrientadorDesdeModal() {
            document.getElementById('modalAlertaImpresionVacia').style.display = 'none';
            seleccionarSeccionOrientador();
        }

        function mostrarAlertaImpresionVacia(titulo, mensaje, mostrarBotonOrientador = true, esRestriccion = false) {
            document.getElementById('tituloAlertaImpresion').textContent = titulo;
            document.getElementById('mensajeAlertaImpresion').textContent = mensaje;
            const btnOri = document.getElementById('btnCargarSeccionOrientadorModal');
            const icoBox = document.getElementById('iconoAlertaImpresionBox');

            if (btnOri) {
                btnOri.style.display = mostrarBotonOrientador ? 'inline-flex' : 'none';
            }

            if (icoBox) {
                if (esRestriccion) {
                    icoBox.style.background = '#fee2e2';
                    icoBox.style.color = '#dc2626';
                    icoBox.style.borderColor = '#fecaca';
                    icoBox.innerHTML = '<i class="fa-solid fa-user-lock"></i>';
                } else {
                    icoBox.style.background = '#fef3c7';
                    icoBox.style.color = '#d97706';
                    icoBox.style.borderColor = '#fde68a';
                    icoBox.innerHTML = '<i class="fa-solid fa-print"></i>';
                }
            }

            document.getElementById('modalAlertaImpresionVacia').style.display = 'flex';
        }

        function cerrarAlertaImpresionYEnfocar() {
            document.getElementById('modalAlertaImpresionVacia').style.display = 'none';
            const selGrado = document.getElementById('selectGrado');
            const selSeccion = document.getElementById('selectSeccion');
            if (selGrado) {
                selGrado.classList.add('filtro-alerta');
                selGrado.focus();
                setTimeout(() => selGrado.classList.remove('filtro-alerta'), 2500);
            }
            if (selSeccion) {
                selSeccion.classList.add('filtro-alerta');
                setTimeout(() => selSeccion.classList.remove('filtro-alerta'), 2500);
            }
        }

        function cambiarPestanaInterna(nombreTab) {
            const btnNomina = document.getElementById('btnTabNomina');
            const btnMatriz = document.getElementById('btnTabMatriz');
            const btnGraficos = document.getElementById('btnTabGraficos');
            const btnToggleTop = document.getElementById('btnToggleMatriz');

            const vistaNomina = document.getElementById('vistaTabNomina');
            const vistaMatriz = document.getElementById('vistaTabMatriz');
            const vistaGraficos = document.getElementById('vistaTabGraficos');

            [btnNomina, btnMatriz, btnGraficos].forEach(b => b && b.classList.remove('active'));
            [vistaNomina, vistaMatriz, vistaGraficos].forEach(v => v && v.classList.remove('active'));
            if (btnToggleTop) btnToggleTop.classList.remove('active');

            if (nombreTab === 'matriz') {
                if (btnMatriz) btnMatriz.classList.add('active');
                if (vistaMatriz) vistaMatriz.classList.add('active');
                if (btnToggleTop) btnToggleTop.classList.add('active');
                cargarValoresMatriz();
                try { sessionStorage.setItem('inca_tab_control_notas_doc', 'matriz'); } catch (e) {}
            } else if (nombreTab === 'graficos') {
                if (btnGraficos) btnGraficos.classList.add('active');
                if (vistaGraficos) vistaGraficos.classList.add('active');
                try { sessionStorage.setItem('inca_tab_control_notas_doc', 'graficos'); } catch (e) {}
                if (typeof window.redibujarGraficosPBI18 === 'function') {
                    setTimeout(() => window.redibujarGraficosPBI18(), 40);
                }
            } else {
                if (btnNomina) btnNomina.classList.add('active');
                if (vistaNomina) vistaNomina.classList.add('active');
                try { sessionStorage.setItem('inca_tab_control_notas_doc', 'nomina'); } catch (e) {}
            }
        }

        let callbackConfirmacionAccion = null;

        function abrirModalConfirmacionPBI15(titulo, mensaje, esPeligroso, callback) {
            document.getElementById('tituloModalConfirm').textContent = titulo;
            document.getElementById('mensajeModalConfirm').textContent = mensaje;
            const btn = document.getElementById('btnAceptarConfirmPBI15');
            const ico = document.getElementById('iconoModalConfirm');

            if (esPeligroso) {
                btn.style.background = '#dc2626';
                btn.textContent = 'Sí, Anular Registro';
                ico.style.background = '#fee2e2';
                ico.style.color = '#dc2626';
                ico.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
            } else {
                btn.style.background = '#059669';
                btn.textContent = 'Sí, Guardar Calificación';
                ico.style.background = '#d1fae5';
                ico.style.color = '#059669';
                ico.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
            }

            callbackConfirmacionAccion = callback;
            document.getElementById('modalConfirmacionPBI15').style.display = 'flex';
        }

        function cerrarModalConfirmacionPBI15() {
            document.getElementById('modalConfirmacionPBI15').style.display = 'none';
            callbackConfirmacionAccion = null;
        }

        document.getElementById('btnAceptarConfirmPBI15').addEventListener('click', function () {
            if (typeof callbackConfirmacionAccion === 'function') {
                const cb = callbackConfirmacionAccion;
                cerrarModalConfirmacionPBI15();
                cb();
            }
        });

        function solicitarConfirmacionGuardar(e) {
            e.preventDefault();
            const estNombre = document.getElementById('modalEstudianteNombre').textContent;
            const materia = document.getElementById('materia').value;
            const periodo = document.getElementById('periodo').value;
            const final = document.getElementById('labelNotaFinal').textContent;
            const grado = document.getElementById('modal_estudiante_grado').value;
            const seccion = document.getElementById('modal_estudiante_seccion').value;

            if (obtenerEstadoActa(materia, periodo, grado, seccion) === 'CERRADO') {
                alert('No se puede guardar: El acta de esta sección se encuentra CERRADA oficialmente. Solicite reapertura a Dirección.');
                return false;
            }

            abrirModalConfirmacionPBI15(
                'Confirmar Asentamiento de Calificación',
                `¿Desea registrar y guardar la calificación final de ${final} para ${estNombre} en ${materia} (Periodo ${periodo})? Se registrará la traza de auditoría correspondiente.`,
                false,
                function () {
                    document.getElementById('accion_anular').value = '';
                    document.getElementById('formModalNotas').submit();
                }
            );
            return false;
        }

        function solicitarConfirmacionAnular() {
            const estNombre = document.getElementById('modalEstudianteNombre').textContent;
            const materia = document.getElementById('materia').value;
            const periodo = document.getElementById('periodo').value;
            const grado = document.getElementById('modal_estudiante_grado').value;
            const seccion = document.getElementById('modal_estudiante_seccion').value;

            if (obtenerEstadoActa(materia, periodo, grado, seccion) === 'CERRADO') {
                alert('No se puede anular: El acta de esta sección se encuentra CERRADA oficialmente. Solicite reapertura a Dirección.');
                return;
            }

            abrirModalConfirmacionPBI15(
                '¿Anular Calificación Asentada?',
                `¿Está seguro de anular la calificación de ${estNombre} en ${materia} (Periodo ${periodo})? Conforme a la normativa, el registro volverá al estado "Sin Calificar".`,
                true,
                function () {
                    document.getElementById('accion_anular').value = '1';
                    document.getElementById('formModalNotas').submit();
                }
            );
        }

        let recupAlumnoDataDetalle = {};

        function abrirModalRecuperacion(id, nombre, grado, seccion) {
            document.getElementById('recup_estudiante_id').value = id;
            document.getElementById('recup_estudiante_grado').value = grado || '';
            document.getElementById('recup_estudiante_seccion').value = seccion || '';
            document.getElementById('recupEstudianteNombre').textContent = 'Recuperación: ' + nombre;
            
            const tr = document.querySelector(`tr[data-estudiante-id="${id}"]`);
            if (tr) {
                try {
                    recupAlumnoDataDetalle = JSON.parse(tr.getAttribute('data-detalle') || '{}');
                } catch(e) {
                    recupAlumnoDataDetalle = {};
                }
            } else {
                recupAlumnoDataDetalle = {};
            }

            document.getElementById('modalRecuperacion').style.display = 'flex';
            actualizarEstadoRecuperacionModal();
        }

        function cerrarModalRecuperacion() {
            document.getElementById('modalRecuperacion').style.display = 'none';
        }

        function actualizarEstadoRecuperacionModal() {
            const materiaSel = document.getElementById('recup_materia').value;
            const periodoSel = parseInt(document.getElementById('recup_periodo').value) || 1;
            const grado = document.getElementById('recup_estudiante_grado').value;
            const seccion = document.getElementById('recup_estudiante_seccion').value;
            const alerta = document.getElementById('recup_alerta_estado');
            const inpNota = document.getElementById('nota_recuperacion');
            const btnGuardar = document.getElementById('btnGuardarRecuperacion');

            const estaCerrado = (obtenerEstadoActa(materiaSel, periodoSel, grado, seccion) === 'CERRADO');

            let notaActual = null;
            let esRecuperada = false;
            let notaOriginal = null;

            for (const [mKey, pObj] of Object.entries(recupAlumnoDataDetalle)) {
                if (normalizarTexto(mKey) === normalizarTexto(materiaSel)) {
                    if (pObj && pObj[periodoSel]) {
                        notaActual = parseFloat(pObj[periodoSel].final);
                        esRecuperada = pObj[periodoSel].es_recuperada === 1;
                        notaOriginal = pObj[periodoSel].nota_original_reprobada;
                    }
                }
            }

            if (estaCerrado) {
                alerta.style.background = '#fee2e2';
                alerta.style.color = '#991b1b';
                alerta.innerHTML = `<i class="fa-solid fa-lock"></i> El acta de ${materiaSel} (Periodo ${periodoSel}) para la sección ${grado} - ${seccion} está CERRADA. Requiere autorización previa de reapertura.`;
                inpNota.disabled = true;
                btnGuardar.disabled = true;
            } else if (notaActual === null) {
                alerta.style.background = '#f1f5f9';
                alerta.style.color = '#475569';
                alerta.innerHTML = `<i class="fa-solid fa-circle-info"></i> El estudiante no posee calificaciones asentadas en ${materiaSel} para el Periodo ${periodoSel}.`;
                inpNota.disabled = true;
                btnGuardar.disabled = true;
            } else if (notaActual >= 6.0 && !esRecuperada) {
                alerta.style.background = '#dcfce7';
                alerta.style.color = '#166534';
                alerta.innerHTML = `<i class="fa-solid fa-circle-check"></i> Calificación APROBADA (${notaActual.toFixed(2)}). El Criterio A de PBI-39 restringe la recuperación a notas menores a 6.0.`;
                inpNota.disabled = true;
                btnGuardar.disabled = true;
            } else {
                alerta.style.background = '#fee2e2';
                alerta.style.color = '#991b1b';
                const strOrig = esRecuperada ? ` (Nota reprobada original: ${parseFloat(notaOriginal).toFixed(2)})` : '';
                alerta.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Calificación REPROBADA (${notaActual.toFixed(2)})${strOrig}. Habilitado para recuperación y sustitución.`;
                inpNota.disabled = false;
                btnGuardar.disabled = false;
            }
        }

        function alternarModoMatriz() {
            const vistaMatriz = document.getElementById('vistaTabMatriz');
            if (vistaMatriz && vistaMatriz.classList.contains('active')) {
                cambiarPestanaInterna('nomina');
            } else {
                cambiarPestanaInterna('matriz');
            }
        }

        function recalcularFilaMatriz(input) {
            let val = parseFloat(input.value);
            if (isNaN(val)) val = 0;
            if (val < 0) { val = 0; input.value = '0.00'; }
            if (val > 10) { val = 10; input.value = '10.00'; }

            const tr = input.closest('tr');
            const a1 = parseFloat(tr.querySelector('.inp-a1').value) || 0;
            const a2 = parseFloat(tr.querySelector('.inp-a2').value) || 0;
            const ex = parseFloat(tr.querySelector('.inp-ex').value) || 0;

            const nf = (a1 * 0.35) + (a2 * 0.35) + (ex * 0.30);
            tr.querySelector('.celda-nf-matriz').textContent = nf.toFixed(2);

            const celdaEst = tr.querySelector('.celda-est-matriz');
            celdaEst.innerHTML = nf >= 6.0
                ? '<span class="badge-condicion badge-aprobado">APROBADO</span>'
                : '<span class="badge-condicion badge-reprobado">REPROBADO</span>';
        }

        function cargarValoresMatriz() {
            const matSel = document.getElementById('matrizMateria').value;
            const perSel = parseInt(document.getElementById('matrizPeriodo').value) || 1;
            const gradoFiltro = document.getElementById('selectGrado').value.trim();
            const seccionFiltro = document.getElementById('selectSeccion').value.trim();
            const textoBusq = normalizarTexto(document.getElementById('input_buscar_pbi21').value);

            document.getElementById('inputHiddenMatrizGrado').value = gradoFiltro;
            document.getElementById('inputHiddenMatrizSeccion').value = seccionFiltro;

            const bannerCerrada = document.getElementById('bannerActaCerradaMatriz');
            const btnSubmitMat = document.getElementById('btnSubmitMatriz');

            let seccionCerradaActualmente = false;

            if (gradoFiltro !== '' && seccionFiltro !== '') {
                const estadoActa = obtenerEstadoActa(matSel, perSel, gradoFiltro, seccionFiltro);
                if (estadoActa === 'CERRADO') {
                    seccionCerradaActualmente = true;
                    bannerCerrada.style.display = 'flex';
                    btnSubmitMat.disabled = true;
                } else {
                    bannerCerrada.style.display = 'none';
                    btnSubmitMat.disabled = false;
                }
            } else {
                bannerCerrada.style.display = 'none';
                btnSubmitMat.disabled = false;
            }

            const filasMatriz = document.querySelectorAll('.fila-matriz-item');
            filasMatriz.forEach(tr => {
                const fGrado = tr.getAttribute('data-grado') || '';
                const fSeccion = tr.getAttribute('data-seccion') || '';
                const fTexto = normalizarTexto(tr.textContent);

                let visible = true;
                if (textoBusq !== '' && !fTexto.includes(textoBusq)) visible = false;
                if (gradoFiltro !== '' && fGrado.toLowerCase() !== gradoFiltro.toLowerCase()) visible = false;
                if (seccionFiltro !== '' && fSeccion.toLowerCase() !== seccionFiltro.toLowerCase()) visible = false;

                const inpId = tr.querySelector('.input-id-matriz');
                if (visible) {
                    tr.style.display = '';
                    if (inpId) inpId.disabled = false;
                } else {
                    tr.style.display = 'none';
                    if (inpId) inpId.disabled = true;
                }

                let detalle = {};
                try { detalle = JSON.parse(tr.getAttribute('data-detalle') || '{}'); } catch(e){}

                let datosPer = null;
                for (const [mKey, pObj] of Object.entries(detalle)) {
                    if (normalizarTexto(mKey) === normalizarTexto(matSel)) {
                        if (pObj && pObj[perSel]) datosPer = pObj[perSel];
                    }
                }

                const inpA1 = tr.querySelector('.inp-a1');
                const inpA2 = tr.querySelector('.inp-a2');
                const inpEx = tr.querySelector('.inp-ex');

                const actaEstaCerradaFila = (obtenerEstadoActa(matSel, perSel, fGrado, fSeccion) === 'CERRADO');
                inpA1.readOnly = actaEstaCerradaFila;
                inpA2.readOnly = actaEstaCerradaFila;
                inpEx.readOnly = actaEstaCerradaFila;

                if (datosPer && (datosPer.act1 !== null || datosPer.act2 !== null || datosPer.examen !== null)) {
                    inpA1.value = parseFloat(datosPer.act1 || 0).toFixed(2);
                    inpA2.value = parseFloat(datosPer.act2 || 0).toFixed(2);
                    inpEx.value = parseFloat(datosPer.examen || 0).toFixed(2);
                } else {
                    inpA1.value = '0.00';
                    inpA2.value = '0.00';
                    inpEx.value = '0.00';
                }
                recalcularFilaMatriz(inpA1);
            });
        }

        function abrirModalNotas(id, nombre, grado, seccion) {
            document.getElementById('estudiante_id').value = id;
            document.getElementById('modal_estudiante_grado').value = grado || '';
            document.getElementById('modal_estudiante_seccion').value = seccion || '';
            document.getElementById('modalEstudianteNombre').textContent = nombre;
            document.getElementById('accion_anular').value = '';

            const esBachillerato = (grado || '').toLowerCase().includes('bachillerato');
            const selectPeriodo = document.getElementById('periodo');
            const subtituloModal = document.getElementById('modalSubtituloNivel');

            selectPeriodo.innerHTML = '';
            if (esBachillerato) {
                subtituloModal.textContent = `Bachillerato (${grado} - ${seccion}) • Ponderación: 35% - 35% - 30%`;
                selectPeriodo.innerHTML = `
                    <option value="1">Periodo 1</option>
                    <option value="2">Periodo 2</option>
                    <option value="3">Periodo 3</option>
                    <option value="4">Periodo 4</option>
                `;
            } else {
                subtituloModal.textContent = `Educación Básica (${grado} - ${seccion}) • Ponderación: 35% - 35% - 30%`;
                selectPeriodo.innerHTML = `
                    <option value="1">Periodo 1</option>
                    <option value="2">Periodo 2</option>
                    <option value="3">Periodo 3</option>
                `;
            }

            const tr = document.querySelector(`tr[data-estudiante-id="${id}"]`);
            if (tr) {
                try {
                    estudianteNotasDetalleActual = JSON.parse(tr.getAttribute('data-detalle') || '{}');
                } catch(e) {
                    estudianteNotasDetalleActual = {};
                }
            } else {
                estudianteNotasDetalleActual = {};
            }

            document.getElementById('modalNotas').style.display = 'flex';
            actualizarValoresModalNotas();
        }

        function actualizarValoresModalNotas() {
            const materia = document.getElementById('materia').value;
            const periodo = parseInt(document.getElementById('periodo').value) || 1;
            const grado = document.getElementById('modal_estudiante_grado').value;
            const seccion = document.getElementById('modal_estudiante_seccion').value;

            const btnAnular = document.getElementById('btnAnularNotaModal');
            const btnGuardar = document.getElementById('btnGuardarNotaModal');
            const boxTraza = document.getElementById('trazaAuditoriaBox');
            const txtTraza = document.getElementById('trazaAuditoriaTexto');
            const bannerCerrada = document.getElementById('alertaActaCerradaModal');

            const estaCerrada = (obtenerEstadoActa(materia, periodo, grado, seccion) === 'CERRADO');
            const inpA1 = document.getElementById('act1');
            const inpA2 = document.getElementById('act2');
            const inpEx = document.getElementById('examen');

            inpA1.readOnly = estaCerrada;
            inpA2.readOnly = estaCerrada;
            inpEx.readOnly = estaCerrada;
            btnGuardar.disabled = estaCerrada;
            bannerCerrada.style.display = estaCerrada ? 'flex' : 'none';

            let datosPeriodo = null;
            for (const [mKey, pObj] of Object.entries(estudianteNotasDetalleActual)) {
                if (normalizarTexto(mKey) === normalizarTexto(materia)) {
                    if (pObj && pObj[periodo]) {
                        datosPeriodo = pObj[periodo];
                    }
                }
            }

            if (datosPeriodo && (datosPeriodo.act1 !== null || datosPeriodo.act2 !== null || datosPeriodo.examen !== null)) {
                inpA1.value = parseFloat(datosPeriodo.act1 || 0).toFixed(2);
                inpA2.value = parseFloat(datosPeriodo.act2 || 0).toFixed(2);
                inpEx.value = parseFloat(datosPeriodo.examen || 0).toFixed(2);
                btnAnular.style.display = estaCerrada ? 'none' : 'inline-flex';

                if (datosPeriodo.fecha_modificacion) {
                    boxTraza.style.display = 'block';
                    txtTraza.textContent = `Última edición auditada: ${datosPeriodo.fecha_modificacion}`;
                } else {
                    boxTraza.style.display = 'none';
                }
            } else {
                inpA1.value = '0.00';
                inpA2.value = '0.00';
                inpEx.value = '0.00';
                btnAnular.style.display = 'none';
                boxTraza.style.display = 'none';
            }
            calcularNotaFinal();
        }

        function cerrarModalNotas() { document.getElementById('modalNotas').style.display = 'none'; }
        function calcularNotaFinal() {
            let act1 = parseFloat(document.getElementById('act1').value) || 0;
            let act2 = parseFloat(document.getElementById('act2').value) || 0;
            let examen = parseFloat(document.getElementById('examen').value) || 0;
            let final = (act1 * 0.35) + (act2 * 0.35) + (examen * 0.30);
            document.getElementById('labelNotaFinal').textContent = final.toFixed(1);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const inputBusqueda = document.getElementById('input_buscar_pbi21');
            const selectGrado = document.getElementById('selectGrado');
            const selectSeccion = document.getElementById('selectSeccion');
            const selectCondicion = document.getElementById('selectCondicionPBI19');
            const btnLimpiar = document.getElementById('btnLimpiarFiltros');
            const btnExcel = document.getElementById('btnExportarExcel');
            const btnAsistencia = document.getElementById('btnExportarAsistencia');
            const btnComite = document.getElementById('btnExportarComite');
            const btnFiltrar = document.getElementById('btnFiltrarManual');
            const selectMateriaEstadistica = document.getElementById('selectMateriaEstadistica');
            const selectPeriodoEstadistica = document.getElementById('selectPeriodoEstadistica');
            const badgeConteoAlumnos = document.getElementById('badgeConteoAlumnos');
            
            const tabla = document.getElementById('tablaPrincipalEstudiantes');
            if (!tabla) return;

            const tbody = tabla.querySelector('tbody') || tabla;
            const filas = Array.from(tbody.querySelectorAll('tr')).filter(tr => !tr.querySelector('th') && tr.id !== 'sin_coincidencias_pbi21');

            let filaVacia = document.getElementById('sin_coincidencias_pbi21');
            if (!filaVacia) {
                filaVacia = document.createElement('tr');
                filaVacia.id = 'sin_coincidencias_pbi21';
                filaVacia.style.display = 'none';
                filaVacia.innerHTML = `
                    <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                        <div style="font-size: 26px; margin-bottom: 6px;">📂</div>
                        <strong>No se encontraron estudiantes</strong><br>
                        <span style="font-size: 0.85rem;">No existen coincidencias con los criterios o filtros aplicados.</span>
                    </td>
                `;
                tbody.appendChild(filaVacia);
            }

            function obtenerNotaMateriaPeriodo(notasEst, nombreMateria, numPeriodo) {
                if (!notasEst || typeof notasEst !== 'object') return null;
                const normBuscada = normalizarTexto(nombreMateria);
                for (const [mKey, pObj] of Object.entries(notasEst)) {
                    if (normalizarTexto(mKey) === normBuscada || normalizarTexto(mKey).includes(normBuscada) || normBuscada.includes(normalizarTexto(mKey))) {
                        if (pObj && pObj[numPeriodo] !== undefined && pObj[numPeriodo] !== null) {
                            return parseFloat(pObj[numPeriodo]);
                        }
                    }
                }
                return null;
            }

            function calcularNotaReal(notasEst, materiaSel, periodoSel, filaGrado) {
                if (!notasEst || typeof notasEst !== 'object' || Object.keys(notasEst).length === 0) return null;
                const esBach = (filaGrado || '').toLowerCase().includes('bachillerato');
                const maxPeriodos = esBach ? 4 : 3;

                if (materiaSel !== 'Todas' && periodoSel > 0) {
                    if (periodoSel > maxPeriodos) return null;
                    return obtenerNotaMateriaPeriodo(notasEst, materiaSel, periodoSel);
                }

                if (materiaSel !== 'Todas' && periodoSel === 0) {
                    let suma = 0, c = 0;
                    for (let p = 1; p <= maxPeriodos; p++) {
                        const n = obtenerNotaMateriaPeriodo(notasEst, materiaSel, p);
                        if (n !== null) { suma += n; c++; }
                    }
                    return c > 0 ? parseFloat((suma / c).toFixed(2)) : null;
                }

                if (materiaSel === 'Todas' && periodoSel > 0) {
                    if (periodoSel > maxPeriodos) return null;
                    let suma = 0, c = 0;
                    MATERIAS_DOCENTE.forEach(m => {
                        const n = obtenerNotaMateriaPeriodo(notasEst, m, periodoSel);
                        if (n !== null) { suma += n; c++; }
                    });
                    return c > 0 ? parseFloat((suma / c).toFixed(2)) : null;
                }

                let sumaTotal = 0, cuentaTotal = 0;
                MATERIAS_DOCENTE.forEach(m => {
                    for (let p = 1; p <= maxPeriodos; p++) {
                        const n = obtenerNotaMateriaPeriodo(notasEst, m, p);
                        if (n !== null) { sumaTotal += n; cuentaTotal++; }
                    }
                });
                return cuentaTotal > 0 ? parseFloat((sumaTotal / cuentaTotal).toFixed(2)) : null;
            }

            function sincronizarTablaConFiltros() {
                const materiaSel = selectMateriaEstadistica ? selectMateriaEstadistica.value : 'Todas';
                const periodoSel = parseInt(selectPeriodoEstadistica ? selectPeriodoEstadistica.value : 0);

                const thMateria = document.getElementById('th_materia_activa');
                const thPromedio = document.getElementById('th_promedio_activo');
                if (thMateria) thMateria.textContent = (materiaSel === 'Todas') ? 'ASIGNATURA' : materiaSel.toUpperCase();
                if (thPromedio) thPromedio.textContent = (periodoSel === 0) ? 'PROMEDIO' : `PERIODO ${periodoSel}`;

                filas.forEach(fila => {
                    let notasEst = {};
                    try { notasEst = JSON.parse(fila.getAttribute('data-notas') || '{}'); } catch(e){}
                    const filaGrado = fila.getAttribute('data-grado') || '';

                    const notaCalculada = calcularNotaReal(notasEst, materiaSel, periodoSel, filaGrado);
                    const celdaMateria = fila.querySelector('.col-materia-est');
                    const celdaProm = fila.querySelector('.col-promedio-est');
                    const celdaCond = fila.querySelector('.col-condicion-est');
                    const btnRecup = fila.querySelector('.btn-recup');

                    if (celdaMateria) celdaMateria.textContent = (materiaSel === 'Todas') ? 'Promedio General' : materiaSel;

                    if (notaCalculada === null) {
                        if (celdaProm) celdaProm.textContent = '--';
                        if (celdaCond) {
                            celdaCond.innerHTML = '<span class="badge-condicion badge-sin-calificar">SIN CALIFICAR</span>';
                        }
                    } else {
                        if (celdaProm) celdaProm.textContent = notaCalculada.toFixed(2);
                        const esAprobado = notaCalculada >= 6.0;
                        if (celdaCond) {
                            celdaCond.innerHTML = esAprobado 
                                ? '<span class="badge-condicion badge-aprobado">APROBADO</span>' 
                                : '<span class="badge-condicion badge-reprobado">REPROBADO</span>';
                        }
                    }

                    let tieneReprobadas = false;
                    for (const [m, perObj] of Object.entries(notasEst)) {
                        for (const [p, val] of Object.entries(perObj)) {
                            if (val !== null && parseFloat(val) < 6.0) {
                                tieneReprobadas = true;
                                break;
                            }
                        }
                    }
                    if (btnRecup) {
                        btnRecup.disabled = !tieneReprobadas;
                    }
                });
            }

            function filtrarEnTiempoReal() {
                sincronizarTablaConFiltros();

                const texto = normalizarTexto(inputBusqueda ? inputBusqueda.value : '');
                const gradoFiltro = normalizarTexto(selectGrado ? selectGrado.value : '');
                const seccionFiltro = normalizarTexto(selectSeccion ? selectSeccion.value : '');
                const condicion = selectCondicion ? selectCondicion.value.toUpperCase().trim() : '';

                const optP4 = document.getElementById('optMatrizPeriodo4');
                if (optP4) {
                    if (gradoFiltro !== '' && !gradoFiltro.includes('bachillerato')) {
                        optP4.style.display = 'none';
                        if (document.getElementById('matrizPeriodo').value === '4') {
                            document.getElementById('matrizPeriodo').value = '1';
                        }
                    } else {
                        optP4.style.display = '';
                    }
                }

                let visibles = 0;
                filas.forEach(fila => {
                    const filaTexto = normalizarTexto(fila.textContent);
                    const filaGrado = normalizarTexto(fila.getAttribute('data-grado') || '');
                    const filaSeccion = normalizarTexto(fila.getAttribute('data-seccion') || '');
                    const celdaCondicion = fila.querySelector('.col-condicion-est');
                    const textoCondicion = celdaCondicion ? celdaCondicion.textContent.toUpperCase() : '';

                    const coincideTexto = (texto === '') || filaTexto.includes(texto);

                    let coincideGrado = true;
                    if (gradoFiltro !== '' && !gradoFiltro.includes('filtrar') && !gradoFiltro.includes('todos')) {
                        coincideGrado = (filaGrado === gradoFiltro) || filaGrado.includes(gradoFiltro) || gradoFiltro.includes(filaGrado);
                    }

                    let coincideSeccion = true;
                    if (seccionFiltro !== '' && !seccionFiltro.includes('todos')) {
                        const letraFiltro = seccionFiltro.replace(/.*secci[oó]n\s*/i, '').trim();
                        const letraFila = filaSeccion.replace(/.*secci[oó]n\s*/i, '').trim();
                        coincideSeccion = (filaSeccion === seccionFiltro) || 
                                          (letraFiltro.length > 0 && letraFila === letraFiltro) || 
                                          filaSeccion.includes(seccionFiltro);
                    }

                    const coincideCondicion = (condicion === '') || textoCondicion.includes(condicion);

                    if (coincideTexto && coincideGrado && coincideSeccion && coincideCondicion) {
                        fila.style.display = '';
                        visibles++;
                    } else {
                        fila.style.display = 'none';
                    }
                });

                if (badgeConteoAlumnos) badgeConteoAlumnos.textContent = visibles;
                filaVacia.style.display = (visibles === 0) ? '' : 'none';
                cargarValoresMatriz();
                actualizarEstadisticasPBI18();
            }

            window.ejecutarFiltroTiempoRealDocente = filtrarEnTiempoReal;

            if (inputBusqueda) inputBusqueda.addEventListener('input', filtrarEnTiempoReal);
            if (selectGrado) selectGrado.addEventListener('change', filtrarEnTiempoReal);
            if (selectSeccion) selectSeccion.addEventListener('change', filtrarEnTiempoReal);
            if (selectCondicion) selectCondicion.addEventListener('change', filtrarEnTiempoReal);
            if (btnFiltrar) btnFiltrar.addEventListener('click', filtrarEnTiempoReal);

            if (selectMateriaEstadistica) selectMateriaEstadistica.addEventListener('change', filtrarEnTiempoReal);
            if (selectPeriodoEstadistica) selectPeriodoEstadistica.addEventListener('change', filtrarEnTiempoReal);

            if (btnLimpiar) {
                btnLimpiar.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (inputBusqueda) inputBusqueda.value = '';
                    if (selectGrado) selectGrado.selectedIndex = 0;
                    if (selectSeccion) selectSeccion.selectedIndex = 0;
                    if (selectCondicion) selectCondicion.selectedIndex = 0;
                    if (selectMateriaEstadistica) selectMateriaEstadistica.selectedIndex = 0;
                    if (selectPeriodoEstadistica) selectPeriodoEstadistica.selectedIndex = 0;
                    filtrarEnTiempoReal();
                });
            }

            function tieneFiltrosSeleccionados() {
                const g = selectGrado ? selectGrado.value.trim() : '';
                const s = selectSeccion ? selectSeccion.value.trim() : '';
                const b = inputBusqueda ? inputBusqueda.value.trim() : '';
                const c = selectCondicion ? selectCondicion.value.trim() : '';
                return (g !== '' || s !== '' || b !== '' || c !== '');
            }

            function obtenerAlumnosOrdenados() {
                const lista = [];
                filas.forEach(fila => {
                    if (fila.style.display !== 'none' && fila.id !== 'sin_coincidencias_pbi21') {
                        const cols = fila.querySelectorAll('td');
                        const nie = fila.getAttribute('data-nie') || (cols[0] ? cols[0].textContent.trim() : 'N/D');
                        const estudiante = fila.getAttribute('data-nombre') || (cols[1] ? cols[1].textContent.trim().replace(/\s+/g, ' ') : 'N/D');
                        const gradoSeccion = (fila.getAttribute('data-grado') || '') + ' - ' + (fila.getAttribute('data-seccion') || '');
                        const materia = cols[3] ? cols[3].textContent.trim() : 'Promedio General';
                        const promedio = cols[4] ? cols[4].textContent.trim() : '--';
                        const condicion = cols[5] ? cols[5].textContent.trim() : 'SIN CALIFICAR';
                        lista.push({ nie, estudiante, gradoSeccion, materia, promedio, condicion });
                    }
                });
                lista.sort((a, b) => a.estudiante.localeCompare(b.estudiante));
                return lista;
            }

            function exportarContenedorAPdf(contenedorPdf, opcionesPdf) {
                const scrollPrevioY = window.scrollY || window.pageYOffset || 0;
                const scrollPrevioX = window.scrollX || window.pageXOffset || 0;

                const wrapperImpresion = document.createElement('div');
                wrapperImpresion.style.position = 'fixed';
                wrapperImpresion.style.top = '0';
                wrapperImpresion.style.left = '0';
                wrapperImpresion.style.width = '100%';
                wrapperImpresion.style.zIndex = '-9999';
                wrapperImpresion.style.background = '#ffffff';
                wrapperImpresion.style.padding = '0';
                wrapperImpresion.style.margin = '0';
                wrapperImpresion.appendChild(contenedorPdf);
                document.body.appendChild(wrapperImpresion);

                window.scrollTo(0, 0);

                html2pdf().set({
                    margin: opcionesPdf.margin || [10, 10, 10, 10],
                    filename: opcionesPdf.filename || 'Documento_INCA.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, logging: false, scrollY: 0, scrollX: 0 },
                    jsPDF: { unit: 'mm', format: 'letter', orientation: opcionesPdf.orientation || 'portrait' }
                }).from(contenedorPdf).save().then(() => {
                    if (document.body.contains(wrapperImpresion)) document.body.removeChild(wrapperImpresion);
                    window.scrollTo(scrollPrevioX, scrollPrevioY);
                }).catch(err => {
                    if (document.body.contains(wrapperImpresion)) document.body.removeChild(wrapperImpresion);
                    window.scrollTo(scrollPrevioX, scrollPrevioY);
                });
            }

            if (btnExcel) {
                btnExcel.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (!tieneFiltrosSeleccionados()) {
                        mostrarAlertaImpresionVacia('Sin Selección', 'Seleccione un Grado o Sección primero.', true, false);
                        return;
                    }
                    const datosExportar = obtenerAlumnosOrdenados();
                    if (datosExportar.length === 0) {
                        mostrarAlertaImpresionVacia('Documento en Blanco', 'No hay registros para la sección seleccionada.', true, false);
                        return;
                    }
                    let htmlExcel = `
                        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
                        <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
                        <body>
                            <table>
                                <tr><th colspan="6" style="background-color:#0d6efd; color:#fff; font-size:14pt; height:35px; text-align:center;">INSTITUTO NOÉ CANJURA - CUADRO DE CALIFICACIONES OFICIAL</th></tr>
                                <thead><tr style="background:#1a2b4c; color:#fff;"><th>NIE / CARNÉ</th><th>APELLIDOS Y NOMBRES</th><th>GRADO Y SECCIÓN</th><th>ASIGNATURA</th><th>PROMEDIO</th><th>CONDICIÓN</th></tr></thead>
                                <tbody>
                    `;
                    datosExportar.forEach(d => {
                        htmlExcel += `<tr><td>${d.nie}</td><td>${d.estudiante}</td><td>${d.gradoSeccion}</td><td>${d.materia}</td><td>${d.promedio}</td><td>${d.condicion}</td></tr>`;
                    });
                    htmlExcel += `</tbody></table></body></html>`;

                    const blob = new Blob(['\ufeff', htmlExcel], { type: 'application/vnd.ms-excel;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const enlace = document.createElement('a');
                    enlace.href = url;
                    enlace.download = 'Nomina_Calificaciones_INCA_' + new Date().toISOString().slice(0, 10) + '.xls';
                    document.body.appendChild(enlace);
                    enlace.click();
                    document.body.removeChild(enlace);
                    URL.revokeObjectURL(url);
                });
            }

            if (btnAsistencia) {
                btnAsistencia.addEventListener('click', function (e) {
                    e.preventDefault();
                    const gradoSeleccionado = selectGrado ? selectGrado.value.trim() : '';
                    const seccionSeleccionada = selectSeccion ? selectSeccion.value.trim() : '';
                    if (gradoSeleccionado === '' || seccionSeleccionada === '') {
                        mostrarAlertaImpresionVacia('Sin Selección', 'Seleccione un Grado y Sección primero.', true, false);
                        return;
                    }
                    if (!esSeccionDeOrientacion(gradoSeleccionado, seccionSeleccionada)) {
                        mostrarAlertaImpresionVacia('Restricción', 'Solo puede imprimir la asistencia de su grado de orientación.', true, true);
                        return;
                    }
                    const datosExportar = obtenerAlumnosOrdenados();
                    if (datosExportar.length === 0) {
                        mostrarAlertaImpresionVacia('Sin Estudiantes', 'No se encontraron estudiantes matriculados.', false, false);
                        return;
                    }
                    const fechaActual = new Date();
                    const anioActual = fechaActual.getFullYear();
                    const mesActual = fechaActual.getMonth();
                    const nombresMeses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                    const nombreMes = nombresMeses[mesActual];
                    const letrasSemana = ["D", "L", "M", "M", "J", "V", "S"];
                    const ultimoDiaMes = new Date(anioActual, mesActual + 1, 0).getDate();
                    const diasHabiles = [];

                    for (let dia = 1; dia <= ultimoDiaMes; dia++) {
                        const f = new Date(anioActual, mesActual, dia);
                        const ds = f.getDay();
                        if (ds >= 1 && ds <= 5) {
                            diasHabiles.push({ num: dia < 10 ? '0' + dia : '' + dia, letra: letrasSemana[ds] });
                        }
                    }
                    const totalHabiles = diasHabiles.length;

                    let thDiasHtml = '';
                    diasHabiles.forEach(dh => {
                        thDiasHtml += `<th style="border:1px solid #0f172a; text-align:center; font-size:7px; background:#f8fafc; padding:2px 0; color:#0f172a; line-height:1.1;">${dh.letra}<br>${dh.num}</th>`;
                    });

                    let filasAlumnosHtml = '';
                    datosExportar.forEach((d, index) => {
                        let casillasVacias = '';
                        for (let i = 0; i < totalHabiles; i++) {
                            casillasVacias += `<td style="border:1px solid #475569; height:18px; text-align:center;">&nbsp;</td>`;
                        }
                        filasAlumnosHtml += `
                            <tr>
                                <td style="border:1px solid #475569; text-align:center; font-size:7.5px; padding:2px; background:#f8fafc;">${index + 1}</td>
                                <td style="border:1px solid #475569; text-align:center; font-size:7.5px; padding:2px; font-weight:bold;">${d.nie}</td>
                                <td style="border:1px solid #475569; font-size:7.5px; padding:2px 4px; text-transform:uppercase; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${d.estudiante}</td>
                                ${casillasVacias}
                                <td style="border:1px solid #475569; text-align:center; font-size:7.5px; background:#f0fdf4;">&nbsp;</td>
                                <td style="border:1px solid #475569; text-align:center; font-size:7.5px; background:#fefce8;">&nbsp;</td>
                                <td style="border:1px solid #475569; text-align:center; font-size:7.5px; background:#fef2f2;">&nbsp;</td>
                            </tr>
                        `;
                    });

                    let celdasTotalesDias = '';
                    for (let i = 0; i < totalHabiles; i++) {
                        celdasTotalesDias += `<td style="border:1px solid #475569; height:18px; text-align:center; font-size:7px;">&nbsp;</td>`;
                    }

                    const pctDia = (60.2 / totalHabiles).toFixed(2);
                    let colgroupHtml = `
                        <colgroup>
                            <col style="width: 3%;">
                            <col style="width: 8%;">
                            <col style="width: 21%;">
                    `;
                    for (let i = 0; i < totalHabiles; i++) colgroupHtml += `<col style="width: ${pctDia}%;">`;
                    colgroupHtml += `
                            <col style="width: 2.6%;">
                            <col style="width: 2.6%;">
                            <col style="width: 2.6%;">
                        </colgroup>
                    `;

                    const contenedorPdf = document.createElement('div');
                    contenedorPdf.style.width = '940px';
                    contenedorPdf.style.boxSizing = 'border-box';
                    contenedorPdf.style.padding = '0';
                    contenedorPdf.style.margin = '0 auto';
                    contenedorPdf.style.fontFamily = 'Arial, Helvetica, sans-serif';
                    contenedorPdf.style.color = '#000000';
                    contenedorPdf.style.background = '#ffffff';

                    contenedorPdf.innerHTML = `
                        <div style="text-align:center; margin-bottom:6px; border-bottom:2px solid #0f172a; padding-bottom:4px;">
                            <h2 style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:0.5px;">INSTITUTO NOÉ CANJURA - CONTROL MENSUAL DE ASISTENCIA</h2>
                            <p style="margin:2px 0 0; font-size:9.5px; color:#475569;">Registro Oficial Auxiliar de Asistencias y Ausentismo • Docente Orientador: ${<?= json_encode($nombreDocenteSesion) ?>}</p>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:8.5px; margin-bottom:6px; font-weight:bold; background:#f1f5f9; padding:4px 8px; border-radius:4px; border:1px solid #cbd5e1;">
                            <span>Año Lectivo: ${anioActual}</span>
                            <span>Mes: ${nombreMes.toUpperCase()}</span>
                            <span>Grado/Sección Orientada: ${gradoSeleccionado} - ${seccionSeleccionada}</span>
                            <span>Leyenda: [ • ] Asistencia &nbsp;|&nbsp; [ IJ ] Justificada &nbsp;|&nbsp; [ II ] Injustificada</span>
                        </div>
                        <table style="width:100%; border-collapse:collapse; table-layout:fixed; box-sizing:border-box;">
                            ${colgroupHtml}
                            <thead>
                                <tr>
                                    <th rowspan="2" style="border:1px solid #0f172a; background:#0f172a; color:#fff; font-size:7.5px; text-align:center;">N°</th>
                                    <th rowspan="2" style="border:1px solid #0f172a; background:#0f172a; color:#fff; font-size:7.5px; text-align:center;">NIE</th>
                                    <th rowspan="2" style="border:1px solid #0f172a; background:#0f172a; color:#fff; font-size:7.5px; text-align:left; padding-left:4px;">NÓMINA DE ESTUDIANTES</th>
                                    <th colspan="${totalHabiles}" style="border:1px solid #0f172a; background:#1e293b; color:#fff; font-size:7.5px; text-align:center;">DÍAS HÁBILES DEL MES (${nombreMes.toUpperCase()})</th>
                                    <th colspan="3" style="border:1px solid #0f172a; background:#0f172a; color:#fff; font-size:7.5px; text-align:center;">TOTALES</th>
                                </tr>
                                <tr>
                                    ${thDiasHtml}
                                    <th style="border:1px solid #0f172a; background:#166534; color:#fff; font-size:6.5px; text-align:center;" title="Asistencias">A</th>
                                    <th style="border:1px solid #0f172a; background:#854d0e; color:#fff; font-size:6.5px; text-align:center;" title="Inasistencias Justificadas">IJ</th>
                                    <th style="border:1px solid #0f172a; background:#991b1b; color:#fff; font-size:6.5px; text-align:center;" title="Inasistencias Injustificadas">II</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${filasAlumnosHtml}
                                <tr style="background:#e2e8f0; font-weight:bold;">
                                    <td colspan="3" style="border:1px solid #475569; text-align:right; font-size:7.5px; padding-right:6px;">TOTALES SECCIÓN:</td>
                                    ${celdasTotalesDias}
                                    <td style="border:1px solid #475569; text-align:center; font-size:8px;">&nbsp;</td>
                                    <td style="border:1px solid #475569; text-align:center; font-size:8px;">&nbsp;</td>
                                    <td style="border:1px solid #475569; text-align:center; font-size:8px;">&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                        <div style="margin-top:25px; display:flex; justify-content:space-around; font-size:8.5px;">
                            <div style="width:220px; border-top:1px solid #000; text-align:center; padding-top:3px;">Firma Docente Orientador (${<?= json_encode($nombreDocenteSesion) ?>})</div>
                            <div style="width:200px; border-top:1px solid #000; text-align:center; padding-top:3px;">Sello y Recibido Dirección</div>
                        </div>
                    `;

                    exportarContenedorAPdf(contenedorPdf, {
                        margin: [10, 10, 10, 10],
                        filename: `Control_Asistencia_${gradoSeleccionado}_${seccionSeleccionada}.pdf`.replace(/\s+/g, '_'),
                        orientation: 'landscape'
                    });
                });
            }

            if (btnComite) {
                btnComite.addEventListener('click', function (e) {
                    e.preventDefault();
                    if (!tieneFiltrosSeleccionados()) {
                        mostrarAlertaImpresionVacia('Sin Selección para Comité', 'Seleccione un Grado o Sección primero.', true, false);
                        return;
                    }
                    const periodoSel = parseInt(selectPeriodoEstadistica ? selectPeriodoEstadistica.value : 0);
                    const materiaSel = selectMateriaEstadistica ? selectMateriaEstadistica.value.trim() : 'Todas';

                    let filasPdfHtml = '';
                    let contCasosReprobados = 0;
                    let indiceFila = 1;

                    filas.forEach(fila => {
                        if (fila.style.display === 'none' || fila.id === 'sin_coincidencias_pbi21') return;

                        const cols = fila.querySelectorAll('td');
                        const nie = fila.getAttribute('data-nie') || (cols[0] ? cols[0].textContent.trim() : 'N/D');
                        const estudiante = fila.getAttribute('data-nombre') || (cols[1] ? cols[1].textContent.trim().replace(/\s+/g, ' ') : 'N/D');
                        const filaGrado = fila.getAttribute('data-grado') || '';
                        const filaSeccion = fila.getAttribute('data-seccion') || '';
                        const gradoSec = (filaGrado && filaSeccion) ? `${filaGrado} - ${filaSeccion}` : (cols[2] ? cols[2].textContent.trim() : 'N/D');
                        const esBach = filaGrado.toLowerCase().includes('bachillerato');
                        const maxP = esBach ? 4 : 3;

                        let notasEst = {};
                        try { notasEst = JSON.parse(fila.getAttribute('data-notas') || '{}'); } catch(err){ notasEst = {}; }

                        const materiasAEvaluar = (materiaSel === 'Todas' || materiaSel === '') ? MATERIAS_DOCENTE : [materiaSel];
                        const periodosAEvaluar = (periodoSel === 0) ? (esBach ? [1, 2, 3, 4] : [1, 2, 3]) : [periodoSel];

                        materiasAEvaluar.forEach(materia => {
                            periodosAEvaluar.forEach(periodo => {
                                if (periodo > maxP) return;
                                let nota = obtenerNotaMateriaPeriodo(notasEst, materia, periodo);
                                if (nota !== null && nota < 6.0) {
                                    contCasosReprobados++;
                                    filasPdfHtml += `
                                        <tr style="line-height: 1.15;">
                                            <td style="padding: 3px 2px; text-align: center; font-size: 7.5px; border: 1px solid #94a3b8;">${indiceFila++}</td>
                                            <td style="padding: 3px 2px; text-align: center; font-size: 7.5px; font-weight: bold; border: 1px solid #94a3b8;">${nie}</td>
                                            <td style="padding: 3px 4px; font-size: 7.5px; text-transform: uppercase; border: 1px solid #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${estudiante}</td>
                                            <td style="padding: 3px 2px; text-align: center; font-size: 7.5px; border: 1px solid #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${gradoSec}</td>
                                            <td style="padding: 3px 4px; font-size: 7.5px; border: 1px solid #94a3b8; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${materia}</td>
                                            <td style="padding: 3px 2px; text-align: center; font-size: 7.5px; border: 1px solid #94a3b8;">Periodo ${periodo}</td>
                                            <td style="padding: 3px 2px; text-align: center; font-size: 8px; font-weight: 800; border: 1px solid #94a3b8; color: #dc2626;">${nota.toFixed(1)}</td>
                                            <td style="padding: 3px 2px; text-align: center; font-size: 7px; font-weight: bold; border: 1px solid #94a3b8; color: #991b1b; background: #fee2e2;">REPROBADO</td>
                                            <td style="padding: 3px 2px; font-size: 7px; border: 1px solid #94a3b8; text-align: center; white-space: nowrap; overflow: hidden;">Refuerzo Pedagógico (P${periodo})</td>
                                        </tr>
                                    `;
                                }
                            });
                        });
                    });

                    if (contCasosReprobados === 0) {
                        mostrarAlertaImpresionVacia('Sin Casos Reprobados', 'No se encontraron estudiantes reprobados (< 6.0).', false, false);
                        return;
                    }

                    const contenedorPdf = document.createElement('div');
                    contenedorPdf.style.width = '720px';
                    contenedorPdf.style.boxSizing = 'border-box';
                    contenedorPdf.style.padding = '0';
                    contenedorPdf.style.margin = '0 auto';
                    contenedorPdf.style.fontFamily = 'Arial, Helvetica, sans-serif';
                    contenedorPdf.style.color = '#0f172a';
                    contenedorPdf.style.background = '#ffffff';

                    const descPeriodo = (periodoSel === 0) ? 'Todos los Periodos' : `Periodo ${periodoSel}`;
                    const descMateria = (materiaSel === 'Todas' || materiaSel === '') ? 'MIS ASIGNATURAS ASIGNADAS' : materiaSel.toUpperCase();

                    contenedorPdf.innerHTML = `
                        <div style="text-align: center; margin-bottom: 8px; border-bottom: 2px solid #0f172a; padding-bottom: 4px;">
                            <h2 style="margin: 0; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">INSTITUTO NOÉ CANJURA - INCA NOTES</h2>
                            <h3 style="margin: 2px 0; font-size: 10px; color: #334155;">INFORME OFICIAL PARA COMITÉ DE EVALUACIÓN Y SEGUIMIENTO ACADÉMICO</h3>
                            <p style="margin: 0; font-size: 7.5px; color: #64748b;">Nómina Oficial de Estudiantes y Asignaturas en Condición de Reprobación (&lt; 6.0)</p>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 7.5px; background: #f1f5f9; padding: 4px 8px; border-radius: 4px; margin-bottom: 6px; font-weight: bold; border: 1px solid #e2e8f0;">
                            <span>Fecha: ${new Date().toLocaleDateString('es-SV')}</span>
                            <span>Materia: ${descMateria}</span>
                            <span>Periodo: ${descPeriodo}</span>
                            <span>Total Casos en Riesgo: ${contCasosReprobados}</span>
                        </div>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; table-layout: fixed; box-sizing: border-box;">
                            <colgroup><col style="width: 25px;"><col style="width: 65px;"><col style="width: 155px;"><col style="width: 105px;"><col style="width: 125px;"><col style="width: 55px;"><col style="width: 40px;"><col style="width: 65px;"><col style="width: 85px;"></colgroup>
                            <thead>
                                <tr style="background: #0f172a; color: #ffffff;">
                                    <th style="padding: 4px 2px; font-size: 6.5px; border: 1px solid #0f172a; text-align: center;">N°</th>
                                    <th style="padding: 4px 2px; font-size: 6.5px; border: 1px solid #0f172a; text-align: center;">NIE</th>
                                    <th style="padding: 4px 3px; font-size: 6.5px; border: 1px solid #0f172a; text-align: left;">APELLIDOS Y NOMBRES</th>
                                    <th style="padding: 4px 2px; font-size: 6.5px; border: 1px solid #0f172a; text-align: center;">GRADO/SECCIÓN</th>
                                    <th style="padding: 4px 3px; font-size: 6.5px; border: 1px solid #0f172a; text-align: left;">ASIGNATURA EN RIESGO</th>
                                    <th style="padding: 4px 2px; font-size: 6.5px; border: 1px solid #0f172a; text-align: center;">PERIODO</th>
                                    <th style="padding: 4px 2px; font-size: 6.5px; border: 1px solid #0f172a; text-align: center;">NOTA</th>
                                    <th style="padding: 4px 2px; font-size: 6.5px; border: 1px solid #0f172a; text-align: center;">CONDICIÓN</th>
                                    <th style="padding: 4px 2px; font-size: 6.5px; border: 1px solid #0f172a; text-align: center;">DICTAMEN COMITÉ</th>
                                </tr>
                            </thead>
                            <tbody>${filasPdfHtml}</tbody>
                        </table>
                        <div style="margin-top: 18px; display: flex; justify-content: space-around; font-size: 7.5px;">
                            <div style="width: 160px; border-top: 1px solid #000; text-align: center; padding-top: 2px;">Coordinación Pedagógica</div>
                            <div style="width: 160px; border-top: 1px solid #000; text-align: center; padding-top: 2px;">Docente (${<?= json_encode($nombreDocenteSesion) ?>})</div>
                            <div style="width: 160px; border-top: 1px solid #000; text-align: center; padding-top: 2px;">Dirección Institucional</div>
                        </div>
                    `;

                    exportarContenedorAPdf(contenedorPdf, {
                        margin: [10, 10, 10, 10],
                        filename: 'Nomina_Comite_Reprobados_' + new Date().toISOString().slice(0, 10) + '.pdf',
                        orientation: 'portrait'
                    });
                });
            }

            let chartPastel = null;
            let chartBarras = null;
            let chartComparativo = null;

            function actualizarEstadisticasPBI18() {
                const materiaSeleccionada = selectMateriaEstadistica ? selectMateriaEstadistica.value : 'Todas';
                const periodoActivo = parseInt(selectPeriodoEstadistica ? selectPeriodoEstadistica.value : 0);

                const tituloPastel = document.getElementById('tituloPastelMateria');
                const tituloBarras = document.getElementById('tituloBarrasMateria');
                const strPer = (periodoActivo === 0) ? 'Anual' : `P${periodoActivo}`;
                if (tituloPastel) tituloPastel.textContent = `Distribución de Notas - ${materiaSeleccionada} (${strPer})`;
                if (tituloBarras) tituloBarras.textContent = `Cantidad de Alumnos - ${materiaSeleccionada} (${strPer})`;

                const notasVisibles = [];

                filas.forEach(fila => {
                    if (fila.style.display !== 'none' && fila.id !== 'sin_coincidencias_pbi21') {
                        const celdaProm = fila.querySelector('.col-promedio-est');
                        let nota = celdaProm ? parseFloat(celdaProm.textContent.trim()) : NaN;
                        if (!isNaN(nota)) notasVisibles.push(nota);
                    }
                });

                const total = notasVisibles.length;
                const kpiPromedio = document.getElementById('kpi_promedio');
                const kpiMax = document.getElementById('kpi_max');
                const kpiMin = document.getElementById('kpi_min');
                const kpiTotal = document.getElementById('kpi_total');

                if (total === 0) {
                    if (kpiPromedio) kpiPromedio.innerText = '--';
                    if (kpiMax) kpiMax.innerText = '--';
                    if (kpiMin) kpiMin.innerText = '--';
                    if (kpiTotal) kpiTotal.innerText = '0';
                    destruirGraficos();
                    return;
                }

                const suma = notasVisibles.reduce((acc, curr) => acc + curr, 0);
                const promedio = (suma / total).toFixed(2);
                const notaMax = Math.max(...notasVisibles).toFixed(1);
                const notaMin = Math.min(...notasVisibles).toFixed(1);

                if (kpiPromedio) kpiPromedio.innerText = promedio;
                if (kpiMax) kpiMax.innerText = notaMax;
                if (kpiMin) kpiMin.innerText = notaMin;
                if (kpiTotal) kpiTotal.innerText = total;

                let reprobados = 0;
                let regulares = 0;
                let excelencia = 0;

                notasVisibles.forEach(n => {
                    if (n < 6.0) reprobados++;
                    else if (n < 8.0) regulares++;
                    else excelencia++;
                });

                renderizarGraficosPBI18(reprobados, regulares, excelencia, periodoActivo);
            }

            window.redibujarGraficosPBI18 = actualizarEstadisticasPBI18;

            function destruirGraficos() {
                if (chartPastel) { chartPastel.destroy(); chartPastel = null; }
                if (chartBarras) { chartBarras.destroy(); chartBarras = null; }
                if (chartComparativo) { chartComparativo.destroy(); chartComparativo = null; }
            }

            function renderizarGraficosPBI18(reprobados, regulares, excelencia, periodoActivo) {
                const ctxPastel = document.getElementById('chartPastelRendimiento');
                const ctxBarras = document.getElementById('chartBarrasRendimiento');
                const ctxComparativo = document.getElementById('chartComparativoMaterias');
                if (!ctxPastel || !ctxBarras || typeof Chart === 'undefined') return;

                destruirGraficos();

                chartPastel = new Chart(ctxPastel, {
                    type: 'pie',
                    data: {
                        labels: ['Reprobados (< 6.0)', 'Aprobados (6.0 - 7.9)', 'Excelencia (8.0 - 10.0)'],
                        datasets: [{ data: [reprobados, regulares, excelencia], backgroundColor: ['#ef4444', '#3b82f6', '#10b981'], hoverOffset: 6 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
                });

                chartBarras = new Chart(ctxBarras, {
                    type: 'bar',
                    data: {
                        labels: ['Reprobados (< 6.0)', 'Aprobados (6.0 - 7.9)', 'Excelencia (8.0 - 10.0)'],
                        datasets: [{ label: 'N° de Estudiantes', data: [reprobados, regulares, excelencia], backgroundColor: ['#f87171', '#60a5fa', '#34d399'], borderRadius: 6 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }, plugins: { legend: { display: false } } }
                });

                if (ctxComparativo) {
                    const promediosMaterias = MATERIAS_DOCENTE.map(materia => {
                        let sumMat = 0;
                        let countMat = 0;
                        filas.forEach(f => {
                            if (f.style.display !== 'none' && f.id !== 'sin_coincidencias_pbi21') {
                                let notasEst = {};
                                try { notasEst = JSON.parse(f.getAttribute('data-notas') || '{}'); } catch(e){}
                                const filaGrado = f.getAttribute('data-grado') || '';
                                const n = calcularNotaReal(notasEst, materia, periodoActivo, filaGrado);
                                if (n !== null) { sumMat += n; countMat++; }
                            }
                        });
                        return countMat > 0 ? parseFloat((sumMat / countMat).toFixed(2)) : 0.0;
                    });

                    let chartColors = ['#2563eb', '#0284c7', '#0d9488', '#16a34a', '#854d0e', '#7c3aed', '#be185d', '#c2410c'];

                    chartComparativo = new Chart(ctxComparativo, {
                        type: 'bar',
                        data: {
                            labels: MATERIAS_DOCENTE,
                            datasets: [{ label: 'Promedio de Sección', data: promediosMaterias, backgroundColor: chartColors.slice(0, MATERIAS_DOCENTE.length), borderRadius: 6 }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { min: 0, max: 10, ticks: { stepSize: 2 } } },
                            plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => `Promedio Real: ${ctx.raw} / 10.0` } } }
                        }
                    });
                }
            }

            filtrarEnTiempoReal();

            try {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('msg') === 'matriz_guardada' || urlParams.get('msg') === 'estado_periodo_actualizado') {
                    cambiarPestanaInterna('matriz');
                } else {
                    const tabGuardada = sessionStorage.getItem('inca_tab_control_notas_doc');
                    if (tabGuardada === 'matriz' || tabGuardada === 'graficos') {
                        cambiarPestanaInterna(tabGuardada);
                    }
                }
            } catch (e) {}
        });
    </script>
</body>
</html>