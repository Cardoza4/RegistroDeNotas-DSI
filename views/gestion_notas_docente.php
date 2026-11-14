<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
    header('Location: index.php?action=dashboard');
    exit;
}

$listaEstudiantes = $estudiantes ?? [];
$msg = $_GET['msg'] ?? '';

// Asignaciones del docente en sesión
$docenteData = $docenteInfo ?? [];
$nombreDocente = trim($_SESSION['nombre'] ?? 'Docente');
$gradoOrientacionDocente = trim($docenteData['grado_orientacion'] ?? 'Ninguno');

$gradosDocenteList = [];
if (!empty($docenteData['grados_imparte'])) {
    $gradosDocenteList = array_map('trim', explode(',', $docenteData['grados_imparte']));
}
if (!empty($gradoOrientacionDocente) && $gradoOrientacionDocente !== 'Ninguno') {
    $gradosDocenteList[] = $gradoOrientacionDocente;
}
$gradosDocenteList = array_values(array_unique(array_filter($gradosDocenteList)));

$materiasDocenteList = [];
if (!empty($docenteData['materias_imparte'])) {
    $materiasDocenteList = array_map('trim', explode(',', $docenteData['materias_imparte']));
}
if (empty($materiasDocenteList)) {
    $materiasDocenteList = ['Matemática', 'Ciencia y Tecnología'];
}

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

$notasBD = [];
$notasDetalleBD = [];
if ($conexionDB instanceof PDO) {
    try {
        $sql = "SELECT estudiante_id, materia, periodo, act1, act2, examen, nota_periodo, nota_original_reprobada, nota_recuperacion, es_recuperada, modificado_por, fecha_modificacion,
                       COALESCE(nota_periodo, (COALESCE(act1,0)*0.35 + COALESCE(act2,0)*0.35 + COALESCE(examen,0)*0.30)) AS final_calc 
                FROM notas_periodos";
        $stmt = $conexionDB->query($sql);
        if ($stmt) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $eId = (int)$row['estudiante_id'];
                $mat = trim($row['materia']);
                $per = (int)$row['periodo'];

                // Solo asentar si el registro no está anulado (si tiene notas válidas)
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
    <title>INCA NOTES - Control de Alumnos y Notas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<<<<<<< Updated upstream
    <!-- Librería ultraligera para generar y descargar directamente el archivo PDF -->
=======
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
>>>>>>> Stashed changes
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
        .navbar { background: #090e17; color: white; padding: 0.85rem 3rem; display: flex; align-items: center; justify-content: space-between; }
        .brand { display: flex; align-items: center; gap: 12px; color: #ffffff; text-decoration: none; font-weight: 800; }
        .container { flex: 1; max-width: 1400px; width: 100%; margin: 0 auto; padding: 2.5rem 1.5rem; }
        .card { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 2rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .title-area h2 { font-size: 1.5rem; font-weight: 800; color: var(--text-main); }
        .title-area p { font-size: 0.88rem; color: var(--text-muted); }
        .badge-orientacion { display: inline-flex; align-items: center; gap: 6px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }
        .filters-bar { background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 1rem; display: flex; gap: 0.85rem; align-items: center; margin-bottom: 1.75rem; flex-wrap: wrap; }
        .filters-bar select, .filters-bar input { padding: 0.55rem 1rem; border: 1px solid var(--border); border-radius: 8px; background: white; font-size: 0.88rem; color: var(--text-main); outline: none; }
        .btn-filter { background: #334155; color: white; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.88rem; cursor: pointer; }
        .btn-matriz-toggle { background: #0f172a; color: white; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; transition: all 0.2s; }
        .btn-matriz-toggle:hover, .btn-matriz-toggle.active { background: #2563eb; }
        .btn-excel { background: #16a34a; color: white; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
        .btn-asistencia { background: #0284c7; color: white; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
        .btn-comite { background: #7c3aed; color: white; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
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

<<<<<<< Updated upstream
        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background: #090e17;
            color: white;
            padding: 0.85rem 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
            text-decoration: none;
            font-weight: 800;
        }

        .container {
            flex: 1;
            max-width: 1350px;
            width: 100%;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        .header-top {
            margin-bottom: 1.5rem;
        }

        .title-area h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.2rem;
        }

        .title-area p {
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        .filters-bar {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1rem;
            display: flex;
            gap: 0.85rem;
            align-items: center;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
        }

        .filters-bar select {
            padding: 0.55rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: white;
            font-size: 0.88rem;
            color: var(--text-main);
            outline: none;
        }

        .btn-filter {
            background: #334155;
            color: white;
            border: none;
            padding: 0.55rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
        }

        .btn-excel {
            background: #16a34a;
            color: white;
            border: none;
            padding: 0.55rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-excel:hover {
            background: #15803d;
            color: white;
        }

        .btn-asistencia {
            background: #0284c7;
            color: white;
            border: none;
            padding: 0.55rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-asistencia:hover {
            background: #0369a1;
            color: white;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
            vertical-align: middle;
        }

        th {
            background: #ffffff;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #eff6ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: bold;
            border: 1px solid #bfdbfe;
            overflow: hidden;
        }

        .user-avatar-circle img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn-action-badge {
            padding: 6px 14px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-right: 4px;
        }
=======
        .btn-action-badge { padding: 6px 12px; border-radius: 6px; color: white; text-decoration: none; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; margin-right: 4px; }
>>>>>>> Stashed changes
        .btn-notas { background: #10b981; cursor: pointer; border: none; }
        .btn-boleta { background: #0284c7; }
        .btn-recup { background: #ea580c; cursor: pointer; border: none; }
        .btn-recup:disabled { background: #cbd5e1; color: #94a3b8; cursor: not-allowed; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: var(--primary); text-decoration: none; font-weight: 600; margin-bottom: 1.25rem; font-size: 0.9rem; }

        /* MATRIZ PBI-12 */
        .matriz-container { display: none; background: #f8fafc; border: 2px solid #cbd5e1; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; }
        .matriz-header-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
        .input-matriz-nota { width: 90px; padding: 0.45rem 0.6rem; border: 1px solid #94a3b8; border-radius: 6px; text-align: center; font-weight: 700; font-size: 0.9rem; background: #ffffff; color: var(--text-main); }
        .btn-guardar-matriz { background: #059669; color: white; border: none; padding: 0.7rem 1.5rem; border-radius: 8px; font-weight: 800; font-size: 0.9rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }

        /* MODALES */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(3px); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; }
        .modal-card { background: #ffffff; border-radius: 16px; width: 100%; max-width: 600px; padding: 2.5rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative; }
        .modal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem; }
        .modal-title h3 { font-size: 1.4rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.2rem; }
        .modal-title p { font-size: 0.85rem; color: var(--text-muted); }
        .modal-close { background: none; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer; }
        .form-grid-modal { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; }
        .form-group-modal { display: flex; flex-direction: column; gap: 0.4rem; margin-bottom: 1.25rem; }
        .form-group-modal label { font-size: 0.82rem; font-weight: 700; color: #334155; }
        .form-group-modal input, .form-group-modal select { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; background: #f8fafc; color: var(--text-main); font-weight: 600; }
        .nota-final-box { background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; margin: 1.5rem 0; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 1.5rem; }
        .btn-modal-cancel { background: #f1f5f9; color: #334155; border: none; padding: 0.75rem 1.25rem; border-radius: 8px; font-weight: 700; cursor: pointer; }
        .btn-modal-save { background: #059669; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn-modal-anular { background: #dc2626; color: white; border: none; padding: 0.75rem 1.25rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn-modal-anular:hover { background: #b91c1c; }
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
            <span style="color:#94a3b8; font-size:0.88rem;"><i class="fa-solid fa-user-shield"></i> Docente: <?= htmlspecialchars($nombreDocente) ?></span>
            <a href="index.php?action=logout" style="color:white; text-decoration:none; font-size:0.85rem; background:rgba(255,255,255,0.1); padding:0.4rem 1rem; border-radius:6px;">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <a href="index.php?action=dashboard" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>

        <div class="card">
            <?php if (!empty($msg)): ?>
                <div style="background:#d1fae5; border:1px solid #a7f3d0; color:#065f46; padding:0.75rem 1rem; border-radius:8px; font-size:0.88rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>
                        <?php 
                        if ($msg === 'nota_anulada_exito') echo '¡Calificación anulada de forma segura! El periodo se restableció a "Sin calificar" y se guardó la traza de auditoría.';
                        elseif ($msg === 'recuperacion_exitosa') echo '¡Evaluación de recuperación registrada y nota sustituida con éxito!';
                        elseif ($msg === 'matriz_guardada') echo '¡Calificaciones de la sección guardadas exitosamente en una sola transacción!';
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

            <div class="header-top">
                <div class="title-area">
                    <h2>Control de Alumnos y Notas Asignadas</h2>
                    <p>Expedientes autorizados según tu carga académica y orientación asignada.</p>
                </div>
                <?php if ($gradoOrientacionDocente !== 'Ninguno'): ?>
                    <div class="badge-orientacion">
                        <i class="fa-solid fa-compass"></i> Grado Orientado: <?= htmlspecialchars($gradoOrientacionDocente) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Barra de Filtros con Input de Búsqueda, Selects, Excel y Asistencia -->
            <div class="filters-bar">
<<<<<<< Updated upstream
                <input type="text" id="input_buscar_pbi21" placeholder="🔍 Buscar por NIE o Nombre..." style="padding: 0.55rem 1rem; border: 1px solid var(--border); border-radius: 8px; background: white; font-size: 0.88rem; outline: none; min-width: 220px;">
                <select name="grado" id="selectGrado">
                    <option value="">-- Filtrar por Grado --</option>
                    <option value="1° Grado">1° Grado</option><option value="2° Grado">2° Grado</option><option value="3° Grado">3° Grado</option>
                    <option value="4° Grado">4° Grado</option><option value="5° Grado">5° Grado</option><option value="6° Grado">6° Grado</option>
                    <option value="7° Grado">7° Grado</option><option value="8° Grado">8° Grado</option><option value="9° Grado">9° Grado</option>
                    <option value="1° Año Bachillerato">1° Año Bachillerato</option><option value="2° Año Bachillerato">2° Año Bachillerato</option>
=======
                <input type="text" id="input_buscar_pbi21" placeholder="🔍 Buscar por NIE o Nombre..." style="min-width: 200px;">
                <select name="grado" id="selectGrado">
                    <option value="">-- Filtrar por Grado Asignado --</option>
                    <?php if (!empty($gradosDocenteList)): ?>
                        <?php foreach ($gradosDocenteList as $gDoc): ?>
                            <option value="<?= htmlspecialchars($gDoc) ?>"><?= htmlspecialchars($gDoc) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
>>>>>>> Stashed changes
                </select>
                <select name="seccion" id="selectSeccion">
                    <option value="">-- Sección --</option>
                    <option value="Sección A">Sección A</option><option value="Sección B">Sección B</option><option value="Sección C">Sección C</option>
                </select>
<<<<<<< Updated upstream
                <button type="button" class="btn-filter" id="btnFiltrarManual">Filtrar</button>
                
                <!-- Botón Excel PBI-36 -->
                <button type="button" class="btn-excel" id="btnExportarExcel">
                    <i class="fa-solid fa-file-excel"></i> Exportar a Excel
                </button>

                <!-- Botón Asistencia PBI-23 -->
                <button type="button" class="btn-asistencia" id="btnExportarAsistencia">
                    <i class="fa-solid fa-clipboard-user"></i> Lista de Asistencia (.pdf)
                </button>

=======

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
                <button type="button" class="btn-excel" id="btnExportarExcel" title="Exportar nómina y boletas del grado orientado con hojas por materia">
                    <i class="fa-solid fa-file-excel"></i> Exportar Cuadro Excel
                </button>
                <button type="button" class="btn-asistencia" id="btnExportarAsistencia">
                    <i class="fa-solid fa-clipboard-user"></i> Lista de Asistencia (.pdf)
                </button>
                <button type="button" class="btn-comite" id="btnExportarComite" title="Nómina oficial de reprobados en mis materias asignadas">
                    <i class="fa-solid fa-user-check"></i> Nómina Comité (.pdf)
                </button>
>>>>>>> Stashed changes
                <a href="#" id="btnLimpiarFiltros" style="color: var(--text-muted); text-decoration: none; font-size: 0.88rem; margin-left: auto;">Limpiar Filtros</a>
            </div>

            <!-- MATRIZ PBI-12 -->
            <div class="matriz-container" id="bloqueMatrizPBI12">
                <form action="index.php?action=guardar_nota" method="POST" id="formMatrizSeccion">
                    <input type="hidden" name="modo_matriz" value="1">
                    <div class="matriz-header-bar">
                        <div>
                            <h3 style="font-size: 1.2rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-table-list" style="color: var(--primary);"></i> Matriz de Calificación Trimestral por Sección
                            </h3>
                            <p style="font-size: 0.82rem; color: var(--text-muted);">Ponderación automática: 35% Act.1 + 35% Act.2 + 30% Examen</p>
                        </div>
                        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                            <div>
                                <label style="font-size: 0.78rem; font-weight: 700; color: #334155; display: block; margin-bottom: 3px;">Asignatura:</label>
                                <select name="materia" id="matrizMateria" onchange="cargarValoresMatriz()" style="padding: 0.45rem 0.8rem; border: 1px solid #94a3b8; border-radius: 6px; font-weight: 700; background: #fff;">
                                    <?php foreach ($materiasDocenteList as $matDoc): ?>
                                        <option value="<?= htmlspecialchars($matDoc) ?>"><?= htmlspecialchars($matDoc) ?></option>
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
                            <button type="submit" class="btn-guardar-matriz">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar Sección (Transacción)
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr style="background: #e2e8f0;">
                                    <th>NIE</th><th>Estudiante</th><th>Grado / Sección</th>
                                    <th style="text-align: center;">Act. 1 (35%)</th>
                                    <th style="text-align: center;">Act. 2 (35%)</th>
                                    <th style="text-align: center;">Examen (30%)</th>
                                    <th style="text-align: center;">Nota Final</th>
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
                                        <tr class="fila-matriz-item" data-id="<?= $eId ?>" data-grado="<?= htmlspecialchars($gradoEst) ?>" data-seccion="<?= htmlspecialchars($secEst) ?>" data-detalle="<?= $detJson ?>">
                                            <td><strong><?= htmlspecialchars($est['nie'] ?? 'N/D') ?></strong><input type="hidden" name="estudiante_ids[]" value="<?= $eId ?>" class="input-id-matriz"></td>
                                            <td><strong><?= htmlspecialchars(($est['apellido'] ?? '') . ', ' . ($est['nombre'] ?? '')) ?></strong></td>
                                            <td><?= htmlspecialchars($gradoEst . ' - ' . $secEst) ?></td>
                                            <td style="text-align: center;"><input type="number" step="0.01" min="0" max="10" name="act1[<?= $eId ?>]" class="input-matriz-nota inp-a1" value="0.00" oninput="recalcularFilaMatriz(this)" required></td>
                                            <td style="text-align: center;"><input type="number" step="0.01" min="0" max="10" name="act2[<?= $eId ?>]" class="input-matriz-nota inp-a2" value="0.00" oninput="recalcularFilaMatriz(this)" required></td>
                                            <td style="text-align: center;"><input type="number" step="0.01" min="0" max="10" name="examen[<?= $eId ?>]" class="input-matriz-nota inp-ex" value="0.00" oninput="recalcularFilaMatriz(this)" required></td>
                                            <td style="text-align: center; font-weight: 800; font-size: 1rem; color: var(--primary);" class="celda-nf-matriz">0.00</td>
                                            <td style="text-align: center;" class="celda-est-matriz"><span class="badge-condicion badge-reprobado">REPROBADO</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>

            <!-- TABLA PRINCIPAL ESTÁNDAR -->
            <div class="table-responsive" id="bloqueTablaEstandar">
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
                                $gradoEstudiante = trim($est['grado'] ?? '');
                                $seccionEstudiante = trim($est['seccion'] ?? '');
                                $notasEstudiante = $notasBD[$eId] ?? [];
                                $jsonNotas = htmlspecialchars(json_encode($notasEstudiante), ENT_QUOTES, 'UTF-8');
                                $jsonDetalle = htmlspecialchars(json_encode($notasDetalleBD[$eId] ?? []), ENT_QUOTES, 'UTF-8');
                            ?>
                                <tr data-estudiante-id="<?= $eId ?>" 
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
                                            <span><?= htmlspecialchars(($est['apellido'] ?? '') . ', ' . ($est['nombre'] ?? '')) ?></span>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($gradoEstudiante . ' - ' . $seccionEstudiante) ?></td>
                                    <td class="col-materia-est"><?= htmlspecialchars($materiasDocenteList[0] ?? 'Asignatura') ?></td>
                                    <td style="text-align: center; font-weight: 800;" class="col-promedio-est">0.00</td>
                                    <td style="text-align: center;" class="col-condicion-est">
                                        <span class="badge-condicion badge-reprobado">REPROBADO</span>
                                    </td>
                                    <td style="text-align: right;">
                                        <button type="button" class="btn-action-badge btn-notas" onclick="abrirModalNotas('<?= $est['id'] ?>', '<?= htmlspecialchars(($est['nombre'] ?? '') . ' ' . ($est['apellido'] ?? ''), ENT_QUOTES) ?>', '<?= htmlspecialchars($gradoEstudiante, ENT_QUOTES) ?>')">
                                            <i class="fa-solid fa-star"></i> Notas
                                        </button>
                                        <button type="button" class="btn-action-badge btn-recup" title="Registrar Nota de Recuperación" onclick="abrirModalRecuperacion('<?= $est['id'] ?>', '<?= htmlspecialchars(($est['nombre'] ?? '') . ' ' . ($est['apellido'] ?? ''), ENT_QUOTES) ?>')">
                                            <i class="fa-solid fa-arrow-up-right-dots"></i> Recuperación
                                        </button>
                                        <a href="index.php?action=boleta_notas&estudiante_id=<?= $est['id'] ?>" class="btn-action-badge btn-boleta" title="Boleta"><i class="fa-solid fa-file-lines"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No tienes estudiantes asignados en los grados autorizados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- SECCIÓN DE ESTADÍSTICAS Y GRÁFICOS DINÁMICOS -->
            <div id="seccion_estadisticas_pbi18" style="margin-top: 2.5rem; border-top: 2px solid var(--border); padding-top: 1.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-chart-pie" style="color: var(--primary);"></i> Rendimiento de Asignaturas que Imparto
                        </h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Métricas calculadas exclusivamente para tus materias autorizadas.</p>
                    </div>
                    <div style="display: flex; gap: 10px; align-items: center; background: #f8fafc; padding: 0.4rem 0.85rem; border-radius: 8px; border: 1px solid var(--border); flex-wrap: wrap;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label for="selectMateriaEstadistica" style="font-size: 0.82rem; font-weight: 700; color: #334155;">Materia:</label>
                            <select id="selectMateriaEstadistica" style="padding: 0.35rem 0.65rem; border: 1px solid var(--border); border-radius: 6px; font-size: 0.82rem; outline: none; background: #fff; font-weight: 600;">
                                <option value="Todas">-- Mis Materias (Global) --</option>
                                <?php foreach ($materiasDocenteList as $mDoc): ?>
                                    <option value="<?= htmlspecialchars($mDoc) ?>"><?= htmlspecialchars($mDoc) ?></option>
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
                        <div style="font-size: 0.78rem; font-weight: 800; color: #1e40af; text-transform: uppercase;">Promedio General</div>
                        <div id="kpi_promedio" style="font-size: 1.9rem; font-weight: 800; color: #1d4ed8; margin-top: 4px;">--</div>
                    </div>
                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 1.1rem; text-align: center;">
                        <div style="font-size: 0.78rem; font-weight: 800; color: #166534; text-transform: uppercase;">Nota Más Alta</div>
                        <div id="kpi_max" style="font-size: 1.9rem; font-weight: 800; color: #15803d; margin-top: 4px;">--</div>
                    </div>
                    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 1.1rem; text-align: center;">
                        <div style="font-size: 0.78rem; font-weight: 800; color: #991b1b; text-transform: uppercase;">Nota Más Baja</div>
                        <div id="kpi_min" style="font-size: 1.9rem; font-weight: 800; color: #b91c1c; margin-top: 4px;">--</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; padding: 1.1rem; text-align: center;">
                        <div style="font-size: 0.78rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Estudiantes Visibles</div>
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
            </div>
        </div>
    </div>

<<<<<<< Updated upstream
    <!-- MODAL DE CALIFICACIONES -->
=======
    <!-- MODAL 1: CALIFICACIONES ORDINARIAS CON AUDITORÍA Y ANULACIÓN (PBI-15) -->
>>>>>>> Stashed changes
    <div class="modal-overlay" id="modalNotas">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">
                    <h3 id="modalEstudianteNombre">Nombre del Estudiante</h3>
                    <p id="modalSubtituloNivel">Periodos y ponderaciones reglamentarias</p>
                </div>
                <button type="button" class="modal-close" onclick="cerrarModalNotas()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form action="index.php?action=guardar_nota" method="POST" id="formModalNotas" onsubmit="return solicitarConfirmacionGuardar(event)">
                <input type="hidden" id="estudiante_id" name="estudiante_id">
                <input type="hidden" id="accion_anular" name="accion_anular" value="">

                <div class="form-grid-modal">
                    <div class="form-group-modal">
                        <label for="materia">Asignatura que Imparte</label>
                        <select id="materia" name="materia" onchange="actualizarValoresModalNotas()">
                            <?php foreach ($materiasDocenteList as $matDoc): ?>
                                <option value="<?= htmlspecialchars($matDoc) ?>"><?= htmlspecialchars($matDoc) ?></option>
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

                <!-- Detalle de traza de auditoría si ya fue modificado -->
                <div id="trazaAuditoriaBox" style="display:none; font-size:0.75rem; color:#64748b; background:#f8fafc; border:1px solid #e2e8f0; padding:6px 10px; border-radius:6px; margin-bottom:1.25rem;">
                    <i class="fa-solid fa-fingerprint"></i> <span id="trazaAuditoriaTexto"></span>
                </div>

                <div class="modal-footer" style="display:flex; justify-content:space-between; align-items:center;">
                    <button type="button" class="btn-modal-anular" id="btnAnularNotaModal" onclick="solicitarConfirmacionAnular()" style="display:none;">
                        <i class="fa-solid fa-ban"></i> Anular Registro
                    </button>
                    <div style="display:flex; gap:10px; margin-left:auto;">
                        <button type="button" class="btn-modal-cancel" onclick="cerrarModalNotas()">Cancelar</button>
                        <button type="submit" class="btn-modal-save"><i class="fa-solid fa-floppy-disk"></i> Guardar Calificación</button>
                    </div>
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

                <div class="form-grid-modal">
                    <div class="form-group-modal">
                        <label for="recup_materia">Asignatura</label>
                        <select id="recup_materia" name="materia" onchange="actualizarEstadoRecuperacionModal()">
                            <?php foreach ($materiasDocenteList as $matDoc): ?>
                                <option value="<?= htmlspecialchars($matDoc) ?>"><?= htmlspecialchars($matDoc) ?></option>
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

    <script>
        const GRADO_ORIENTADO_DOCENTE = "<?= htmlspecialchars($gradoOrientacionDocente, ENT_QUOTES) ?>";
        const NOMBRE_DOCENTE_SESION = "<?= htmlspecialchars($nombreDocente, ENT_QUOTES) ?>";
        const MIS_MATERIAS_DOCENTE = <?= json_encode($materiasDocenteList) ?>;

        const TODAS_LAS_MATERIAS_OFICIALES = [
            'Matemática',
            'Lenguaje y Literatura',
            'Estudios Sociales y Cívica',
            'Ciencia y Tecnología',
            'Idioma Extranjero (Inglés)',
            'Educación Física'
        ];

        function normalizarTexto(txt) {
            return (txt || '').toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim();
        }

        // PBI-15: CONFIRMACIÓN EXPLÍCITA EN VENTANA MODAL
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

            abrirModalConfirmacionPBI15(
                '¿Anular Calificación Asentada?',
                `¿Está seguro de anular la calificación de ${estNombre} en ${materia} (Periodo ${periodo})? Conforme a la normativa, el registro volverá al estado "Sin Calificar" y se recalculará de inmediato el promedio.`,
                true,
                function () {
                    document.getElementById('accion_anular').value = '1';
                    document.getElementById('formModalNotas').submit();
                }
            );
        }

        // PBI-39: CONTROL DE RECUPERACIÓN EXCLUSIVA PARA REPROBADOS (< 6.0)
        let recupAlumnoDataDetalle = {};

        function abrirModalRecuperacion(id, nombre) {
            document.getElementById('recup_estudiante_id').value = id;
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
            const alerta = document.getElementById('recup_alerta_estado');
            const inpNota = document.getElementById('nota_recuperacion');
            const btnGuardar = document.getElementById('btnGuardarRecuperacion');

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

            if (notaActual === null) {
                alerta.style.background = '#f1f5f9';
                alerta.style.color = '#475569';
                alerta.innerHTML = `<i class="fa-solid fa-circle-info"></i> El estudiante no posee calificaciones asentadas en ${materiaSel} para el Periodo ${periodoSel}.`;
                inpNota.disabled = true;
                btnGuardar.disabled = true;
            } else if (notaActual >= 6.0 && !esRecuperada) {
                alerta.style.background = '#dcfce7';
                alerta.style.color = '#166534';
                alerta.innerHTML = `<i class="fa-solid fa-circle-check"></i> Calificación APROBADA (${notaActual.toFixed(2)}). El Criterio A de PBI-39 restringe la recuperación exclusivamente a notas inferiores a 6.0.`;
                inpNota.disabled = true;
                btnGuardar.disabled = true;
            } else {
                alerta.style.background = '#fee2e2';
                alerta.style.color = '#991b1b';
                const strOrig = esRecuperada ? ` (Nota reprobada original: ${parseFloat(notaOriginal).toFixed(2)})` : '';
                alerta.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Calificación REPROBADA (${notaActual.toFixed(2)})${strOrig}. Cumple con Criterio A: Habilitado para evaluación de recuperación y sustitución.`;
                inpNota.disabled = false;
                btnGuardar.disabled = false;
            }
        }

        // PBI-12 MATRIZ INTERACTIVA
        function alternarModoMatriz() {
            const bloqueMatriz = document.getElementById('bloqueMatrizPBI12');
            const btnToggle = document.getElementById('btnToggleMatriz');
            if (bloqueMatriz.style.display === 'block') {
                bloqueMatriz.style.display = 'none';
                btnToggle.classList.remove('active');
            } else {
                bloqueMatriz.style.display = 'block';
                btnToggle.classList.add('active');
                cargarValoresMatriz();
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
            const gradoFiltro = normalizarTexto(document.getElementById('selectGrado').value);
            const seccionFiltro = normalizarTexto(document.getElementById('selectSeccion').value);
            const textoBusq = normalizarTexto(document.getElementById('input_buscar_pbi21').value);

            const filasMatriz = document.querySelectorAll('.fila-matriz-item');
            filasMatriz.forEach(tr => {
                const fGrado = normalizarTexto(tr.getAttribute('data-grado') || '');
                const fSeccion = normalizarTexto(tr.getAttribute('data-seccion') || '');
                const fTexto = normalizarTexto(tr.innerText);

                let visible = true;
                if (textoBusq !== '' && !fTexto.includes(textoBusq)) visible = false;
                if (gradoFiltro !== '' && !fGrado.includes(gradoFiltro)) visible = false;
                if (seccionFiltro !== '' && !fSeccion.includes(seccionFiltro)) visible = false;

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

        // GESTIÓN DE NOTAS INDIVIDUAL CON PRECARGA Y ANULACIÓN (PBI-15)
        let estudianteNotasDetalleActual = {};

        function abrirModalNotas(id, nombre, grado) {
            document.getElementById('estudiante_id').value = id;
            document.getElementById('modalEstudianteNombre').textContent = nombre;
            document.getElementById('accion_anular').value = '';

            const esBachillerato = (grado || '').toLowerCase().includes('bachillerato');
            const selectPeriodo = document.getElementById('periodo');
            const subtituloModal = document.getElementById('modalSubtituloNivel');

            selectPeriodo.innerHTML = '';
            if (esBachillerato) {
                subtituloModal.textContent = 'Bachillerato (4 Periodos) • Ponderación: 35% - 35% - 30%';
                selectPeriodo.innerHTML = `
                    <option value="1">Periodo 1</option>
                    <option value="2">Periodo 2</option>
                    <option value="3">Periodo 3</option>
                    <option value="4">Periodo 4</option>
                `;
            } else {
                subtituloModal.textContent = 'Educación Básica (3 Periodos) • Ponderación: 35% - 35% - 30%';
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
            const btnAnular = document.getElementById('btnAnularNotaModal');
            const boxTraza = document.getElementById('trazaAuditoriaBox');
            const txtTraza = document.getElementById('trazaAuditoriaTexto');

            let datosPeriodo = null;
            for (const [mKey, pObj] of Object.entries(estudianteNotasDetalleActual)) {
                if (normalizarTexto(mKey) === normalizarTexto(materia)) {
                    if (pObj && pObj[periodo]) {
                        datosPeriodo = pObj[periodo];
                    }
                }
            }

            if (datosPeriodo && (datosPeriodo.act1 !== null || datosPeriodo.act2 !== null || datosPeriodo.examen !== null)) {
                document.getElementById('act1').value = parseFloat(datosPeriodo.act1 || 0).toFixed(2);
                document.getElementById('act2').value = parseFloat(datosPeriodo.act2 || 0).toFixed(2);
                document.getElementById('examen').value = parseFloat(datosPeriodo.examen || 0).toFixed(2);
                btnAnular.style.display = 'inline-flex';

                if (datosPeriodo.fecha_modificacion) {
                    boxTraza.style.display = 'block';
                    txtTraza.textContent = `Última edición auditada: ${datosPeriodo.fecha_modificacion}`;
                } else {
                    boxTraza.style.display = 'none';
                }
            } else {
                document.getElementById('act1').value = '0.00';
                document.getElementById('act2').value = '0.00';
                document.getElementById('examen').value = '0.00';
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
                        <span style="font-size: 0.85rem;">No existen coincidencias con los criterios aplicados.</span>
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

            // PBI-15: Cálculo que maneja estados null (Sin calificar)
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
                    MIS_MATERIAS_DOCENTE.forEach(m => {
                        const n = obtenerNotaMateriaPeriodo(notasEst, m, periodoSel);
                        if (n !== null) { suma += n; c++; }
                    });
                    return c > 0 ? parseFloat((suma / c).toFixed(2)) : null;
                }

                let sumaTotal = 0, cuentaTotal = 0;
                MIS_MATERIAS_DOCENTE.forEach(m => {
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

                    if (celdaMateria) celdaMateria.textContent = (materiaSel === 'Todas') ? (MIS_MATERIAS_DOCENTE[0] || 'Asignatura') : materiaSel;

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

                    // PBI-39: Habilitar botón de recuperación si tiene notas inferiores a 6.0
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
                    const filaTexto = normalizarTexto(fila.innerText);
                    const filaGrado = normalizarTexto(fila.getAttribute('data-grado') || '');
                    const filaSeccion = normalizarTexto(fila.getAttribute('data-seccion') || '');
                    const celdaCondicion = fila.querySelector('.col-condicion-est');
                    const textoCondicion = celdaCondicion ? celdaCondicion.innerText.toUpperCase() : '';

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

                filaVacia.style.display = (visibles === 0) ? '' : 'none';
                if (document.getElementById('bloqueMatrizPBI12').style.display === 'block') {
                    cargarValoresMatriz();
                }
                actualizarEstadisticasDocente();
            }

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

            // EXPORTACIÓN A EXCEL MULTIPESTAÑA PROTEGIDA CONTRA SHORT_OPEN_TAG
            if (btnExcel) {
                btnExcel.addEventListener('click', function (e) {
                    e.preventDefault();

                    const gradoMeta = (GRADO_ORIENTADO_DOCENTE !== '' && GRADO_ORIENTADO_DOCENTE !== 'Ninguno') 
                        ? GRADO_ORIENTADO_DOCENTE 
                        : (selectGrado ? selectGrado.value : '');

                    let alumnosGradoOrientado = [];
                    filas.forEach(fila => {
                        if (fila.id === 'sin_coincidencias_pbi21') return;
                        const fGrado = fila.getAttribute('data-grado') || '';
                        
                        let califica = false;
                        if (gradoMeta !== '') {
                            califica = (normalizarTexto(fGrado) === normalizarTexto(gradoMeta));
                        } else {
                            califica = (fila.style.display !== 'none');
                        }

                        if (califica) {
                            const cols = fila.querySelectorAll('td');
                            const nie = cols[0] ? cols[0].innerText.trim() : 'N/D';
                            const estudiante = cols[1] ? cols[1].innerText.trim().replace(/\s+/g, ' ') : 'N/D';
                            const gradoSec = cols[2] ? cols[2].innerText.trim() : 'N/D';
                            let notasEst = {};
                            try { notasEst = JSON.parse(fila.getAttribute('data-notas') || '{}'); } catch(err){}
                            alumnosGradoOrientado.push({ nie, estudiante, gradoSec, grado: fGrado, notas: notasEst });
                        }
                    });

                    if (alumnosGradoOrientado.length === 0) {
                        alert(`No hay alumnos registrados en tu grado de orientación (${gradoMeta || 'Sin sección asignada'}).`);
                        return;
                    }

                    alumnosGradoOrientado.sort((a, b) => a.estudiante.localeCompare(b.estudiante));

                    const gradoTextoHeader = gradoMeta || (alumnosGradoOrientado[0].gradoSec);
                    const esBachiller = gradoTextoHeader.toLowerCase().includes('bachillerato');
                    const cantPeriodos = esBachiller ? 4 : 3;

                    function escapeXml(unsafe) {
                        return (unsafe || '').toString()
                            .replace(/&/g, '&amp;')
                            .replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;')
                            .replace(/"/g, '&quot;')
                            .replace(/'/g, '&apos;');
                    }

                    let xml = '<' + '?xml version="1.0" encoding="UTF-8"?>\n' +
'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"\n' +
' xmlns:o="urn:schemas-microsoft-com:office:office"\n' +
' xmlns:x="urn:schemas-microsoft-com:office:excel"\n' +
' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"\n' +
' xmlns:html="http://www.w3.org/TR/REC-html40">\n' +
' <Styles>\n' +
'  <Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="10"/></Style>\n' +
'  <Style ss:ID="Title"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="13" ss:Bold="1"/></Style>\n' +
'  <Style ss:ID="Header"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#0F172A" ss:Pattern="Solid"/></Style>\n' +
'  <Style ss:ID="CellLeft"><Alignment ss:Horizontal="Left" ss:Vertical="Center"/></Style>\n' +
'  <Style ss:ID="CellCenter"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>\n' +
'  <Style ss:ID="CellScore"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1"/></Style>\n' +
'  <Style ss:ID="CellAprobado"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#166534"/><Interior ss:Color="#DCFCE7" ss:Pattern="Solid"/></Style>\n' +
'  <Style ss:ID="CellReprobado"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Bold="1" ss:Color="#991B1B"/><Interior ss:Color="#FEE2E2" ss:Pattern="Solid"/></Style>\n' +
'  <Style ss:ID="CellSinCalif"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:FontName="Calibri" ss:Size="9" ss:Color="#64748B"/><Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/></Style>\n' +
' </Styles>\n';

                    xml += `<Worksheet ss:Name="Resumen General"><Table><Row><Cell ss:MergeAcross="5" ss:StyleID="Title"><Data ss:Type="String">INSTITUTO NOÉ CANJURA - RESUMEN DE SECCIÓN</Data></Cell></Row><Row><Cell ss:StyleID="Header"><Data ss:Type="String">N°</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">NIE</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">ESTUDIANTE</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">GRADO</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">PROMEDIO</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">ESTADO</Data></Cell></Row>`;

                    alumnosGradoOrientado.forEach((al, idx) => {
                        let sumaTotal = 0, cuentaTotal = 0;
                        TODAS_LAS_MATERIAS_OFICIALES.forEach(m => {
                            for (let p = 1; p <= cantPeriodos; p++) {
                                const n = obtenerNotaMateriaPeriodo(al.notas, m, p);
                                if (n !== null) { sumaTotal += n; cuentaTotal++; }
                            }
                        });
                        const promGen = cuentaTotal > 0 ? (sumaTotal / cuentaTotal) : null;
                        const estadoEst = promGen === null ? 'SIN CALIFICAR' : (promGen >= 6.0 ? 'APROBADO' : 'REPROBADO');
                        const estiloEst = promGen === null ? 'CellSinCalif' : (promGen >= 6.0 ? 'CellAprobado' : 'CellReprobado');

                        xml += `<Row><Cell ss:StyleID="CellCenter"><Data ss:Type="Number">${idx + 1}</Data></Cell><Cell ss:StyleID="CellCenter"><Data ss:Type="String">${escapeXml(al.nie)}</Data></Cell><Cell ss:StyleID="CellLeft"><Data ss:Type="String">${escapeXml(al.estudiante)}</Data></Cell><Cell ss:StyleID="CellCenter"><Data ss:Type="String">${escapeXml(al.gradoSec)}</Data></Cell><Cell ss:StyleID="CellScore"><Data ss:Type="String">${promGen !== null ? promGen.toFixed(2) : '--'}</Data></Cell><Cell ss:StyleID="${estiloEst}"><Data ss:Type="String">${estadoEst}</Data></Cell></Row>`;
                    });
                    xml += `</Table></Worksheet>`;

                    TODAS_LAS_MATERIAS_OFICIALES.forEach(materia => {
                        const pestanaNom = materia.slice(0, 20);
                        xml += `<Worksheet ss:Name="${escapeXml(pestanaNom)}"><Table><Row><Cell ss:MergeAcross="${cantPeriodos + 4}" ss:StyleID="Title"><Data ss:Type="String">${escapeXml(materia)}</Data></Cell></Row><Row><Cell ss:StyleID="Header"><Data ss:Type="String">N°</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">NIE</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">ESTUDIANTE</Data></Cell>`;
                        for (let p = 1; p <= cantPeriodos; p++) xml += `<Cell ss:StyleID="Header"><Data ss:Type="String">P${p}</Data></Cell>`;
                        xml += `<Cell ss:StyleID="Header"><Data ss:Type="String">NF</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">ESTADO</Data></Cell></Row>`;

                        alumnosGradoOrientado.forEach((al, idx) => {
                            let sumaMat = 0, cuentaMat = 0;
                            let celdasPeriodosXml = '';
                            for (let p = 1; p <= cantPeriodos; p++) {
                                const n = obtenerNotaMateriaPeriodo(al.notas, materia, p);
                                if (n !== null) {
                                    sumaMat += n;
                                    cuentaMat++;
                                    celdasPeriodosXml += `<Cell ss:StyleID="CellCenter"><Data ss:Type="Number">${n.toFixed(1)}</Data></Cell>`;
                                } else {
                                    celdasPeriodosXml += `<Cell ss:StyleID="CellCenter"><Data ss:Type="String">---</Data></Cell>`;
                                }
                            }
                            const nf = cuentaMat > 0 ? (sumaMat / cuentaMat) : null;
                            const estadoM = nf === null ? 'SIN CALIFICAR' : (nf >= 6.0 ? 'APROBADO' : 'REPROBADO');
                            const estiloM = nf === null ? 'CellSinCalif' : (nf >= 6.0 ? 'CellAprobado' : 'CellReprobado');

                            xml += `<Row><Cell ss:StyleID="CellCenter"><Data ss:Type="Number">${idx + 1}</Data></Cell><Cell ss:StyleID="CellCenter"><Data ss:Type="String">${escapeXml(al.nie)}</Data></Cell><Cell ss:StyleID="CellLeft"><Data ss:Type="String">${escapeXml(al.estudiante)}</Data></Cell>${celdasPeriodosXml}<Cell ss:StyleID="CellScore"><Data ss:Type="String">${nf !== null ? nf.toFixed(1) : '--'}</Data></Cell><Cell ss:StyleID="${estiloM}"><Data ss:Type="String">${estadoM}</Data></Cell></Row>`;
                        });
                        xml += `</Table></Worksheet>`;
                    });

                    xml += `\n</Workbook>`;

                    const blob = new Blob([xml], { type: 'application/vnd.ms-excel;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const enlace = document.createElement('a');
                    enlace.href = url;
                    enlace.download = `Cuadro_Notas_${escapeXml(gradoTextoHeader).replace(/\s+/g, '_')}_${new Date().toISOString().slice(0, 10)}.xls`;
                    document.body.appendChild(enlace);
                    enlace.click();
                    document.body.removeChild(enlace);
                    URL.revokeObjectURL(url);
                });
            }

            // COMITÉ PDF
            if (btnComite) {
                btnComite.addEventListener('click', function (e) {
                    e.preventDefault();
                    const periodoSel = parseInt(selectPeriodoEstadistica ? selectPeriodoEstadistica.value : 0);
                    const materiaSel = selectMateriaEstadistica ? selectMateriaEstadistica.value.trim() : 'Todas';

                    let materiasAEvaluar = (materiaSel !== 'Todas' && materiaSel !== '' && MIS_MATERIAS_DOCENTE.includes(materiaSel))
                        ? [materiaSel]
                        : MIS_MATERIAS_DOCENTE;

                    let filasPdfHtml = '';
                    let contCasosReprobados = 0;
                    let indiceFila = 1;

                    filas.forEach(fila => {
                        if (fila.style.display === 'none' || fila.id === 'sin_coincidencias_pbi21') return;

                        const cols = fila.querySelectorAll('td');
                        const nie = cols[0] ? cols[0].innerText.trim() : 'N/D';
                        const estudiante = cols[1] ? cols[1].innerText.trim().replace(/\s+/g, ' ') : 'N/D';
                        const gradoSec = cols[2] ? cols[2].innerText.trim() : 'N/D';
                        const filaGrado = fila.getAttribute('data-grado') || '';
                        const esBach = filaGrado.toLowerCase().includes('bachillerato');
                        const maxP = esBach ? 4 : 3;

                        let notasEst = {};
                        try { notasEst = JSON.parse(fila.getAttribute('data-notas') || '{}'); } catch(err){}

                        const periodosAEvaluar = (periodoSel === 0) ? (esBach ? [1, 2, 3, 4] : [1, 2, 3]) : [periodoSel];

                        materiasAEvaluar.forEach(materia => {
                            periodosAEvaluar.forEach(periodo => {
                                if (periodo > maxP) return;
                                let nota = obtenerNotaMateriaPeriodo(notasEst, materia, periodo);
                                if (nota !== null && nota < 6.0) {
                                    contCasosReprobados++;
                                    filasPdfHtml += `
                                        <tr style="line-height: 1.15;">
                                            <td style="padding: 2.5px 2px; text-align: center; font-size: 7px; border: 1px solid #94a3b8;">${indiceFila++}</td>
                                            <td style="padding: 2.5px 2px; text-align: center; font-size: 7px; font-weight: bold; border: 1px solid #94a3b8;">${nie}</td>
                                            <td style="padding: 2.5px 4px; font-size: 7px; text-transform: uppercase; border: 1px solid #94a3b8;">${estudiante}</td>
                                            <td style="padding: 2.5px 2px; text-align: center; font-size: 7px; border: 1px solid #94a3b8;">${gradoSec}</td>
                                            <td style="padding: 2.5px 4px; font-size: 7px; border: 1px solid #94a3b8; font-weight: 600;">${materia}</td>
                                            <td style="padding: 2.5px 2px; text-align: center; font-size: 7px; border: 1px solid #94a3b8;">Periodo ${periodo}</td>
                                            <td style="padding: 2.5px 2px; text-align: center; font-size: 7.5px; font-weight: 800; border: 1px solid #94a3b8; color: #dc2626;">${nota.toFixed(1)}</td>
                                            <td style="padding: 2.5px 2px; text-align: center; font-size: 6.5px; font-weight: bold; border: 1px solid #94a3b8; color: #991b1b; background: #fee2e2;">REPROBADO</td>
                                            <td style="padding: 2.5px 2px; font-size: 6.5px; border: 1px solid #94a3b8; text-align: center;">Refuerzo Pedagógico (P${periodo})</td>
                                        </tr>
                                    `;
                                }
                            });
                        });
                    });

                    if (contCasosReprobados === 0) {
                        filasPdfHtml = `<tr><td colspan="9" style="text-align: center; padding: 20px; font-size: 9px; color: #166534; background: #f0fdf4;">Sin registros de reprobación en tus materias asignadas.</td></tr>`;
                    }

                    const contenedorPdf = document.createElement('div');
                    contenedorPdf.style.width = '720px';
                    contenedorPdf.style.padding = '0';
                    contenedorPdf.style.margin = '0 auto';
                    contenedorPdf.style.fontFamily = 'Arial, Helvetica, sans-serif';

                    contenedorPdf.innerHTML = `
                        <div style="text-align: center; margin-bottom: 8px; border-bottom: 2px solid #0f172a; padding-bottom: 4px;">
                            <h2 style="margin: 0; font-size: 12px; text-transform: uppercase;">INSTITUTO NOÉ CANJURA - INCA NOTES</h2>
                            <h3 style="margin: 2px 0; font-size: 10px; color: #334155;">INFORME DE EVALUACIÓN PARA COMITÉ PEDAGÓGICO</h3>
                        </div>
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px;">
                            <thead>
                                <tr style="background: #0f172a; color: #ffffff;">
                                    <th>N°</th><th>NIE</th><th>ESTUDIANTE</th><th>GRADO</th><th>ASIGNATURA</th><th>PERIODO</th><th>NOTA</th><th>CONDICIÓN</th><th>DICTAMEN</th>
                                </tr>
                            </thead>
                            <tbody>${filasPdfHtml}</tbody>
                        </table>
                    `;

                    document.body.appendChild(contenedorPdf);
                    html2pdf().set({
                        margin: [10, 10, 10, 10],
                        filename: 'Nomina_Comite_Docente.pdf',
                        html2canvas: { scale: 2 },
                        jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' }
                    }).from(contenedorPdf).save().then(() => {
                        document.body.removeChild(contenedorPdf);
                    });
                });
            }

            // ASISTENCIA PDF
            if (btnAsistencia) {
                btnAsistencia.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.print();
                });
            }

            // GRÁFICOS Y KPIS
            let chartPastel = null;
            let chartBarras = null;

            function actualizarEstadisticasDocente() {
                const materiaSeleccionada = selectMateriaEstadistica ? selectMateriaEstadistica.value : 'Todas';
                const periodoActivo = parseInt(selectPeriodoEstadistica ? selectPeriodoEstadistica.value : 0);

                const notasVisibles = [];
                filas.forEach(fila => {
                    if (fila.style.display !== 'none' && fila.id !== 'sin_coincidencias_pbi21') {
                        const celdaProm = fila.querySelector('.col-promedio-est');
                        let nota = celdaProm ? parseFloat(celdaProm.innerText.trim()) : NaN;
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
                    return;
                }

                const suma = notasVisibles.reduce((acc, curr) => acc + curr, 0);
                if (kpiPromedio) kpiPromedio.innerText = (suma / total).toFixed(2);
                if (kpiMax) kpiMax.innerText = Math.max(...notasVisibles).toFixed(1);
                if (kpiMin) kpiMin.innerText = Math.min(...notasVisibles).toFixed(1);
                if (kpiTotal) kpiTotal.innerText = total;

                let reprobados = 0, regulares = 0, excelencia = 0;
                notasVisibles.forEach(n => {
                    if (n < 6.0) reprobados++;
                    else if (n < 8.0) regulares++;
                    else excelencia++;
                });

                const ctxPastel = document.getElementById('chartPastelRendimiento');
                const ctxBarras = document.getElementById('chartBarrasRendimiento');
                if (!ctxPastel || !ctxBarras || typeof Chart === 'undefined') return;

                if (chartPastel) chartPastel.destroy();
                if (chartBarras) chartBarras.destroy();

                chartPastel = new Chart(ctxPastel, {
                    type: 'pie',
                    data: {
                        labels: ['Reprobados (< 6.0)', 'Aprobados (6.0 - 7.9)', 'Excelencia (8.0 - 10.0)'],
                        datasets: [{ data: [reprobados, regulares, excelencia], backgroundColor: ['#ef4444', '#3b82f6', '#10b981'] }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                chartBarras = new Chart(ctxBarras, {
                    type: 'bar',
                    data: {
                        labels: ['Reprobados (< 6.0)', 'Aprobados (6.0 - 7.9)', 'Excelencia (8.0 - 10.0)'],
                        datasets: [{ data: [reprobados, regulares, excelencia], backgroundColor: ['#f87171', '#60a5fa', '#34d399'] }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }

            filtrarEnTiempoReal();
        });
    </script>

    <!-- Implementación PBI-21, PBI-36 y PBI-23: Búsqueda, Filtrado y Exportaciones -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputBusqueda = document.getElementById('input_buscar_pbi21');
        const selectGrado = document.getElementById('selectGrado');
        const selectSeccion = document.getElementById('selectSeccion');
        const btnLimpiar = document.getElementById('btnLimpiarFiltros');
        const btnExcel = document.getElementById('btnExportarExcel');
        const btnAsistencia = document.getElementById('btnExportarAsistencia');
        const btnFiltrar = document.getElementById('btnFiltrarManual');
        
        const tabla = document.querySelector('table');
        if (!tabla) return;

        const tbody = tabla.querySelector('tbody') || tabla;
        const filas = Array.from(tbody.querySelectorAll('tr')).filter(tr => !tr.querySelector('th') && tr.id !== 'sin_coincidencias_pbi21');

        let filaVacia = document.getElementById('sin_coincidencias_pbi21');
        if (!filaVacia) {
            filaVacia = document.createElement('tr');
            filaVacia.id = 'sin_coincidencias_pbi21';
            filaVacia.style.display = 'none';
            filaVacia.innerHTML = `
                <td colspan="5" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                    <div style="font-size: 26px; margin-bottom: 6px;">📂</div>
                    <strong>No se encontraron estudiantes</strong><br>
                    <span style="font-size: 0.85rem;">No existen coincidencias con los criterios o filtros aplicados.</span>
                </td>
            `;
            tbody.appendChild(filaVacia);
        }

        function filtrarEnTiempoReal() {
            const texto = inputBusqueda ? inputBusqueda.value.toLowerCase().trim() : '';
            const grado = selectGrado ? selectGrado.value.toLowerCase().trim() : '';
            const seccion = selectSeccion ? selectSeccion.value.toLowerCase().trim() : '';

            let visibles = 0;
            filas.forEach(fila => {
                const contenido = fila.innerText.toLowerCase();

                const coincideTexto = texto === '' || contenido.includes(texto);
                const coincideGrado = grado === '' || grado.includes('filtrar') || grado.includes('todos') || contenido.includes(grado);
                const coincideSeccion = seccion === '' || seccion.includes('sección') || seccion.includes('seccion') || seccion.includes('todos') || contenido.includes(seccion);

                if (coincideTexto && coincideGrado && coincideSeccion) {
                    fila.style.display = '';
                    visibles++;
                } else {
                    fila.style.display = 'none';
                }
            });

            filaVacia.style.display = (visibles === 0) ? '' : 'none';
        }

        if (inputBusqueda) inputBusqueda.addEventListener('input', filtrarEnTiempoReal);
        if (selectGrado) selectGrado.addEventListener('change', filtrarEnTiempoReal);
        if (selectSeccion) selectSeccion.addEventListener('change', filtrarEnTiempoReal);
        if (btnFiltrar) btnFiltrar.addEventListener('click', filtrarEnTiempoReal);

        // Limpiar Filtros
        if (btnLimpiar) {
            btnLimpiar.style.cursor = 'pointer';
            btnLimpiar.addEventListener('click', function (e) {
                e.preventDefault();
                if (inputBusqueda) inputBusqueda.value = '';
                if (selectGrado) selectGrado.selectedIndex = 0;
                if (selectSeccion) selectSeccion.selectedIndex = 0;
                filtrarEnTiempoReal();
            });
        }

        function obtenerAlumnosOrdenados() {
            const lista = [];
            filas.forEach(fila => {
                if (fila.style.display !== 'none' && fila.id !== 'sin_coincidencias_pbi21') {
                    const cols = fila.querySelectorAll('td');
                    if (cols.length >= 4) {
                        const nie = cols[0].innerText.trim();
                        const estudiante = cols[1].innerText.trim();
                        const gradoSeccion = cols[2].innerText.trim();
                        const correo = cols[3].innerText.trim();
                        lista.push({ nie, estudiante, gradoSeccion, correo });
                    }
                }
            });
            lista.sort((a, b) => a.estudiante.localeCompare(b.estudiante));
            return lista;
        }

        // PBI-36: Exportación de Notas a Excel
        if (btnExcel) {
            btnExcel.addEventListener('click', function (e) {
                e.preventDefault();
                const datosExportar = obtenerAlumnosOrdenados();

                let htmlExcel = `
                    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
                    <head>
                        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                        <style>
                            th { background-color: #1a2b4c; color: #ffffff; font-weight: bold; border: 1px solid #000000; text-align: center; height: 28px; }
                            td { border: 0.5pt solid #cccccc; vertical-align: middle; }
                            .col-texto { mso-number-format: "\\@"; text-align: left; }
                            .col-centro { mso-number-format: "\\@"; text-align: center; }
                            .col-dec { mso-number-format: "0\\.00"; text-align: right; }
                            .aprobado { color: #16a34a; font-weight: bold; text-align: center; }
                        </style>
                    </head>
                    <body>
                        <table>
                            <tr>
                                <th colspan="8" style="background-color: #0d6efd; color: #ffffff; font-size: 14pt; height: 35px; text-align: center;">
                                    INSTITUTO NOÉ CANJURA - CUADRO DE CALIFICACIONES OFICIAL
                                </th>
                            </tr>
                            <tr>
                                <td colspan="8" style="background-color: #f8fafc; font-size: 10pt;">
                                    <strong>Documento:</strong> Nómina Oficial Consolidada &nbsp;|&nbsp; <strong>Año Lectivo:</strong> 2026
                                </td>
                            </tr>
                            <tr></tr>
                            <thead>
                                <tr>
                                    <th style="width: 120px;">NIE / CARNÉ</th>
                                    <th style="width: 250px;">APELLIDOS Y NOMBRES</th>
                                    <th style="width: 200px;">GRADO Y SECCIÓN</th>
                                    <th style="width: 100px;">ACT 1 (35%)</th>
                                    <th style="width: 100px;">ACT 2 (35%)</th>
                                    <th style="width: 100px;">EXAMEN (30%)</th>
                                    <th style="width: 110px;">NOTA FINAL</th>
                                    <th style="width: 120px;">ESTADO</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                if (datosExportar.length > 0) {
                    datosExportar.forEach(d => {
                        htmlExcel += `
                            <tr>
                                <td class="col-centro">${d.nie}</td>
                                <td class="col-texto">${d.estudiante}</td>
                                <td class="col-centro">${d.gradoSeccion}</td>
                                <td class="col-dec">7.00</td>
                                <td class="col-dec">5.60</td>
                                <td class="col-dec">6.30</td>
                                <td class="col-dec" style="font-weight: bold;">6.30</td>
                                <td class="aprobado">APROBADO</td>
                            </tr>
                        `;
                    });
                } else {
                    htmlExcel += `<tr><td colspan="8" style="text-align:center; padding: 15px;">No hay datos para exportar.</td></tr>`;
                }

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

        // PBI-23: DESCARGA DIRECTA DE LISTA DE ASISTENCIA A ARCHIVO .PDF CON CONSOLIDADOS FÍSICOS
        if (btnAsistencia) {
            btnAsistencia.addEventListener('click', function (e) {
                e.preventDefault();
                const datosExportar = obtenerAlumnosOrdenados();

                // 1. Fecha y mes dinámico actual
                const fechaActual = new Date();
                const anioActual = fechaActual.getFullYear();
                const mesActual = fechaActual.getMonth();

                const nombresMeses = [
                    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
                    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
                ];
                const nombreMes = nombresMeses[mesActual];
                const letrasSemana = ["D", "L", "M", "M", "J", "V", "S"];

                // 2. Días hábiles de lunes a viernes
                const ultimoDiaMes = new Date(anioActual, mesActual + 1, 0).getDate();
                const diasHabiles = [];

                for (let dia = 1; dia <= ultimoDiaMes; dia++) {
                    const fecha = new Date(anioActual, mesActual, dia);
                    const diaSem = fecha.getDay();
                    if (diaSem >= 1 && diaSem <= 5) {
                        diasHabiles.push({
                            num: dia < 10 ? '0' + dia : '' + dia,
                            letra: letrasSemana[diaSem]
                        });
                    }
                }

                const totalHabiles = diasHabiles.length;

                // Encabezados de días compactados para encajar en el ancho horizontal
                let thDiasHtml = '';
                diasHabiles.forEach(dh => {
                    thDiasHtml += `
                        <th style="width: 19px; border: 1px solid #1e293b; text-align: center; font-size: 7px; background: #f8fafc; padding: 1px; color: #0f172a; line-height: 1.1;">
                            ${dh.letra}<br>${dh.num}
                        </th>
                    `;
                });

                // Filas por estudiante con las casillas de conteo en blanco para escribir a mano
                let filasAlumnosHtml = '';
                datosExportar.forEach((d, index) => {
                    let casillasVacias = '';
                    for (let i = 0; i < totalHabiles; i++) {
                        casillasVacias += `<td style="border: 1px solid #475569; height: 18px; width: 19px; text-align: center;">&nbsp;</td>`;
                    }

                    filasAlumnosHtml += `
                        <tr>
                            <td style="border: 1px solid #475569; text-align: center; font-size: 8px; padding: 2px; background: #f8fafc;">${index + 1}</td>
                            <td style="border: 1px solid #475569; text-align: center; font-size: 8px; padding: 2px; font-weight: bold;">${d.nie}</td>
                            <td style="border: 1px solid #475569; font-size: 7.5px; padding: 2px 4px; text-transform: uppercase; white-space: nowrap; overflow: hidden;">${d.estudiante}</td>
                            ${casillasVacias}
                            <td style="border: 1px solid #475569; width: 24px; text-align: center; font-size: 8px; background: #f0fdf4;">&nbsp;</td>
                            <td style="border: 1px solid #475569; width: 24px; text-align: center; font-size: 8px; background: #fefce8;">&nbsp;</td>
                            <td style="border: 1px solid #475569; width: 24px; text-align: center; font-size: 8px; background: #fef2f2;">&nbsp;</td>
                        </tr>
                    `;
                });

                // Casillas vacías para la fila de totales globales por día
                let celdasTotalesDias = '';
                for (let i = 0; i < totalHabiles; i++) {
                    celdasTotalesDias += `<td style="border: 1px solid #475569; height: 18px; text-align: center; font-size: 7px;">&nbsp;</td>`;
                }

                // 3. Contenedor HTML adaptado al ancho de página horizontal (Letter Landscape)
                const contenedorPdf = document.createElement('div');
                contenedorPdf.style.padding = '4px 6px';
                contenedorPdf.style.fontFamily = 'Arial, sans-serif';
                contenedorPdf.style.color = '#000000';

                contenedorPdf.innerHTML = `
                    <div style="text-align: center; margin-bottom: 6px; border-bottom: 2px solid #0f172a; padding-bottom: 3px;">
                        <h2 style="margin: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">INSTITUTO NOÉ CANJURA - CONTROL MENSUAL DE ASISTENCIA</h2>
                        <p style="margin: 2px 0 0 0; font-size: 9.5px; color: #475569;">Registro Oficial Auxiliar de Asistencias y Ausentismo</p>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; font-size: 8.5px; margin-bottom: 6px; font-weight: bold; background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">
                        <span>Año Lectivo: ${anioActual}</span>
                        <span>Mes: ${nombreMes.toUpperCase()}</span>
                        <span>Grado/Sección: ${selectGrado ? selectGrado.value || 'General' : ''} ${selectSeccion ? selectSeccion.value : ''}</span>
                        <span>Leyenda: [ • ] Asistencia &nbsp;|&nbsp; [ IJ ] Inasistencia Justificada &nbsp;|&nbsp; [ II ] Inasistencia Injustificada</span>
                    </div>

                    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 22px; border: 1px solid #1e293b; background: #0f172a; color: #fff; font-size: 7.5px; text-align: center;">N°</th>
                                <th rowspan="2" style="width: 65px; border: 1px solid #1e293b; background: #0f172a; color: #fff; font-size: 7.5px; text-align: center;">NIE</th>
                                <th rowspan="2" style="width: 155px; border: 1px solid #1e293b; background: #0f172a; color: #fff; font-size: 7.5px; text-align: left; padding-left: 4px;">NÓMINA DE ESTUDIANTES</th>
                                <th colspan="${totalHabiles}" style="border: 1px solid #1e293b; background: #1e293b; color: #fff; font-size: 7.5px; text-align: center;">DÍAS HÁBILES DEL MES (${nombreMes.toUpperCase()})</th>
                                <th colspan="3" style="width: 72px; border: 1px solid #1e293b; background: #0f172a; color: #fff; font-size: 7.5px; text-align: center;">TOTALES MES</th>
                            </tr>
                            <tr>
                                ${thDiasHtml}
                                <th style="width: 24px; border: 1px solid #1e293b; background: #166534; color: #fff; font-size: 6.5px; text-align: center; padding: 1px;" title="Asistencias">A</th>
                                <th style="width: 24px; border: 1px solid #1e293b; background: #854d0e; color: #fff; font-size: 6.5px; text-align: center; padding: 1px;" title="Inasistencias Justificadas">IJ</th>
                                <th style="width: 24px; border: 1px solid #1e293b; background: #991b1b; color: #fff; font-size: 6.5px; text-align: center; padding: 1px;" title="Inasistencias Injustificadas">II</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${filasAlumnosHtml}
                            <tr style="background: #e2e8f0; font-weight: bold;">
                                <td colspan="3" style="border: 1px solid #475569; text-align: right; font-size: 7.5px; padding-right: 6px;">TOTALES GENERALES SECCIÓN:</td>
                                ${celdasTotalesDias}
                                <td style="border: 1px solid #475569; text-align: center; font-size: 8px;">&nbsp;</td>
                                <td style="border: 1px solid #475569; text-align: center; font-size: 8px;">&nbsp;</td>
                                <td style="border: 1px solid #475569; text-align: center; font-size: 8px;">&nbsp;</td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="margin-top: 24px; display: flex; justify-content: space-around; font-size: 8.5px;">
                        <div style="width: 200px; border-top: 1px solid #000; text-align: center; padding-top: 3px;">Firma del Docente</div>
                        <div style="width: 200px; border-top: 1px solid #000; text-align: center; padding-top: 3px;">Sello y Recibido Dirección</div>
                    </div>
                `;

                // 4. Parámetros de ajuste de márgenes para asegurar el 100% visible
                const opcionesPdf = {
                    margin: [4, 5, 4, 5],
                    filename: 'Control_Asistencia_' + nombreMes + '_' + anioActual + '.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, logging: false },
                    jsPDF: { unit: 'mm', format: 'letter', orientation: 'landscape' }
                };

                // Inserción temporal en el DOM para evitar que html2pdf falle en blanco
                document.body.appendChild(contenedorPdf);
                html2pdf().set(opcionesPdf).from(contenedorPdf).save().then(() => {
                    document.body.removeChild(contenedorPdf);
                }).catch(err => {
                    console.error("Error al generar PDF:", err);
                    if (document.body.contains(contenedorPdf)) {
                        document.body.removeChild(contenedorPdf);
                    }
                });
            });
        }

        // Ejecutar inicialización del filtro y cerrar el listener del DOM
        filtrarEnTiempoReal();
    });
    </script>
</body>
</html>