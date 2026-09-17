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

        /* Estilos del Modal Flotante */
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
            max-width: 560px;
            padding: 2.25rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
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
        .form-group-modal.full {
            grid-column: span 2;
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
            font-size: 0.92rem;
            background: #f8fafc;
            color: var(--text-main);
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
                    <span>¡Operación realizada con éxito en el sistema!</span>
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
                                        <?php else: ?>
                                            <span style="color:var(--text-muted); font-size:0.82rem;">-- Sin asignar --</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($u['telefono'] ?? '--') ?></td>
                                    <td><?= htmlspecialchars($u['genero'] ?? 'Masculino') ?></td>
                                    <td>
                                        <button type="button" class="btn-action-icon btn-edit" title="Editar perfil" onclick="abrirModalEditar(
                                            '<?= $u['id'] ?>',
                                            '<?= htmlspecialchars($u['nombre'] ?? '', ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($u['apellido'] ?? '', ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($u['telefono'] ?? '', ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($u['genero'] ?? 'Masculino', ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars($u['correo'] ?? $u['username'] ?? '', ENT_QUOTES) ?>'
                                        )">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="index.php?action=eliminar_usuario&id=<?= $u['id'] ?>" class="btn-action-icon btn-delete" title="Eliminar registro" onclick="return confirm('¿Estás seguro de eliminar permanentemente este perfil?');">
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

    <!-- MODAL DE EDICIÓN -->
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
                        <label for="edit_telefono">Teléfono (0000-0000)</label>
                        <input type="text" id="edit_telefono" name="telefono" placeholder="7896-5202">
                    </div>
                    <div class="form-group-modal">
                        <label for="edit_genero">Género</label>
                        <select id="edit_genero" name="genero">
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                        </select>
                    </div>
                </div>

                <div class="form-group-modal full">
                    <label for="edit_password">Nueva Contraseña <span style="color:var(--text-muted); font-weight:normal;">(Dejar en blanco para no cambiarla)</span></label>
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
            
            // Actualizar clases activas en los botones
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

        function abrirModalEditar(id, nombre, apellido, telefono, genero, correo) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_apellido').value = apellido;
            document.getElementById('edit_telefono').value = telefono;
            document.getElementById('edit_genero').value = genero || 'Masculino';
            document.getElementById('edit_password').value = '';
            document.getElementById('modalSubCorreo').textContent = correo || 'Usuario institucional';
            
            document.getElementById('modalEditar').style.display = 'flex';
        }

        function cerrarModalEditar() {
            document.getElementById('modalEditar').style.display = 'none';
        }
    </script>
</body>
</html>