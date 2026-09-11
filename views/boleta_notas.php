<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
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
                <div class="col-10 text-center pe-md-5">
                    <h6 class="fw-bold mb-0 text-uppercase tracking-wide">Instituto Nacional Noé Canjura</h6>
                    <p class="text-muted mb-0 small text-uppercase">Departamento de Registro y Control Académico</p>
                    <h5 class="fw-bold text-dark mt-1 mb-0">BOLETA DE CALIFICACIONES</h5>
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

        </div>
    </div>

</body>
</html>