<?php
<<<<<<< Updated upstream
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
=======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
>>>>>>> Stashed changes
}

if (!isset($_SESSION['rol'])) {
    header("Location: index.php?action=login");
    exit();
}

$datosEstudiante = !empty($boleta) ? $boleta[0] : null;

// Determinar cantidad de periodos: 4 para Bachillerato, 3 para Básica
$esBachillerato = false;
if ($datosEstudiante) {
    $gradoNorm = strtoupper($datosEstudiante['grado']);
    $esBachillerato = (strpos($gradoNorm, 'B') !== false || strpos($gradoNorm, 'BACHILLERATO') !== false);
}
$numPeriodos = $esBachillerato ? 4 : 3;

// Agrupar calificaciones en matriz: [Materia][Periodo] = Promedio
$matrizNotas = [];
if (!empty($boleta)) {
    foreach ($boleta as $fila) {
        $mat = $fila['materia'];
        $per = intval($fila['periodo']);
        $matrizNotas[$mat][$per] = floatval($fila['promedio']);
    }
}
<<<<<<< Updated upstream
=======

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

// Regla MINED: Bachillerato = 4 Periodos; Educación Básica (1° a 9° Grado) = 3 Periodos
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

        if (!empty($mat) && $numPeriodo > 0 && $numPeriodo <= $totalPeriodos) {
            $calificacionesMap[$mat][$numPeriodo] = $promVal;
        }
    }
}

$sumaPromediosMaterias = 0;
$totalMaterias = count($asignaturasDefecto);
$todosLosPeriodosCompletos = true;
$materiasEnRecuperacion = [];
>>>>>>> Stashed changes
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Calificaciones - <?= htmlspecialchars($datosEstudiante['nie'] ?? 'INCA') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
<<<<<<< Updated upstream
        body { background-color: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; color: #1e293b; }
        .boleta-sheet { max-width: 950px; margin: 30px auto; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        .table-siges { border: 1px solid #cbd5e1; font-size: 13.5px; margin-bottom: 0; }
        .table-siges th { background-color: #f8fafc; font-weight: 700; color: #334155; border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; padding: 10px; }
        .table-siges td { border: 1px solid #cbd5e1; vertical-align: middle; padding: 8px 12px; }
        .meta-box { border: 1px solid #cbd5e1; border-collapse: collapse; font-size: 13px; width: 100%; }
        .meta-box td { border: 1px solid #cbd5e1; padding: 6px 12px; }
        .meta-label { font-weight: bold; background-color: #f8fafc; color: #475569; width: 170px; }
        .badge-aprobado { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; font-weight: 600; }
        .badge-reprobado { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; font-weight: 600; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; color: black; font-size: 12px; }
            .boleta-sheet { max-width: 100%; margin: 0; padding: 0 !important; box-shadow: none; border: none !important; }
            .table-siges th { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .meta-box td { padding: 4px 8px; }
=======
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
        .header-institucional { display: flex; align-items: center; justify-content: space-between; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-card); margin-bottom: 1.5rem; }
        .inst-left { display: flex; align-items: center; gap: 1.25rem; }
        .logo-circle { width: 62px; height: 62px; border-radius: 50%; background: #ffffff; border: 1px solid #cbd5e1; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 4px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); }
        .logo-circle img { width: 100%; height: 100%; object-fit: contain; }
        .inst-titles h1 { font-size: 1.4rem; font-weight: 900; color: #0b1a30; letter-spacing: 0.3px; }
        .inst-titles p { font-size: 0.9rem; color: var(--text-muted); margin-top: 2px; }
        .badge-anio { background: #f8fafc; border: 1px solid var(--border-card); padding: 0.5rem 1.2rem; border-radius: 25px; font-size: 0.85rem; font-weight: 800; color: #1e293b; }
        .student-details-card { background: #f8fafc; border: 1px solid var(--border-card); border-radius: 12px; padding: 1.25rem 1.75rem; display: grid; grid-template-columns: 2.2fr 1.2fr 1fr; row-gap: 1rem; column-gap: 1.5rem; margin-bottom: 1.5rem; }
        .field-box span { display: block; font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.3rem; }
        .field-box p { font-size: 1.05rem; font-weight: 800; color: #0f172a; }
        .table-wrap { border: 1px solid var(--border-card); border-radius: 12px; overflow: hidden; margin-bottom: 1.5rem; }
        table { width: 100%; border-collapse: collapse; text-align: center; }
        th { background-color: #ffffff; color: #475569; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.6px; text-transform: uppercase; padding: 1rem 0.75rem; border-bottom: 1px solid var(--border-card); }
        th.th-left { text-align: left; padding-left: 1.5rem; width: 34%; }
        td { padding: 0.85rem 0.75rem; border-bottom: 1px solid #f1f5f9; font-size: 0.88rem; color: #475569; }
        .td-materia { text-align: left; padding-left: 1.5rem !important; font-weight: 700; color: #1e293b; }
        .td-final { font-weight: 800; color: #0f172a; }
        .badge-status { display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; }
        .badge-reprobado { background-color: #fde8e8; color: #c81e1e; }
        .badge-aprobado { background-color: #def7ec; color: #03543f; }
        .badge-pendiente { background-color: #f1f5f9; color: #64748b; }
        .row-promedio-global td { background-color: #ffffff; border-top: 1px solid var(--border-card); border-bottom: none; padding: 1.1rem 0.75rem; }
        .label-promedio-global { text-align: right; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: #1e293b; letter-spacing: 0.6px; padding-right: 1.5rem !important; }
        .val-promedio-global { font-size: 1.1rem; font-weight: 800; color: #0284c7; }
        .alert-recuperacion { background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; }
        .alert-recuperacion-header { display: flex; align-items: center; gap: 8px; color: #b91c1c; font-size: 0.95rem; font-weight: 800; margin-bottom: 0.4rem; }
        .alert-recuperacion p { color: #7f1d1d; font-size: 0.85rem; margin-bottom: 0.5rem; }
        .recuperacion-tags { display: flex; flex-wrap: wrap; gap: 6px; }
        .recuperacion-tag { background: #ffffff; border: 1px solid #fca5a5; color: #b91c1c; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; }
        .global-summary-card { background: #f8fafc; border: 1px solid var(--border-card); border-radius: 12px; padding: 1.25rem 1.75rem; display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 2.5rem; }
        .summary-col span { display: block; font-size: 0.7rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 0.3rem; }
        .summary-col .val-score { font-size: 1.35rem; font-weight: 800; color: #0284c7; }
        .summary-col .val-text { font-size: 1.15rem; font-weight: 800; color: var(--text-dark); }
        .summary-col .val-status { font-size: 1.15rem; font-weight: 900; letter-spacing: 0.5px; text-transform: uppercase; }
        .status-aprobado     { color: #16a34a; }
        .status-recuperacion { color: #dc2626; }
        .status-curso        { color: #eab308; }
        .signatures-row { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; padding-top: 0.5rem; }
        .sig-block { text-align: center; }
        .sig-divider { border-top: 1px solid #94a3b8; margin-bottom: 0.5rem; }
        .sig-block h4 { font-size: 0.9rem; font-weight: 800; color: var(--text-dark); }
        .sig-block p { font-size: 0.8rem; color: var(--text-muted); }

        /* REGLAS ESTRICTAS DE IMPRESIÓN (1 PÁGINA CARTA) */
        @media print {
            @page {
                size: letter portrait;
                margin: 10mm;
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
            .top-action-bar {
                display: none !important;
            }
            .header-institucional {
                padding-bottom: 0.5rem !important;
                margin-bottom: 0.5rem !important;
            }
            .logo-circle {
                width: 44px !important;
                height: 44px !important;
            }
            .inst-titles h1 {
                font-size: 1.05rem !important;
            }
            .inst-titles p {
                font-size: 0.75rem !important;
            }
            .badge-anio {
                padding: 0.25rem 0.8rem !important;
                font-size: 0.75rem !important;
            }
            .student-details-card {
                padding: 0.45rem 0.75rem !important;
                margin-bottom: 0.5rem !important;
                row-gap: 0.35rem !important;
            }
            .field-box span {
                font-size: 0.6rem !important;
                margin-bottom: 0.08rem !important;
            }
            .field-box p {
                font-size: 0.85rem !important;
            }
            .table-wrap {
                margin-bottom: 0.5rem !important;
                border-radius: 6px !important;
            }
            th {
                padding: 0.4rem 0.35rem !important;
                font-size: 0.65rem !important;
            }
            td {
                padding: 0.35rem 0.35rem !important;
                font-size: 0.75rem !important;
            }
            .alert-recuperacion {
                padding: 0.4rem 0.75rem !important;
                margin-bottom: 0.45rem !important;
            }
            .alert-recuperacion p {
                font-size: 0.75rem !important;
                margin-bottom: 0.25rem !important;
            }
            .global-summary-card {
                padding: 0.45rem 0.75rem !important;
                margin-bottom: 0.8rem !important;
                gap: 0.6rem !important;
            }
            .summary-col span {
                font-size: 0.6rem !important;
                margin-bottom: 0.08rem !important;
            }
            .summary-col .val-score, .summary-col .val-text, .summary-col .val-status {
                font-size: 0.9rem !important;
            }
            .signatures-row {
                padding-top: 0.5rem !important;
                gap: 2.5rem !important;
            }
            .sig-block h4 {
                font-size: 0.75rem !important;
            }
            .sig-block p {
                font-size: 0.65rem !important;
            }
>>>>>>> Stashed changes
        }
    </style>
</head>
<body>

    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print" style="max-width: 950px; margin: auto;">
            <a href="index.php?action=notas" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver al Panel
            </a>
            <button onclick="window.print();" class="btn btn-primary btn-sm fw-bold shadow-sm">
                <i class="fa-solid fa-print me-1"></i> Imprimir Boleta Oficial
            </button>
        </div>

        <div class="boleta-sheet p-4 p-md-5 border">
            <!-- Encabezado Institucional -->
            <div class="row align-items-center mb-4">
                <div class="col-2 text-center">
                    <img src="img/logo_inca.png" alt="Logo INCA" class="img-fluid" style="max-height: 75px;">
                </div>
<<<<<<< Updated upstream
                <div class="col-10 text-center pe-md-5">
                    <h6 class="fw-bold mb-0 text-uppercase tracking-wide">Instituto Nacional Noé Canjura</h6>
                    <p class="text-muted mb-0 small text-uppercase">Departamento de Registro y Control Académico</p>
                    <h5 class="fw-bold text-dark mt-1 mb-0">BOLETA DE CALIFICACIONES</h5>
=======
                <div class="inst-titles">
                    <h1>INSTITUTO NOÉ CANJURA</h1>
                    <p>Sistema Integrado de Calificaciones Oficiales</p>
>>>>>>> Stashed changes
                </div>
            </div>

            <?php if ($datosEstudiante): ?>
            <!-- Ficha Técnica -->
            <table class="meta-box mb-4">
                <tr>
                    <td class="meta-label">Sede Educativa:</td>
                    <td colspan="3"><strong>20281 - INSTITUTO NOÉ CANJURA</strong></td>
                </tr>
                <tr>
                    <td class="meta-label">Plan de Estudio:</td>
                    <td colspan="3"><?= $esBachillerato ? 'Bachillerato General / Técnico' : 'Educación Básica / Tercer Ciclo'; ?></td>
                </tr>
                <tr>
                    <td class="meta-label">Estudiante:</td>
                    <td><strong class="text-dark"><?= htmlspecialchars($datosEstudiante['nie'] . ' - ' . $datosEstudiante['apellido'] . ', ' . $datosEstudiante['nombre']); ?></strong></td>
                    <td class="meta-label" style="width: 100px;">Año:</td>
                    <td style="width: 110px;"><strong>2026</strong></td>
                </tr>
                <tr>
                    <td class="meta-label">Grado:</td>
                    <td><strong><?= htmlspecialchars($datosEstudiante['grado']); ?></strong></td>
                    <td class="meta-label">Sección:</td>
                    <td><strong>Sección <?= htmlspecialchars($datosEstudiante['seccion']); ?></strong></td>
                </tr>
            </table>

            <!-- Tabla de Calificaciones Estilo SIGES -->
            <div class="table-responsive mb-3">
                <table class="table table-siges align-middle">
                    <thead>
                        <tr>
                            <th class="text-start ps-3" style="width: 45%;">Componente / Plan de Estudio</th>
                            <?php for ($p = 1; $p <= $numPeriodos; $p++): ?>
                                <th style="width: 10%;">P<?= $p ?></th>
                            <?php endfor; ?>
                            <th style="width: 12%;">NF</th>
                            <th style="width: 18%;">Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sumaNotaFinalGlobal = 0;
                        $materiasContadas = 0;

                        foreach ($matrizNotas as $nombreMateria => $periodos): 
                            $sumaPeriodos = 0;
                            $periodosAsignados = 0;
                        ?>
                        <tr>
                            <td class="ps-3 fw-bold text-dark"><?= htmlspecialchars($nombreMateria); ?></td>
                            
                            <?php for ($p = 1; $p <= $numPeriodos; $p++): ?>
                                <td class="text-center">
                                    <?php 
                                    if (isset($periodos[$p])): 
                                        $val = $periodos[$p];
                                        $sumaPeriodos += $val;
                                        $periodosAsignados++;
                                        echo '<span class="' . ($val < 6.0 ? 'text-danger fw-bold' : 'text-dark') . '">' . number_format($val, 1) . '</span>';
                                    else: 
                                        echo '<span class="text-muted">---</span>';
                                    endif; 
                                    ?>
                                </td>
                            <?php endfor; ?>

                            <?php 
                            if ($periodosAsignados > 0) {
                                $notaFinal = $sumaPeriodos / $periodosAsignados;
                                $aprobado = ($notaFinal >= 6.0);
                                $sumaNotaFinalGlobal += $notaFinal;
                                $materiasContadas++;
                            } else {
                                $notaFinal = null;
                            }
                            ?>

                            <td class="text-center fw-bold fs-6 <?= ($notaFinal !== null && $notaFinal < 6.0) ? 'text-danger' : 'text-primary' ?>">
                                <?= $notaFinal !== null ? number_format($notaFinal, 1) : '---' ?>
                            </td>

                            <td class="text-center">
                                <?php if ($notaFinal !== null): ?>
                                    <span class="badge <?= $aprobado ? 'badge-aprobado' : 'badge-reprobado' ?> px-3 py-1">
                                        <?= $aprobado ? 'Aprobado' : 'Reprobado' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border px-2 py-1 small">Pendiente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <!-- Fila de Resumen Global -->
                        <?php 
                        $promedioGlobal = $materiasContadas > 0 ? ($sumaNotaFinalGlobal / $materiasContadas) : 0;
                        $globalAprobado = ($promedioGlobal >= 6.0);
                        ?>
                        <tr class="fw-bold" style="background-color: #f8fafc; border-top: 2px solid #94a3b8;">
                            <td class="ps-3 text-uppercase text-secondary">Promedio Institucional Global</td>
                            <td colspan="<?= $numPeriodos ?>" class="text-end text-muted small pe-2">PROMEDIO:</td>
                            <td class="text-center text-primary fs-6"><?= number_format($promedioGlobal, 1) ?></td>
                            <td class="text-center">
                                <span class="badge <?= $globalAprobado ? 'bg-success' : 'bg-danger' ?> px-2 py-1 text-uppercase">
                                    <?= $globalAprobado ? 'Promovido' : 'En Recuperación' ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="text-muted small mb-4" style="font-size: 11px;">
                <strong>Nomenclatura:</strong> 
                <?php for($i=1; $i<=$numPeriodos; $i++): ?>P<?= $i ?> = Periodo <?= $i ?>; <?php endfor; ?>
                NF = Nota Final. Escala de 0.0 a 10.0 (Aprobación mínima: 6.0).
            </p>

            <!-- Firmas -->
            <div class="row pt-5 text-center small text-secondary">
                <div class="col-6">
                    <p class="mb-0">__________________________________________</p>
                    <p class="fw-bold text-dark mb-0">Director(a) del Centro Educativo</p>
                    <span class="text-muted small">Firma y Sello Oficial</span>
                </div>
                <div class="col-6">
                    <p class="mb-0">__________________________________________</p>
                    <p class="fw-bold text-dark mb-0">Docente Encargado / Orientador</p>
                    <span class="text-muted small">Firma de Registro</span>
                </div>
            </div>

            <?php else: ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-file-circle-xmark fa-3x text-muted mb-3 opacity-50"></i>
                <h5 class="text-secondary fw-bold">No existen registros académicos asociados</h5>
                <p class="text-muted small">Verifique que el estudiante cuente con calificaciones asentadas.</p>
            </div>
            <?php endif; ?>

<<<<<<< Updated upstream
=======
        <div class="signatures-row">
            <div class="sig-block">
                <div class="sig-divider"></div>
                <h4>Docente Guía / Orientador</h4>
                <p>Firma y Sello</p>
            </div>
            <div class="sig-block">
                <div class="sig-divider"></div>
                <h4>Dirección Institucional</h4>
                <p>Instituto Noé Canjura</p>
            </div>
>>>>>>> Stashed changes
        </div>
    </div>

</body>
</html>