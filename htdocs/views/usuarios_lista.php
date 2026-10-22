<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php?action=login');
    exit;
}
$lista = $usuarios ?? $perfiles ?? $estudiantes ?? [];
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA NOTES - Directorio de Perfiles</title>
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

        .btn-register-top {
            background: var(--primary);
            color: white;
            padding: 0.65rem 1.25rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background 0.2s;
        }

        .btn-register-top:hover {
            background: #1d4ed8;
        }

        .filters-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.75rem;
            flex-wrap: wrap;
            gap: 1rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1.25rem;
        }

        .filter-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: #f1f5f9;
            color: var(--text-muted);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .filter-btn.active, .filter-btn:hover {
            background: var(--primary);
            color: white;
        }

        .search-box {
            position: relative;
            min-width: 280px;
        }

        .search-box input {
            width: 100%;
            padding: 0.55rem 1rem 0.55rem 2.25rem;
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

        .badge-rol {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 6px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .rol-docente { background: #fef3c7; color: #b45309; }
        .rol-estudiante { background: #e0f2fe; color: #0369a1; }
        .rol-admin { background: #d1fae5; color: #065f46; }

        .tag-doc {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            color: #334155;
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
            margin-right: 4px;
        }
        .btn-edit { background: #eab308; }
        .btn-delete { background: #ef4444; }
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

        /* Modal Flotante Ampliado */
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
            max-width: 660px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 2.25rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 1rem;
        }

        .modal-title h3 {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 0.2rem;
        }

        .modal-title p {
            font-size: 0.88rem;
            color: var(--text-muted);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--text-muted);
            cursor: pointer;
        }

        .section-separator-modal {
            grid-column: span 2;
            font-size: 0.82rem;
            font-weight: 800;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px dashed var(--border);
            padding-bottom: 0.35rem;
            margin-top: 0.5rem;
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-grid-modal {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 0.85rem;
        }

        .form-group-modal {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            margin-bottom: 0.85rem;
        }
        .form-group-modal.full {
            grid-column: span 2;
        }

        .form-group-modal label {
            font-size: 0.82rem;
            font-weight: 700;
            color: #334155;
        }

        .form-group-modal input, 
        .form-group-modal select,
        .form-group-modal textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.92rem;
            background: #f8fafc;
            color: var(--text-main);
        }

        .form-group-modal textarea {
            resize: vertical;
            min-height: 65px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 1.5rem;
            border-top: 1px solid var(--border);
            padding-top: 1.25rem;
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
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
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
        <a href="index.php?action=logout" style="color:white; text-decoration:none; font-size:0.85rem; background:rgba(255,255,255,0.1); padding:0.4rem 1rem; border-radius:6px;">Cerrar Sesión</a>
    </nav>

    <div class="container">
        <a href="index.php?action=dashboard" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Volver al Panel Principal</a>
        
        <div class="card">
            <?php if (!empty($msg)): ?>
                <div style="background:#d1fae5; border:1px solid #a7f3d0; color:#065f46; padding:0.75rem 1rem; border-radius:8px; font-size:0.88rem; font-weight:600; margin-bottom:1.5rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>¡Perfil institucional actualizado con éxito!</span>
                </div>
            <?php endif; ?>

            <div class="header-top">
                <div class="title-area">
                    <h2>Directorio de Perfiles Registrados</h2>
                    <p>Visualización, asignaciones institucionales y administración de accesos.</p>
                </div>
                <a href="index.php?action=registro" class="btn-register-top">
                    <i class="fa-solid fa-user-plus"></i> Registrar Nueva Cuenta
                </a>
            </div>

            <div class="filters-bar">
                <div class="filter-buttons">
                    <button class="filter-btn active" onclick="filtrarPorRol('todos', this)"><i class="fa-solid fa-users"></i> Todos</button>
                    <button class="filter-btn" onclick="filtrarPorRol('estudiante', this)"><i class="fa-solid fa-chalkboard-user"></i> Estudiantes</button>
                    <button class="filter-btn" onclick="filtrarPorRol('docente', this)"><i class="fa-solid fa-user-tie"></i> Docentes</button>
                    <button class="filter-btn" onclick="filtrarPorRol('administrador', this)"><i class="fa-solid fa-shield-halved"></i> Administradores</button>
                </div>
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Buscar por nombre o correo..." id="searchInput" onkeyup="filtrarTabla()">
                </div>
            </div>

            <div class="table-responsive">
                <table id="tablaPerfiles">
                    <thead>
                        <tr>
                            <th>Usuario / Nombre</th>
                            <th>Rol</th>
                            <th>Correo Institucional</th>
                            <th>Documentos / Asignación</th>
                            <th>Teléfono</th>
                            <th>Género</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($lista)): ?>
                            <?php foreach ($lista as $u): 
                                $rol = strtoupper($u['rol'] ?? 'ESTUDIANTE');
                                $claseRol = 'rol-estudiante';
                                if ($rol === 'DOCENTE') $claseRol = 'rol-docente';
                                if ($rol === 'ADMIN' || $rol === 'ADMINISTRADOR') $claseRol = 'rol-admin';

                                // Extraer identificador visible (NIE para estudiantes o DUI/Escalafón)
                                $identificador = !empty($u['nie']) ? $u['nie'] : (!empty($u['username']) ? $u['username'] : '');
                            ?>
                                <tr data-rol="<?= strtolower($rol) ?>">
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar-circle">
                                                <?php 
                                                $fNom = basename($u['foto'] ?? '');
                                                if (!empty($fNom) && file_exists(__DIR__ . '/../uploads/' . $fNom)): 
                                                ?>
                                                    <img src="uploads/<?= htmlspecialchars($fNom) ?>" alt="Foto">
                                                <?php else: ?>
                                                    <i class="fa-solid fa-user"></i>
                                                <?php endif; ?>
                                            </div>
                                            <span><strong><?= htmlspecialchars(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '')) ?: htmlspecialchars($u['username'] ?? 'Usuario') ?></strong></span>
                                        </div>
                                    </td>
                                    <td><span class="badge-rol <?= $claseRol ?>"><?= htmlspecialchars($rol) ?></span></td>
                                    <td><?= htmlspecialchars($u['correo'] ?? $u['username'] ?? 'N/D') ?></td>
                                    <td>
                                        <?php if (!empty($u['nie'])): ?>
                                            <span class="tag-doc">NIE: <?= htmlspecialchars($u['nie']) ?></span>
                                        <?php elseif (!empty($u['escalafon'])): ?>
                                            <span class="tag-doc">ESC: <?= htmlspecialchars($u['escalafon']) ?></span>
                                        <?php elseif (!empty($u['dui'])): ?>
                                            <span class="tag-doc">DUI: <?= htmlspecialchars($u['dui']) ?></span>
                                        <?php else: ?>
                                            <span style="color:var(--text-muted); font-size:0.82rem;">-- Sin asignar --</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($u['telefono'] ?? '--') ?></td>
                                    <td><?= htmlspecialchars($u['genero'] ?? 'Masculino') ?></td>
                                    <td>
                                        <button type="button" class="btn-action-icon btn-edit" title="Editar perfil completo"
                                            data-id="<?= $u['id'] ?>"
                                            data-nombre="<?= htmlspecialchars($u['nombre'] ?? '', ENT_QUOTES) ?>"
                                            data-apellido="<?= htmlspecialchars($u['apellido'] ?? '', ENT_QUOTES) ?>"
                                            data-correo="<?= htmlspecialchars($u['correo'] ?? $u['username'] ?? '', ENT_QUOTES) ?>"
                                            data-rol="<?= htmlspecialchars($rol, ENT_QUOTES) ?>"
                                            data-telefono="<?= htmlspecialchars($u['telefono'] ?? '', ENT_QUOTES) ?>"
                                            data-genero="<?= htmlspecialchars($u['genero'] ?? 'Masculino', ENT_QUOTES) ?>"
                                            data-nie="<?= htmlspecialchars($u['nie'] ?? '', ENT_QUOTES) ?>"
                                            data-grado="<?= htmlspecialchars($u['grado'] ?? '1° Año Bachillerato', ENT_QUOTES) ?>"
                                            data-seccion="<?= htmlspecialchars($u['seccion'] ?? 'Sección A', ENT_QUOTES) ?>"
                                            data-encargado="<?= htmlspecialchars($u['encargado'] ?? '', ENT_QUOTES) ?>"
                                            data-dui-encargado="<?= htmlspecialchars($u['dui_encargado'] ?? '', ENT_QUOTES) ?>"
                                            data-direccion="<?= htmlspecialchars($u['direccion'] ?? '', ENT_QUOTES) ?>"
                                            data-dui="<?= htmlspecialchars($u['dui'] ?? '', ENT_QUOTES) ?>"
                                            data-escalafon="<?= htmlspecialchars($u['escalafon'] ?? '', ENT_QUOTES) ?>"
                                            data-cargo="<?= htmlspecialchars($u['cargo'] ?? 'Administrador', ENT_QUOTES) ?>"
                                            data-materias="<?= htmlspecialchars($u['materias_imparte'] ?? '', ENT_QUOTES) ?>"
                                            data-orientacion="<?= htmlspecialchars($u['grado_orientacion'] ?? 'Ninguno', ENT_QUOTES) ?>"
                                            onclick="abrirModalEditarConDatos(this)">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="index.php?action=eliminar_usuario&id=<?= $u['id'] ?>" class="btn-action-icon btn-delete" title="Eliminar registro" onclick="return confirm('¿Estás seguro de eliminar permanentemente este perfil y su expediente?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">No se encontraron registros en el directorio.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL DE EDICIÓN CON TODOS LOS CAMPOS -->
    <div class="modal-overlay" id="modalEditar">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">
                    <h3>Editar Perfil de Usuario</h3>
                    <p id="modalSubCorreo">correo@inca.edu.sv</p>
                </div>
                <button type="button" class="modal-close" onclick="cerrarModalEditar()"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <form action="index.php?action=actualizar_usuario_directorio" method="POST">
                <input type="hidden" id="edit_id" name="id">
                <input type="hidden" id="edit_rol_hidden" name="rol">

                <!-- 1. DATOS GENERALES -->
                <div class="section-separator-modal">
                    <i class="fa-solid fa-user"></i> Datos Personales y de Contacto
                </div>

                <div class="form-grid-modal">
                    <div class="form-group-modal">
                        <label for="edit_nombre">Nombres</label>
                        <input type="text" id="edit_nombre" name="nombre" required>
                    </div>
                    <div class="form-group-modal">
                        <label for="edit_apellido">Apellidos</label>
                        <input type="text" id="edit_apellido" name="apellido" required>
                    </div>
                </div>

                <div class="form-grid-modal">
                    <div class="form-group-modal">
                        <label for="edit_correo">Correo Institucional</label>
                        <input type="text" id="edit_correo" name="correo" required>
                    </div>
                    <div class="form-group-modal">
                        <label for="edit_telefono">Teléfono de Contacto</label>
                        <input type="text" id="edit_telefono" name="telefono" placeholder="7123-4567" maxlength="9">
                    </div>
                </div>

                <div class="form-grid-modal">
                    <div class="form-group-modal">
                        <label for="edit_genero">Género</label>
                        <select id="edit_genero" name="genero">
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                        </select>
                    </div>
                    <div class="form-group-modal">
                        <label for="edit_rol_display">Rol Institucional</label>
                        <input type="text" id="edit_rol_display" readonly style="background:#f1f5f9; color:#475569; font-weight:700;">
                    </div>
                </div>

                <!-- 2. CAMPOS ESPECÍFICOS DE ESTUDIANTE -->
                <div id="bloqueCamposEstudiante" style="display: none;">
                    <div class="section-separator-modal">
                        <i class="fa-solid fa-graduation-cap"></i> Expediente de Matrícula (Estudiante)
                    </div>

                    <div class="form-grid-modal">
                        <div class="form-group-modal">
                            <label for="edit_nie">NIE / Carné</label>
                            <input type="text" id="edit_nie" name="nie">
                        </div>
                        <div class="form-group-modal">
                            <label for="edit_grado">Grado Escolar</label>
                            <select id="edit_grado" name="grado">
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
                    </div>

                    <div class="form-grid-modal">
                        <div class="form-group-modal">
                            <label for="edit_seccion">Sección Asignada</label>
                            <select id="edit_seccion" name="seccion">
                                <option value="Sección A">Sección A</option>
                                <option value="Sección B">Sección B</option>
                                <option value="Sección C">Sección C</option>
                            </select>
                        </div>
                        <div class="form-group-modal">
                            <label for="edit_encargado">Nombre del Encargado / Tutor</label>
                            <input type="text" id="edit_encargado" name="encargado">
                        </div>
                    </div>

                    <div class="form-grid-modal">
                        <div class="form-group-modal">
                            <label for="edit_dui_encargado">DUI del Encargado</label>
                            <input type="text" id="edit_dui_encargado" name="dui_encargado" placeholder="00000000-0" maxlength="10">
                        </div>
                        <div class="form-group-modal">
                            <label for="edit_direccion">Dirección Residencial</label>
                            <input type="text" id="edit_direccion" name="direccion">
                        </div>
                    </div>
                </div>

                <!-- 3. CAMPOS ESPECÍFICOS DE DOCENTE -->
                <div id="bloqueCamposDocente" style="display: none;">
                    <div class="section-separator-modal">
                        <i class="fa-solid fa-chalkboard-user"></i> Asignaciones y Registro Docente
                    </div>

                    <div class="form-grid-modal">
                        <div class="form-group-modal">
                            <label for="edit_dui_docente">Número de DUI</label>
                            <input type="text" id="edit_dui_docente" name="dui" placeholder="00000000-0" maxlength="10">
                        </div>
                        <div class="form-group-modal">
                            <label for="edit_escalafon">Número de Escalafón</label>
                            <input type="text" id="edit_escalafon" name="escalafon" placeholder="ESC-00000" maxlength="9">
                        </div>
                    </div>

                    <div class="form-grid-modal">
                        <div class="form-group-modal">
                            <label for="edit_materias">Materias que imparte</label>
                            <input type="text" id="edit_materias" name="materias_imparte" placeholder="Ej: Matemática, Lenguaje">
                        </div>
                        <div class="form-group-modal">
                            <label for="edit_orientacion">Grado de Orientación</label>
                            <select id="edit_orientacion" name="grado_orientacion">
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
                    </div>
                </div>

                <!-- 4. CAMPOS ESPECÍFICOS DE ADMINISTRADOR -->
                <div id="bloqueCamposAdmin" style="display: none;">
                    <div class="section-separator-modal">
                        <i class="fa-solid fa-shield-halved"></i> Datos Administrativos
                    </div>

                    <div class="form-grid-modal">
                        <div class="form-group-modal">
                            <label for="edit_dui_admin">Número de DUI</label>
                            <input type="text" id="edit_dui_admin" name="dui_admin" placeholder="00000000-0" maxlength="10">
                        </div>
                        <div class="form-group-modal">
                            <label for="edit_cargo">Cargo Institucional</label>
                            <select id="edit_cargo" name="cargo">
                                <option value="Directora">Directora</option>
                                <option value="Registro academico">Registro académico</option>
                                <option value="Administrador">Administrador</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 5. SEGURIDAD Y CONTRASEÑA -->
                <div class="section-separator-modal">
                    <i class="fa-solid fa-lock"></i> Credenciales de Acceso
                </div>

                <div class="form-group-modal full">
                    <label for="edit_password">Nueva Contraseña <span style="color:var(--text-muted); font-weight:normal;">(Dejar en blanco para conservar la actual)</span></label>
                    <input type="password" id="edit_password" name="nueva_password" placeholder="••••••••">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModalEditar()">Cancelar</button>
                    <button type="submit" class="btn-modal-save">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let rolSeleccionado = 'todos';

        function filtrarPorRol(rol, btn) {
            rolSeleccionado = rol;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            aplicarFiltros();
        }

        function filtrarTabla() {
            aplicarFiltros();
        }

        function aplicarFiltros() {
            let input = document.getElementById('searchInput');
            let filter = input.value.toLowerCase();
            let table = document.getElementById('tablaPerfiles');
            let tr = table.getElementsByTagName('tr');

            for (let i = 1; i < tr.length; i++) {
                let row = tr[i];
                let rolFila = row.getAttribute('data-rol') || '';
                let coincideRol = (rolSeleccionado === 'todos' || rolFila.includes(rolSeleccionado) || (rolSeleccionado === 'administrador' && rolFila.includes('admin')));

                let tdNombre = row.getElementsByTagName('td')[0];
                let tdCorreo = row.getElementsByTagName('td')[2];
                let txtNombre = tdNombre ? tdNombre.textContent || tdNombre.innerText : '';
                let txtCorreo = tdCorreo ? tdCorreo.textContent || tdCorreo.innerText : '';
                let coincideTexto = (txtNombre.toLowerCase().indexOf(filter) > -1 || txtCorreo.toLowerCase().indexOf(filter) > -1);

                if (coincideRol && coincideTexto) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        }

        function abrirModalEditarConDatos(btn) {
            const d = btn.dataset;

            document.getElementById('edit_id').value = d.id;
            document.getElementById('edit_rol_hidden').value = d.rol;
            document.getElementById('edit_rol_display').value = d.rol;
            document.getElementById('edit_nombre').value = d.nombre;
            document.getElementById('edit_apellido').value = d.apellido;
            document.getElementById('edit_correo').value = d.correo;
            document.getElementById('edit_telefono').value = d.telefono;
            document.getElementById('edit_genero').value = d.genero || 'Masculino';
            document.getElementById('edit_password').value = '';
            document.getElementById('modalSubCorreo').textContent = d.correo || 'Usuario institucional';

            const rolUpper = (d.rol || '').toUpperCase();
            const bEst = document.getElementById('bloqueCamposEstudiante');
            const bDoc = document.getElementById('bloqueCamposDocente');
            const bAdm = document.getElementById('bloqueCamposAdmin');

            bEst.style.display = 'none';
            bDoc.style.display = 'none';
            bAdm.style.display = 'none';

            if (rolUpper === 'ESTUDIANTE') {
                bEst.style.display = 'block';
                document.getElementById('edit_nie').value = d.nie;
                document.getElementById('edit_grado').value = d.grado || '1° Año Bachillerato';
                document.getElementById('edit_seccion').value = d.seccion || 'Sección A';
                document.getElementById('edit_encargado').value = d.encargado;
                document.getElementById('edit_dui_encargado').value = d.duiEncargado;
                document.getElementById('edit_direccion').value = d.direccion;
            } else if (rolUpper === 'DOCENTE') {
                bDoc.style.display = 'block';
                document.getElementById('edit_dui_docente').value = d.dui;
                document.getElementById('edit_escalafon').value = d.escalafon;
                document.getElementById('edit_materias').value = d.materias;
                document.getElementById('edit_orientacion').value = d.orientacion || 'Ninguno';
            } else {
                bAdm.style.display = 'block';
                document.getElementById('edit_dui_admin').value = d.dui;
                document.getElementById('edit_cargo').value = d.cargo || 'Administrador';
            }

            document.getElementById('modalEditar').style.display = 'flex';
        }

        function cerrarModalEditar() {
            document.getElementById('modalEditar').style.display = 'none';
        }
    </script>
</body>
</html>