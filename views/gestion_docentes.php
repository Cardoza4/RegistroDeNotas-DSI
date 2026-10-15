<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id']) || strtoupper($_SESSION['rol'] ?? '') === 'ESTUDIANTE') {
    header('Location: index.php?action=dashboard');
    exit;
}
$msg = $_GET['msg'] ?? '';
$docentes = $docentes ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Gestión de Personal Docente</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --primary-light: #eff6ff;
            --primary-border: #bfdbfe;
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

        .admin-tabs {
            display: flex;
            gap: 2rem;
            border-bottom: 2px solid var(--border);
            margin-bottom: 2rem;
        }

        .admin-tab {
            text-decoration: none;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-muted);
            padding-bottom: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            transition: color 0.2s;
        }

        .admin-tab.active {
            color: var(--text-main);
        }

        .admin-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--text-main);
            border-radius: 2px 2px 0 0;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .title-area h2 {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .title-area p {
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        .search-box {
            position: relative;
            min-width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.25rem;
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: 0.88rem;
            background: #f8fafc;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.85rem;
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

        .badge-escalafon {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
        }

        .tag-item {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.78rem;
            font-weight: 600;
            margin-right: 4px;
            margin-bottom: 4px;
        }

        .tag-grado {
            display: inline-block;
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.78rem;
            font-weight: 600;
            margin-right: 4px;
            margin-bottom: 4px;
        }

        .btn-action-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 0.85rem;
            transition: opacity 0.2s;
        }
        .btn-edit { background: #eab308; }
        .btn-action-icon:hover { opacity: 0.85; }

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

        /* MODAL REDISEÑADO ELEGANTE */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-card {
            background: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 620px;
            max-height: 94vh;
            overflow-y: auto;
            padding: 2rem 2.25rem;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

        .modal-card::-webkit-scrollbar {
            width: 6px;
        }
        .modal-card::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 4px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 0.85rem;
        }

        .modal-title h3 {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.2rem;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--text-muted);
            cursor: pointer;
            transition: color 0.2s;
        }
        .modal-close:hover {
            color: #dc2626;
        }

        .form-group-modal {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            margin-bottom: 1rem;
        }

        .form-group-modal label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-group-modal input, .form-group-modal select {
            width: 100%;
            padding: 0.7rem 0.95rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.92rem;
            background: #f8fafc;
            color: var(--text-main);
            transition: all 0.2s;
        }

        .form-group-modal input:focus, .form-group-modal select:focus {
            outline: none;
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        /* CUADRÍCULAS TIPO CHIP/TARJETAS */
        .selection-container {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px;
        }

        .chip-grid-grados {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }

        .chip-grid-materias {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
        }

        .custom-chip {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease-in-out;
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            white-space: nowrap;
        }

        .custom-chip:hover {
            border-color: #93c5fd;
            background-color: #f0f9ff;
        }

        .custom-chip input[type="checkbox"] {
            appearance: auto;
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
            margin: 0;
            cursor: pointer;
            flex-shrink: 0;
        }

        .custom-chip.checked-active {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
            font-weight: 700;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.08);
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 1.25rem;
            border-top: 1px solid var(--border);
            padding-top: 1.15rem;
        }

        .btn-modal-cancel {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid var(--border);
            padding: 0.65rem 1.25rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-modal-cancel:hover {
            background: #e2e8f0;
        }

        .btn-modal-save {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }
        .btn-modal-save:hover {
            background: #1d4ed8;
        }

        @media (max-width: 580px) {
            .chip-grid-grados { grid-template-columns: repeat(2, 1fr); }
            .chip-grid-materias { grid-template-columns: 1fr; }
            .modal-card { padding: 1.5rem; }
        }
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
            <span style="color:#94a3b8; font-size:0.88rem;"><i class="fa-solid fa-user-shield"></i> Panel de ADMINISTRADOR</span>
            <a href="index.php?action=logout" style="color:white; text-decoration:none; font-size:0.85rem; background:rgba(255,255,255,0.1); padding:0.4rem 1rem; border-radius:6px;">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <a href="index.php?action=dashboard" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Inicio</a>
        
        <div class="admin-tabs">
            <a href="index.php?action=gestion_notas" class="admin-tab">
                <i class="fa-solid fa-graduation-cap"></i> Control de Alumnos y Notas
            </a>
            <a href="index.php?action=gestion_docentes" class="admin-tab active">
                <i class="fa-solid fa-users-gear"></i> Gestión de Personal Docente
            </a>
        </div>

        <div class="card">
            <?php if (!empty($msg)): ?>
                <div style="background:#d1fae5; border:1px solid #a7f3d0; color:#065f46; padding:0.75rem 1rem; border-radius:8px; font-size:0.88rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>¡Asignaciones del docente actualizadas con éxito!</span>
                </div>
            <?php endif; ?>

            <div class="header-top">
                <div class="title-area">
                    <h2>Gestión de Personal Docente</h2>
                    <p>Administración de escalafón, grados de orientación y asignación de materias y grados autorizados.</p>
                </div>
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Buscar docente por nombre o escalafón..." id="searchInput" onkeyup="filtrarTabla()">
                </div>
            </div>

            <div class="table-responsive">
                <table id="tablaDocentes">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Escalafón</th>
                            <th>Orientación Asignada</th>
                            <th>Grados donde Imparte</th>
                            <th>Materias que Imparte</th>
                            <th style="text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($docentes)): ?>
                            <?php foreach ($docentes as $doc): ?>
                                <tr>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar-circle">
                                                <?php 
                                                $fNom = basename($doc['foto'] ?? '');
                                                if (!empty($fNom) && file_exists(__DIR__ . '/../uploads/' . $fNom)): 
                                                ?>
                                                    <img src="uploads/<?= htmlspecialchars($fNom) ?>" alt="Foto">
                                                <?php else: ?>
                                                    <i class="fa-solid fa-user-tie"></i>
                                                <?php endif; ?>
                                            </div>
                                            <span><strong><?= htmlspecialchars(($doc['nombre'] ?? '') . ' ' . ($doc['apellido'] ?? '')) ?: htmlspecialchars($doc['username'] ?? 'Docente') ?></strong></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-escalafon"><?= htmlspecialchars($doc['escalafon'] ?? 'Sin asignar') ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600; color: var(--primary);"><?= htmlspecialchars($doc['grado_orientacion'] ?? 'Ninguno') ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($doc['grados_imparte'])): ?>
                                            <?php foreach (explode(',', $doc['grados_imparte']) as $gImp): ?>
                                                <span class="tag-grado"><?= htmlspecialchars(trim($gImp)) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.85rem;">-- Solo Orientación --</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($doc['materias_imparte'])): ?>
                                            <?php foreach (explode(',', $doc['materias_imparte']) as $mat): ?>
                                                <span class="tag-item"><?= htmlspecialchars(trim($mat)) ?></span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-size: 0.85rem;">-- Sin materias --</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" class="btn-action-icon btn-edit" title="Editar asignaciones" onclick="abrirModal(
                                            '<?= $doc['id'] ?>',
                                            '<?= htmlspecialchars(($doc['nombre'] ?? '') . ' ' . ($doc['apellido'] ?? ''), ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($doc['escalafon'] ?? '', ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($doc['grado_orientacion'] ?? '', ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($doc['materias_imparte'] ?? '', ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($doc['grados_imparte'] ?? '', ENT_QUOTES) ?>'
                                        )">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">No hay docentes registrados en el sistema.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL DE EDICIÓN CON TARJETAS BALANCEADAS -->
    <div class="modal-overlay" id="modalDocente">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">
                    <h3 id="modalNombreDocente">Editar Docente</h3>
                    <p style="font-size:0.85rem; color:var(--text-muted);">Asigna orientación, materias y todos los grados donde imparte clases.</p>
                </div>
                <button type="button" class="modal-close" onclick="cerrarModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="index.php?action=actualizar_docente_gestion" method="POST">
                <input type="hidden" id="doc_id" name="id">

                <div class="form-group-modal">
                    <label for="doc_escalafon">Número de Escalafón</label>
                    <input type="text" id="doc_escalafon" name="escalafon" placeholder="Ej: ESC-45258">
                </div>

                <div class="form-group-modal">
                    <label for="doc_grado">Grado de Orientación Asignado (Único)</label>
                    <select id="doc_grado" name="grado_orientacion">
                        <option value="Ninguno">Ninguno</option>
                        <option value="1° Grado">1° Grado</option>
                        <option value="2° Grado">2° Grado</option>
                        <option value="3° Grado">3° Grado</option>
                        <option value="4° Grado">4° Grado</option>
                        <option value="5° Grado">5° Grado</option>
                        <option value="6° Grado">6° Grado</option>
                        <option value="7° Grado">7° Grado</option>
                        <option value="8° Grado">8° Grado</option>
                        <option value="9° Grado">9° Grado</option>
                        <option value="1° Año Bachillerato">1° Año Bachillerato</option>
                        <option value="2° Año Bachillerato">2° Año Bachillerato</option>
                    </select>
                </div>

                <!-- GRADOS EN QUE IMPARTE CLASES (CHIPS EN 3 COLUMNAS) -->
                <div class="form-group-modal">
                    <label>
                        <span>Grados donde Imparte Clases</span>
                        <span style="color:var(--primary); font-size:0.75rem;">(Múltiple selección)</span>
                    </label>
                    <div class="selection-container">
                        <div class="chip-grid-grados">
                            <label class="custom-chip"><input type="checkbox" name="grados[]" value="1° Grado" onchange="actualizarEstadoChip(this)"> 1° Grado</label>
                            <label class="custom-chip"><input type="checkbox" name="grados[]" value="2° Grado" onchange="actualizarEstadoChip(this)"> 2° Grado</label>
                            <label class="custom-chip"><input type="checkbox" name="grados[]" value="3° Grado" onchange="actualizarEstadoChip(this)"> 3° Grado</label>
                            <label class="custom-chip"><input type="checkbox" name="grados[]" value="4° Grado" onchange="actualizarEstadoChip(this)"> 4° Grado</label>
                            <label class="custom-chip"><input type="checkbox" name="grados[]" value="5° Grado" onchange="actualizarEstadoChip(this)"> 5° Grado</label>
                            <label class="custom-chip"><input type="checkbox" name="grados[]" value="6° Grado" onchange="actualizarEstadoChip(this)"> 6° Grado</label>
                            <label class="custom-chip"><input type="checkbox" name="grados[]" value="7° Grado" onchange="actualizarEstadoChip(this)"> 7° Grado</label>
                            <label class="custom-chip"><input type="checkbox" name="grados[]" value="8° Grado" onchange="actualizarEstadoChip(this)"> 8° Grado</label>
                            <label class="custom-chip"><input type="checkbox" name="grados[]" value="9° Grado" onchange="actualizarEstadoChip(this)"> 9° Grado</label>
                            <label class="custom-chip"><input type="checkbox" name="grados[]" value="1° Año Bachillerato" onchange="actualizarEstadoChip(this)"> 1° Bachillerato</label>
                            <label class="custom-chip" style="grid-column: span 2;"><input type="checkbox" name="grados[]" value="2° Año Bachillerato" onchange="actualizarEstadoChip(this)"> 2° Bachillerato</label>
                        </div>
                    </div>
                </div>

                <!-- MATERIAS QUE IMPARTE (CHIPS EN 2 COLUMNAS) -->
                <div class="form-group-modal">
                    <label>
                        <span>Materias que Imparte</span>
                        <span style="color:var(--primary); font-size:0.75rem;">(Máximo 2 asignaturas)</span>
                    </label>
                    <div class="selection-container">
                        <div class="chip-grid-materias">
                            <label class="custom-chip"><input type="checkbox" name="materias[]" value="Matemática" onchange="validarMaterias(this); actualizarEstadoChip(this);"> Matemática</label>
                            <label class="custom-chip"><input type="checkbox" name="materias[]" value="Ciencia y Tecnología" onchange="validarMaterias(this); actualizarEstadoChip(this);"> Ciencia y Tecnología</label>
                            <label class="custom-chip"><input type="checkbox" name="materias[]" value="Lenguaje y Literatura" onchange="validarMaterias(this); actualizarEstadoChip(this);"> Lenguaje y Literatura</label>
                            <label class="custom-chip"><input type="checkbox" name="materias[]" value="Estudios Sociales y Cívica" onchange="validarMaterias(this); actualizarEstadoChip(this);"> Estudios Sociales</label>
                            <label class="custom-chip"><input type="checkbox" name="materias[]" value="Idioma Extranjero (Inglés)" onchange="validarMaterias(this); actualizarEstadoChip(this);"> Inglés</label>
                            <label class="custom-chip"><input type="checkbox" name="materias[]" value="Educación Física" onchange="validarMaterias(this); actualizarEstadoChip(this);"> Educación Física</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-modal-save"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function actualizarEstadoChip(input) {
            const chip = input.closest('.custom-chip');
            if (chip) {
                if (input.checked) {
                    chip.classList.add('checked-active');
                } else {
                    chip.classList.remove('checked-active');
                }
            }
        }

        function abrirModal(id, nombre, escalafon, grado, materias, gradosImparte) {
            document.getElementById('doc_id').value = id;
            document.getElementById('modalNombreDocente').textContent = 'Docente: ' + nombre;
            document.getElementById('doc_escalafon').value = escalafon;
            
            let selectGrado = document.getElementById('doc_grado');
            selectGrado.value = grado || 'Ninguno';

            // Resetear y marcar materias
            let checkboxesMat = document.querySelectorAll('input[name="materias[]"]');
            checkboxesMat.forEach(cb => {
                cb.checked = false;
                actualizarEstadoChip(cb);
            });
            if (materias) {
                let listaMaterias = materias.split(',').map(m => m.trim().toLowerCase());
                checkboxesMat.forEach(cb => {
                    if (listaMaterias.includes(cb.value.trim().toLowerCase()) || listaMaterias.some(m => m.includes(cb.value.toLowerCase()) || cb.value.toLowerCase().includes(m))) {
                        cb.checked = true;
                        actualizarEstadoChip(cb);
                    }
                });
            }

            // Resetear y marcar grados que imparte
            let checkboxesGrad = document.querySelectorAll('input[name="grados[]"]');
            checkboxesGrad.forEach(cb => {
                cb.checked = false;
                actualizarEstadoChip(cb);
            });
            if (gradosImparte) {
                let listaGrados = gradosImparte.split(',').map(g => g.trim().toLowerCase());
                checkboxesGrad.forEach(cb => {
                    if (listaGrados.includes(cb.value.trim().toLowerCase())) {
                        cb.checked = true;
                        actualizarEstadoChip(cb);
                    }
                });
            }

            document.getElementById('modalDocente').style.display = 'flex';
        }

        function validarMaterias(element) {
            let seleccionados = document.querySelectorAll('input[name="materias[]"]:checked');
            if (seleccionados.length > 2) {
                alert('Solo puedes seleccionar un máximo de 2 materias.');
                element.checked = false;
                actualizarEstadoChip(element);
            }
        }

        function cerrarModal() {
            document.getElementById('modalDocente').style.display = 'none';
        }

        function filtrarTabla() {
            let input = document.getElementById('searchInput');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('tablaDocentes');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let tdNombre = tr[i].getElementsByTagName('td')[0];
                let tdEscalafon = tr[i].getElementsByTagName('td')[1];
                if (tdNombre || tdEscalafon) {
                    let txtNombre = tdNombre ? tdNombre.textContent || tdNombre.innerText : '';
                    let txtEscalafon = tdEscalafon ? tdEscalafon.textContent || tdEscalafon.innerText : '';
                    if (txtNombre.toLowerCase().indexOf(filter) > -1 || txtEscalafon.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>
</html>