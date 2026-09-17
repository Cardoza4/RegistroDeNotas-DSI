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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Registro y Control de Calificaciones</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

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
            gap: 1rem;
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
        .btn-notas { background: #10b981; cursor: pointer; border: none; }
        .btn-boleta { background: #0284c7; }
        .btn-action-badge:hover { opacity: 0.9; }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
        }

        /* MODAL DE CALIFICACIONES */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(3px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-card {
            background: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1rem;
        }

        .modal-title h3 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.2rem;
        }

        .modal-title p {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--text-muted);
            cursor: pointer;
        }

        .form-grid-modal {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.25rem;
        }

        .form-group-modal {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            margin-bottom: 1.25rem;
        }

        .form-group-modal label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
        }

        .form-group-modal input, .form-group-modal select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            background: #f8fafc;
            color: var(--text-main);
            font-weight: 600;
        }

        .nota-final-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0;
        }

        .nota-final-box span:first-child {
            font-size: 0.9rem;
            font-weight: 800;
            color: #334155;
            letter-spacing: 0.5px;
        }

        .nota-final-box span:last-child {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 1.5rem;
        }

        .btn-modal-cancel {
            background: #f1f5f9;
            color: #334155;
            border: none;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-modal-save {
            background: #059669;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-modal-save:hover { background: #047857; }
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
            <span style="color:#94a3b8; font-size:0.88rem;"><i class="fa-solid fa-chalkboard-user"></i> Panel de DOCENTE</span>
            <a href="index.php?action=logout" style="color:white; text-decoration:none; font-size:0.85rem; background:rgba(255,255,255,0.1); padding:0.4rem 1rem; border-radius:6px;">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <a href="index.php?action=dashboard" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>
        
        <div class="card">
            <?php if (!empty($msg)): ?>
                <div style="background:#d1fae5; border:1px solid #a7f3d0; color:#065f46; padding:0.75rem 1rem; border-radius:8px; font-size:0.88rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>¡Calificación guardada con éxito en el sistema!</span>
                </div>
            <?php endif; ?>

            <div class="header-top">
                <div class="title-area">
                    <h2>Registro y Control de Calificaciones</h2>
                    <p>Ingresa los insumos oficiales por periodo (Act 1 35%, Act 2 35%, Examen 30%) y genera la boleta.</p>
                </div>
            </div>

            <div class="filters-bar">
                <select>
                    <option>-- Filtrar por Grado --</option>
                    <option>1° Grado</option><option>2° Grado</option><option>3° Grado</option>
                    <option>4° Grado</option><option>5° Grado</option><option>6° Grado</option>
                    <option>7° Grado</option><option>8° Grado</option><option>9° Grado</option>
                    <option>1° Año Bachillerato</option><option>2° Año Bachillerato</option>
                </select>
                <select>
                    <option>-- Sección --</option>
                    <option>Sección A</option><option>Sección B</option><option>Sección C</option>
                </select>
                <button class="btn-filter">Filtrar</button>
                <a href="#" style="color: var(--text-muted); text-decoration: none; font-size: 0.88rem; margin-left: auto;">Limpiar Filtros</a>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>NIE</th>
                            <th>ESTUDIANTE</th>
                            <th>GRADO/SECCIÓN</th>
                            <th>CORREO ELECTRÓNICO</th>
                            <th style="text-align: right;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($listaEstudiantes)): ?>
                            <?php foreach ($listaEstudiantes as $est): ?>
                                <tr>
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
                                    <td><?= htmlspecialchars(($est['grado'] ?? '') . ' - ' . ($est['seccion'] ?? '')) ?></td>
                                    <td><?= htmlspecialchars($est['correo'] ?? 'N/D') ?></td>
                                    <td style="text-align: right;">
                                        <button type="button" class="btn-action-badge btn-notas" onclick="abrirModalNotas('<?= $est['id'] ?>', '<?= htmlspecialchars(($est['nombre'] ?? '') . ' ' . ($est['apellido'] ?? ''), ENT_QUOTES) ?>')">
                                            <i class="fa-solid fa-star"></i> Notas
                                        </button>
                                        <a href="index.php?action=boleta_notas&estudiante_id=<?= $est['id'] ?>" class="btn-action-badge btn-boleta" title="Generar Boleta"><i class="fa-solid fa-file-lines"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay estudiantes registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL DE NOTAS -->
    <div class="modal-overlay" id="modalNotas">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">
                    <h3 id="modalEstudianteNombre">Nombre del Estudiante</h3>
                    <p>Bachillerato (4 Periodos) • Ponderación: 35% - 35% - 30%</p>
                </div>
                <button type="button" class="modal-close" onclick="cerrarModalNotas()"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="index.php?action=guardar_nota" method="POST">
                <input type="hidden" id="estudiante_id" name="estudiante_id">

                <div class="form-grid-modal">
                    <div class="form-group-modal">
                        <label for="materia">Asignatura</label>
                        <select id="materia" name="materia">
                            <option value="Matemática">Matemática</option>
                            <option value="Lenguaje y Literatura">Lenguaje y Literatura</option>
                            <option value="Estudios Sociales y Cívica">Estudios Sociales y Cívica</option>
                            <option value="Ciencia y Tecnología">Ciencia y Tecnología</option>
                            <option value="Idioma Extranjero (Inglés)">Idioma Extranjero (Inglés)</option>
                            <option value="Educación Física">Educación Física</option>
                        </select>
                    </div>
                    <div class="form-group-modal">
                        <label for="periodo">Periodo a Calificar</label>
                        <select id="periodo" name="periodo">
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
                        <input type="number" step="0.1" min="0" max="10" id="act1" name="act1" value="0.0" oninput="calcularNotaFinal()" required>
                    </div>
                    <div class="form-group-modal">
                        <label for="act2">Actividad 2 (35%)</label>
                        <input type="number" step="0.1" min="0" max="10" id="act2" name="act2" value="0.0" oninput="calcularNotaFinal()" required>
                    </div>
                </div>

                <div class="form-group-modal">
                    <label for="examen">Examen (30%)</label>
                    <input type="number" step="0.1" min="0" max="10" id="examen" name="examen" value="0.0" oninput="calcularNotaFinal()" required>
                </div>

                <div class="nota-final-box">
                    <span>NOTA FINAL DEL PERIODO:</span>
                    <span id="labelNotaFinal">0.0</span>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModalNotas()">Cancelar</button>
                    <button type="submit" class="btn-modal-save"><i class="fa-solid fa-floppy-disk"></i> Guardar Calificación</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalNotas(id, nombre) {
            document.getElementById('estudiante_id').value = id;
            document.getElementById('modalEstudianteNombre').textContent = nombre;
            document.getElementById('act1').value = '0.0';
            document.getElementById('act2').value = '0.0';
            document.getElementById('examen').value = '0.0';
            document.getElementById('labelNotaFinal').textContent = '0.0';
            document.getElementById('modalNotas').style.display = 'flex';
        }

        function cerrarModalNotas() {
            document.getElementById('modalNotas').style.display = 'none';
        }

        function calcularNotaFinal() {
            let act1 = parseFloat(document.getElementById('act1').value) || 0;
            let act2 = parseFloat(document.getElementById('act2').value) || 0;
            let examen = parseFloat(document.getElementById('examen').value) || 0;

            let final = (act1 * 0.35) + (act2 * 0.35) + (examen * 0.30);
            document.getElementById('labelNotaFinal').textContent = final.toFixed(1);
        }
    </script>
</body>
</html>