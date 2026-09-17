<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}

if (!isset($_SESSION['rol'])) {
    header("Location: index.php?action=login");
    exit();
}

$datosEstudiante = !empty($boleta) ? $boleta[0] : null;

// Determinar total de periodos oficiales según el grado escolar
$esBachillerato = false;
if ($datosEstudiante) {
    $gradoNorm = strtoupper($datosEstudiante['grado']);
    $esBachillerato = (strpos($gradoNorm, 'B') !== false || strpos($gradoNorm, 'BACHILLERATO') !== false);
}
$periodosReglamentarios = $esBachillerato ? 4 : 3;

// Agrupar calificaciones por asignatura
$materiasAgrupadas = [];
if (!empty($boleta)) {
    foreach ($boleta as $fila) {
        $materiasAgrupadas[$fila['materia']][] = $fila;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta de Calificaciones - INCA NOTES</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif; }
        .boleta-card { max-width: 900px; margin: 30px auto; background: white; border-radius: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .badge-aprobado { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-reprobado { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .fila-final { background-color: #f1f5f9; font-weight: bold; border-top: 2px solid #cbd5e1; }
        .resumen-box { background-color: #0f172a; color: white; border-radius: 10px; }
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .boleta-card { box-shadow: none; margin: 0; max-width: 100%; border: none !important; }
        }
    </style>
</head>
<body>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print" style="max-width: 900px; margin: auto;">
            <a href="index.php?action=notas" class="btn btn-outline-secondary btn-sm fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver al Panel
            </a>
            <button onclick="window.print();" class="btn btn-primary btn-sm fw-bold">
                <i class="fa-solid fa-print me-1"></i> Imprimir Boleta Oficial
            </button>
        </div>

        <div class="boleta-card p-4 p-md-5 border">
            <!-- Membrete Institucional -->
            <div class="row align-items-center border-bottom pb-4 mb-4">
                <div class="col-3 col-sm-2 text-center">
                    <img src="img/logo_inca.png" alt="Logo INCA" class="img-fluid" style="max-height: 80px;">
                </div>
                <div class="col-9 col-sm-10">
                    <h4 class="fw-bold mb-1 text-dark">INSTITUTO NACIONAL NOÉ CANJURA</h4>
                    <p class="text-muted mb-0 small">Sistema Integrado de Control Académico — INCA NOTES</p>
                    <span class="badge bg-secondary mt-1">Reporte Oficial de Calificaciones y Rendimiento</span>
                </div>
            </div>

            <?php if ($datosEstudiante): ?>
            <!-- Datos del Estudiante -->
            <div class="row g-3 bg-light p-3 rounded-3 mb-4">
                <div class="col-sm-5">
                    <span class="text-muted small d-block">Estudiante:</span>
                    <strong class="text-dark fs-6"><?= htmlspecialchars($datosEstudiante['nombre'] . ' ' . $datosEstudiante['apellido']); ?></strong>
                </div>
                <div class="col-sm-3">
                    <span class="text-muted small d-block">NIE / Carné:</span>
                    <strong class="text-primary fs-6"><?= htmlspecialchars($datosEstudiante['nie']); ?></strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted small d-block">Grado y Sección:</span>
                    <strong class="text-dark fs-6"><?= htmlspecialchars($datosEstudiante['grado'] . ' - Sec. ' . $datosEstudiante['seccion']); ?></strong>
                </div>
            </div>

            <!-- Tabla de Calificaciones y Cálculo Final -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-center small text-secondary">
                        <tr>
                            <th class="text-start">Asignatura</th>
                            <th style="width: 90px;">Periodo</th>
                            <th style="width: 110px;">Act. 1 (35%)</th>
                            <th style="width: 110px;">Act. 2 (35%)</th>
                            <th style="width: 110px;">Examen (30%)</th>
                            <th style="width: 110px;">Promedio</th>
                            <th style="width: 120px;">Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalPromediosMaterias = 0;
                        $totalMateriasEvaluadas = count($materiasAgrupadas);

                        foreach ($materiasAgrupadas as $nombreMateria => $notasPeriodo): 
                            $sumaPeriodos = 0;
                            $periodosAsentados = count($notasPeriodo);

                            foreach ($notasPeriodo as $nota): 
                                $prom = floatval($nota['promedio']);
                                $sumaPeriodos += $prom;
                                $aprobadoPeriodo = ($prom >= 6.0);
                        ?>
                            <tr class="text-center">
                                <td class="text-start fw-semibold text-dark"><?= htmlspecialchars($nombreMateria); ?></td>
                                <td><span class="badge bg-light text-dark border">P-<?= htmlspecialchars($nota['periodo']); ?></span></td>
                                <td><?= number_format($nota['act1'], 2); ?></td>
                                <td><?= number_format($nota['act2'], 2); ?></td>
                                <td><?= number_format($nota['examen'], 2); ?></td>
                                <td class="fw-bold text-dark"><?= number_format($prom, 2); ?></td>
                                <td>
                                    <span class="badge <?= $aprobadoPeriodo ? 'badge-aprobado' : 'badge-reprobado'; ?> px-2 py-1">
                                        <?= $aprobadoPeriodo ? 'Aprobado' : 'Reprobado'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; 

                            // Cálculo del promedio final de la asignatura
                            $promedioFinalMateria = $periodosAsentados > 0 ? ($sumaPeriodos / $periodosAsentados) : 0;
                            $materiaAprobada = ($promedioFinalMateria >= 6.0);
                            $totalPromediosMaterias += $promedioFinalMateria;
                        ?>
                            <!-- Fila de Calificación Final por Materia -->
                            <tr class="fila-final text-center">
                                <td colspan="5" class="text-end text-uppercase text-secondary pe-3">
                                    <i class="fa-solid fa-award me-1 text-primary"></i> Calificación Final (<?= htmlspecialchars($nombreMateria); ?>):
                                </td>
                                <td class="fw-bold text-primary fs-6"><?= number_format($promedioFinalMateria, 2); ?></td>
                                <td>
                                    <span class="badge <?= $materiaAprobada ? 'badge-aprobado' : 'badge-reprobado'; ?> px-2 py-1 fs-7">
                                        <?= $materiaAprobada ? 'APROBADA' : 'REPROBADA'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Resumen Institucional Global -->
            <?php 
            $promedioGeneralGlobal = $totalMateriasEvaluadas > 0 ? ($totalPromediosMaterias / $totalMateriasEvaluadas) : 0;
            $estadoGlobal = ($promedioGeneralGlobal >= 6.0);
            ?>
            <div class="row g-3 my-3">
                <div class="col-md-12">
                    <div class="p-3 resumen-box d-flex flex-wrap justify-content-between align-items-center">
                        <div>
                            <span class="text-uppercase small tracking-wider text-slate-300 d-block">Rendimiento Institucional Global</span>
                            <h5 class="fw-bold mb-0 text-white">Promedio Global del Ciclo: <?= number_format($promedioGeneralGlobal, 2); ?> / 10.0</h5>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <span class="badge <?= $estadoGlobal ? 'bg-success' : 'bg-danger'; ?> fs-6 px-3 py-2 text-uppercase">
                                Condición: <?= $estadoGlobal ? 'Promovido / Aprobado' : 'En Recuperación'; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Firmas Oficiales -->
            <div class="row mt-5 pt-4 text-center text-muted small border-top">
                <div class="col-6">
                    <p class="mb-0">_______________________________</p>
                    <p class="fw-bold">Firma del Docente Encargado</p>
                </div>
                <div class="col-6">
                    <p class="mb-0">_______________________________</p>
                    <p class="fw-bold">Sello de Dirección</p>
                </div>
            </div>

            <?php else: ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3 opacity-50"></i>
                <h5 class="text-secondary fw-bold">No hay calificaciones registradas para este estudiante</h5>
                <p class="text-muted small">Selecciona el botón de "Notas" en el panel anterior para asentar las evaluaciones.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>