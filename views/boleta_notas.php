<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['rol'])) {
    header("Location: index.php?action=login");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA Notes - Mi Historial Académico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif; }
        .navbar-dark { background-color: #0f172a; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 1px 3px rgb(0 0 0 / 0.1); }
        .table th { background-color: #f1f5f9; color: #475569; font-weight: 600; font-size: 13px; }
        .text-aprobado { color: #16a34a; font-weight: bold; }
        .text-reprobado { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php?action=dashboard">
                <img src="/registro_academico/img/logo_inca.png" alt="Logo" height="30" class="me-2"> INCA NOTES
            </a>
            <div class="ms-auto text-white small me-3">
                <i class="fa-solid fa-user-graduation me-1"></i> Perfil de Estudiante
            </div>
            <a href="index.php?action=logout" class="btn btn-sm btn-outline-light fw-bold">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card card-custom bg-white p-4">
                    
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Mi Boleta de Calificaciones</h4>
                            <p class="text-muted small mb-0">Consulta tus promedios oficiales registrados por tus docentes del Instituto Noé Canjura.</p>
                        </div>
                        <div>
                            <a href="index.php?action=dashboard" class="btn btn-sm btn-outline-secondary fw-bold px-3">
                                <i class="fa-solid fa-arrow-left me-1"></i> Volver al Panel
                            </a>
                            <?php if(!empty($registros)): ?>
                                <a href="index.php?action=imprimir_boleta&nie=<?php echo $registros[0]['nie']; ?>" target="_blank" class="btn btn-sm btn-dark fw-bold px-3 ms-2">
                                    <i class="fa-solid fa-print me-1"></i> Imprimir
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if(!empty($registros)): ?>
                        <div class="bg-light p-3 rounded-3 mb-4 small border">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <span class="text-muted d-block">Estudiante:</span>
                                    <strong class="text-dark fs-6"><?php echo $registros[0]['apellido'] . ", " . $registros[0]['nombre']; ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block">NIE / Carné:</span>
                                    <strong class="text-dark fs-6"><?php echo $registros[0]['nie']; ?></strong>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted d-block">Grado y Sección:</span>
                                    <span class="badge bg-dark text-white mt-1">
                                        <?php echo $registros[0]['grado'] . " - Secc. " . $registros[0]['seccion']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle border">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-start">Asignatura</th>
                                        <th style="width: 15%;">Actividad 1 (35%)</th>
                                        <th style="width: 15%;">Actividad 2 (35%)</th>
                                        <th style="width: 15%;">Examen Final (30%)</th>
                                        <th style="width: 15%;">Promedio Final</th>
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
                                                $clase = ($promedio >= 6.0) ? 'text-aprobado' : 'text-reprobated';
                                                if ($clase === 'text-reprobated') { $clase = 'text-reprobado'; }
                                            ?>
                                            <span class="<?php echo $clase; ?>">
                                                <?php echo number_format($promedio, 2); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center py-5 m-0 shadow-sm rounded-3">
                            <i class="fa-solid fa-circle-info fa-2x mb-3 text-secondary"></i>
                            <h5>Sin Calificaciones Registradas</h5>
                            <p class="text-muted small m-0">Aún no se han asentado notas en el sistema para tu número de NIE en este ciclo escolar.</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>