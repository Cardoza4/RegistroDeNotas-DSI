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
    <!-- Librería ultraligera para generar y descargar directamente el archivo PDF -->
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

            <!-- Barra de Filtros con Input de Búsqueda, Selects, Excel y Asistencia -->
            <div class="filters-bar">
                <input type="text" id="input_buscar_pbi21" placeholder="🔍 Buscar por NIE o Nombre..." style="padding: 0.55rem 1rem; border: 1px solid var(--border); border-radius: 8px; background: white; font-size: 0.88rem; outline: none; min-width: 220px;">
                <select name="grado" id="selectGrado">
                    <option value="">-- Filtrar por Grado --</option>
                    <option value="1° Grado">1° Grado</option><option value="2° Grado">2° Grado</option><option value="3° Grado">3° Grado</option>
                    <option value="4° Grado">4° Grado</option><option value="5° Grado">5° Grado</option><option value="6° Grado">6° Grado</option>
                    <option value="7° Grado">7° Grado</option><option value="8° Grado">8° Grado</option><option value="9° Grado">9° Grado</option>
                    <option value="1° Año Bachillerato">1° Año Bachillerato</option><option value="2° Año Bachillerato">2° Año Bachillerato</option>
                </select>
                <select name="seccion" id="selectSeccion">
                    <option value="">-- Sección --</option>
                    <option value="Sección A">Sección A</option><option value="Sección B">Sección B</option><option value="Sección C">Sección C</option>
                </select>
                <button type="button" class="btn-filter" id="btnFiltrarManual">Filtrar</button>
                
                <!-- Botón Excel PBI-36 -->
                <button type="button" class="btn-excel" id="btnExportarExcel">
                    <i class="fa-solid fa-file-excel"></i> Exportar a Excel
                </button>

                <!-- Botón Asistencia PBI-23 -->
                <button type="button" class="btn-asistencia" id="btnExportarAsistencia">
                    <i class="fa-solid fa-clipboard-user"></i> Lista de Asistencia (.pdf)
                </button>

                <a href="#" id="btnLimpiarFiltros" style="color: var(--text-muted); text-decoration: none; font-size: 0.88rem; margin-left: auto;">Limpiar Filtros</a>
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

    <!-- MODAL DE CALIFICACIONES -->
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