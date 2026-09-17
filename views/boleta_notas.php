<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?action=login');
    exit;
}

// Respaldo de datos del estudiante si vienen incompletos
if (empty($estudiante) || empty($estudiante['nie']) || $estudiante['nie'] === '---') {
    if (file_exists(__DIR__ . '/../config/Conexion.php')) {
        require_once __DIR__ . '/../config/Conexion.php';
    } elseif (file_exists(__DIR__ . '/../config/conexion.php')) {
        require_once __DIR__ . '/../config/conexion.php';
    }

    $dbConn = class_exists('Conexion') ? (new Conexion())->conectar() : ($pdo ?? null);
    if ($dbConn) {
        $idBuscar = intval($_GET['estudiante_id'] ?? $_GET['id'] ?? 0);
        if ($idBuscar > 0) {
            try {
                $stmtEst = $dbConn->prepare("SELECT * FROM estudiantes WHERE id = :id LIMIT 1");
                $stmtEst->execute([':id' => $idBuscar]);
                $datos = $stmtEst->fetch(PDO::FETCH_ASSOC);
                if ($datos) $estudiante = $datos;
            } catch (PDOException $e) {}
        }
    }
}

$est = $estudiante ?? [];
$nombre = trim($est['nombre'] ?? '');
$apellido = trim($est['apellido'] ?? '');
$nombreCompleto = trim($nombre . ' ' . $apellido);
if (empty($nombreCompleto) || $nombreCompleto === 'Estudiante Registrado') {
    $nombreCompleto = !empty($est['nombre_completo']) ? $est['nombre_completo'] : 'Estudiante INCA';
}

$nie = !empty($est['nie']) && $est['nie'] !== '---' ? $est['nie'] : '---';
$grado = !empty($est['grado']) ? $est['grado'] : '9° Grado';
$seccion = !empty($est['seccion']) ? $est['seccion'] : 'Sección A';

// Regla dinámica: Bachillerato (1° o 2° Año) usa 4 periodos; 1° a 9° Grado usan 3 periodos
$esBachillerato = (stripos($grado, 'bachillerato') !== false);
$totalPeriodos = $esBachillerato ? 4 : 3;

$asignaturasDefecto = [
    'Matemática',
    'Lenguaje y Literatura',
    'Estudios Sociales y Cívica',
    'Ciencia y Tecnología',
    'Idioma Extranjero (Inglés)',
    'Educación Física'
];

$calificacionesMap = [];
$fuenteNotas = !empty($notas) ? $notas : (!empty($calificaciones) ? $calificaciones : []);

if (!empty($fuenteNotas) && is_array($fuenteNotas)) {
    foreach ($fuenteNotas as $n) {
        $mat = trim($n['materia'] ?? '');
        $per = trim((string)($n['periodo'] ?? ''));
        $promVal = floatval($n['promedio'] ?? $n['nota_periodo'] ?? 0);

        $numPeriodo = 0;
        if (preg_match('/(\d+)/', $per, $matches)) {
            $numPeriodo = intval($matches[1]);
        }

        if (!empty($mat) && $numPeriodo > 0) {
            $calificacionesMap[$mat][$numPeriodo] = $promVal;
        }
    }
}

$sumaPromediosMaterias = 0;
$totalMaterias = count($asignaturasDefecto);
$todosLosPeriodosCompletos = true;
$materiasEnRecuperacion = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Notas — <?= htmlspecialchars($nombreCompleto) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-navy: #0b1a30;
            --border-card: #e2e8f0;
            --text-main: #2c3e50;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; }
        body { background-color: #f8fafc; color: var(--text-main); padding: 2.5rem 1rem; min-height: 100vh; }
        .boleta-container { max-width: 1100px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 2.5rem 3rem; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03); border: 1px solid var(--border-card); }
        .top-action-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem; }
        .btn-action { display: inline-flex; align-items: center; gap: 8px; padding: 0.65rem 1.25rem; border-radius: 8px; font-size: 0.9rem; font-weight: 700; text-decoration: none; cursor: pointer; border: none; transition: all 0.2s; }
        .btn-back { background: #ffffff; color: #334155; border: 1px solid var(--border-card); }
        .btn-back:hover { background: #f1f5f9; }
        .btn-print { background: #0070f3; color: white; box-shadow: 0 4px 10px rgba(0, 112, 243, 0.2); }
        .btn-print:hover { background: #0060df; }
        .header-institucional { display: flex; align-items: center; justify-content: space-between; padding-bottom: 2rem; border-bottom: 1px solid var(--border-card); margin-bottom: 2rem; }
        .inst-left { display: flex; align-items: center; gap: 1.5rem; }
        .logo-circle { width: 68px; height: 68px; border-radius: 50%; background: #ffffff; border: 1px solid #cbd5e1; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); }
        .logo-circle img { width: 100%; height: 100%; object-fit: contain; }
        .inst-titles h1 { font-size: 1.5rem; font-weight: 900; color: #0b1a30; letter-spacing: 0.3px; }
        .inst-titles p { font-size: 0.95rem; color: var(--text-muted); margin-top: 2px; }
        .badge-anio { background: #f8fafc; border: 1px solid var(--border-card); padding: 0.55rem 1.4rem; border-radius: 25px; font-size: 0.88rem; font-weight: 800; color: #1e293b; }
        .student-details-card { background: #f8fafc; border: 1px solid var(--border-card); border-radius: 14px; padding: 1.75rem 2.2rem; display: grid; grid-template-columns: 2.2fr 1.2fr 1fr; row-gap: 1.5rem; column-gap: 2rem; margin-bottom: 2rem; }
        .field-box span { display: block; font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.4rem; }
        .field-box p { font-size: 1.15rem; font-weight: 800; color: #0f172a; }
        .table-wrap { border: 1px solid var(--border-card); border-radius: 12px; overflow: hidden; margin-bottom: 2rem; }
        table { width: 100%; border-collapse: collapse; text-align: center; }
        th { background-color: #ffffff; color: #475569; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.6px; text-transform: uppercase; padding: 1.25rem 1rem; border-bottom: 1px solid var(--border-card); }
        th.th-left { text-align: left; padding-left: 1.8rem; width: 34%; }
        td { padding: 1.1rem 1rem; border-bottom: 1px solid #f1f5f9; font-size: 0.92rem; color: #475569; }
        .td-materia { text-align: left; padding-left: 1.8rem !important; font-weight: 700; color: #1e293b; }
        .td-final { font-weight: 800; color: #0f172a; }
        .badge-status { display: inline-block; padding: 4px 14px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; }
        .badge-reprobado { background-color: #fde8e8; color: #c81e1e; }
        .badge-aprobado { background-color: #def7ec; color: #03543f; }
        .badge-pendiente { background-color: #f1f5f9; color: #64748b; }
        .row-promedio-global td { background-color: #ffffff; border-top: 1px solid var(--border-card); border-bottom: none; padding: 1.3rem 1rem; }
        .label-promedio-global { text-align: right; font-size: 0.82rem; font-weight: 800; text-transform: uppercase; color: #1e293b; letter-spacing: 0.6px; padding-right: 1.8rem !important; }
        .val-promedio-global { font-size: 1.15rem; font-weight: 800; color: #0284c7; }
        .alert-recuperacion { background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 1.5rem 2rem; margin-bottom: 2rem; }
        .alert-recuperacion-header { display: flex; align-items: center; gap: 10px; color: #b91c1c; font-size: 1.05rem; font-weight: 800; margin-bottom: 0.6rem; }
        .alert-recuperacion p { color: #7f1d1d; font-size: 0.9rem; margin-bottom: 0.75rem; }
        .recuperacion-tags { display: flex; flex-wrap: wrap; gap: 8px; }
        .recuperacion-tag { background: #ffffff; border: 1px solid #fca5a5; color: #b91c1c; padding: 5px 14px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; }
        .global-summary-card { background: #f8fafc; border: 1px solid var(--border-card); border-radius: 12px; padding: 1.5rem 2rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 3.5rem; }
        .summary-col span { display: block; font-size: 0.72rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.4rem; }
        .summary-col .val-score { font-size: 1.45rem; font-weight: 800; color: #0284c7; }
        .summary-col .val-text { font-size: 1.25rem; font-weight: 800; color: var(--text-dark); }
        .summary-col .val-status { font-size: 1.25rem; font-weight: 900; letter-spacing: 0.5px; text-transform: uppercase; }
        .status-aprobado     { color: #16a34a; }
        .status-recuperacion { color: #dc2626; }
        .status-curso        { color: #eab308; }
        .signatures-row { display: grid; grid-template-columns: 1fr 1fr; gap: 5rem; padding-top: 1rem; }
        .sig-block { text-align: center; }
        .sig-divider { border-top: 1px solid #94a3b8; margin-bottom: 0.6rem; }
        .sig-block h4 { font-size: 0.95rem; font-weight: 800; color: var(--text-dark); }
        .sig-block p { font-size: 0.82rem; color: var(--text-muted); }
        
        @media (max-width: 768px) {
            .header-institucional { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .student-details-card { grid-template-columns: 1fr; }
            .global-summary-card { grid-template-columns: 1fr; }
            .signatures-row { grid-template-columns: 1fr; gap: 2.5rem; }
        }

        /* DISEÑO ESTRICTO PARA UNA SOLA PÁGINA EN IMPRESIÓN */
        @media print {
            @page {
                size: letter portrait;
                margin: 0.3cm;
            }
            body {
                background: white !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .boleta-container {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
            }
            .top-action-bar, .btn-action {
                display: none !important;
            }
            .header-institucional {
                padding-bottom: 0.35rem !important;
                margin-bottom: 0.35rem !important;
            }
            .logo-circle {
                width: 40px !important;
                height: 40px !important;
            }
            .inst-titles h1 {
                font-size: 1rem !important;
            }
            .inst-titles p {
                font-size: 0.75rem !important;
            }
            .badge-anio {
                padding: 0.2rem 0.8rem !important;
                font-size: 0.7rem !important;
            }
            .student-details-card {
                padding: 0.35rem 0.6rem !important;
                margin-bottom: 0.35rem !important;
                row-gap: 0.3rem !important;
            }
            .field-box span {
                font-size: 0.6rem !important;
                margin-bottom: 0.05rem !important;
            }
            .field-box p {
                font-size: 0.85rem !important;
            }
            .table-wrap {
                margin-bottom: 0.35rem !important;
                border-radius: 6px !important;
            }
            th {
                padding: 0.35rem 0.3rem !important;
                font-size: 0.65rem !important;
            }
            td {
                padding: 0.3rem 0.3rem !important;
                font-size: 0.75rem !important;
            }
            .alert-recuperacion {
                padding: 0.35rem 0.6rem !important;
                margin-bottom: 0.35rem !important;
            }
            .alert-recuperacion p {
                font-size: 0.75rem !important;
                margin-bottom: 0.3rem !important;
            }
            .global-summary-card {
                padding: 0.35rem 0.6rem !important;
                margin-bottom: 0.6rem !important;
                gap: 0.5rem !important;
            }
            .summary-col span {
                font-size: 0.6rem !important;
                margin-bottom: 0.1rem !important;
            }
            .summary-col .val-score, .summary-col .val-text, .summary-col .val-status {
                font-size: 0.9rem !important;
            }
            .signatures-row {
                padding-top: 0.1rem !important;
                gap: 2rem !important;
            }
            .sig-block h4 {
                font-size: 0.75rem !important;
            }
            .sig-block p {
                font-size: 0.65rem !important;
            }
        }
    </style>
</head>
<body>

    <div class="boleta-container">
        <div class="top-action-bar">
            <a href="index.php?action=gestion_notas" class="btn-action btn-back">
                <i class="fa-solid fa-arrow-left"></i> Volver al Listado
            </a>
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fa-solid fa-print"></i> Imprimir Boleta Oficial
            </button>
        </div>

        <div class="header-institucional">
            <div class="inst-left">
                <div class="logo-circle">
                    <img src="img/logo_inca.png" alt="Escudo INCA" onerror="this.src='https://ui-avatars.com/api/?name=INCA&background=0b1a30&color=fff'">
                </div>
                <div class="inst-titles">
                    <h1>INSTITUTO NACIONAL DR. NOÉ CANJURA</h1>
                    <p>Sistema Integrado de Calificaciones Oficiales</p>
                </div>
            </div>
            <div class="badge-anio">Año Lectivo 2026</div>
        </div>

        <div class="student-details-card">
            <div class="field-box">
                <span>Estudiante</span>
                <p><?= htmlspecialchars($nombreCompleto) ?></p>
            </div>
            <div class="field-box">
                <span>NIE / Carné</span>
                <p><?= htmlspecialchars($nie) ?></p>
            </div>
            <div class="field-box">
                <span>Nivel / Grado</span>
                <p><?= htmlspecialchars($grado) ?></p>
            </div>
            <div class="field-box" style="grid-column: 1 / -1;">
                <span>Sección</span>
                <p><?= htmlspecialchars($seccion) ?></p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="th-left">ASIGNATURA</th>
                        <?php for ($p = 1; $p <= $totalPeriodos; $p++): ?>
                            <th>PERIODO <?= $p ?></th>
                        <?php endfor; ?>
                        <th>NOTA FINAL</th>
                        <th>RESULTADO</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($asignaturasDefecto as $asignatura): 
                        $sumaPeriodos = 0;
                        $periodosRegistradosMateria = 0;
                    ?>
                        <tr>
                            <td class="td-materia"><?= htmlspecialchars($asignatura) ?></td>

                            <?php for ($p = 1; $p <= $totalPeriodos; $p++): 
                                $tieneNota = isset($calificacionesMap[$asignatura][$p]);
                                $valorNota = $tieneNota ? $calificacionesMap[$asignatura][$p] : 0.0;

                                if ($tieneNota && $valorNota > 0) {
                                    $periodosRegistradosMateria++;
                                } elseif (!$tieneNota) {
                                    $todosLosPeriodosCompletos = false;
                                }

                                $sumaPeriodos += $valorNota;
                            ?>
                                <td><?= number_format($valorNota, 1) ?></td>
                            <?php endfor; ?>

                            <?php 
                                $notaFinal = round($sumaPeriodos / $totalPeriodos, 1);
                                $sumaPromediosMaterias += $notaFinal;
                                $materiaAprobada = ($notaFinal >= 6.0);

                                if (!$materiaAprobada && $periodosRegistradosMateria === $totalPeriodos) {
                                    $materiasEnRecuperacion[] = $asignatura;
                                }
                            ?>

                            <td class="td-final"><?= number_format($notaFinal, 1) ?></td>
                            <td>
                                <?php if ($periodosRegistradosMateria < $totalPeriodos && $notaFinal == 0): ?>
                                    <span class="badge-status badge-pendiente">PENDIENTE</span>
                                <?php elseif ($materiaAprobada): ?>
                                    <span class="badge-status badge-aprobado">APROBADO</span>
                                <?php else: ?>
                                    <span class="badge-status badge-reprobado">REPROBADO</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php 
                        $promedioGlobal = $totalMaterias > 0 ? round($sumaPromediosMaterias / $totalMaterias, 2) : 0.00;

                        if ($promedioGlobal >= 8.5) {
                            $escalaCualitativa = 'EXCELENTE';
                        } elseif ($promedioGlobal >= 7.0) {
                            $escalaCualitativa = 'MUY BUENO';
                        } elseif ($promedioGlobal >= 6.0) {
                            $escalaCualitativa = 'REGULAR';
                        } else {
                            $escalaCualitativa = 'DEFICIENTE';
                        }

                        if (!$todosLosPeriodosCompletos && $promedioGlobal == 0) {
                            $condicionFinal = 'EN CURSO';
                            $claseCondicion = 'status-curso';
                        } else {
                            if (empty($materiasEnRecuperacion) && $promedioGlobal >= 6.0) {
                                $condicionFinal = 'APROBADO';
                                $claseCondicion = 'status-aprobado';
                            } else {
                                $condicionFinal = 'RECUPERACIÓN';
                                $claseCondicion = 'status-recuperacion';
                            }
                        }
                    ?>

                    <tr class="row-promedio-global">
                        <td colspan="<?= $totalPeriodos + 1 ?>" class="label-promedio-global">
                            PROMEDIO GLOBAL INSTITUCIONAL:
                        </td>
                        <td class="val-promedio-global">
                            <?= number_format($promedioGlobal, 2) ?>
                        </td>
                        <td>
                            <span class="badge-status <?= ($promedioGlobal >= 6.0) ? 'badge-aprobado' : 'badge-reprobado' ?>">
                                <?= $escalaCualitativa ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if (!empty($materiasEnRecuperacion)): ?>
            <div class="alert-recuperacion">
                <div class="alert-recuperacion-header">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>ALERTA DE PERIODO DE RECUPERACIÓN</span>
                </div>
                <p>El estudiante no alcanzó la nota mínima reglamentaria (6.0) y debe presentarse a periodo extraordinario en:</p>
                <div class="recuperacion-tags">
                    <?php foreach ($materiasEnRecuperacion as $matRep): ?>
                        <span class="recuperacion-tag">
                            <i class="fa-solid fa-book-open"></i> <?= htmlspecialchars($matRep) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="global-summary-card">
            <div class="summary-col">
                <span>Promedio Global Acumulado</span>
                <p class="val-score"><?= number_format($promedioGlobal, 1) ?> / 10.0</p>
            </div>
            <div class="summary-col">
                <span>Escala Cualitativa</span>
                <p class="val-text"><?= $escalaCualitativa ?></p>
            </div>
            <div class="summary-col">
                <span>Condición Final del Alumno</span>
                <p class="val-status <?= $claseCondicion ?>"><?= $condicionFinal ?></p>
            </div>
        </div>

        <div class="signatures-row">
            <div class="sig-block">
                <div class="sig-divider"></div>
                <h4>Docente Guía / Orientador</h4>
                <p>Firma y Sello</p>
            </div>
            <div class="sig-block">
                <div class="sig-divider"></div>
                <h4>Dirección Institucional</h4>
                <p>Instituto Nacional Dr. Noé Canjura</p>
            </div>
        </div>
    </div>
</body>
</html>