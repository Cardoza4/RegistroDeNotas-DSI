<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== 'docente' && $_SESSION['rol'] !== 'administrador')) {
    header("Location: index.php?action=login");
    exit();
}
$esAdmin = ($_SESSION['rol'] === 'administrador');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA Notes - Control del Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif; }
        .navbar-dark { background-color: #0f172a; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 1px 3px rgb(0 0 0 / 0.1); }
        .table th { background-color: #f1f5f9; color: #475569; font-weight: 600; font-size: 13px; }
        .nav-tabs .nav-link { color: #475569; font-weight: 600; border: none; padding: 12px 20px; }
        .nav-tabs .nav-link.active { color: #0f172a; border-bottom: 3px solid #0f172a; background: none; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php?action=dashboard">
                <img src="/registro_academico/img/logo_inca.png" alt="Logo" height="30" class="me-2"> INCA NOTES
            </a>
            <div class="ms-auto text-white small me-3">
                <i class="fa-solid fa-user-shield me-1"></i> <?php echo $esAdmin ? 'Panel de Administrador' : 'Panel del Docente'; ?>
            </div>
            <a href="index.php?action=logout" class="btn btn-sm btn-outline-light fw-bold">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container-fluid px-4 mb-5">
        
        <?php if ($esAdmin): ?>
        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="alumnos-tab" data-bs-toggle="tab" data-bs-target="#panelAlumnos" type="button" role="tab" aria-controls="panelAlumnos" aria-selected="true">
                    <i class="fa-solid fa-graduation-cap me-2"></i>Control de Alumnos y Notas
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="docentes-tab" data-bs-toggle="tab" data-bs-target="#panelDocentes" type="button" role="tab" aria-controls="panelDocentes" aria-selected="false">
                    <i class="fa-solid fa-user-tie me-2"></i>Gestión de Personal Docente
                </button>
            </li>
        </ul>
        <?php endif; ?>

        <div class="tab-content" id="adminTabsContent">
            
            <div class="tab-pane fade show active" id="panelAlumnos" role="tabpanel" aria-labelledby="alumnos-tab">
                <div class="card card-custom bg-white p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <a href="index.php?action=dashboard" class="btn btn-outline-secondary btn-sm fw-bold shadow-sm">
                                <i class="fa-solid fa-arrow-left me-1"></i> Volver
                            </a>
                            <div>
                                <h4 class="fw-bold text-dark mb-1">Control de Matrícula y Notas</h4>
                                <p class="text-muted small mb-0">Visualiza, inscribe, modifica estudiantes y gestiona sus calificaciones oficiales.</p>
                            </div>
                        </div>
                        <button class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modalInscribir">
                            <i class="fa-solid fa-user-plus me-1"></i> Inscribir Nuevo Estudiante
                        </button>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-4">
                        <form method="GET" action="index.php" class="row g-2 align-items-center">
                            <input type="hidden" name="action" value="notas">
                            <div class="col-md-4">
                                <select name="grado" class="form-select form-select-sm">
                                    <option value="">-- Filtrar por Grado --</option>
                                    <option value="1G" <?php echo (isset($_GET['grado']) && $_GET['grado']=='1G')?'selected':''; ?>>1° Grado</option>
                                    <option value="2G" <?php echo (isset($_GET['grado']) && $_GET['grado']=='2G')?'selected':''; ?>>2° Grado</option>
                                    <option value="3G" <?php echo (isset($_GET['grado']) && $_GET['grado']=='3G')?'selected':''; ?>>3° Grado</option>
                                    <option value="4G" <?php echo (isset($_GET['grado']) && $_GET['grado']=='4G')?'selected':''; ?>>4° Grado</option>
                                    <option value="5G" <?php echo (isset($_GET['grado']) && $_GET['grado']=='5G')?'selected':''; ?>>5° Grado</option>
                                    <option value="6G" <?php echo (isset($_GET['grado']) && $_GET['grado']=='6G')?'selected':''; ?>>6° Grado</option>
                                    <option value="7G" <?php echo (isset($_GET['grado']) && $_GET['grado']=='7G')?'selected':''; ?>>7° Grado</option>
                                    <option value="8G" <?php echo (isset($_GET['grado']) && $_GET['grado']=='8G')?'selected':''; ?>>8° Grado</option>
                                    <option value="9G" <?php echo (isset($_GET['grado']) && $_GET['grado']=='9G')?'selected':''; ?>>9° Grado</option>
                                    <option value="1B" <?php echo (isset($_GET['grado']) && $_GET['grado']=='1B')?'selected':''; ?>>1° Bachillerato</option>
                                    <option value="2B" <?php echo (isset($_GET['grado']) && $_GET['grado']=='2B')?'selected':''; ?>>2° Bachillerato</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="seccion" class="form-select form-select-sm">
                                    <option value="">-- Sección --</option>
                                    <option value="A" <?php echo (isset($_GET['seccion']) && $_GET['seccion']=='A')?'selected':''; ?>>Sección A</option>
                                    <option value="B" <?php echo (isset($_GET['seccion']) && $_GET['seccion']=='B')?'selected':''; ?>>Sección B</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-secondary w-100 fw-bold">Filtrar</button>
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="index.php?action=notas" class="btn btn-sm btn-link text-decoration-none text-muted">Limpiar Filtros</a>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>NIE</th>
                                    <th>Estudiante</th>
                                    <th>Grado/Sección</th>
                                    <th>Correo Electrónico</th>
                                    <th class="text-center" style="width: 350px;">Acciones de Control</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($estudiantes)): foreach($estudiantes as $est): ?>
                                <tr>
                                    <td class="fw-bold text-secondary"><?php echo $est['nie']; ?></td>
                                    <td class="fw-bold text-dark"><?php echo $est['apellido'] . ", " . $est['nombre']; ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo $est['grado'] . " - Sec. " . $est['seccion']; ?></span></td>
                                    <td class="text-muted small"><?php echo $est['correo'] ?: '---'; ?></td>
                                    <td class="text-center">
                                        <div class="btn-group gap-1">
                                            <button class="btn btn-sm btn-success fw-bold btn-notas" data-id="<?php echo $est['id']; ?>" data-nombre="<?php echo $est['nombre'].' '.$est['apellido']; ?>" data-bs-toggle="modal" data-bs-target="#modalNotas">
                                                <i class="fa-solid fa-star me-1"></i> Notas
                                            </button>
                                            <button class="btn btn-sm btn-warning fw-bold text-white btn-editar" data-id="<?php echo $est['id']; ?>" data-nie="<?php echo $est['nie']; ?>" data-nombre="<?php echo $est['nombre']; ?>" data-apellido="<?php echo $est['apellido']; ?>" data-correo="<?php echo $est['correo']; ?>" data-grado="<?php echo $est['grado']; ?>" data-seccion="<?php echo $est['seccion']; ?>" data-bs-toggle="modal" data-bs-target="#modalEditar">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <a href="index.php?action=imprimir_boleta&nie=<?php echo $est['nie']; ?>" target="_blank" class="btn btn-sm btn-info text-white fw-bold">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                            <a href="index.php?action=borrar_estudiante&id=<?php echo $est['id']; ?>&nie=<?php echo $est['nie']; ?>" onclick="return confirm('¿Estás seguro de dar de baja definitiva a este alumno?');" class="btn btn-sm btn-danger">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No se encontraron estudiantes registrados.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if ($esAdmin): ?>
            <div class="tab-pane fade" id="panelDocentes" role="tabpanel" aria-labelledby="docentes-tab">
                <div class="card card-custom bg-white p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <a href="index.php?action=dashboard" class="btn btn-outline-secondary btn-sm fw-bold shadow-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i> Volver
                        </a>
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Gestión de Personal Docente</h4>
                            <p class="text-muted small mb-0">Panel global de administración del Instituto Noé Canjura. Alta y baja de cuentas de profesores.</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID / Código de Usuario</th>
                                    <th>Nombre Completo del Docente</th>
                                    <th>Rol Asignado</th>
                                    <th class="text-center" style="width: 200px;">Acciones de Control</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($docentes)): foreach($docentes as $doc): ?>
                                <tr>
                                    <td class="fw-bold text-secondary"><i class="fa-solid fa-id-card me-2"></i><?php echo $doc['username']; ?></td>
                                    <td class="fw-bold text-dark"><?php echo $doc['apellido'] . ", " . $doc['nombre']; ?></td>
                                    <td><span class="badge bg-primary text-white">DOCENTE INSTITUCIONAL</span></td>
                                    <td class="text-center">
                                        <a href="index.php?action=borrar_estudiante&docente_id=<?php echo $doc['id']; ?>" onclick="return confirm('¿Estás seguro de dar de baja definitiva a este DOCENTE del sistema? No podrá volver a ingresar.');" class="btn btn-sm btn-danger fw-bold">
                                            <i class="fa-solid fa-user-minus me-1"></i> Eliminar Docente
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No hay docentes registrados en la institución.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="modal fade" id="modalInscribir" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php?action=registrar_estudiante" method="POST">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Matrícula Escolar</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-secondary">NIE (Carné Único)</label>
                            <input type="text" name="nie" class="form-control" placeholder="Ej: 0225558" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Nombres</label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Apellidos</label>
                            <input type="text" name="apellido" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-secondary">Correo Institucional</label>
                            <input type="email" name="correo" class="form-control" placeholder="opcional@institucion.edu.sv">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Grado Escolar</label>
                            <select name="grado" class="form-select" required>
                                <option value="1G">1° Grado</option><option value="2G">2° Grado</option>
                                <option value="3G">3° Grado</option><option value="4G">4° Grado</option>
                                <option value="5G">5° Grado</option><option value="6G">6° Grado</option>
                                <option value="7G">7° Grado</option><option value="8G">8° Grado</option>
                                <option value="9G" selected>9° Grado</option>
                                <option value="1B">1° Bachillerato</option><option value="2B">2° Bachillerato</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Sección asignada</label>
                            <select name="seccion" class="form-select" required>
                                <option value="A" selected>Sección A</option>
                                <option value="B">Sección B</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary fw-bold" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-primary fw-bold">Guardar Matrícula</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditar" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php?action=modificar_estudiante" method="POST">
                    <div class="modal-header bg-warning text-white">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-pen me-2"></i>Modificar Ficha de Alumno</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <input type="hidden" name="id_estudiante" id="edit_id">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-secondary">NIE (No modificable)</label>
                            <input type="text" name="nie" id="edit_nie" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Nombres</label>
                            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Apellidos</label>
                            <input type="text" name="apellido" id="edit_apellido" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-secondary">Correo Institucional</label>
                            <input type="email" name="correo" id="edit_correo" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Grado Escolar</label>
                            <select name="grado" id="edit_grado" class="form-select" required>
                                <option value="1G">1° Grado</option><option value="2G">2° Grado</option>
                                <option value="3G">3° Grado</option><option value="4G">4° Grado</option>
                                <option value="5G">5° Grado</option><option value="6G">6° Grado</option>
                                <option value="7G">7° Grado</option><option value="8G">8° Grado</option>
                                <option value="9G">9° Grado</option>
                                <option value="1B">1° Bachillerato</option><option value="2B">2° Bachillerato</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary">Sección</label>
                            <select name="seccion" id="edit_seccion" class="form-select" required>
                                <option value="A">Sección A</option>
                                <option value="B">Sección B</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary fw-bold" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-sm btn-warning text-white fw-bold">Actualizar Datos</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalNotas" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="index.php?action=guardar_notas" method="POST">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-calculator me-2"></i>Evaluar Rendimiento</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <input type="hidden" name="estudiante_id" id="nota_estudiante_id">
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-muted">Estudiante Seleccionado:</label>
                            <input type="text" id="nota_nombre_estudiante" class="form-control bg-light fw-bold" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold text-secondary">Asignatura</label>
                            <select name="materia_id" class="form-select" required>
                                <?php if(isset($materias)): foreach($materias as $mat): ?>
                                    <option value="<?php echo $mat['id']; ?>"><?php echo $mat['nombre_materia']; ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Act 1 (35%)</label>
                            <input type="number" name="act1" class="form-control" step="0.01" min="0" max="10" value="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Act 2 (35%)</label>
                            <input type="number" name="act2" class="form-control" step="0.01" min="0" max="10" value="0.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">Examen (30%)</label>
                            <input type="number" name="examen" class="form-control" step="0.01" min="0" max="10" value="0.00" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary fw-bold" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-sm btn-success fw-bold">Procesar Calificaciones</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.btn-notas').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('nota_estudiante_id').value = this.getAttribute('data-id');
                document.getElementById('nota_nombre_estudiante').value = this.getAttribute('data-nombre');
            });
        });

        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_id').value = this.getAttribute('data-id');
                document.getElementById('edit_nie').value = this.getAttribute('data-nie');
                document.getElementById('edit_nombre').value = this.getAttribute('data-nombre');
                document.getElementById('edit_apellido').value = this.getAttribute('data-apellido');
                document.getElementById('edit_correo').value = this.getAttribute('data-correo');
                document.getElementById('edit_grado').value = this.getAttribute('data-grado');
                document.getElementById('edit_seccion').value = this.getAttribute('data-seccion');
            });
        });

        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam === 'docentes') {
            const docenteTabTrigger = document.querySelector('#docentes-tab');
            if (docenteTabTrigger) {
                const tab = new bootstrap.Tab(docenteTabTrigger);
                tab.show();
            }
        }
    </script>
</body>
</html>