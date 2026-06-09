<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'docente' && $_SESSION['rol'] !== 'administrador' && $_SESSION['rol'] !== 'estudiante')) {
    header("Location: index.php?action=login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boleta Oficial de Notas - INCA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #ffffff; font-family: 'Segoe UI', system-ui, sans-serif; color: #1e293b; }
        .boleta-container { max-width: 850px; margin: 0 auto; padding: 30px; }
        .header-print { text-align: center; margin-bottom: 35px; border-bottom: 3px double #0f172a; padding-bottom: 20px; }
        .logo-inca { height: 75px; margin-bottom: 15px; }
        .table-report th { background-color: #0f172a !important; color: #ffffff !important; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; }
        .table-report td { font-size: 14px; padding: 10px 12px; }
        .info-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 30px; }
        .text-reprobado { color: #b91c1c; font-weight: bold; }
        .text-aprobado { color: #15803d; font-weight: bold; }
        
        @media print {
            .no-print { display: none !important; }
            .boleta-container { padding: 0; margin: 0; width: 100%; max-width: 100%; }
            body { background-color: #ffffff; }
            .table-report th { background-color: #0f172a !important; color: #ffffff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="container no-print my-4 text-center">
        <button onclick="window.print();" class="btn btn-dark fw-bold px-4 shadow-sm">
            <i class="fa-solid fa-print me-2"></i> Imprimir o Guardar como PDF
        </button>
        <a href="index.php?action=notas" class="btn btn-outline-secondary fw-bold px-4 ms-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Volver a Control
        </a>
    </div>

    <div class="boleta-container">
        
        <div class="header-print">
            <img src="/registro_academico/img/logo_inca.png" alt="Logo INCA" class="logo-inca">
            <h3 class="fw-bold m-0" style="color: #0f172a; letter-spacing: 0.5px;">INSTITUTO NOÉ CANJURA (INCA)</h3>
            <p class="text-secondary small fw-semibold uppercase m-0 mt-1" style="letter-spacing: 1px;">CUADRO REGISTRO DE RENDIMIENTO ACADÉMICO</p>
        </div>

        <?php if(!empty($registros)): ?>
            <div class="info-box">
                <div class="row g-3 small">
                    <div class="col-6 col-sm-4">
                        <span class="text-muted d-block">ESTUDIANTE:</span>
                        <strong class="text-dark fs-6"><?php echo $registros[0]['apellido'] . ", " . $registros[0]['nombre']; ?></strong>
                    </div>
                    <div class="col-6 col-sm-4">
                        <span class="text-muted d-block">NIE / CARNÉ:</span>
                        <strong class="text-dark fs-6"><?php echo $registros[0]['nie']; ?></strong>
                    </div>
                    <div class="col-6 col-sm-4">
                        <span class="text-muted d-block">AÑO LECTIVO:</span>
                        <strong class="text-dark fs-6"><?php echo date('Y'); ?></strong>
                    </div>
                    <div class="col-6 col-sm-6">
                        <span class="text-muted d-block">GRADO:</span>
                        <strong class="text-dark">
                            <?php 
                                $grados_map = [
                                    '1G'=>'1° Grado', '2G'=>'2° Grado', '3G'=>'3° Grado', 
                                    '4G'=>'4° Grado', '5G'=>'5° Grado', '6G'=>'6° Grado', 
                                    '7G'=>'7° Grado', '8G'=>'8° Grado', '9G'=>'9° Grado', 
                                    '1B'=>'1° Año de Bachillerato', '2B'=>'2° Año de Bachillerato'
                                ];
                                echo $grados_map[$registros[0]['grado']] ?? $registros[0]['grado']; 
                            ?>
                        </strong>
                    </div>
                    <div class="col-6 col-sm-6">
                        <span class="text-muted d-block">SECCIÓN:</span>
                        <strong class="text-dark">Sección "<?php echo $registros[0]['seccion']; ?>"</strong>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-report align-middle">
                    <thead>
                        <tr class="text-center">
                            <th class="text-start" style="width: 40%;">Asignatura / Materia</th>
                            <th style="width: 15%;">Actividad 1<br>(35%)</th>
                            <th style="width: 15%;">Actividad 2<br>(35%)</th>
                            <th style="width: 15%;">Examen Final<br>(30%)</th>
                            <th style="width: 15%;">Nota Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($registros as $reg): ?>
                        <tr>
                            <td class="fw-bold text-dark"><?php echo $reg['nombre_materia']; ?></td>
                            <td class="text-center text-secondary"><?php echo ($reg['actividad1'] === null || $reg['actividad1'] === '') ? '0.00' : number_format($reg['actividad1'], 2); ?></td>
                            <td class="text-center text-secondary"><?php echo ($reg['actividad2'] === null || $reg['actividad2'] === '') ? '0.00' : number_format($reg['actividad2'], 2); ?></td>
                            <td class="text-center text-secondary"><?php echo ($reg['examen_final'] === null || $reg['examen_final'] === '') ? '0.00' : number_format($reg['examen_final'], 2); ?></td>
                            <td class="text-center">
                                <?php 
                                    $promedio = isset($reg['promedio_final']) ? floatval($reg['promedio_final']) : 0.00;
                                    $clase_nota = ($promedio >= 6.0) ? 'text-aprobado' : 'text-reprobado';
                                ?>
                                <span class="<?php echo $clase_nota; ?>">
                                    <?php echo number_format($promedio, 2); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row g-4 text-center mt-5 pt-4" style="font-size: 13px;">
                <div class="col-6">
                    <div class="mx-auto border-top border-dark border-1 pt-2" style="width: 200px;">
                        <strong>F. Docente Encargado</strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="mx-auto border-top border-dark border-1 pt-2" style="width: 200px;">
                        <strong>Sello Institucional</strong>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="alert alert-warning text-center my-5">
                <h5>Expediente Incompleto</h5>
                <p class="m-0 small">No se encontraron registros de materias o calificaciones asociadas a este número de NIE.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>